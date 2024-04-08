<?php

namespace Propaganistas\LaravelSms\Events;

use Propaganistas\LaravelSms\SmsMessage;

class SmsSent
{
    public function __construct(
        public $recipient,
        public SmsMessage $message
    ) {
    }
}
