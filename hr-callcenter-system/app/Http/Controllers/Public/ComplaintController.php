<?php
// app/Http/Controllers/Public/ComplaintController.php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\CaseUpdate as ComplaintUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ComplaintController extends Controller
{
    /**
     * Show the complaint submission form
     */
    public function create()
    {
        return view('portal.complaint.create');
    }

    /**
     * Store a new complaint
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'id_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'complaint_type' => 'required|string|in:illegal_trade,corruption,misconduct,property_dispute,harassment,fraud,environmental,other',
            'complaint_type_other' => 'required_if:complaint_type,other|nullable|string|max:255',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_location' => 'required|string|max:255',
            'officer_name' => 'nullable|string|max:255',
            'officer_badge' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'evidence_description' => 'nullable|string|max:1000',
            'priority' => 'required|string|in:low,medium,high,critical',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt',
            'confiscated_items' => 'nullable|string|max:500',
            'confiscated_value' => 'nullable|numeric|min:0',
            'confiscation_reason' => 'nullable|string|max:255',
            'confiscation_location' => 'nullable|string|max:255',
            'is_anonymous' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                // Generate a unique filename
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // Store the file
                $path = $file->storeAs(
                    'complaints/' . date('Y/m/d'),
                    $filename,
                    'public'
                );

                $attachments[] = $path;
            }
            $data['attachments'] = $attachments;
        }

        // Set anonymous flag
        $data['is_anonymous'] = $request->has('is_anonymous');

        // Create complaint
        $complaint = Complaint::create($data);

        // Send confirmation email
        try {
            Mail::send(
                'emails.complaint-confirmation',
                ['complaint' => $complaint],
                function ($message) use ($complaint) {
                    $message->to($complaint->email)
                        ->subject('የቅሬታ ማረጋገጫ - ቲኬት ቁጥር: ' . $complaint->ticket_number);
                }
            );

            Log::info('Confirmation email sent to: ' . $complaint->email);
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
        }

        return redirect()->route('complaint.status', ['ticket' => $complaint->ticket_number])
            ->with('success', 'ቅሬታዎ በተሳካ ሁኔታ ቀርቧል። የቲኬት ቁጥርዎ: ' . $complaint->ticket_number);
    }

    /**
     * Show the complaint tracking form
     */
    public function track(Request $request)
    {
        $ticket = $request->get('ticket');

        if ($ticket) {
            return redirect()->route('complaint.status', ['ticket' => $ticket]);
        }

        return view('portal.complaint.track');
    }

    /**
     * Show complaint status by ticket number
     */
    public function showStatus($ticket)
    {
        $complaint = Complaint::where('ticket_number', $ticket)->first();

        if (!$complaint) {
            return redirect()->route('complaint.track')
                ->with('error', 'ቲኬት ቁጥሩ አልተገኘም። እባክዎ ዳግም ይሞክሩ።');
        }

        // Increment view count
        $complaint->increment('view_count');
        $complaint->update(['last_viewed_by_complainant' => now()]);

        // Get public updates
        // Using morphTo structure for updates via caseable
        $updates = $complaint->updates()
            ->where('is_public', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('portal.complaint.status', compact('complaint', 'updates'));
    }

    /**
     * Check complaint status via AJAX
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'ticket_number' => 'required|string',
        ]);

        $complaint = Complaint::where('ticket_number', $request->ticket_number)->first();

        if (!$complaint) {
            return response()->json([
                'found' => false,
                'message' => 'ቲኬት ቁጥሩ አልተገኘም'
            ]);
        }

        // Increment view count for AJAX checks too
        $complaint->increment('view_count');
        $complaint->update(['last_viewed_by_complainant' => now()]);

        return response()->json([
            'found' => true,
            'ticket_number' => $complaint->ticket_number,
            'full_name' => $complaint->full_name,
            'status' => $complaint->status_name,
            'status_code' => $complaint->status,
            'priority' => $complaint->priority_name,
            'created_at' => $complaint->created_at->format('Y-m-d H:i'),
            'last_update' => $complaint->updated_at->diffForHumans(),
            'resolution' => $complaint->resolution,
            'resolved_at' => $complaint->resolved_at ? $complaint->resolved_at->format('Y-m-d H:i') : null,
            'status_color' => match ($complaint->status) {
                'pending' => 'warning',
                'under_review' => 'info',
                'assigned' => 'primary',
                'investigating' => 'info',
                'resolved' => 'success',
                'closed' => 'secondary',
                'reopened' => 'danger',
                default => 'secondary'
            },
        ]);
    }
}
