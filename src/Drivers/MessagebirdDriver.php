<?php

namespace Propaganistas\LaravelSms\Drivers;

use MessageBird\Client;
use MessageBird\Objects\Message;

class MessagebirdDriver extends SmsDriver
{
    public function __construct(
        protected Client $client,
        protected array $config = []
    ) {
        parent::__construct($config);
    }

    protected function performSend(): void
    {
        $messagebird = new Message;
        $messagebird->originator = $this->config['originator'] ?? null;
        $messagebird->recipients = [$this->recipient];
        $messagebird->body = (string) $this->message;

        $response = $this->client->messages->create($messagebird);
    }

    public function getBalance(): float
    {
        $response = $this->client->balance->read();

        return $response->amount;
    }
}
