<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Sms Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send any sms
    | messages sent by your application. Alternative mailers may be setup
    | and used as needed; however, this mailer will be used by default.
    |
    */

    'default' => env('SMS_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Supported: "messagebird", "mail", "log", "array"
    |
    */

    'mailers' => [
        'messagebird' => [
            'driver' => 'messagebird',
            'access_key' => env('MESSAGEBIRD_ACCESS_KEY'),
            'originator' => env('MESSAGEBIRD_ORIGINATOR'),
            'unit_price' => env('MESSAGEBIRD_UNIT_PRICE', 0),
        ],

        'mail' => [
            'driver' => 'mail',
            'mailer' => env('SMS_MAIL_MAILER'),
        ],

        'log' => [
            'driver' => 'log',
            'channel' => env('SMS_LOG_CHANNEL'),
            'level' => env('SMS_LOG_LEVEL', 'debug'),
        ],

        'array' => [
            'driver' => 'array',
        ],
    ],

];
