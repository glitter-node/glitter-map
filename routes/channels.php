<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('places.map', function () {
    return true;
});
