<?php
// app/Http/Controllers/Public/TipController.php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TipController extends Controller
{
    /**
     * Show the tip submission form
     */
    public function create()
    {
        return view('portal.tip.create');
    }

    /**
     * Store a new tip
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reporter_name' => 'nullable|string|max:255',
            'reporter_email' => 'nullable|email|max:255',
            'reporter_phone' => 'nullable|string|max:20',
            'is_anonymous' => 'sometimes|boolean',
            'tip_type' => 'required|string|in:illegal_trade,alcohol_sales,land_grabbing,drug_activity,counterfeit_goods,illegal_construction,environmental_violation,other',
            'tip_type_other' => 'required_if:tip_type,other|nullable|string|max:255',
            'location' => 'required|string|max:255',
            'sub_city' => 'nullable|string|max:100',
            'woreda' => 'nullable|string|max:100',
            'specific_address' => 'nullable|string|max:255',
            'description' => 'required|string|max:5000',
            'suspect_name' => 'nullable|string|max:255',
            'suspect_description' => 'nullable|string|max:1000',
            'suspect_vehicle' => 'nullable|string|max:255',
            'suspect_company' => 'nullable|string|max:255',
            'has_evidence' => 'sometimes|boolean',
            'evidence_description' => 'nullable|string|max:1000',
            'evidence_files.*' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,mp4,mov,pdf,doc,docx,xls,xlsx,txt',
            'urgency_level' => 'required|string|in:low,medium,high,immediate',
            'is_ongoing' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Handle anonymous flag
        if ($request->has('is_anonymous') && $request->is_anonymous) {
            $data['reporter_name'] = null;
            $data['reporter_email'] = null;
            $data['reporter_phone'] = null;
            $data['is_anonymous'] = true;
        } else {
            $data['is_anonymous'] = false;
        }

        // Handle evidence files
        if ($request->hasFile('evidence_files')) {
            $files = [];
            foreach ($request->file('evidence_files') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs(
                    'tips/' . date('Y/m/d'),
                    $filename,
                    'public'
                );
                $files[] = $path;
            }
            $data['evidence_files'] = $files;
        }

        // Set boolean flags
        $data['has_evidence'] = $request->hasFile('evidence_files') || !empty($request->evidence_description);
        $data['is_ongoing'] = $request->has('is_ongoing');

        // Create tip
        $tip = Tip::create($data);

        // Generate tracking URL for anonymous tips
        $trackingUrl = null;
        if ($tip->is_anonymous && $tip->access_token) {
            $trackingUrl = route('tip.track.anonymous', ['token' => $tip->access_token]);
        }

        // Send confirmation if email provided
        if ($tip->reporter_email) {
            try {
                Mail::send(
                    'emails.tip-confirmation',
                    ['tip' => $tip, 'tracking_url' => $trackingUrl],
                    function ($message) use ($tip) {
                        $message->to($tip->reporter_email)
                            ->subject('የምክር ማረጋገጫ - ቁጥር: ' . $tip->tip_number);
                    }
                );

                Log::info('Tip confirmation email sent to: ' . $tip->reporter_email);
            } catch (\Exception $e) {
                Log::error('Email sending failed: ' . $e->getMessage());
            }
        }

        return view('portal.tip.submit-success', compact('tip', 'trackingUrl'));
    }

    /**
     * Track anonymous tip using token
     */
    public function trackAnonymous($token)
    {
        $tip = Tip::where('access_token', $token)->first();

        if (!$tip) {
            return redirect()->route('home')
                ->with('error', 'ልክ ያልሆነ የመከታተያ አገናኝ');
        }

        // Update last accessed
        $tip->update(['last_accessed' => now()]);

        return view('portal.tip.status', compact('tip'));
    }
}
