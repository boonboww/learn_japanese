<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlphabetController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\KanjiController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\UserVocabularyController;

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/refresh', [AuthController::class, 'refresh']);
Route::get('auth/me', [AuthController::class, 'me']);

Route::post('auth/vocabularies/{id}/bookmark', [UserVocabularyController::class, 'toggleBookmark']);
Route::post('auth/vocabularies/{id}/status', [UserVocabularyController::class, 'updateStatus']);

Route::get('alphabets', [AlphabetController::class, 'index']);
Route::get('vocabularies', [VocabularyController::class, 'index']);
Route::get('vocabularies/random', [VocabularyController::class, 'random']);
Route::get('vocabularies/{id}', [VocabularyController::class, 'show'])
    ->whereNumber('id');
Route::get('kanjis', [KanjiController::class, 'index']);
Route::get('kanjis/{id}', [KanjiController::class, 'show']);

Route::get('quizzes/dynamic', [QuizController::class, 'generateDynamicQuiz']);

