<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $code,
        public readonly string $purpose
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->purpose === 'password_reset'
            ? 'Mã OTP đặt lại mật khẩu'
            : 'Mã OTP xác thực email mới';

        $message = $this->purpose === 'password_reset'
            ? 'Bạn vừa yêu cầu đặt lại mật khẩu tài khoản Nông Sản Việt.'
            : 'Bạn vừa yêu cầu thay đổi email tài khoản Nông Sản Việt.';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Xin chào!')
            ->line($message)
            ->line('Mã OTP của bạn là:')
            ->line($this->code)
            ->line('Mã có hiệu lực trong 5 phút. Không chia sẻ mã này với bất kỳ ai.')
            ->line('Nếu bạn không thực hiện yêu cầu này, hãy bỏ qua email.');
    }
}
