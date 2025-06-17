<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerificationCodeRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected AuthService $authService;

     public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
   {
     try {
                $result = $this->authService->register($request->validated());

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'user' => $result['user'],
                        'token' => $result['token']
                    ]
        
                ], 201);

        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->email,
                $request->password
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user_id' => $result['user_id'],
                    'temp_token' => $result['temp_token']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function verifyCode(VerificationCodeRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $result = $this->authService->verifyCode(
                $user,
                $request->code,
                $request->type
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user' => $result['user'],
                    'token' => $result['token']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function resendCode(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:email_verification,login_verification,password_reset'
        ]);

        try {
            $user = auth()->user();
            $sent = $this->authService->resendVerificationCode($user, $request->type);

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Code de vérification renvoyé'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du code'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function requestPasswordReset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        try {
            $sent = $this->authService->requestPasswordReset($request->email);

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Code de réinitialisation envoyé par email'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du code'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $reset = $this->authService->resetPassword(
                $request->email,
                $request->password,
                $request->code
            );

            if ($reset) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mot de passe réinitialisé avec succès'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

     public function logout(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $this->authService->logout($user);

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function me(Request $request): JsonResponse
    {
        $user = auth()->user()->load('companies.formeJuridique');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * Informations de l'utilisateur connecté
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
            'message' => 'Utilisateur récupéré avec succès'
        ]);
    }

    

    
       


}
