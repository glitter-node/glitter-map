<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Google\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();
        $user = $this->resolveGoogleUser($googleUser->getEmail(), $googleUser->getName(), true);

        Auth::login($user);

        request()->session()->regenerate();

        return redirect()->route('places.index');
    }

    public function oneTap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'credential' => ['required', 'string'],
        ]);

        $client = new Client([
            'client_id' => config('services.google.client_id'),
        ]);

        $payload = $client->verifyIdToken($validated['credential']);

        abort_if(! is_array($payload), 401, 'Invalid Google credential.');
        abort_if(blank($payload['email'] ?? null), 422, 'Google account email is required.');
        abort_if(($payload['email_verified'] ?? false) !== true, 422, 'Google account email must be verified.');
        abort_if(($payload['exp'] ?? 0) < now()->timestamp, 401, 'Google credential has expired.');

        $user = $this->resolveGoogleUser(
            $payload['email'],
            $payload['name'] ?? null,
            true,
        );

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['success' => true]);
    }

    protected function resolveGoogleUser(?string $email, ?string $name, bool $emailVerified): User
    {
        abort_if(blank($email), 422, 'Google account email is required.');
        abort_if(! $emailVerified, 422, 'Google account email must be verified.');

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            return $user;
        }

        return User::query()->create([
            'email' => $email,
            'name' => $name ?: Str::before($email, '@'),
            'password' => Hash::make(Str::password(24)),
            'email_verified_at' => now(),
        ]);
    }
}
