<?php

namespace Propaganistas\LaravelSms\Tests\Notifications;

use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelSms\Notifications\SmsMailable;
use Propaganistas\LaravelSms\SmsMessage;
use Propaganistas\LaravelSms\Tests\TestCase;

class SmsMailableTest extends TestCase
{
    #[Test]
    public function it_builds()
    {
        $mailable = new SmsMailable('0123', new SmsMessage('foo'));

        $mailable->build();

        $this->assertSame('[SMS] 0123', $mailable->subject);
        $this->assertSame('foo', $this->getProtectedProperty($mailable, 'html'));
    }

    #[Test]
    public function it_builds_and_returns_self()
    {
        $mailable = new SmsMailable('0123', new SmsMessage('foo'));

        $this->assertSame($mailable, $mailable->build());
    }
}
