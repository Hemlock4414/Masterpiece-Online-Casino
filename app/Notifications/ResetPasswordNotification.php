<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
   public $token;

   public function __construct($token)
   {
       $this->token = $token;
   }

   public function via($notifiable)
   {
       return ['mail'];
   }

   public function toMail($notifiable)
   {
       $frontendUrl = config('app.url');
       return (new MailMessage)
           ->subject('Passwort zurücksetzen')
           ->line('Sie erhalten diese E-Mail, weil für Ihr Konto eine Anfrage zum Zurücksetzen des Passworts eingegangen ist.')
           ->action('Passwort zurücksetzen', $frontendUrl . '/passwort-neu-setzen?token=' . $this->token . '&email=' . $notifiable->getEmailForPasswordReset())
           ->line('Dieser Link zum Zurücksetzen des Passworts läuft in ' . config('auth.passwords.users.expire') . ' Minuten ab.')           ->line('Falls Sie keine Zurücksetzung Ihres Passworts angefordert haben, ist keine weitere Aktion erforderlich.');
   }
}