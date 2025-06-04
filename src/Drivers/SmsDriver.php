<?php

namespace Propaganistas\LaravelSms\Drivers;

use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;
use Propaganistas\LaravelSms\Events\SmsSending;
use Propaganistas\LaravelSms\Events\SmsSent;
use Propaganistas\LaravelSms\SmsMessage;

abstract class SmsDriver
{
    protected ?Dispatcher $dispatcher = null;

    protected string $recipient;

    protected SmsMessage $message;

    public function __construct(
        protected array $config = []
    ) {
    }

    public function to(string $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    final public function send(SmsMessage|string $message): void
    {
        if (empty($this->recipient)) {
            throw new InvalidArgumentException('No recipient defined for SMS message');
        }

        $this->message = is_string($message) ? new SmsMessage($message) : $message;

        if ($this->dispatcher !== null) {
            $this->dispatcher->dispatch(
                new SmsSending($this->recipient, $this->message)
            );
        }

        $this->performSend();

        if ($this->dispatcher !== null) {
            $this->dispatcher->dispatch(
                new SmsSent($this->recipient, $this->message)
            );
        }
    }

    abstract protected function performSend(): void;

    public function getUnitPrice(): float
    {
        return $this->config['unit_price'] ?? 0;
    }

    public function getBalance(): float
    {
        return INF;
    }

    public function withNotifiable($notifiable): static
    {
        return $this;
    }

    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }
}
