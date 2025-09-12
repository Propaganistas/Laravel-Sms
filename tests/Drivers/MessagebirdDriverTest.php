<?php

namespace Propaganistas\LaravelSms\Tests\Drivers;

use Exception;
use Illuminate\Support\Facades\Log;
use MessageBird\Client;
use MessageBird\Objects\Balance;
use MessageBird\Objects\Message;
use MessageBird\Objects\MessageResponse;
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
    public function it_logs_each_failed_delivery()
    {
        Log::shouldReceive('error')->once()->withArgs(function (string $message, array $context) {
            return $context['recipient']['recipient'] === 31612345677
                && $context['recipient']['statusReason'] === 'some obscure error';
        });

        Log::shouldReceive('error')->once()->withArgs(function (string $message, array $context) {
            return $context['recipient']['recipient'] === 31612345679
                && $context['recipient']['statusReason'] === 'unknown subscriber';
        });

        $mock = Mockery::mock(Client::class);
        $messagesMock = Mockery::mock(Messages::class);

        $mock->messages = $messagesMock;

        $messagesMock->shouldReceive('create')->andReturn(
            (new MessageResponse)->loadFromStdclass(json_decode(<<<'JSON'
{
  "id":"98154fa03532c2c3fc7b341b46487018",
  "href":"https://rest.messagebird.com/messages/98154fa03532c2c3fc7b341b46487018",
  "direction":"mt",
  "type":"sms",
  "originator":"bar",
  "body":"foo",
  "reference":null,
  "validity":null,
  "gateway":240,
  "typeDetails":{},
  "datacoding":"plain",
  "mclass":1,
  "scheduledDatetime":null,
  "createdDatetime":"2016-04-29T09:42:26+00:00",
  "recipients":{
    "totalCount":3,
    "totalSentCount":2,
    "totalDeliveredCount":1,
    "totalDeliveryFailedCount":0,
    "items":[
      {
        "recipient":31612345677,
        "status":"delivery_failed",
        "statusReason":"some obscure error",
        "statusErrorCode":100,
        "statusDatetime":"2016-04-29T09:42:26+00:00",
        "recipientCountry":"Netherlands",
        "recipientCountryPrefix":31,
        "recipientOperator":"",
        "mccmnc":null,
        "mcc":null,
        "mnc":null,
        "messageLength":44,
        "messagePartCount":1,
        "price":{
            "amount":null,
            "currency":null
        }
      },
      {
        "recipient":31612345678,
        "status":"sent",
        "statusReason":"successfully delivered",
        "statusErrorCode":null,
        "statusDatetime":"2016-04-29T09:42:26+00:00",
        "recipientCountry":"Netherlands",
        "recipientCountryPrefix":31,
        "recipientOperator":"KPN",
        "mccmnc":"20408",
        "mcc":"204",
        "mnc":"08",
        "messageLength":44,
        "messagePartCount":1,
        "price":{
            "amount":0.075,
            "currency":"EUR"
        }
      },
      {
        "recipient":31612345679,
        "status":"delivery_failed",
        "statusReason":"unknown subscriber",
        "statusErrorCode":1,
        "statusDatetime":"2016-04-29T09:42:26+00:00",
        "recipientCountry":"Netherlands",
        "recipientCountryPrefix":31,
        "recipientOperator":"",
        "mccmnc":null,
        "mcc":null,
        "mnc":null,
        "messageLength":44,
        "messagePartCount":1,
        "price":{
            "amount":null,
            "currency":null
        }
      }
    ]
  }
}
JSON
            ))
        );

        $driver = new MessagebirdDriver($mock, ['originator' => 'bar']);
        $driver->to('0123')->send('foo');
    }

    #[Test]
    public function it_throws_when_all_failed_delivery()
    {
        $this->expectException(Exception::class, 'SMS delivery failed');

        $mock = Mockery::mock(Client::class);
        $messagesMock = Mockery::mock(Messages::class);

        $mock->messages = $messagesMock;

        $messagesMock->shouldReceive('create')->andReturn(
            (new MessageResponse)->loadFromStdclass(json_decode(<<<'JSON'
{
  "id":"98154fa03532c2c3fc7b341b46487018",
  "href":"https://rest.messagebird.com/messages/98154fa03532c2c3fc7b341b46487018",
  "direction":"mt",
  "type":"sms",
  "originator":"bar",
  "body":"foo",
  "reference":null,
  "validity":null,
  "gateway":240,
  "typeDetails":{},
  "datacoding":"plain",
  "mclass":1,
  "scheduledDatetime":null,
  "createdDatetime":"2016-04-29T09:42:26+00:00",
  "recipients":{
    "totalCount":2,
    "totalSentCount":2,
    "totalDeliveredCount":0,
    "totalDeliveryFailedCount":0,
    "items":[
      {
        "recipient":31612345677,
        "status":"delivery_failed",
        "statusReason":"some obscure error",
        "statusErrorCode":100,
        "statusDatetime":"2016-04-29T09:42:26+00:00",
        "recipientCountry":"Netherlands",
        "recipientCountryPrefix":31,
        "recipientOperator":"",
        "mccmnc":null,
        "mcc":null,
        "mnc":null,
        "messageLength":44,
        "messagePartCount":1,
        "price":{
            "amount":null,
            "currency":null
        }
      },
      {
        "recipient":31612345679,
        "status":"delivery_failed",
        "statusReason":"unknown subscriber",
        "statusErrorCode":1,
        "statusDatetime":"2016-04-29T09:42:26+00:00",
        "recipientCountry":"Netherlands",
        "recipientCountryPrefix":31,
        "recipientOperator":"",
        "mccmnc":null,
        "mcc":null,
        "mnc":null,
        "messageLength":44,
        "messagePartCount":1,
        "price":{
            "amount":null,
            "currency":null
        }
      }
    ]
  }
}
JSON
            ))
        );

        $driver = new MessagebirdDriver($mock, ['originator' => 'bar']);
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

    #[Test]
    public function it_connects_with_messagebird_when_returning_balance()
    {
        $mock = Mockery::mock(Client::class);
        $balanceMock = Mockery::mock(Balance::class);

        $mock->balance = $balanceMock;
        $mock->balance->amount = 200;

        $balanceMock->shouldReceive('read')->once()->withNoArgs()->andReturn($balanceMock);

        $driver = new MessagebirdDriver($mock);
        $driver->getBalance();
    }
}
