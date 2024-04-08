<?php

namespace Propaganistas\LaravelSms\Drivers;

use Psr\Log\LoggerInterface;

class LogDriver extends SmsDriver
{
    protected string $recipient;

    public function __construct(
        protected LoggerInterface $logger,
        protected array $config = []
    ) {
        parent::__construct($config);
    }

    protected function performSend(): void
    {
        $level = $this->config['level'] ?? 'debug';

        $this->logger->{$level}("[SMS] ($this->recipient) {$this->message}");
    }

    public function logger(): LoggerInterface
    {
        return $this->logger;
    }
}
