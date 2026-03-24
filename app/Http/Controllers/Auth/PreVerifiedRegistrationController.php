<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PreVerifiedEmail;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PreVerifiedRegistrationController extends Controller
{
    public function showEmailRequestForm(): View
    {
        return view('auth.email-request');
    }

    public function sendVerificationEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
        ]);

        $record = PreVerifiedEmail::query()->updateOrCreate(
            ['email' => $validated['email']],
            [
                'token' => Str::random(64),
                'verified_at' => null,
                'expires_at' => now()->addMinutes(30),
            ],
        );

        $verificationUrl = route('auth.email.verify', $record->token);

        Mail::raw(
            "Click the link to continue your registration:\n\n{$verificationUrl}\n\nThis link expires in 30 minutes.",
            function ($message) use ($record): void {
                $message
                    ->to($record->email)
                    ->subject('Verify your email');
            }
        );

        return back()->with('success', 'Verification link sent. Check your inbox.');
    }

    public function verify(string $token, Request $request): RedirectResponse
    {
        $record = PreVerifiedEmail::query()->where('token', $token)->first();

        if (! $record || $record->expires_at->isPast()) {
            return redirect()
                ->route('auth.email.request')
                ->with('error', 'This verification link is invalid or expired.');
        }

        if (! is_null($record->verified_at)) {
            return redirect()
                ->route('auth.email.request')
                ->with('error', 'This verification link has already been used.');
        }

        if (User::query()->where('email', $record->email)->exists()) {
            return redirect()
                ->route('auth.email.request')
                ->with('error', 'This email is already registered.');
        }

        $record->forceFill([
            'verified_at' => now(),
        ])->save();

        $request->session()->put('verified_email', $record->email);
        $request->session()->put('verified_email_token', $record->token);

        return redirect()
            ->route('auth.register.show')
            ->with('success', 'Email verified. Complete your registration.');
    }

    public function showRegistrationForm(Request $request): View
    {
        return view('auth.register', [
            'email' => $request->session()->get('verified_email'),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $email = $request->session()->get('verified_email');
        $token = $request->session()->get('verified_email_token');

        $record = PreVerifiedEmail::query()
            ->where('email', $email)
            ->where('token', $token)
            ->whereNotNull('verified_at')
            ->firstOrFail();

        if ($record->expires_at->isPast()) {
            $request->session()->forget(['verified_email', 'verified_email_token']);

            return redirect()
                ->route('auth.email.request')
                ->with('error', 'Your verification expired. Request a new email link.');
        }

        if (User::query()->where('email', $email)->exists()) {
            $request->session()->forget(['verified_email', 'verified_email_token']);
            $record->delete();

            return redirect()
                ->route('auth.email.request')
                ->with('error', 'This email is already registered.');
        }

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make(Str::password(24)),
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        $request->session()->forget(['verified_email', 'verified_email_token']);
        $record->delete();

        $request->session()->regenerate();

        return redirect()
            ->route('restaurants.index')
            ->with('success', 'Registration completed successfully.');
    }
}
