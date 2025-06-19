<?php

namespace Propaganistas\LaravelSms\Tests\Drivers;

use Exception;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelSms\Drivers\MailDriver;
use Propaganistas\LaravelSms\Notifications\SmsMailable;
use Propaganistas\LaravelSms\Tests\TestCase;

class MailDriverTest extends TestCase
{
    #[Test]
    public function it_constructs()
    {
        $mailer = Mail::getFacadeRoot()->mailer();

        $this->assertInstanceOf(MailDriver::class, new MailDriver($mailer));
        $this->assertInstanceOf(MailDriver::class, new MailDriver($mailer, ['foo' => 'bar']));
    }

    #[Test]
    public function it_sends_mail_when_sending()
    {
        Mail::fake();

        $mailer = Mail::getFacadeRoot();

        $driver = new MailDriver($mailer->mailer());
        $driver->to('0123')->toAddress('foo@example.org')->send('foo');

        Mail::assertSent(SmsMailable::class, function (SmsMailable $mailable) {
            return $mailable->recipient === '0123'
                && (string) $mailable->message === 'foo';
        });
    }

    #[Test]
    public function it_throws_when_no_address_set()
    {
        $this->expectException(Exception::class);

        Mail::fake();

        $mailer = Mail::getFacadeRoot();

        $driver = new MailDriver($mailer->mailer());
        $driver->to('0123')->send('foo');

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_sets_to_address()
    {
        $driver = new MailDriver(Mail::getFacadeRoot()->mailer());
        $driver->toAddress('foo@example.com');
        $this->assertEquals([new Address('foo@example.com')], $this->getProtectedProperty($driver, 'mailRecipients'));

        $driver = new MailDriver(Mail::getFacadeRoot()->mailer());
        $driver->toAddress('foo@example.com', 'John Doe');
        $this->assertEquals([new Address('foo@example.com', 'John Doe')], $this->getProtectedProperty($driver, 'mailRecipients'));

        $driver = new MailDriver(Mail::getFacadeRoot()->mailer());
        $driver->toAddress(['foo@example.com' => 'John Doe']);
        $this->assertEquals([new Address('foo@example.com', 'John Doe')], $this->getProtectedProperty($driver, 'mailRecipients'));
    }

    #[Test]
    public function it_merges_notifiable_as_mail_recipient()
    {
        $driver = new MailDriver(Mail::getFacadeRoot()->mailer());

        $driver->to('0123')->withNotifiable(new class
        {
            use Notifiable;

            public function routeNotificationForMail()
            {
                return 'foo@example.com';
            }
        })->withNotifiable(new class
        {
            use Notifiable;

            public function routeNotificationForMail()
            {
                return ['bar@example.com' => 'John Doe'];
            }
        });

        $this->assertEquals([
            new Address('foo@example.com'),
            new Address('bar@example.com', 'John Doe'),
        ], $this->getProtectedProperty($driver, 'mailRecipients'));
    }

    #[Test]
    public function it_returns_inf_balance()
    {
        $mailer = Mail::getFacadeRoot()->mailer();

        $driver = new MailDriver($mailer);
        $this->assertSame(INF, $driver->getBalance());
    }
}
