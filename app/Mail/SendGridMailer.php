<?php

namespace App\Mail;

use SendGrid\Mail\Mail as SendGridMail;
use Illuminate\Support\Facades\Log;

class SendGridMailer
{
    public static function send($to, $subject, $htmlContent)
    {
        try {
            // Get API key
            $apiKey = config('services.sendgrid.api_key');
            
            if (!$apiKey) {
                Log::error('SendGrid API key is not configured');
                return false;
            }

            // Create email
            $email = new SendGridMail();
            $email->setFrom(
                config('mail.from.address', 'salah-eddine.magueri@btpvillage.com'), 
                config('mail.from.name', 'Alaq Seal Vision')
            );
            $email->setSubject($subject);
            $email->addTo($to);
            $email->addContent("text/html", $htmlContent);

            // Create SendGrid client
            $sendgrid = new \SendGrid($apiKey);
            
            // SEND THE EMAIL (this was missing!)
            $response = $sendgrid->send($email);
            
            // Log response
            Log::info('SendGrid Response', [
                'status' => $response->statusCode(),
                'body' => $response->body(),
                'to' => $to,
                'subject' => $subject,
            ]);
            
            // Check if successful (2xx status codes = success)
            $success = $response->statusCode() >= 200 && $response->statusCode() < 300;
            
            if (!$success) {
                Log::error('SendGrid failed', [
                    'status' => $response->statusCode(),
                    'response' => $response->body(),
                ]);
            }
            
            return $success;
            
        } catch (\Exception $e) {
            Log::error('SendGrid Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}