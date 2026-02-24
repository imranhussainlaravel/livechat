<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — health check only
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'status'  => 'running',
        'app'     => 'LiveChat API',
        'version' => 'v1',
    ]);
});
