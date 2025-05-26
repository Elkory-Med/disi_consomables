<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('SOMELEC-DISI - Inscription en attente d\'approbation')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre inscription sur la plateforme SOMELEC-DISI a été effectuée avec succès.')
            ->line('Votre compte est actuellement en attente d\'approbation par l\'administrateur.')
            ->line('Vous recevrez une notification par email dès que votre compte sera approuvé.')
            ->line('Merci de votre patience.')
            ->salutation('Cordialement,')
            ->salutation('L\'équipe SOMELEC-DISI');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
