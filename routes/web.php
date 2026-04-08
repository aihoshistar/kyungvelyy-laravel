<!-- routes/web.php -->
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\AdminManageController;
use App\Http\Controllers\AdminCollageController;
use App\Http\Controllers\AdminController; // 💡 상단에 추가

Route::get('/', function () {
    return redirect('/ko');
});

// 💡 where 조건 추가
Route::prefix('{locale}')->where(['locale' => 'ko|en|ja'])->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('home');
});

Route::prefix('api')->group(function () {
    Route::get('/photos/{photo}/reactions', [PostController::class, 'getReactions']);
    Route::post('/photos/{photo}/react', [PostController::class, 'toggleReaction']);
});

// 💡 web 미들웨어 제거
Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/logs/monitoring-dashboard', [MonitoringController::class, 'index'])->name('admin.monitoring');

    // 💡 깔끔해진 클래스 호출
    Route::get('/upload', [AdminController::class, 'uploadView']);
    Route::post('/upload-process', [AdminController::class, 'processUpload']);

    Route::get('/manage', [AdminManageController::class, 'index']);
    Route::get('/api/posts', [AdminManageController::class, 'getPosts']);
    Route::put('/api/posts/{id}', [AdminManageController::class, 'updatePost']);
    Route::put('/api/photos/{id}', [AdminManageController::class, 'updatePhoto']);
    Route::delete('/api/posts/{id}', [AdminManageController::class, 'destroyPost']);

    Route::get('/collage', [AdminCollageController::class, 'index'])->name('admin.collage.index');
    Route::get('/collage/api/posts', [AdminCollageController::class, 'searchPosts']);
    Route::get('/collage/api/posts/{id}/photos', [AdminCollageController::class, 'getPhotos']);

    Route::get('/frame', function() { return view('admin.frame'); })->name('admin.frame.index');
    Route::get('/picture-control', function() { return view('admin.picture-control'); })->name('admin.picture-control.index');
});
