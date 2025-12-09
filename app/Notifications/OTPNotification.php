<?php

namespace App\Notifications;

use Ichtrojan\Otp\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OTPNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $otp;

    private $subject;

    private $message;

    public function __construct(string $subject = 'phoneVerify', string $locale = 'ar')
    {
        $this->otp = new Otp;
        $this->subject = $subject;

        if ($locale === 'ar') {
            if ($subject === 'phoneVerify') {
                $this->message = 'استخدم هذا الرمز للتحقق من رقم هاتفك خلال دقيقتين';
            } else {
                $this->message = 'استخدم هذا الرمز لإعادة تعيين كلمة المرور خلال دقيقتين';
            }
        } else {
            if ($subject === 'phoneVerify') {
                $this->message = 'Use this code to verify your phone number within 2 minutes';
            } else {
                $this->message = 'Use this code to reset your password within 2 minutes';
            }
        }
    }

    public function via(object $notifiable): array
    {
        return ['sms'];
    }

    public function toSms(object $notifiable): string
    {
        $otp = $this->otp->generate($notifiable->phone_number, 'numeric', 6, 2);
        Log::info('Generated OTP', [
            'phone' => $notifiable->phone_number,
            'otp' => $otp->token,
        ]);

        return "{$this->message}: {$otp->token}";
    }
}
