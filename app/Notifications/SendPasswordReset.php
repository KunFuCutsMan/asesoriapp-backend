<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;

class SendPasswordReset extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     * 
     * @var string $code    Código para reiniciar la contraseña. Debe de ser 6 dígitos
     */
    public function __construct(private $code)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['vonage'];
    }

    /**
     * Get the Vonage / SMS representation of the notification.
     * 
     * @param Estudiante $notifiable
     */
    public function toVonage(object $notifiable): VonageMessage
    {
        $nombre = $notifiable->nombre;
        $codigo = $this->code;

        $message = "¡Hola $nombre! Tu código de verificación para reiniciar tu contraseña es $codigo. Expira en 10 minutos";

        return (new VonageMessage)
            ->content($message);
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
