<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/api/documentation', function () {
    return redirect('/api/documentation');
});


require __DIR__.'/auth.php';
