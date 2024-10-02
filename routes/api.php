<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NatPunchingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Create the client in state
Route::post('/register', [NatPunchingController::class, 'register']);

// Pull client from the state
Route::get('/peer-info/{client_id}', [NatPunchingController::class, 'getPeerInfo']);

// Handle the client sending in a heartbeat
Route::post('/heartbeat', [NatPunchingController::class, 'heartbeat']);
