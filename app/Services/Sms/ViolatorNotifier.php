<?php

namespace App\Services\Sms;

use App\Models\ConfiscatedAsset;
use App\Models\PenaltyReceipt;
use App\Models\SmsMessage;
use App\Models\ViolationRecord;
use App\Models\WarningLetter;
use Illuminate\Support\Facades\App;

class ViolatorNotifier
{
    public function __construct(protected SmsManager $sms)
    {
    }

    public function penaltyReceiptIssued(PenaltyReceipt $receipt): ?SmsMessage
    {
        $record = $receipt->violationRecord;
        $violator = $record?->violator;

        if (! $violator) {
            return null;
        }

        $message = $this->render('penalty_receipt', [
            'name'         => $this->violatorName($violator),
            'receipt'      => $receipt->receipt_number,
            'issued_date'  => $receipt->issued_date?->format('Y-m-d'),
            'amount'       => number_format((float) $receipt->fine_amount, 2),
            'woreda'       => $record?->woreda?->name_am ?? $record?->woreda?->name_en ?? '—',
            'deadline'     => $receipt->payment_deadline?->format('Y-m-d'),
        ]);

        return $this->sms->send($violator->phone, $message, [
            'template_key'    => 'penalty_receipt',
            'notifiable_type' => PenaltyReceipt::class,
            'notifiable_id'   => $receipt->id,
            'violator_id'     => $violator->id,
        ]);
    }

    public function warningIssued(WarningLetter $letter): ?SmsMessage
    {
        $record = $letter->violationRecord;
        $violator = $record?->violator;

        if (! $violator) {
            return null;
        }

        $templateKey = $letter->warning_type === 'twenty_four_hour' ? 'warning_24h' : 'warning_3d';

        $message = $this->render($templateKey, [
            'name'       => $this->violatorName($violator),
            'regulation' => trim(($letter->regulation_number ?? $record->regulation_number ?? '') . ' ' .
                                 ($letter->article ? 'አንቀጽ ' . $letter->article : '') . ' ' .
                                 ($letter->sub_article ? 'ንዑስ አንቀጽ ' . $letter->sub_article : '')) ?: '—',
            'deadline'   => $letter->deadline?->format('Y-m-d H:i'),
        ]);

        return $this->sms->send($violator->phone, $message, [
            'template_key'    => $templateKey,
            'notifiable_type' => WarningLetter::class,
            'notifiable_id'   => $letter->id,
            'violator_id'     => $violator->id,
        ]);
    }

    public function paymentOverdue(PenaltyReceipt $receipt): ?SmsMessage
    {
        $violator = $receipt->violationRecord?->violator;
        if (! $violator) {
            return null;
        }

        $message = $this->render('payment_overdue', [
            'name'    => $this->violatorName($violator),
            'receipt' => $receipt->receipt_number,
            'amount'  => number_format((float) $receipt->fine_amount, 2),
        ]);

        return $this->sms->send($violator->phone, $message, [
            'template_key'    => 'payment_overdue',
            'notifiable_type' => PenaltyReceipt::class,
            'notifiable_id'   => $receipt->id,
            'violator_id'     => $violator->id,
        ]);
    }

    public function courtFiled(PenaltyReceipt $receipt): ?SmsMessage
    {
        $violator = $receipt->violationRecord?->violator;
        if (! $violator) {
            return null;
        }

        $message = $this->render('court_filed', [
            'name'    => $this->violatorName($violator),
            'receipt' => $receipt->receipt_number,
            'amount'  => number_format((float) ($receipt->court_fine_amount ?? ($receipt->fine_amount * 2)), 2),
        ]);

        return $this->sms->send($violator->phone, $message, [
            'template_key'    => 'court_filed',
            'notifiable_type' => PenaltyReceipt::class,
            'notifiable_id'   => $receipt->id,
            'violator_id'     => $violator->id,
        ]);
    }

    public function complianceThanks(ViolationRecord $record): ?SmsMessage
    {
        $violator = $record->violator;
        if (! $violator) {
            return null;
        }

        $message = $this->render('compliance_thanks', [
            'name' => $this->violatorName($violator),
        ]);

        return $this->sms->send($violator->phone, $message, [
            'template_key'    => 'compliance_thanks',
            'notifiable_type' => ViolationRecord::class,
            'notifiable_id'   => $record->id,
            'violator_id'     => $violator->id,
        ]);
    }

    protected function violatorName($violator): string
    {
        return $violator->full_name_am ?: ($violator->full_name_en ?: '—');
    }

    protected function render(string $key, array $tokens): string
    {
        $locale = App::getLocale() === 'en' ? 'en' : 'am';

        $template = __('sms.' . $key, [], $locale);

        foreach ($tokens as $token => $value) {
            $template = str_replace(':' . $token, (string) ($value ?? '—'), $template);
        }

        return $template;
    }
}
