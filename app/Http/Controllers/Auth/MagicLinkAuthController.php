<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class MagicLinkAuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('restaurants.index');
        }

        return view('auth.login');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $this->normalizeEmail($validated['email']);

        $magicUrl = URL::temporarySignedRoute(
            'auth.magic',
            now()->addMinutes(15),
            ['email' => $email],
        );

        Mail::raw(
            "Use this secure access link to continue:\n\n{$magicUrl}\n\nThis link expires in 15 minutes.",
            function ($message) use ($email): void {
                $message
                    ->to($email)
                    ->subject('Your access link');
            }
        );

        return back()->with('success', 'If the address can receive messages, a secure access link is on the way.');
    }

    public function consumeLink(Request $request): RedirectResponse
    {
        $email = $this->normalizeEmail((string) $request->query('email'));

        abort_unless(filter_var($email, FILTER_VALIDATE_EMAIL), 403);

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $this->nameFromEmail($email),
                'email' => $email,
                'password' => Hash::make(Str::password(24)),
                'email_verified_at' => now(),
            ]);
        } elseif (is_null($user->email_verified_at)) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('restaurants.index')
            ->with('success', 'Access granted.');
    }

    protected function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    protected function nameFromEmail(string $email): string
    {
        $name = Str::of(Str::before($email, '@'))
            ->replace(['.', '_', '-'], ' ')
            ->squish()
            ->title()
            ->value();

        return $name !== '' ? $name : 'Restaurant Guest';
    }
}
