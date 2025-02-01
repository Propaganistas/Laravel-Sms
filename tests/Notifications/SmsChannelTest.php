<?php

namespace Propaganistas\LaravelSms\Tests\Notifications;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelSms\Notifications\SmsChannel;
use Propaganistas\LaravelSms\SmsManager;
use Propaganistas\LaravelSms\Tests\TestCase;

class SmsChannelTest extends TestCase
{
    #[Test]
    public function it_constructs()
    {
        $this->assertInstanceOf(SmsChannel::class, new SmsChannel($this->app[SmsManager::class]));
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
