<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status'  => 'running',
        'app'     => 'LiveChat API',
        'version' => 'v1.0',
    ]);
});
