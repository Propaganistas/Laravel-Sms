<?php

namespace Propaganistas\LaravelSms\Testing;

use Illuminate\Support\Testing\Fakes\Fake;
use Illuminate\Support\Traits\ForwardsCalls;
use PHPUnit\Framework\Assert as PHPUnit;
use Propaganistas\LaravelSms\Drivers\ArrayDriver;
use Propaganistas\LaravelSms\SmsManager;

class SmsFake implements Fake
{
    use ForwardsCalls;

    protected ArrayDriver $mailer;

    public function __construct(
        public SmsManager $manager
    ) {
        $this->mailer = $manager->mailer('array');
    }

    public function sent($callback)
    {
        $callback = $callback ?: fn () => true;

        return $this->mailer->messages()->filter(function ($message) use ($callback) {
            return $callback($message['recipient'], $message['message']);
        });
    }

    public function assertSent($callback = null)
    {
        PHPUnit::assertTrue(
            $this->sent($callback)->count() > 0,
            'The expected sms was not sent.'
        );
    }

    public function assertSentTimes($callback, $times = 1)
    {
        PHPUnit::assertTrue(
            $this->sent($callback)->count() === $times,
            'The expected sms was not sent.'
        );
    }

    public function assertNotSent($callback)
    {
        PHPUnit::assertCount(
            0, $this->sent($callback),
            'The unexpected sms was sent.'
        );
    }

    public function assertNothingSent()
    {
        $smses = $this->mailer
            ->messages()
            ->map(fn ($message) => "[{$message['recipient']}] {$message['message']}")
            ->join(' | ');

        PHPUnit::assertEmpty($this->mailer->messages(), 'The following SMSes were sent unexpectedly: '.$smses);
    }

    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->mailer, $method, $parameters);
    }
}
