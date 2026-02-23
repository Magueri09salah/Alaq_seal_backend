<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function googleRedirect()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function googleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if ($user) {
                // Update google_id if not set
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                    }
                    } else {
                        // Create new user
                        $user = User::create([
                            'name'      => $googleUser->getName(),
                            'email'     => $googleUser->getEmail(),
                            'google_id' => $googleUser->getId(),
                            'phone'     => '', // Will be updated later if needed
                            'password'  => null, // No password for Google users
                            ]);
                            }
                            
                            // Create token
                            $token = $user->createToken('auth_token')->plainTextToken;


            // Redirect to frontend with token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173/');
            return redirect()->away("{$frontendUrl}/auth/google/callback?token={$token}&user=" . urlencode(json_encode($user)));

        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173/');
            return redirect()->away("{$frontendUrl}/login?error=google_auth_failed");
        }
    }

    
}
