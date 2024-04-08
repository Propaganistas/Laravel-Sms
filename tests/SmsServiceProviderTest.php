<?php

namespace Propaganistas\LaravelSms\Tests;

use Illuminate\Notifications\ChannelManager;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelSms\SmsManager;

class SmsServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_manager_singleton()
    {
        $this->assertTrue($this->app->bound('sms.manager'));
        $this->assertSame('sms.manager', $this->app->getAlias(SmsManager::class));
        $this->assertTrue($this->app->isShared('sms.manager'));

        $this->assertInstanceOf(SmsManager::class, $this->app->make('sms.manager'));
    }

    #[Test]
    public function it_registers_sms_channel()
    {
        $manager = $this->app->make(ChannelManager::class);

        $this->assertArrayHasKey('sms', $this->getProtectedProperty($manager, 'customCreators'));
    }
}
