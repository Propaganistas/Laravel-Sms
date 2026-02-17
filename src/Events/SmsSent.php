<?php

namespace Propaganistas\LaravelSms\Events;

class SmsSent
{
    public function __construct(
        public $recipient,
        public string $message,
        public int $amount,
        public float $cost
    ) {}
}
