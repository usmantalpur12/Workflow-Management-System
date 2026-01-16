<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupEmailConfig extends Command
{
    protected $signature = 'setup:email {driver=log}';
    protected $description = 'Setup email configuration safely';

    public function handle()
    {
        $driver = $this->argument('driver');
        
        $this->info('Setting up email configuration...');
        
        switch ($driver) {
            case 'log':
                $this->setupLogDriver();
                break;
            case 'mailtrap':
                $this->setupMailtrapDriver();
                break;
            case 'gmail':
                $this->setupGmailDriver();
                break;
            default:
                $this->error('Invalid driver. Use: log, mailtrap, or gmail');
                return;
        }
        
        $this->info('Email configuration updated!');
        $this->info('Run: php artisan config:cache');
    }
    
    private function setupLogDriver()
    {
        $this->info('Setting up LOG driver (emails will be saved to log files)...');
        
        $config = [
            'MAIL_MAILER' => 'log',
            'MAIL_HOST' => '127.0.0.1',
            'MAIL_PORT' => '2525',
            'MAIL_USERNAME' => 'null',
            'MAIL_PASSWORD' => 'null',
            'MAIL_ENCRYPTION' => 'null',
            'MAIL_FROM_ADDRESS' => 'noreply@workflow-system.com',
            'MAIL_FROM_NAME' => 'Workflow Management System'
        ];
        
        $this->displayConfig($config);
        $this->info('✅ Safe for development - no real emails sent');
    }
    
    private function setupMailtrapDriver()
    {
        $this->info('Setting up MAILTRAP driver (safe email testing)...');
        
        $config = [
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'sandbox.smtp.mailtrap.io',
            'MAIL_PORT' => '2525',
            'MAIL_USERNAME' => 'your-mailtrap-username',
            'MAIL_PASSWORD' => 'your-mailtrap-password',
            'MAIL_ENCRYPTION' => 'tls',
            'MAIL_FROM_ADDRESS' => 'noreply@workflow-system.com',
            'MAIL_FROM_NAME' => 'Workflow Management System'
        ];
        
        $this->displayConfig($config);
        $this->info('📧 Sign up at https://mailtrap.io for credentials');
    }
    
    private function setupGmailDriver()
    {
        $this->info('Setting up GMAIL driver (production use)...');
        
        $config = [
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.gmail.com',
            'MAIL_PORT' => '587',
            'MAIL_USERNAME' => 'your-email@gmail.com',
            'MAIL_PASSWORD' => 'your-16-character-app-password',
            'MAIL_ENCRYPTION' => 'tls',
            'MAIL_FROM_ADDRESS' => 'your-email@gmail.com',
            'MAIL_FROM_NAME' => 'Workflow Management System'
        ];
        
        $this->displayConfig($config);
        $this->info('🔐 Generate App Password: https://myaccount.google.com/apppasswords');
        $this->warn('⚠️  Use App Password, NOT your regular Gmail password!');
    }
    
    private function displayConfig($config)
    {
        $this->info('Add these to your .env file:');
        $this->line('');
        
        foreach ($config as $key => $value) {
            $this->line("{$key}={$value}");
        }
        
        $this->line('');
    }
}