<?php

namespace Propaganistas\LaravelSms\Drivers;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
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

        $errored = 0;

        foreach ($response->recipients->items as $recipient) {
            if ($recipient->status === 'delivery_failed') {
                Log::error('SMS delivery failed', array_merge(
                    Arr::except((array) $response, ['recipients']),
                    ['recipient' => (array) $recipient]
                ));

                $errored++;
            }
        }

        if ($response->recipients->totalCount === $errored) {
            throw new Exception('SMS delivery failed');
        }
    }

    public function getBalance(): float
    {
        $response = $this->client->balance->read();

        return $response->amount;
    }
}
