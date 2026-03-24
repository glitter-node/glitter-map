<?php

namespace App\Http\Middleware;

use App\Models\PreVerifiedEmail;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailPreVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->session()->get('verified_email');
        $token = $request->session()->get('verified_email_token');

        if (blank($email) || blank($token)) {
            return redirect()
                ->route('auth.email.request')
                ->with('error', 'Verify your email before continuing to registration.');
        }

        $record = PreVerifiedEmail::query()
            ->where('email', $email)
            ->where('token', $token)
            ->whereNotNull('verified_at')
            ->first();

        if (! $record || $record->expires_at->isPast()) {
            $request->session()->forget(['verified_email', 'verified_email_token']);

            return redirect()
                ->route('auth.email.request')
                ->with('error', 'Your email verification session expired. Request a new link.');
        }

        return $next($request);
    }
}
