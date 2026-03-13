<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Obnovení hesla')
            ->greeting('Dobrý den,')
            ->line('Tento e-mail jste obdrželi, protože jsme přijali žádost o obnovení hesla pro váš účet.')
            ->action('Obnovit heslo', $url)
            ->line('Tento odkaz na obnovení hesla vyprší za ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . ' minut.')
            ->line('Pokud jste o obnovení hesla nežádali, není nutná žádná další akce.')
            ->salutation('S pozdravem, Tým ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
