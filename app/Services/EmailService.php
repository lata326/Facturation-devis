<?php

namespace App\Services;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public function sendVerificationCode(User $user, string $type): bool
    {
        try {
            $verificationCode = VerificationCode::createForUser($user->user_id, $type);
            
            $subject = $this->getSubjectByType($type);
            $template = $this->getTemplateByType($type);
            
            Mail::send($template, [
                'user' => $user,
                'code' => $verificationCode->code,
                'expiresAt' => $verificationCode->expires_at->format('H:i'),
            ], function ($message) use ($user, $subject) {
                $message->to($user->email, $user->full_name)
                       ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur envoi email: ' . $e->getMessage());
            return false;
        }
    }

    private function getSubjectByType(string $type): string
    {
        return match($type) {
            'email_verification' => 'Vérification de votre compte',
            'login_verification' => 'Code de connexion',
            'password_reset' => 'Réinitialisation de votre mot de passe',
            default => 'Code de vérification'
        };
    }

    private function getTemplateByType(string $type): string
    {
        return match($type) {
            'email_verification' => 'emails.verification',
            'login_verification' => 'emails.login-verification',
            'password_reset' => 'emails.password-reset',
            default => 'emails.default'
        };
    }
}