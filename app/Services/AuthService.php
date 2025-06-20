<?php

namespace App\Services;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthService
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function register(array $data): array
    {
        $user = User::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telephone' => $data['telephone'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Envoyer le code de vérification email
        $this->emailService->sendVerificationCode($user, 'email_verification');

        return [
            'user' => $user,
            'token' => $token,
            'message' => 'Inscription réussie. '
        ];
    }

    public function login(string $email, string $password): array
    {
        
        $user = User::where('email', $email)->first();
         
        
        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Identifiants incorrects');
        }


        // Envoyer le code de vérification de connexion
        $this->emailService->sendVerificationCode($user, 'login_verification');

        return [
            'user_id' => $user->user_id,
            'message' => 'Code de vérification envoyé par email',
            'temp_token' => $user->createToken('temp_token', ['verify-login'])->plainTextToken
        ];
    }

    public function verifyCode(User $user, string $code, string $type): array
    {
        $verificationCode = VerificationCode::where('user_id', $user->user_id)
            ->where('code', $code)
            ->where('type', $type)
            ->where('used', false)
            ->first();

        if (!$verificationCode) {
            throw new \Exception('Code de vérification invalide');
        }

        if ($verificationCode->isExpired()) {
            throw new \Exception('Code de vérification expiré');
        }

        // Marquer le code comme utilisé
        $verificationCode->update(['used' => true]);

        // Actions selon le type
        switch ($type) {
            case 'email_verification':
                $user->update([
                    'email_verified_at' => now(),
                    'is_active' => true
                ]);
                break;
            
            case 'login_verification':
                $user->update(['derniere_connexion' => now()]);
                // Révoquer le token temporaire et créer un nouveau token complet
                $user->tokens()->where('name', 'temp_token')->delete();
                break;
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('companies'),
            'token' => $token,
            'message' => 'Vérification réussie'
        ];
    }

    public function resendVerificationCode(User $user, string $type): bool
    {
        return $this->emailService->sendVerificationCode($user, $type);
    }

    public function resetPassword(string $email, string $password, string $code): bool
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            throw new \Exception('Utilisateur non trouvé');
        }

        $verificationCode = VerificationCode::where('user_id', $user->user_id)
            ->where('code', $code)
            ->where('type', 'password_reset')
            ->where('used', false)
            ->first();

        if (!$verificationCode || $verificationCode->isExpired()) {
            throw new \Exception('Code de vérification invalide ou expiré');
        }

        $user->update(['password' => Hash::make($password)]);
        $verificationCode->update(['used' => true]);

        // Révoquer tous les tokens existants
        $user->tokens()->delete();

        return true;
    }

    public function requestPasswordReset(string $email): bool
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            throw new \Exception('Aucun compte associé à cet email');
        }

        return $this->emailService->sendVerificationCode($user, 'password_reset');
    }

    public function logout(User $user): bool
    {
        $user->currentAccessToken()->delete();
        return true;
    }
}