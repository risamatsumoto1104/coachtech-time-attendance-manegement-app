<?php

namespace App\Notifications;


use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmailNotification extends VerifyEmail
{
    // メールの通知内容をカスタマイズ
    // メールで送信する内容を定義
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->line('メール認証を行ってください。')
            ->action('メール認証', $verificationUrl)
            ->line('このリンクは 60 分以内に有効です。');
    }

    // メール認証用URLをカスタマイズ
    // メール認証のURLを生成
    protected function verificationUrl($notifiable)
    {
        // 署名付き URL を生成
        return URL::temporarySignedRoute('email.verify', now()->addMinutes(60), [
            'user_id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ]);
    }
}
