<?php

use App\Providers\AppServiceProvider;
use App\Providers\BroadcastTraceServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;

return [
    AppServiceProvider::class,
    BroadcastTraceServiceProvider::class,
    SocialiteServiceProvider::class,
];
