<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('restaurants.map', function () {
    return true;
});
