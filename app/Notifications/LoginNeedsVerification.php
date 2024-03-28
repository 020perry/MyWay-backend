<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Twilio\Rest\Client;



class LoginNeedsVerification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
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
        return [TwilioChannel::class];
    }

    public function toTwilio($notifiable)
    {
        // Instantieer de Twilio client met je account SID en auth token uit .env
        $twilio = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

        // Gebruik Twilio's Verify service om een SMS te versturen
        $twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
            ->verifications
            ->create($notifiable->phone, "sms");

        // Omdat je nu de Verify service gebruikt, hoef je geen code te genereren en op te slaan.
        // De Verify service handelt de code en verificatie voor je af.
        return (new TwilioSmsMessage())
            ->content("A verification code is being sent to your phone.");
    }

//    public function toTwilio($notifiable)
//    {
//        $loginCode = rand(111111, 999999);
//
//        $notifiable->update([
//            'login_code' => $loginCode
//        ]);
//
//        return (new TwilioSmsMessage())
//            ->content("Your MyWay login code is {$loginCode}, don't share this with anyone!");
//    }
//    public function toTwilio($notifiable)
//    {
//        $twilio = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));
//        $twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
//            ->verifications
//            ->create($notifiable->phone, "sms");
//    }
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
