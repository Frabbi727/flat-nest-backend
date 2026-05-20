<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'));

Route::get('/admin', fn () => response()->file(public_path('admin-panel/index.html')));

// Catch-all: redirect any /admin/* to /admin (SPA handles routing client-side)
Route::get('/admin/{any}', fn () => redirect('/admin'))->where('any', '.*');
