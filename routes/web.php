<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ValoMatchController;
Route::get('/', function () {
    return view('welcome');
});


Route::get('/valo-matches/create', [ValoMatchController::class, 'create'])
    ->name('valo_matches.create');

Route::post('/valo-matches', [ValoMatchController::class, 'store'])
    ->name('valo_matches.store');
Route::get('/valo-matches', [ValoMatchController::class, 'index'])
    ->name('valo_matches.index');