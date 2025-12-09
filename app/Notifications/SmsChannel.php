<?php

namespace App\Notifications;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function __construct(protected SmsService $smsService) {}

    public function send($notifiable, Notification $notification)
    {
        \Log::info('SmsChannel: send() reached!');

        if (! $notifiable->phone_number) {
            return;
        }

        $smsData = $notification->toSms($notifiable);
        $phone = $notifiable->phone_number;
        if (str_starts_with($phone, '0')) {
            $phone = '+963'.substr($phone, 1);
        }
        $this->smsService->sendSMS($phone, $smsData);
    }
}
