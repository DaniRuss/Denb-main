<?php

namespace App\Services;

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
            'location' => $data['sub_city'] . ', Woreda ' . $data['woreda'],
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

    public function reviewByDirector(Tip $tip, string $decision, ?string $comment = null): Tip
    {
        $this->ensureCallTip($tip);
        $this->ensureStatus($tip, [Tip::STATUS_PENDING_DIRECTOR_REVIEW]);

        $approved = $decision === 'approve';

        $tip->update([
            'status' => $approved ? Tip::STATUS_DISPATCHED : Tip::STATUS_REJECTED,
            'director_comment' => $comment,
            'director_reviewed_at' => now(),
            'dispatched_at' => $approved ? now() : null,
            'investigation_status' => $approved ? Tip::STATUS_DISPATCHED : $tip->investigation_status,
        ]);

        return $tip->refresh();
    }

    public function updateInvestigation(Tip $tip, array $data): Tip
    {
        $this->ensureCallTip($tip);
        $this->ensureStatus($tip, [Tip::STATUS_DISPATCHED, Tip::STATUS_UNDER_INVESTIGATION, Tip::STATUS_CLOSED, Tip::STATUS_ESCALATED_TO_SUB_CITY]);

        $investigationStatus = $data['investigation_status'];
        $status = match ($investigationStatus) {
            Tip::STATUS_CLOSED => Tip::STATUS_CLOSED,
            Tip::STATUS_ESCALATED_TO_SUB_CITY => Tip::STATUS_ESCALATED_TO_SUB_CITY,
            default => Tip::STATUS_UNDER_INVESTIGATION,
        };

        $tip->update([
            'status' => $status,
            'investigation_status' => $investigationStatus,
            'sub_city_notes' => $data['sub_city_notes'] ?? null,
            'closed_at' => $status === Tip::STATUS_CLOSED ? now() : null,
        ]);

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
