<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\ValoMatchController;

Route::get('/valo-matches/create', [ValoMatchController::class, 'create'])
    ->name('valo_matches.create');

Route::post('/valo-matches', [ValoMatchController::class, 'store'])
    ->name('valo_matches.store');