<?php

namespace App\Providers;

use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class BrevoMailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        app(MailManager::class)->extend('brevo', function (array $config) {
            $factory = new BrevoTransportFactory();

            return $factory->create(new Dsn(
                'brevo+api',
                'default',
                $config['key'],
            ));
        });
    }
}
