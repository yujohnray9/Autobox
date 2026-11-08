<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Mailjet\Transport\MailjetTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

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
        Mail::extend('mailjet', function () {
            return (new MailjetTransportFactory())->create(
                new Dsn(
                    'mailjet+api',
                    'default',
                    config('services.mailjet.key'),
                    config('services.mailjet.secret')
                )
            );
        });

        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function ($notifiable, $token) {
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('AUTOBOX — Reset Administrator Password')
                ->view('emails.admin_password_reset', [
                    'resetUrl' => $resetUrl,
                    'user'     => $notifiable,
                    'expire'   => $expire,
                ]);
        });
    }
}
