<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\SendGridMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * POST /api/v1/password/email
     * Send reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Don't reveal if email exists
            return response()->json([
                'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé.'
            ]);
        }

        // Generate token
        $token = Str::random(60);

        // Store token (expires in 1 hour)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Create reset URL
        $resetUrl = env('FRONTEND_URL', 'http://localhost:5173') . "/reset-password?token={$token}&email={$request->email}";

        // Send email
        $htmlContent = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #3b82f6;'>Réinitialisation de mot de passe</h2>
                <p>Bonjour {$user->name},</p>
                <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton ci-dessous :</p>
                <a href='{$resetUrl}' style='display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 8px; margin: 20px 0;'>
                    Réinitialiser mon mot de passe
                </a>
                <p style='color: #666; font-size: 14px;'>Ce lien expire dans 1 heure.</p>
                <p style='color: #666; font-size: 14px;'>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;'>
                <p style='color: #999; font-size: 12px;'>© " . date('Y') . " Alaq Seal Vision. Tous droits réservés.</p>
            </div>
        ";

        try {
            $sent = SendGridMailer::send(
                $request->email,
                'Réinitialisation de votre mot de passe',
                $htmlContent
            );

            if (!$sent) {
                \Log::error('SendGrid send() returned false for: ' . $request->email);
                return response()->json([
                    'error' => 'Erreur lors de l\'envoi de l\'email'
                ], 500);
            }

            return response()->json([
                'message' => 'Un email de réinitialisation a été envoyé.'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('SendGrid Exception: ' . $e->getMessage());
            return response()->json([
                'error' => 'Exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/v1/password/reset
     * Reset password with token
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',      // lowercase
                'regex:/[A-Z]/',      // uppercase
                'regex:/[0-9]/',      // digit
                'regex:/[@$!%*#?&]/', // special char
            ],
        ]);

        // Find reset record
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return response()->json(['error' => 'Token invalide ou expiré'], 400);
        }

        // Check if token matches
        if (!Hash::check($request->token, $resetRecord->token)) {
            return response()->json(['error' => 'Token invalide'], 400);
        }

        // Check if token is expired (1 hour)
        if (now()->diffInHours($resetRecord->created_at) > 1) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['error' => 'Token expiré'], 400);
        }

        // Update password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
    }
}