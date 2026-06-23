<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mail::extend('brevo', function (array $config) {
            return new BrevoApiTransport($config['key']);
        });

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:6500');
            
            return $frontendUrl . '/verify-email/' . $notifiable->uuid . '/' . sha1($notifiable->getEmailForVerification());
        });

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Verify Email Address')
                ->view('emails.custom-verify-email', ['url' => $url]);
        });
    }
}
