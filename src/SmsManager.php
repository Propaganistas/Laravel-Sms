<?php

namespace Propaganistas\LaravelSms;

use Aws\Sns\SnsClient;
use Closure;
use Exception;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Log\LogManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use MessageBird\Client as MessagebirdClient;
use Propaganistas\LaravelSms\Drivers\ArrayDriver;
use Propaganistas\LaravelSms\Drivers\LogDriver;
use Propaganistas\LaravelSms\Drivers\MailDriver;
use Propaganistas\LaravelSms\Drivers\MessagebirdDriver;
use Propaganistas\LaravelSms\Drivers\SmsDriver;
use Propaganistas\LaravelSms\Drivers\SnsDriver;
use Psr\Log\LoggerInterface;

class SmsManager
{
    protected array $mailers = [];

    protected $customCreators = [];

    public function __construct(
        protected Container $container,
        protected Config $config
    ) {}

    public function getDefaultMailer()
    {
        return $this->config->get('sms.default');
    }

    public function mailer($name = null): SmsDriver
    {
        $name = $name ?: $this->getDefaultMailer();

        $this->mailers[$name] = $this->mailers[$name] ?? $this->resolve($name);

        return $this->mailers[$name];
    }

    protected function resolve($name): SmsDriver
    {
        $config = $this->config->get("sms.mailers.{$name}");

        if (is_null($config)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve NULL mailer for [%s].', static::class
            ));
        }

        if (isset($this->customCreators[$name])) {
            return $this->customCreators[$name]($config);
        }

        $driver = $config['driver'];
        $method = 'create'.Str::studly($driver).'Driver';

        if (method_exists($this, $method)) {
            return $this->{$method}($config)->setDispatcher(
                $this->container->make(Dispatcher::class)
            );
        }

        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }

    protected function createArrayDriver(array $config): ArrayDriver
    {
        return new ArrayDriver($config);
    }

    protected function createLogDriver(array $config): LogDriver
    {
        $logger = $this->container->make(LoggerInterface::class);

        if ($logger instanceof LogManager) {
            $logger = $logger->channel($config['channel']);
        }

        return new LogDriver($logger, $config);
    }

    protected function createMailDriver(array $config): MailDriver
    {
        $mailer = $this->container->make('mail.manager')->mailer(
            $config['mailer'] ?? null
        );

        return new MailDriver($mailer, $config);
    }

    protected function createMessagebirdDriver(array $config): MessagebirdDriver
    {
        $config = array_merge(
            $this->config->get('services.messagebird', []),
            $config
        );

        if (! class_exists(MessagebirdClient::class)) {
            throw new Exception('The Messagebird library is not installed. Please run `composer require messagebird/php-rest-api` to install it.');
        }

        return new MessagebirdDriver(
            new MessagebirdClient($config['access_key']), $config
        );
    }

    protected function createSnsDriver(array $config): SnsDriver
    {
        $config = array_merge(
            $this->config->get('services.sns', []),
            ['version' => 'latest'],
            $config
        );

        $config['key'] = $config['key'] ?? $config['access_key'];

        if (! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        if (! class_exists(SnsClient::class)) {
            throw new Exception('The AWS SDK is not installed. Please run `composer require aws/aws-sdk-php` to install it.');
        }

        return new SnsDriver(
            new SnsClient($config), $config
        );
    }

    public function extend($mailer, Closure $callback)
    {
        $this->customCreators[$mailer] = $callback;

        return $this;
    }

    public function __call($method, $parameters)
    {
        return $this->mailer()->$method(...$parameters);
    }
}
