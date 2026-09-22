<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use App\Services\TipWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallTipWorkflowController extends Controller
{
    public function __construct(private readonly TipWorkflowService $workflowService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('create_call_tips') || $request->user()?->hasRole('admin'), 403);

        $data = $request->validate([
            'description' => ['required', 'string', 'max:5000'],
            'caller_name' => ['nullable', 'string', 'max:255'],
            'caller_phone' => ['nullable', 'string', 'max:30'],
            'tip_type' => ['required', 'string', 'in:' . implode(',', array_keys(Tip::getTipTypeOptions()))],
            'tip_type_other' => ['required_if:tip_type,other', 'nullable', 'string', 'max:255'],
            'urgency_level' => ['required', 'string', 'in:' . implode(',', array_keys(Tip::getUrgencyOptions()))],
            'suspect_name' => ['nullable', 'string', 'max:255'],
            'suspect_description' => ['nullable', 'string', 'max:1000'],
            'evidence_description' => ['nullable', 'string', 'max:1000'],
            'sub_city' => ['required', 'string', 'in:' . implode(',', array_keys(Tip::getAddisAbabaSubCities()))],
            'woreda' => ['required', 'string', 'in:' . implode(',', array_keys(Tip::getWoredaOptions()))],
        ]);

        $tip = $this->workflowService->submitCallTip($data, $request->user());

        return response()->json([
            'message' => 'Tip recorded and sent to supervisor review.',
            'data' => $tip,
        ], 201);
    }

    public function supervisorReview(Request $request, Tip $tip): JsonResponse
    {
        abort_unless($request->user()?->can('review_supervisor_call_tips') || $request->user()?->hasRole('admin'), 403);

        $data = $request->validate([
            'decision' => ['required', 'string', 'in:approve,reject'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $tip = $this->workflowService->reviewBySupervisor($tip, $data['decision'], $data['comment'] ?? null);

        return response()->json([
            'message' => 'Supervisor review recorded.',
            'data' => $tip,
        ]);
    }

    public function directorReview(Request $request, Tip $tip): JsonResponse
    {
        abort_unless($request->user()?->can('review_director_call_tips') || $request->user()?->hasRole('admin'), 403);

        $data = $request->validate([
            'decision' => ['required', 'string', 'in:approve,reject,investigate'],
            'dispatch_to' => ['nullable', 'string', 'in:sub_city,woreda'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $tip = $data['decision'] === 'investigate'
            ? $this->workflowService->investigateByDirector($tip, $data['comment'] ?? null)
            : $this->workflowService->reviewByDirector($tip, $data['decision'], $data['comment'] ?? null, $data['dispatch_to'] ?? null);

        return response()->json([
            'message' => 'Director review recorded.',
            'data' => $tip,
        ]);
    }

    public function updateInvestigation(Request $request, Tip $tip): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user?->hasRole('admin') ||
            ($user?->can('review_director_call_tips') && $tip->status === Tip::STATUS_UNDER_INVESTIGATION && $tip->dispatch_to === 'head_office') ||
            ($user?->can('manage_sub_city_call_tips') && filled($user->sub_city) && $user->sub_city === $tip->sub_city) ||
            ($user?->can('manage_woreda_call_tips') && filled($user->sub_city) && filled($user->woreda) && $user->sub_city === $tip->sub_city && $user->woreda === $tip->woreda),
            403
        );

        $data = $request->validate([
            'investigation_status' => [
                'required',
                'string',
                'in:' . implode(',', [
                    Tip::STATUS_UNDER_INVESTIGATION,
                    Tip::STATUS_DISPATCHED,
                    Tip::STATUS_CLOSED,
                    Tip::STATUS_ESCALATED_TO_SUB_CITY,
                    Tip::STATUS_ESCALATED_TO_HEAD_OFFICE,
                ]),
            ],
            'dispatch_to' => ['nullable', 'string', 'in:woreda,head_office'],
            'sub_city_notes' => ['nullable', 'string', 'max:4000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt'],
        ]);

        if (
            $data['investigation_status'] === Tip::STATUS_DISPATCHED &&
            $user?->can('manage_sub_city_call_tips') &&
            filled($user->sub_city) &&
            $user->sub_city === $tip->sub_city
        ) {
            $data['dispatch_to'] = 'woreda';
        }

        if ($request->hasFile('attachments')) {
            $data['attachments'] = collect($request->file('attachments'))
                ->map(fn ($file) => $file->store('case-updates/' . now()->format('Y/m/d'), 'public'))
                ->all();
        }

        $tip = $this->workflowService->updateInvestigation($tip, $data, $user);

        return response()->json([
            'message' => 'Sub-city investigation status updated.',
            'data' => $tip,
        ]);
    }
}
