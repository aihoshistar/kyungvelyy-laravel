<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\AdminManageController;

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

Route::get('/', function () {
    return redirect('/ko');
});


Route::prefix('{locale}')->group(function () {

    // 메인 페이지 라우트
    // 여기서 DB 데이터를 가져와 welcome 뷰로 전달합니다. (기존 로직 유지)
    Route::get('/', [PostController::class, 'index'])->name('home');
});

Route::prefix('api')->group(function () {
    Route::get('/photos/{photo}/reactions', [PostController::class, 'getReactions']);
    Route::post('/photos/{photo}/react', [PostController::class, 'toggleReaction']);
});

Route::get('/admin/logs/monitoring-dashboard', [MonitoringController::class, 'index'])->name('admin.monitoring');
Route::get('/admin/upload', [\App\Http\Controllers\AdminController::class, 'uploadView']);
Route::post('/admin/upload-process', [\App\Http\Controllers\AdminController::class, 'processUpload']);

Route::get('/admin/manage', [AdminManageController::class, 'index']);
Route::get('/admin/api/posts', [AdminManageController::class, 'getPosts']);
Route::put('/admin/api/posts/{id}', [AdminManageController::class, 'updatePost']);
Route::put('/admin/api/photos/{id}', [AdminManageController::class, 'updatePhoto']);