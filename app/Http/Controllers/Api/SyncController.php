<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AwarenessEngagement;
use App\Models\EngagementAttendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SyncController — receives batched offline records from the paramilitary officer's device.
 *
 * POST /api/offline/sync
 * Body: { records: [ { local_uuid, engagement_type, ..., attendees: [] } ] }
 */
class SyncController extends Controller
{
    public function sync(Request $request)
    {
        $user = $request->user();
        $results = [];

        if (!$request->has('records') || !is_array($request->records)) {
            return response()->json(['error' => 'No records provided.'], 400);
        }

        foreach ($request->records as $raw) {
            try {
                DB::beginTransaction();

                // Per-record validation allows partial batch success
                $validator = \Illuminate\Support\Facades\Validator::make($raw, [
                    'local_uuid'       => 'required|uuid',
                    'campaign_id'      => 'required|integer|exists:campaigns,id',
                    'engagement_type'  => 'required|string',
                    'sub_city_id'      => 'required|integer|exists:sub_cities,id',
                    'woreda_id'        => 'required|integer|exists:woredas,id',
                    'violation_type'   => 'required|string',
                    'session_datetime' => 'required|date',
                ]);

                if ($validator->fails()) {
                    DB::rollBack();
                    $results[] = [
                        'local_uuid' => $raw['local_uuid'] ?? 'unknown',
                        'status'     => 'error',
                        'reason'     => $validator->errors()->first(),
                    ];
                    continue;
                }

                $existing = AwarenessEngagement::where('local_uuid', $raw['local_uuid'])->first();
                if ($existing) {
                    DB::rollBack();
                    $results[] = [
                        'local_uuid' => $raw['local_uuid'],
                        'status'     => 'skipped',
                        'server_id'  => $existing->id,
                        'reason'     => 'Already synced',
                    ];
                    continue;
                }

                // Handle Base64 Image Processing
                $photoPath = null;
                if (!empty($raw['violation_photo_path']) && preg_match('/^data:image\/(\w+);base64,/', $raw['violation_photo_path'])) {
                    $data = substr($raw['violation_photo_path'], strpos($raw['violation_photo_path'], ',') + 1);
                    $data = base64_decode($data);
                    $fileName = 'engagements/photo_' . $raw['local_uuid'] . '.jpg';
                    \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $data);
                    $photoPath = $fileName;
                }

                $engagementCode = 'ENG-' . date('Ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

                $engagement = AwarenessEngagement::create([
                    'engagement_code'      => $engagementCode,
                    'local_uuid'           => $raw['local_uuid'],
                    'campaign_id'          => $raw['campaign_id'],
                    'engagement_type'      => $raw['engagement_type'],
                    'sub_city_id'          => $raw['sub_city_id'],
                    'woreda_id'            => $raw['woreda_id'],
                    'block_number'         => $raw['block_number'] ?? null,
                    'violation_type'       => $raw['violation_type'],
                    'round_number'         => $raw['round_number'] ?? 1,
                    'citizen_name'         => $raw['citizen_name'] ?? null,
                    'citizen_gender'       => $raw['citizen_gender'] ?? null,
                    'citizen_age'          => $raw['citizen_age'] ?? null,
                    'headcount'            => $raw['headcount'] ?? null,
                    'stakeholder_partner'  => $raw['stakeholder_partner'] ?? null,
                    'organization_type'    => $raw['organization_type'] ?? null,
                    'org_headcount_male'   => $raw['org_headcount_male'] ?? null,
                    'org_headcount_female' => $raw['org_headcount_female'] ?? null,
                    'session_datetime'     => $raw['session_datetime'],
                    'created_by'           => $user->id,
                    'status'               => 'draft',
                    'is_offline_draft'     => true,
                    'created_at_mobile'    => $raw['created_at_mobile'] ?? now(),
                    'synced_at'            => now(),
                    'violation_photo_path' => $photoPath,
                    'officer_signature'    => $raw['officer_signature'] ?? null,
                ]);

                if (!empty($raw['attendees']) && is_array($raw['attendees'])) {
                    foreach ($raw['attendees'] as $att) {
                        EngagementAttendee::create([
                            'engagement_id' => $engagement->id,
                            'name_am'       => $att['name_am'] ?? '',
                            'gender'        => $att['gender'] ?? 'male',
                            'age'           => $att['age'] ?? null,
                        ]);
                    }
                }

                DB::commit();
                $results[] = [
                    'local_uuid' => $raw['local_uuid'],
                    'status'     => 'synced',
                    'server_id'  => $engagement->id,
                ];

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Offline sync record failed: ' . $e->getMessage());
                $results[] = [
                    'local_uuid' => $raw['local_uuid'] ?? 'unknown',
                    'status'     => 'error',
                    'reason'     => 'Server Error: ' . $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'synced_at' => now()->toIso8601String(),
            'results'   => $results,
        ]);
    }
}
