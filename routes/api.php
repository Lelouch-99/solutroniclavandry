<?php

use App\Http\Controllers\ActivationController;
use Illuminate\Support\Facades\Route;

Route::get('/controllers/{controllerId}/pending', [ActivationController::class, 'pending']);

Route::post('/activations/{activationId}/result', [ActivationController::class, 'result']);