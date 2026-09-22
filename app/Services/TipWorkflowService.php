<?php

namespace App\Services;

use App\Models\CaseUpdate;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class TipWorkflowService
{
    public function submitCallTip(array $data, User $user): Tip
    {
        $hasEvidence = filled($data['evidence_description'] ?? null);

        return Tip::create([
            'description' => $data['description'],
            'caller_name' => $data['caller_name'] ?? null,
            'caller_phone' => $data['caller_phone'] ?? null,
            'sub_city' => $data['sub_city'],
            'woreda' => $data['woreda'],
            'unique_place' => $data['unique_place'] ?? null,
            'location' => $data['sub_city'] . ', Woreda ' . $data['woreda'] . ($data['unique_place'] ? ' (' . $data['unique_place'] . ')' : ''),
            'tip_type' => $data['tip_type'],
            'tip_type_other' => $data['tip_type_other'] ?? null,
            'urgency_level' => $data['urgency_level'],
            'suspect_name' => $data['suspect_name'] ?? null,
            'suspect_description' => $data['suspect_description'] ?? null,
            'evidence_description' => $data['evidence_description'] ?? null,
            'is_anonymous' => false,
            'has_evidence' => $hasEvidence,
            'is_ongoing' => false,
            'status' => Tip::STATUS_PENDING_SUPERVISOR_REVIEW,
            'tip_source' => Tip::SOURCE_CALL_CENTER,
            'created_by' => $user->id,
            'reporter_name' => $data['caller_name'] ?? null,
            'reporter_phone' => $data['caller_phone'] ?? null,
        ]);
    }

    public function reviewBySupervisor(Tip $tip, string $decision, ?string $comment = null): Tip
    {
        $this->ensureCallTip($tip);
        $this->ensureStatus($tip, [Tip::STATUS_PENDING_SUPERVISOR_REVIEW]);

        $tip->update([
            'status' => $decision === 'approve' ? Tip::STATUS_PENDING_DIRECTOR_REVIEW : Tip::STATUS_REJECTED,
            'supervisor_comment' => $comment,
            'supervisor_reviewed_at' => now(),
        ]);

        return $tip->refresh();
    }

    public function reviewByDirector(Tip $tip, string $decision, ?string $comment = null, ?string $dispatchTo = null): Tip
    {
        $this->ensureCallTip($tip);
        $this->ensureStatus($tip, [Tip::STATUS_PENDING_DIRECTOR_REVIEW, Tip::STATUS_ESCALATED_TO_HEAD_OFFICE]);

        $approved = $decision === 'approve';

        $tip->update([
            'status' => $approved ? Tip::STATUS_DISPATCHED : Tip::STATUS_REJECTED,
            'dispatch_to' => $approved ? ($dispatchTo ?? 'sub_city') : null,
            'director_comment' => $comment,
            'director_reviewed_at' => now(),
            'dispatched_at' => $approved ? now() : null,
            'investigation_status' => $approved ? Tip::STATUS_DISPATCHED : $tip->investigation_status,
        ]);

        return $tip->refresh();
    }

    public function investigateByDirector(Tip $tip, ?string $comment = null): Tip
    {
        $this->ensureCallTip($tip);
        $this->ensureStatus($tip, [Tip::STATUS_PENDING_DIRECTOR_REVIEW, Tip::STATUS_ESCALATED_TO_HEAD_OFFICE]);

        $tip->update([
            'status' => Tip::STATUS_UNDER_INVESTIGATION,
            'dispatch_to' => 'head_office',
            'director_comment' => $comment,
            'director_reviewed_at' => now(),
            'investigation_status' => Tip::STATUS_UNDER_INVESTIGATION,
        ]);

        return $tip->refresh();
    }

    public function updateInvestigation(Tip $tip, array $data, ?User $user = null): Tip
    {
        $this->ensureCallTip($tip);
        $this->ensureStatus($tip, [
            Tip::STATUS_DISPATCHED,
            Tip::STATUS_UNDER_INVESTIGATION,
            Tip::STATUS_CLOSED,
            Tip::STATUS_ESCALATED_TO_SUB_CITY,
            Tip::STATUS_ESCALATED_TO_HEAD_OFFICE,
        ]);

        $investigationStatus = $data['investigation_status'];
        $status = match ($investigationStatus) {
            Tip::STATUS_DISPATCHED => Tip::STATUS_DISPATCHED,
            Tip::STATUS_CLOSED => Tip::STATUS_CLOSED,
            Tip::STATUS_ESCALATED_TO_SUB_CITY => Tip::STATUS_ESCALATED_TO_SUB_CITY,
            Tip::STATUS_ESCALATED_TO_HEAD_OFFICE => Tip::STATUS_ESCALATED_TO_HEAD_OFFICE,
            default => Tip::STATUS_UNDER_INVESTIGATION,
        };

        if ($status === Tip::STATUS_DISPATCHED && ($data['dispatch_to'] ?? null) === 'woreda' && blank($tip->woreda)) {
            throw ValidationException::withMessages([
                'woreda' => 'This case must have a recorded woreda before it can be sent to a woreda office.',
            ]);
        }

        $tip->update([
            'status' => $status,
            'investigation_status' => $investigationStatus,
            'dispatch_to' => match ($status) {
                Tip::STATUS_DISPATCHED => $data['dispatch_to'] ?? $tip->dispatch_to,
                Tip::STATUS_ESCALATED_TO_HEAD_OFFICE => 'head_office',
                default => $tip->dispatch_to,
            },
            'sub_city_notes' => $data['sub_city_notes'] ?? null,
            'closed_at' => $status === Tip::STATUS_CLOSED ? now() : null,
        ]);

        if ($user) {
            $attachments = array_values(array_filter((array) ($data['attachments'] ?? [])));
            $message = filled($data['sub_city_notes'] ?? null)
                ? $data['sub_city_notes']
                : 'Investigation status updated to ' . (Tip::getStatusLabels()[$investigationStatus] ?? str($investigationStatus)->replace('_', ' ')->title()->toString()) . '.';

            CaseUpdate::create([
                'caseable_id' => $tip->id,
                'caseable_type' => $tip->getMorphClass(),
                'user_id' => $user->id,
                'update_type' => 'investigation_note',
                'message' => $message,
                'attachments' => $attachments ?: null,
                'is_public' => false,
                'notify_complainant' => false,
            ]);
        }

        return $tip->refresh();
    }

    private function ensureCallTip(Tip $tip): void
    {
        if ($tip->tip_source !== Tip::SOURCE_CALL_CENTER) {
            throw ValidationException::withMessages([
                'tip' => 'This workflow is only valid for call-center tips.',
            ]);
        }
    }

    private function ensureStatus(Tip $tip, array $allowedStatuses): void
    {
        if (! in_array($tip->status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'The tip cannot be transitioned from its current status.',
            ]);
        }
    }
}
