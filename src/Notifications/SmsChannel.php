<?php

namespace Propaganistas\LaravelSms\Notifications;

use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use Propaganistas\LaravelSms\SmsManager;

class SmsChannel
{
    public function __construct(
        protected SmsManager $smsManager,
        protected ?Dispatcher $dispatcher = null
    ) {
    }

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSms($notifiable);

        $recipient = $notifiable->routeNotificationFor('sms');

        try {
            $this->smsManager
                ->to($recipient)
                ->withNotifiable($notifiable)
                ->send($message);
        } catch (Exception $e) {
            $this->sendNotificationFailed($notifiable, $notification, $e);
        }
    }

    protected function sendNotificationFailed($notifiable, Notification $notification, Exception $e)
    {
        if ($this->dispatcher !== null) {
            $this->dispatcher->dispatch(
                new NotificationFailed($notifiable, $notification, 'sms', [
                    'message' => $e->getMessage(),
                ])
            );
        }
    }
}
