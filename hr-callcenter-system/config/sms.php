<?php

return [

    'default' => env('SMS_DRIVER', 'log'),

    'from' => env('SMS_FROM', 'DenbMaskeber'),

    'dry_run' => env('SMS_DRY_RUN', false),

    'drivers' => [

        'log' => [
            'channel' => env('SMS_LOG_CHANNEL', 'stack'),
        ],

        'afromessage' => [
            'base_url' => env('AFROMESSAGE_BASE_URL', 'https://api.afromessage.com/api/send'),
            'token'    => env('AFROMESSAGE_TOKEN'),
            'sender'   => env('AFROMESSAGE_SENDER_ID'),
            'identifier_id' => env('AFROMESSAGE_IDENTIFIER_ID'),
            'callback' => env('AFROMESSAGE_CALLBACK'),
            'timeout'  => (int) env('AFROMESSAGE_TIMEOUT', 15),
        ],

    ],

    'country_code' => env('SMS_COUNTRY_CODE', '251'),

    'templates' => [
        'penalty_receipt'    => 'sms.penalty_receipt',
        'warning_24h'        => 'sms.warning_24h',
        'warning_3d'         => 'sms.warning_3d',
        'payment_overdue'    => 'sms.payment_overdue',
        'court_filed'        => 'sms.court_filed',
        'compliance_thanks'  => 'sms.compliance_thanks',
    ],

];
