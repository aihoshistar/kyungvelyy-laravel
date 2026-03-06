<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [PostController::class, 'index'])->name('home');

Route::prefix('api')->group(function () {
    // 1. 반응 데이터 가져오기 (GET)
    Route::get('/photos/{photo}/reactions', [PostController::class, 'getReactions']);
    // 2. 반응 토글하기 (POST) - (기존에 작성하신 부분)
    Route::post('/photos/{photo}/react', [PostController::class, 'toggleReaction']);
});
