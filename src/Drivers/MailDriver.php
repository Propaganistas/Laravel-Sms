<?php

namespace Propaganistas\LaravelSms\Drivers;

use Exception;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Arr;
use Propaganistas\LaravelSms\Notifications\SmsMailable;

class MailDriver extends SmsDriver
{
    protected $mailRecipients = [];

    public function __construct(
        protected Mailer $mailer,
        protected array $config = []
    ) {
        parent::__construct($config);
    }

    public function withNotifiable($notifiable): static
    {
        $this->mergeRecipients($notifiable->routeNotificationFor('mail'));

        return $this;
    }

    public function toAddress($address, $name = null): static
    {
        $this->mergeRecipients(
            $name === null ? $address : [$address => $name]
        );

        return $this;
    }

    protected function performSend(): void
    {
        if (empty($this->mailRecipients)) {
            throw new Exception('Mail recipient not set');
        }

        $this->mailer
            ->to($this->mailRecipients)
            ->send(new SmsMailable($this->recipient, $this->message));
    }

    protected function mergeRecipients($recipients)
    {
        $recipients = collect(Arr::wrap($recipients))->map(function ($recipient, $email) {
            return is_numeric($email)
                ? new Address(is_string($recipient) ? $recipient : $recipient->email)
                : new Address($email, $recipient);
        })->values()->all();

        $this->mailRecipients = array_merge($this->mailRecipients, $recipients);
    }
}
