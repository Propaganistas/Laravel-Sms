<?php

namespace Propaganistas\LaravelSms\Tests\Drivers;

use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelSms\Drivers\LogDriver;
use Propaganistas\LaravelSms\Tests\TestCase;

class LogDriverTest extends TestCase
{
    #[Test]
    public function it_constructs()
    {
        $logger = Log::getFacadeRoot();

        $this->assertInstanceOf(LogDriver::class, new LogDriver($logger));
        $this->assertInstanceOf(LogDriver::class, new LogDriver($logger, ['foo' => 'bar']));
    }

    #[Test]
    public function it_writes_to_log_when_sending()
    {
        Log::shouldReceive('debug')->once()->withArgs(['[SMS] (0123) foo']);

        $driver = new LogDriver(Log::getFacadeRoot());
        $driver->to('0123')->send('foo');
    }

    #[Test]
    public function it_writes_to_log_with_configured_level()
    {
        Log::shouldReceive('warning')->once()->withArgs(['[SMS] (0123) foo']);

        $driver = new LogDriver(Log::getFacadeRoot(), ['level' => 'warning']);
        $driver->to('0123')->send('foo');
    }

    #[Test]
    public function it_returns_inf_balance()
    {
        $driver = new LogDriver(Log::getFacadeRoot());
        $this->assertSame(INF, $driver->getBalance());
    }
}
