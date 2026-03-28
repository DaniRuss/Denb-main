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

                $type = $raw['record_type'] ?? 'awareness_engagement';

                if ($type === 'awareness_engagement') {
                    // ── Awareness Engagement Sync ──
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
                        $results[] = [ 'local_uuid' => $raw['local_uuid'] ?? 'unknown', 'status' => 'error', 'reason' => $validator->errors()->first() ];
                        continue;
                    }

                    if (AwarenessEngagement::where('local_uuid', $raw['local_uuid'])->exists()) {
                        DB::rollBack();
                        $results[] = [ 'local_uuid' => $raw['local_uuid'], 'status' => 'skipped', 'reason' => 'Already synced' ];
                        continue;
                    }

                    // Photo Processing
                    $photoPath = null;
                    if (!empty($raw['violation_photo_path']) && str_starts_with($raw['violation_photo_path'], 'data:image')) {
                        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $raw['violation_photo_path']));
                        $photoPath = 'engagements/photo_' . $raw['local_uuid'] . '.jpg';
                        \Illuminate\Support\Facades\Storage::disk('public')->put($photoPath, $data);
                    }

                    $engagement = AwarenessEngagement::create([
                        'engagement_code'      => 'ENG-' . date('Ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                        'local_uuid'           => $raw['local_uuid'],
                        'campaign_id'          => $raw['campaign_id'],
                        'engagement_type'      => $raw['engagement_type'],
                        'sub_city_id'          => $raw['sub_city_id'],
                        'woreda_id'            => $raw['woreda_id'],
                        'block_number'         => $raw['block_number'] ?? null,
                        'violation_type'       => $raw['violation_type'],
                        'citizen_name'         => $raw['citizen_name'] ?? null,
                        'citizen_gender'       => $raw['citizen_gender'] ?? null,
                        'citizen_age'          => $raw['citizen_age'] ?? null,
                        'headcount'            => $raw['headcount'] ?? null,
                        'stakeholder_partner'  => $raw['stakeholder_partner'] ?? null,
                        'organization_type'    => $raw['organization_type'] ?? null,
                        'org_headcount_male'   => $raw['org_headcount_male'] ?? null,
                        'org_headcount_female' => $raw['org_headcount_female'] ?? null,
                        'session_datetime'     => $raw['session_datetime'],
                        'round_number'         => $raw['round_number'] ?? 1,
                        'created_by'           => $user->id,
                        'status'               => 'draft',
                        'violation_photo_path' => $photoPath,
                        'officer_signature'    => $raw['officer_signature'] ?? null,
                    ]);

                    if (!empty($raw['attendees'])) {
                        foreach ($raw['attendees'] as $att) {
                            EngagementAttendee::create([
                                'engagement_id' => $engagement->id,
                                'name_am'       => $att['name_am'] ?? '',
                                'gender'        => $att['gender'] ?? 'male',
                                'age'           => $att['age'] ?? null,
                            ]);
                        }
                    }

                    $results[] = [ 'local_uuid' => $raw['local_uuid'], 'status' => 'synced', 'server_id' => $engagement->id ];

                } else if ($type === 'volunteer_tip') {
                    // ── Volunteer Tip Sync ──
                    $validator = \Illuminate\Support\Facades\Validator::make($raw, [
                        'local_uuid'         => 'required|uuid',
                        'violation_type'     => 'required|string',
                        'violation_location' => 'required|string',
                        'sub_city_id'        => 'required|integer|exists:sub_cities,id',
                        'woreda_id'          => 'required|integer|exists:woredas,id',
                        'violation_date'     => 'required|date',
                        'reported_date'      => 'required|date',
                    ]);

                    if ($validator->fails()) {
                        DB::rollBack();
                        $results[] = [ 'local_uuid' => $raw['local_uuid'] ?? 'unknown', 'status' => 'error', 'reason' => $validator->errors()->first() ];
                        continue;
                    }

                    // Photo Processing (Evidence)
                    $evidencePath = null;
                    if (!empty($raw['evidence_photo']) && str_starts_with($raw['evidence_photo'], 'data:image')) {
                        $pData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $raw['evidence_photo']));
                        $evidencePath = 'tips/evidence_' . $raw['local_uuid'] . '.jpg';
                        \Illuminate\Support\Facades\Storage::disk('public')->put($evidencePath, $pData);
                    }

                    // Signature Processing
                    $sigPath = null;
                    if (!empty($raw['volunteer_signature_path']) && str_starts_with($raw['volunteer_signature_path'], 'data:image')) {
                        $sData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $raw['volunteer_signature_path']));
                        $sigPath = 'tips/sig_' . $raw['local_uuid'] . '.png';
                        \Illuminate\Support\Facades\Storage::disk('public')->put($sigPath, $sData);
                    }

                    $tip = \App\Models\VolunteerTip::create([
                        'tip_code'                 => 'TIP-' . date('Ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                        'suspect_name'             => $raw['suspect_name'] ?? null,
                        'violation_type'           => $raw['violation_type'],
                        'violation_location'       => $raw['violation_location'],
                        'sub_city_id'              => $raw['sub_city_id'],
                        'woreda_id'                => $raw['woreda_id'],
                        'block_number'             => $raw['block_number'] ?? null,
                        'violation_date'           => $raw['violation_date'],
                        'reported_date'            => $raw['reported_date'],
                        'volunteer_name'           => $raw['volunteer_name'] ?? null,
                        'is_anonymous'             => $raw['is_anonymous'] ?? false,
                        'volunteer_signature_path' => $sigPath,
                        'evidence_photo'           => $evidencePath,
                        'received_by'              => $user->id,
                        'status'                   => 'pending',
                    ]);

                    $results[] = [ 'local_uuid' => $raw['local_uuid'], 'status' => 'synced', 'server_id' => $tip->id ];
                }

                DB::commit();

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Offline sync failed: ' . $e->getMessage());
                $results[] = [ 'local_uuid' => $raw['local_uuid'] ?? 'unknown', 'status' => 'error', 'reason' => 'Server Error' ];
            }
        }

        return response()->json([
            'synced_at' => now()->toIso8601String(),
            'results'   => $results,
        ]);
    }
}
