<?php

namespace Propaganistas\LaravelSms\Drivers;

use Aws\Exception\AwsException;
use Aws\Sns\SnsClient;
use Exception;
use Illuminate\Support\Facades\Log;

class SnsDriver extends SmsDriver
{
    public function __construct(
        protected SnsClient $client,
        protected array $config = []
    ) {
        parent::__construct($config);
    }

    protected function performSend(): void
    {
        $attributes = [
            'AWS.SNS.SMS.SMSType' => [
                'DataType' => 'String',
                'StringValue' => 'Transactional',
            ],
        ];

        if (! empty($this->config['sender_id'])) {
            $attributes += [
                'AWS.SNS.SMS.SenderID' => [
                    'DataType' => 'String',
                    'StringValue' => $this->config['sender_id'],
                ],
            ];
        }

        $parameters = [
            'Message' => (string) $this->message,
            'PhoneNumber' => $this->recipient,
            'MessageAttributes' => $attributes,
        ];

        try {
            $this->client->publish($parameters);
        } catch (AwsException $e) {
            Log::error('SMS delivery failed', array_merge([
                'error' => $e->getMessage(),
                'aws_error' => $e->getAwsErrorMessage(),
                'aws_error_code' => $e->getAwsErrorCode(),
                'aws_error_type' => $e->getAwsErrorType(),
                'recipient' => $this->recipient,
            ]));

            throw new Exception('SMS delivery failed');
        }
    }
}
