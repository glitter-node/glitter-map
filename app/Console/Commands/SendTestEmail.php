<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTestEmail extends Command
{
    protected $signature = 'mail:test';

    protected $description = 'Send a test email using the configured SMTP mailer';

    public function handle(): int
    {
        $required = [
            'mail.default' => config('mail.default'),
            'mail.mailers.smtp.host' => config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.username' => config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => config('mail.mailers.smtp.password'),
            'mail.from.address' => config('mail.from.address'),
        ];

        if ($required['mail.default'] !== 'smtp') {
            $this->error('MAIL_MAILER is not set to smtp.');

            return self::FAILURE;
        }

        foreach ($required as $key => $value) {
            if (blank($value)) {
                $this->error("Missing required mail configuration: {$key}");

                return self::FAILURE;
            }
        }

        try {
            Mail::raw('SMTP configuration is working.', function ($message): void {
                $message
                    ->to('gim@glitter.kr')
                    ->subject('SMTP Test');
            });

            $this->info('Test email sent successfully to gim@glitter.kr.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Test email failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
