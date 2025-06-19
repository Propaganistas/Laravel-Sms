<?php

namespace Propaganistas\LaravelSms\Tests\Drivers;

use Illuminate\Support\Collection;
use Illuminate\Support\Testing\Fakes\Fake;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelSms\Drivers\ArrayDriver;
use Propaganistas\LaravelSms\SmsMessage;
use Propaganistas\LaravelSms\Tests\TestCase;

class ArrayDriverTest extends TestCase
{
    #[Test]
    public function it_implements_fake()
    {
        $this->assertInstanceOf(Fake::class, new ArrayDriver);
    }

    #[Test]
    public function it_constructs()
    {
        $this->assertInstanceOf(ArrayDriver::class, new ArrayDriver);
        $this->assertInstanceOf(ArrayDriver::class, new ArrayDriver(['foo' => 'bar'], new Collection(['foo' => 'bar'])));
    }

    #[Test]
    public function it_stores_messages_when_sending()
    {
        $driver = new ArrayDriver;
        $driver->to('0123')->send('foo');
        $driver->to('45678')->send('bar');

        $expected = new Collection([
            ['recipient' => '0123', 'message' => new SmsMessage('foo')],
            ['recipient' => '45678', 'message' => new SmsMessage('bar')],
        ]);

        $this->assertEquals($expected, $driver->messages());
    }

    #[Test]
    public function it_returns_messages()
    {
        $driver = new ArrayDriver([], new Collection);
        $messages = $driver->messages();
        $this->assertInstanceOf(Collection::class, $messages);
        $this->assertEquals(new Collection, $messages);

        $driver = new ArrayDriver([], new Collection(['foo' => 'bar']));
        $messages = $driver->messages();
        $this->assertInstanceOf(Collection::class, $messages);
        $this->assertEquals(new Collection(['foo' => 'bar']), $messages);
    }

    #[Test]
    public function it_flushes_messages()
    {
        $driver = new ArrayDriver([], new Collection(['foo' => 'bar']));
        $driver->flush();
        $this->assertEquals(new Collection, $driver->messages());
    }

    #[Test]
    public function it_returns_inf_balance()
    {
        $driver = new ArrayDriver;
        $this->assertSame(INF, $driver->getBalance());
    }
}
