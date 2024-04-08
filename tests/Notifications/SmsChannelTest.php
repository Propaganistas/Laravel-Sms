<?php

namespace Propaganistas\LaravelSms\Tests\Notifications;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelSms\Notifications\SmsChannel;
use Propaganistas\LaravelSms\SmsManager;
use Propaganistas\LaravelSms\Tests\TestCase;
use RuntimeException;

class SmsChannelTest extends TestCase
{
    #[Test]
    public function it_constructs()
    {
        $this->assertInstanceOf(SmsChannel::class, new SmsChannel($this->app[SmsManager::class]));
        $this->assertInstanceOf(SmsChannel::class, new SmsChannel($this->app[SmsManager::class], $this->app[Dispatcher::class]));
    }

    #[Test]
    public function it_sends()
    {
        $notifiable = new StubNotifiable;
        $notification = new StubNotification;

        $mock = Mockery::mock(SmsManager::class);
        $mock->shouldReceive('to')->once()->withArgs(['0123'])->andReturnSelf();
        $mock->shouldReceive('withNotifiable')->once()->withArgs([$notifiable])->andReturnSelf();
        $mock->shouldReceive('send')->once()->withArgs(['go foo']);

        $channel = new SmsChannel($mock);
        $channel->send($notifiable, $notification);
    }

    #[Test]
    public function it_dispatches_notification_failed_event_on_exception()
    {
        Event::fake();

        $notifiable = new StubNotifiable;
        $notification = new StubNotification;

        $mock = Mockery::mock(SmsManager::class);
        $mock->shouldReceive('to')->once()->withArgs(['0123'])->andReturnSelf();
        $mock->shouldReceive('withNotifiable')->once()->withArgs([$notifiable])->andReturnSelf();
        $mock->shouldReceive('send')->once()->andThrow(new RuntimeException('Oh no'));

        $channel = new SmsChannel($mock, Event::getFacadeRoot());
        $channel->send($notifiable, $notification);

        Event::assertDispatched(NotificationFailed::class, function (NotificationFailed $event) use ($notifiable, $notification) {
            return $event->notifiable === $notifiable
                && $event->notification === $notification
                && $event->channel === 'sms'
                && $event->data === ['message' => 'Oh no'];
        });
    }
}

class StubNotifiable
{
    use Notifiable;

    public $test = 'go';

    public function routeNotificationForSms()
    {
        return '0123';
    }
}

class StubNotification extends Notification
{
    public function toSms($notifiable)
    {
        return $notifiable->test.' foo';
    }
}
