<?php

namespace Propaganistas\LaravelSms\Notifications;

use Illuminate\Notifications\Notification;
use Propaganistas\LaravelSms\SmsManager;

class SmsChannel
{
    public function __construct(
        protected SmsManager $smsManager,
    ) {
    }

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSms($notifiable);

        $recipient = $notifiable->routeNotificationFor('sms');

        $this->smsManager
            ->to($recipient)
            ->withNotifiable($notifiable)
            ->send($message);
    }
}
