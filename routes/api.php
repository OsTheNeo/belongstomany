<?php

use Ostheneo\Belongstomany\Http\Controllers\ResourceController;

Route::get('/{resource}/options/{relationship}/{optionsLabel}/{dependsOnValue?}/{dependsOnKey?}', [ResourceController::class, 'index']);
