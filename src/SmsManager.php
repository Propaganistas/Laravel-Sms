<?php

namespace Propaganistas\LaravelSms;

use Closure;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Log\LogManager;
use Illuminate\Support\Str;
use InvalidArgumentException;
use MessageBird\Client as MessagebirdClient;
use Propaganistas\LaravelSms\Drivers\ArrayDriver;
use Propaganistas\LaravelSms\Drivers\LogDriver;
use Propaganistas\LaravelSms\Drivers\MailDriver;
use Propaganistas\LaravelSms\Drivers\MessagebirdDriver;
use Propaganistas\LaravelSms\Drivers\SmsDriver;
use Psr\Log\LoggerInterface;

class SmsManager
{
    protected array $mailers = [];

    protected $customCreators = [];

    public function __construct(
        protected Container $container,
        protected Config $config
    ) {
    }

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
            return $this->callCustomCreator($name);
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
        $client = $this->container->make(MessagebirdClient::class);
        $client->setAccessKey($config['access_key']);

        return new MessagebirdDriver($client, $config);
    }

    protected function callCustomCreator($mailer)
    {
        return $this->customCreators[$mailer]($this->container);
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
