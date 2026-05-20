<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlphabetController;
use App\Http\Controllers\VocabularyController;

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/refresh', [AuthController::class, 'refresh']);
Route::get('auth/me', [AuthController::class, 'me']);

Route::get('alphabets', [AlphabetController::class, 'index']);
Route::get('vocabularies', [VocabularyController::class, 'index']);
Route::get('vocabularies/{id}', [VocabularyController::class, 'show']);

