<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/neststay'));

// Public legal pages
Route::get('/privacy-policy', fn () => view('legal.privacy-policy'))->name('privacy-policy');
Route::get('/terms-and-conditions', fn () => view('legal.terms-and-conditions'))->name('terms-and-conditions');
Route::get('/delete-account', fn () => view('legal.delete-account'))->name('delete-account');

Route::get('/admin', fn () => response()->file(public_path('admin-panel/index.html')));

// Catch-all: redirect any /admin/* to /admin (SPA handles routing client-side)
Route::get('/admin/{any}', fn () => redirect('/admin'))->where('any', '.*');

// NestStay web UI
Route::get('/neststay', fn () => response()->file(public_path('neststay/index.html')));
Route::get('/neststay/{any}', fn () => response()->file(public_path('neststay/index.html')))->where('any', '.*');
