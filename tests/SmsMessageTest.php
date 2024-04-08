<?php

namespace Propaganistas\LaravelSms\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Propaganistas\LaravelSms\SmsMessage;
use TypeError;

class SmsMessageTest extends TestCase
{
    #[Test]
    public function it_constructs()
    {
        $message = new SmsMessage('foo');

        $this->assertInstanceOf(SmsMessage::class, $message);
        $this->assertSame('foo', $this->getProtectedProperty($message, 'content'));
    }

    #[Test]
    public function it_doesnt_construct_null()
    {
        $this->expectException(TypeError::class);

        new SmsMessage(null);
    }

    #[Test]
    public function it_sets_content()
    {
        $message = new SmsMessage;
        $message->content('foo');

        $this->assertSame('foo', $this->getProtectedProperty($message, 'content'));
    }

    #[Test]
    public function it_stringifies()
    {
        $message = new SmsMessage;
        $message->content('foo');

        $this->assertSame('foo', (string) $message);
    }

    #[Test]
    public function it_detects_unicode()
    {
        $message = new SmsMessage('foo');
        $this->assertFalse($message->hasUnicode());

        $message = new SmsMessage('🔥');
        $this->assertTrue($message->hasUnicode());
    }

    #[Test]
    #[TestWith([0, 0])]
    #[TestWith([1, 160])]
    #[TestWith([2, 306])]
    #[TestWith([3, 459])]
    #[TestWith([4, 612])]
    #[TestWith([5, 765])]
    #[TestWith([6, 918])]
    #[TestWith([7, 1071])]
    #[TestWith([8, 1224])]
    #[TestWith([9, 1377])]
    public function it_returns_amount_of_sms_for_plain_text(int $expected, int $length)
    {
        $message = new SmsMessage(str_repeat('x', $length));
        $this->assertSame($expected, $message->amount());

        if ($expected < 9) {
            $message = new SmsMessage(str_repeat('x', $length + 1));
            $this->assertSame($expected + 1, $message->amount());
        }
    }

    #[Test]
    #[TestWith([0, 0])]
    #[TestWith([1, 70])]
    #[TestWith([2, 134])]
    #[TestWith([3, 201])]
    #[TestWith([4, 268])]
    #[TestWith([5, 335])]
    #[TestWith([6, 402])]
    #[TestWith([7, 469])]
    #[TestWith([8, 536])]
    #[TestWith([9, 603])]
    public function it_returns_amount_of_sms_for_unicode_text(int $expected, int $length)
    {
        $message = new SmsMessage(str_repeat('🔥', $length));
        $this->assertSame($expected, $message->amount());

        if ($expected < 9) {
            $message = new SmsMessage(str_repeat('🔥', $length + 1));
            $this->assertSame($expected + 1, $message->amount());
        }
    }

    #[Test]
    #[TestWith([0, 'x', 0])]
    #[TestWith([100, 'x', 0.5])]
    #[TestWith([100, '🔥', 1])]
    public function it_calculates_cost_for_default_mailer(int $length, string $character, float $expected)
    {
        $default = $this->app['config']->get('sms.default');
        $this->app['config']->set("sms.mailers.{$default}.unit_price", 0.5);

        $message = new SmsMessage(str_repeat($character, $length));
        $this->assertSame($expected, $message->cost());
    }

    #[Test]
    #[TestWith([0, 'x', 0])]
    #[TestWith([100, 'x', 0.75])]
    #[TestWith([100, '🔥', 1.5])]
    public function it_calculates_cost_for_chosen_mailer(int $length, string $character, float $expected)
    {
        $this->app['config']->set('sms.mailers.foo.driver', 'array');
        $this->app['config']->set('sms.mailers.foo.unit_price', 0.75);

        $message = new SmsMessage(str_repeat($character, $length));
        $this->assertSame($expected, $message->cost('foo'));
    }
}
