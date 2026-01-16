<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupGmailLessSecure extends Command
{
    protected $signature = 'setup:gmail-less-secure';
    protected $description = 'Setup Gmail with less secure apps (when app password not available)';

    public function handle()
    {
        $this->info('Setting up Gmail with Less Secure Apps...');
        $this->line('');
        
        $this->warn('⚠️  IMPORTANT: This method is less secure but works when App Password is not available');
        $this->line('');
        
        $this->info('Step 1: Enable Less Secure Apps');
        $this->line('1. Go to: https://myaccount.google.com/security');
        $this->line('2. Turn ON "Less secure app access"');
        $this->line('3. Confirm the change');
        $this->line('');
        
        $this->info('Step 2: Add these to your .env file:');
        $this->line('');
        
        $config = [
            'MAIL_MAILER=smtp',
            'MAIL_HOST=smtp.gmail.com',
            'MAIL_PORT=587',
            'MAIL_USERNAME=your-gmail@gmail.com',
            'MAIL_PASSWORD=your-regular-gmail-password',
            'MAIL_ENCRYPTION=tls',
            'MAIL_FROM_ADDRESS=your-gmail@gmail.com',
            'MAIL_FROM_NAME="Workflow Management System"'
        ];
        
        foreach ($config as $line) {
            $this->line($line);
        }
        
        $this->line('');
        $this->info('Step 3: Run these commands:');
        $this->line('php artisan config:cache');
        $this->line('php artisan test:notifications');
        $this->line('');
        
        $this->warn('⚠️  Security Note:');
        $this->line('- This method uses your regular Gmail password');
        $this->line('- It\'s less secure than App Password');
        $this->line('- Consider using Mailtrap for testing instead');
        $this->line('');
        
        $this->info('Alternative: Use Mailtrap for safe testing');
        $this->line('Sign up at: https://mailtrap.io');
        $this->line('Then run: php artisan setup:email mailtrap');
    }
}