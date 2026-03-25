<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class BroadcastTrace
{
    public static function log(string $stage, array $context = []): void
    {
        Log::info("[broadcast-trace] {$stage}", $context);
    }
}
