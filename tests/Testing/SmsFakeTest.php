<?php

namespace Propaganistas\LaravelSms\Tests\Testing;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Propaganistas\LaravelSms\Drivers\ArrayDriver;
use Propaganistas\LaravelSms\Facades\Sms;
use Propaganistas\LaravelSms\SmsMessage;
use Propaganistas\LaravelSms\Testing\SmsFake;
use Propaganistas\LaravelSms\Tests\TestCase;

class SmsFakeTest extends TestCase
{
    #[Test]
    public function it_uses_array_drivers()
    {
        $fake = $this->app->make(SmsFake::class);

        $this->assertInstanceOf(ArrayDriver::class, $this->getProtectedProperty($fake, 'mailer'));
    }

    #[Test]
    public function it_asserts_sent_true()
    {
        $fake = Sms::fake();
        $fake->to('0123')->send('foo');

        $fake->assertSent();
    }

    #[Test]
    public function it_asserts_sent_false()
    {
        $this->expectException(ExpectationFailedException::class);

        $fake = Sms::fake();

        $fake->assertSent();
    }

    #[Test]
    public function it_asserts_sent_with_callback_true()
    {
        $fake = Sms::fake();
        $fake->to('0123')->send('foo');

        $fake->assertSent(function ($recipient, SmsMessage $message) {
            return $recipient === '0123' && (string) $message === 'foo';
        });
    }

    #[Test]
    public function it_asserts_sent_with_callback_false()
    {
        $this->expectException(ExpectationFailedException::class);

        $fake = Sms::fake();
        $fake->to('0123')->send('bar');

        $fake->assertSent(function ($recipient, SmsMessage $message) {
            return $recipient === '0123' && (string) $message === 'foo';
        });
    }

    #[Test]
    public function it_asserts_sent_times_true()
    {
        $fake = Sms::fake();
        $fake->to('0123')->send('foo');
        $fake->to('0123')->send('foo');

        $fake->assertSentTimes(function ($recipient, SmsMessage $message) {
            return $recipient === '0123' && (string) $message === 'foo';
        }, 2);
    }

    #[Test]
    public function it_asserts_sent_times_false()
    {
        $this->expectException(ExpectationFailedException::class);

        $fake = Sms::fake();
        $fake->to('0123')->send('bar');

        $fake->assertSent(function ($recipient, SmsMessage $message) {
            return $recipient === '0123' && (string) $message === 'foo';
        }, 2);
    }

    #[Test]
    public function it_asserts_not_sent_true()
    {
        $fake = Sms::fake();
        $fake->to('0123')->send('bar');

        $fake->assertNotSent(function ($recipient, SmsMessage $message) {
            return $recipient === '0123' && (string) $message === 'foo';
        });
    }

    #[Test]
    public function it_asserts_not_sent_false()
    {
        $this->expectException(ExpectationFailedException::class);

        $fake = Sms::fake();
        $fake->to('0123')->send('foo');

        $fake->assertNotSent(function ($recipient, SmsMessage $message) {
            return $recipient === '0123' && (string) $message === 'foo';
        });
    }

    #[Test]
    public function it_asserts_nothing_sent_true()
    {
        $fake = Sms::fake();

        $fake->assertNothingSent();
    }

    #[Test]
    public function it_asserts_nothing_sent_false()
    {
        $this->expectException(ExpectationFailedException::class);

        $fake = Sms::fake();
        $fake->to('0123')->send('foo');

        $fake->assertNothingSent();
    }
}
