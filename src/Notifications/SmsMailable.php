<?php

namespace Propaganistas\LaravelSms\Notifications;

use Illuminate\Mail\Mailable;
use Propaganistas\LaravelSms\SmsMessage;

class SmsMailable extends Mailable
{
    public function __construct(
        public string $recipient,
        public SmsMessage $message
    ) {
    }

    public function build()
    {
        return $this
            ->subject('[SMS] '.$this->recipient)
            ->html((string) $this->message);
    }
}
