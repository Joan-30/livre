<?php

namespace App\Notifications;

use App\Models\Livre;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NouvelleRecommandationNotification extends Notification
{
    use Queueable;

    private Livre $livre;
    private int $scoreAffinite;

    /**
     * Create a new notification instance.
     */
    public function __construct(Livre $livre, int $scoreAffinite)
    {
        $this->livre = $livre;
        $this->scoreAffinite = $scoreAffinite;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'livre_id'       => $this->livre->id,
            'titre'          => $this->livre->titre,
            'score_affinite' => $this->scoreAffinite,
            'message'        => "Le livre « {$this->livre->titre} » correspond à votre profil d'ambiance avec un score de {$this->scoreAffinite}% !",
        ];
    }
}
