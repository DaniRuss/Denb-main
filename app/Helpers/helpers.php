<?php
// app/Helpers/helpers.php

if (!function_exists('generateTicketNumber')) {
    function generateTicketNumber($prefix = 'CMP')
    {
        return $prefix . '-' . date('Ymd') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('formatEthiopianDate')) {
    function formatEthiopianDate($date, $format = 'Y-m-d')
    {
        if (!$date)
            return null;
        // Convert Gregorian to Ethiopian calendar
        // This is a placeholder - implement actual conversion
        return $date->format($format);
    }
}

if (!function_exists('getStatusBadgeClass')) {
    function getStatusBadgeClass($status)
    {
        return match ($status) {
            'pending' => 'warning',
            'under_review' => 'info',
            'assigned' => 'primary',
            'investigating' => 'info',
            'resolved' => 'success',
            'closed' => 'secondary',
            'reopened' => 'danger',
            'verified' => 'success',
            'action_taken' => 'success',
            default => 'secondary'
        };
    }
}

if (!function_exists('getPriorityBadgeClass')) {
    function getPriorityBadgeClass($priority)
    {
        return match ($priority) {
            'low' => 'secondary',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
            'immediate' => 'danger',
            default => 'secondary'
        };
    }
}
