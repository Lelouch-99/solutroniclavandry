<?php

use App\Http\Controllers\ActivationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ActivationController::class, 'dashboard'])->name('dashboard');

Route::post('/activations', [ActivationController::class, 'storeFromWeb'])
    ->name('activations.store');

Route::post('/reset-processing', [ActivationController::class, 'resetProcessing'])
    ->name('activations.reset_processing');