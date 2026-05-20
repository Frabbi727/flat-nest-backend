<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'));

// Public legal pages
Route::get('/privacy-policy', fn () => view('legal.privacy-policy'))->name('privacy-policy');
Route::get('/terms-and-conditions', fn () => view('legal.terms-and-conditions'))->name('terms-and-conditions');

Route::get('/admin', fn () => response()->file(public_path('admin-panel/index.html')));

// Catch-all: redirect any /admin/* to /admin (SPA handles routing client-side)
Route::get('/admin/{any}', fn () => redirect('/admin'))->where('any', '.*');
