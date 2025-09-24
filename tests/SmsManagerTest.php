<?php

namespace Propaganistas\LaravelSms\Tests;

use Aws\Sns\SnsClient;
use InvalidArgumentException;
use MessageBird\Client;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Propaganistas\LaravelSms\Drivers\ArrayDriver;
use Propaganistas\LaravelSms\Drivers\LogDriver;
use Propaganistas\LaravelSms\Drivers\MailDriver;
use Propaganistas\LaravelSms\Drivers\MessagebirdDriver;
use Propaganistas\LaravelSms\Drivers\SnsDriver;

class SmsManagerTest extends TestCase
{
    #[Test]
    public function it_gets_default_mailer()
    {
        $default = $this->app['sms.manager']->getDefaultMailer();

        $this->assertSame($this->app['config']->get('sms.default'), $default);
    }

    #[Test]
    public function it_resolves_default_mailer()
    {
        $manager = $this->app['sms.manager'];

        $this->assertInstanceOf(LogDriver::class, $manager->mailer());
    }

    #[Test]
    public function it_resolves_log_mailer()
    {
        $manager = $this->app['sms.manager'];

        $this->assertInstanceOf(LogDriver::class, $manager->mailer('log'));
    }

    #[Test]
    #[TestWith(['single', StreamHandler::class])]
    #[TestWith(['daily', RotatingFileHandler::class])]
    public function it_resolves_log_mailer_with_configured_channel(string $channel, string $handler)
    {
        $this->app['config']->set('sms.mailers.log.channel', $channel);

        $mailer = $this->app['sms.manager']->mailer('log');

        $actual = $this->getProtectedProperty($mailer, 'logger')->getLogger()->getHandlers()[0];

        $this->assertInstanceOf($handler, $actual);
    }

    #[Test]
    public function it_resolves_array_mailer()
    {
        $manager = $this->app['sms.manager'];

        $this->assertInstanceOf(ArrayDriver::class, $manager->mailer('array'));
    }

    #[Test]
    public function it_resolves_mail_mailer()
    {
        $manager = $this->app['sms.manager'];

        $this->assertInstanceOf(MailDriver::class, $manager->mailer('mail'));
    }

    #[Test]
    #[TestWith(['smtp'])]
    #[TestWith(['sendmail'])]
    public function it_resolves_mail_mailer_with_configured_mailer(string $transport)
    {
        $this->app['config']->set('sms.mailers.mail.mailer', $transport);

        $mailer = $this->getProtectedProperty(
            $this->app['sms.manager']->mailer('mail'),
            'mailer'
        );

        $name = $this->getProtectedProperty($mailer, 'name');

        $this->assertSame($transport, $name);
    }

    #[Test]
    public function it_resolves_messagebird_mailer_with_configured_access_key()
    {
        $this->app['config']->set('sms.mailers.messagebird.access_key', 'foo');

        $this->app->bind(Client::class, function ($app, $params) use (&$capturedParams) {
            $capturedParams = $params;

            return new Client(...$params);
        });

        $driver = $this->app['sms.manager']->mailer('messagebird');

        $this->assertInstanceOf(MessagebirdDriver::class, $driver);

        $this->assertEquals('foo', $capturedParams[0]);
    }

    #[Test]
    public function it_resolves_sns_mailer_with_configured_credentials()
    {
        $this->app['config']->set('sms.mailers.sns.key', 'foo');
        $this->app['config']->set('sms.mailers.sns.secret', 'bar');

        $this->app->bind(SnsClient::class, function ($app, $params) use (&$capturedParams) {
            $capturedParams = $params;

            return new SnsClient(...$params);
        });

        $driver = $this->app['sms.manager']->mailer('sns');

        $this->assertInstanceOf(SnsDriver::class, $driver);

        $this->assertEquals([
            'credentials' => [
                'key' => 'foo',
                'secret' => 'bar',
            ],
            'version' => 'latest',
            'service' => 'email',
            'driver' => 'sns',
            'access_key' => null,
            'secret' => 'bar',
            'originator' => null,
            'unit_price' => 0,
            'key' => 'foo',
        ], $capturedParams[0]);
    }

    #[Test]
    public function it_throws_on_unknown_mailer()
    {
        $this->expectException(InvalidArgumentException::class);

        $manager = $this->app['sms.manager'];
        $manager->mailer('foo');
    }
}
