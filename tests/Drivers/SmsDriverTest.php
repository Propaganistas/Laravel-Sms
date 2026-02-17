<?php

namespace Propaganistas\LaravelSms\Tests\Drivers;

use Exception;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Propaganistas\LaravelSms\Drivers\SmsDriver;
use Propaganistas\LaravelSms\Events\SmsSending;
use Propaganistas\LaravelSms\Events\SmsSent;
use Propaganistas\LaravelSms\Facades\Sms;
use Propaganistas\LaravelSms\SmsMessage;
use Propaganistas\LaravelSms\Tests\TestCase;

class SmsDriverTest extends TestCase
{
    #[Test]
    public function it_sets_recipient()
    {
        $driver = new SucceedingDriver;
        $driver->to('0123');

        $this->assertSame('0123', $this->getProtectedProperty($driver, 'recipient'));
    }

    #[Test]
    public function it_throws_when_sending_without_recipient()
    {
        $this->expectException(InvalidArgumentException::class);

        $driver = new SucceedingDriver;
        $driver->send('foo');
    }

    #[Test]
    public function it_sets_dispatcher()
    {
        Event::fake();
        $dispatcher = Event::getFacadeRoot();

        $driver = new SucceedingDriver;

        $this->assertNull($this->getProtectedProperty($driver, 'dispatcher'));
        $driver->setDispatcher($dispatcher);
        $this->assertSame($dispatcher, $this->getProtectedProperty($driver, 'dispatcher'));
    }

    #[Test]
    public function it_dispatches_sending_event_before_send()
    {
        $this->expectException(Exception::class);
        Event::fake();

        $driver = new FailingDriver;
        $driver->setDispatcher(Event::getFacadeRoot());

        $driver->to('0123')->send('foo');

        Event::assertDispatched(SmsSending::class, function (SmsSending $event) {
            return $event->recipient === '0123' && (string) $event->message === 'foo';
        });

        Event::assertNotDispatched(SmsSent::class);
    }

    #[Test]
    public function it_dispatches_sent_event_after_send()
    {
        Event::fake();

        $driver = new SucceedingDriver;
        $driver->setDispatcher(Event::getFacadeRoot());

        $driver->to('0123')->send('foo');

        Event::assertDispatched(SmsSent::class, function (SmsSent $event) {
            return $event->recipient === '0123' && (string) $event->message === 'foo';
        });
    }

    #[Test]
    public function it_returns_unit_price()
    {
        $driver = new SucceedingDriver;
        $this->assertSame(0.0, $driver->getUnitPrice());

        $driver = new SucceedingDriver(['unit_price' => 0.5]);
        $this->assertSame(0.5, $driver->getUnitPrice());
    }

    #[Test]
    public function it_returns_default_inf_balance()
    {
        $driver = new SucceedingDriver;
        $this->assertSame(INF, $driver->getBalance());
    }

    #[Test]
    #[TestWith([0, 'x', 0])]
    #[TestWith([100, 'x', 0.75])]
    #[TestWith([100, '🔥', 1.5])]
    public function it_estimates_cost(int $length, string $character, float $expected)
    {
        $this->app['config']->set('sms.mailers.foo.driver', 'array');
        $this->app['config']->set('sms.mailers.foo.unit_price', 0.75);

        $message = new SmsMessage(str_repeat($character, $length));
        $this->assertSame($expected, Sms::mailer('foo')->estimateCost($message));
    }

    #[Test]
    public function it_exposes_a_with_notifiable_method_for_use_in_notification_channel()
    {
        $driver = new SucceedingDriver;

        $this->assertTrue(method_exists($driver, 'withNotifiable'));
    }
}

class FailingDriver extends SmsDriver
{
    protected function performSend(): void
    {
        throw new Exception;
    }
}

class SucceedingDriver extends SmsDriver
{
    protected function performSend(): void
    {
        //
    }
}
