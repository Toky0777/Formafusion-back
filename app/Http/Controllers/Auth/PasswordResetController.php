<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BrevoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    protected $brevoService;

    public function __construct(BrevoService $brevoService)
    {
        $this->brevoService = $brevoService;
    }

    /**
     * Envoyer l'email de réinitialisation
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();

        // Générer un token de réinitialisation
        $resetToken = Str::random(60);

        // Stocker le token et l'horodatage
        $user->remember_token = Hash::make($resetToken);
        $user->reset_token_created_at = Carbon::now();
        $user->save();

        // Créer le lien de réinitialisation
        $resetLink = env('FRONTEND_URL', 'https://mg.formafusion.io') . '/reset-password?token=' . $resetToken . '&email=' . urlencode($email);

        // Préparer les données pour le template
        $emailData = [
            'resetLink' => $resetLink,
            'email' => $email,
        ];

        // Rendre le template Blade
        $htmlContent = view('emails.password-reset', $emailData)->render();

        // Envoyer l'email via Brevo
        $subject = 'Réinitialisation de votre mot de passe FormaFusion';

        try {
            $this->brevoService->sendEmail($email, $subject, $htmlContent);

            return response()->json([
                'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email.'
            ], 200);
        } catch (\Exception $e) {
            // En cas d'erreur, nettoyer le token
            $user->remember_token = null;
            $user->reset_token_created_at = null;
            $user->save();

            return response()->json([
                'message' => 'Erreur lors de l\'envoi de l\'email.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Vérifier si un token existe
        if (!$user->remember_token) {
            return response()->json([
                'message' => 'Token invalide ou expiré.'
            ], 400);
        }

        // Vérifier si le token a expiré (60 minutes)
        if (
            !$user->reset_token_created_at ||
            Carbon::parse($user->reset_token_created_at)->addMinutes(60)->isPast()
        ) {

            // Nettoyer le token expiré
            $user->remember_token = null;
            $user->reset_token_created_at = null;
            $user->save();

            return response()->json([
                'message' => 'Le token a expiré.'
            ], 400);
        }

        // Vérifier le token
        if (!Hash::check($request->token, $user->remember_token)) {
            return response()->json([
                'message' => 'Token invalide.'
            ], 400);
        }

        // Mettre à jour le mot de passe et nettoyer le token
        $user->password = Hash::make($request->password);
        $user->remember_token = null;
        $user->reset_token_created_at = null;
        $user->save();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès.'
        ], 200);
    }

    /**
     * Vérifier la validité d'un token
     */
    public function verifyToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->remember_token) {
            return response()->json([
                'valid' => false,
                'message' => 'Token invalide ou expiré.'
            ], 400);
        }

        // Vérifier l'expiration
        if (
            !$user->reset_token_created_at ||
            Carbon::parse($user->reset_token_created_at)->addMinutes(60)->isPast()
        ) {

            // Nettoyer le token expiré
            $user->remember_token = null;
            $user->reset_token_created_at = null;
            $user->save();

            return response()->json([
                'valid' => false,
                'message' => 'Le token a expiré.'
            ], 400);
        }

        // Vérifier le token
        if (!Hash::check($request->token, $user->remember_token)) {
            return response()->json([
                'valid' => false,
                'message' => 'Token invalide.'
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Token valide.'
        ], 200);
    }
}
