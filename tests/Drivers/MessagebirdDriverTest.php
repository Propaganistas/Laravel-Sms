<?php

namespace Propaganistas\LaravelSms\Tests\Drivers;

use MessageBird\Client;
use MessageBird\Objects\Message;
use MessageBird\Resources\Messages;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelSms\Drivers\MessagebirdDriver;
use Propaganistas\LaravelSms\Tests\TestCase;

class MessagebirdDriverTest extends TestCase
{
    #[Test]
    public function it_constructs()
    {
        $this->assertInstanceOf(MessagebirdDriver::class, new MessagebirdDriver(new Client));
        $this->assertInstanceOf(MessagebirdDriver::class, new MessagebirdDriver(new Client, ['foo' => 'bar']));
    }

    #[Test]
    public function it_connects_with_messagebird_when_sending()
    {
        $mock = Mockery::mock(Client::class);
        $messagesMock = Mockery::mock(Messages::class);

        $mock->messages = $messagesMock;

        $messagesMock->shouldReceive('create')->once()->withArgs(function ($message) {
            return $message instanceof Message
                && $message->originator === null
                && $message->recipients === ['0123']
                && $message->body === 'foo';
        });

        $driver = new MessagebirdDriver($mock);
        $driver->to('0123')->send('foo');
    }

    #[Test]
    public function it_connects_with_messagebird_with_configured_originator()
    {
        $mock = Mockery::mock(Client::class);
        $messagesMock = Mockery::mock(Messages::class);

        $mock->messages = $messagesMock;

        $messagesMock->shouldReceive('create')->once()->withArgs(function ($message) {
            return $message instanceof Message
                && $message->originator === 'bar'
                && $message->recipients === ['0123']
                && $message->body === 'foo';
        });

        $driver = new MessagebirdDriver($mock, ['originator' => 'bar']);
        $driver->to('0123')->send('foo');
    }
}
