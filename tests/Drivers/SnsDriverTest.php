<?php

namespace Propaganistas\LaravelSms\Tests\Drivers;

use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Sns\SnsClient;
use Exception;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelSms\Drivers\SnsDriver;
use Propaganistas\LaravelSms\Tests\TestCase;

class SnsDriverTest extends TestCase
{
    #[Test]
    public function it_constructs()
    {
        $this->assertInstanceOf(SnsDriver::class, new SnsDriver(new SnsClient(['region' => 'eu-west-1'])));
    }

    #[Test]
    public function it_connects_with_sns_when_sending()
    {
        $mock = Mockery::mock(SnsClient::class);

        $mock->shouldReceive('publish')->once()->withArgs([
            [
                'Message' => 'foo',
                'PhoneNumber' => '+32470123456',
                'MessageAttributes' => [
                    'AWS.SNS.SMS.SMSType' => [
                        'DataType' => 'String',
                        'StringValue' => 'Transactional',
                    ],
                ],
            ],
        ]);

        $driver = new SnsDriver($mock, ['region' => 'eu-west-1']);
        $driver->to('+32470123456')->send('foo');
    }

    #[Test]
    public function it_connects_with_sns_with_configured_sender_id()
    {
        $mock = Mockery::mock(SnsClient::class);

        $mock->shouldReceive('publish')->once()->withArgs([
            [
                'Message' => 'foo',
                'PhoneNumber' => '+32470123456',
                'MessageAttributes' => [
                    'AWS.SNS.SMS.SMSType' => [
                        'DataType' => 'String',
                        'StringValue' => 'Transactional',
                    ],
                    'AWS.SNS.SMS.SenderID' => [
                        'DataType' => 'String',
                        'StringValue' => 'bar',
                    ],
                ],
            ],
        ]);

        $driver = new SnsDriver($mock, ['sender_id' => 'bar', 'region' => 'eu-west-1']);
        $driver->to('+32470123456')->send('foo');
    }

    #[Test]
    public function it_logs_failed_delivery()
    {
        Log::shouldReceive('error')->once()->withArgs(function (string $message, array $context) {
            return $context['error'] == 'Foo'
                && $context['aws_error'] === 'mymessage'
                && $context['aws_error_code'] === 'mycode'
                && $context['aws_error_type'] === 'mytype';
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SMS delivery failed');

        $mock = Mockery::mock(SnsClient::class);

        $command = new Command('foo');
        $e = new AwsException('Foo', $command, [
            'request_id' => '10',
            'type' => 'mytype',
            'code' => 'mycode',
            'message' => 'mymessage',
        ]);

        $mock->shouldReceive('publish')->once()->andThrow($e);

        $driver = new SnsDriver($mock, ['region' => 'eu-west-1']);
        $driver->to('0123')->send('foo');
    }

    #[Test]
    public function it_throws_when_failed_delivery()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SMS delivery failed');

        $mock = Mockery::mock(SnsClient::class);

        $e = new AwsException('Foo', new Command('foo'), []);

        $mock->shouldReceive('publish')->once()->andThrow($e);

        $driver = new SnsDriver($mock, ['region' => 'eu-west-1']);
        $driver->to('0123')->send('foo');
    }

    #[Test]
    public function it_returns_inf_balance()
    {
        $driver = new SnsDriver(new SnsClient(['region' => 'eu-west-1']));
        $this->assertSame(INF, $driver->getBalance());
    }
}
