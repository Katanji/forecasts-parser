<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/admin.php';

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-log', function () {
    info('This is a test log message');
    return 'Log written!';
});
