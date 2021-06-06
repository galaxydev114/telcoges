<?php
namespace Crater\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Illuminate\Auth\Notifications\ResetPassword;

class MailResetPasswordNotification extends ResetPassword
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        parent::__construct($token);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $link = url( "/reset-password/". $this->token );

        return ( new MailMessage )
            ->subject('Notificación de restablecimiento de contraseña')
            ->greeting('Hola!')
            ->line("Hola! Recibió este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para su cuenta." )
            ->action('Restablecer la contraseña', $link )
            ->line("Este enlace de restablecimiento de contraseña caducará en ".config('auth.passwords.users.expire')." minutos" )
            ->line("Si no solicitó un restablecimiento de contraseña, no es necesario realizar ninguna otra acción." )
            ->salutation('Saludos, '.config('app.name'));

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
