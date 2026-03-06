<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MonitoringController;
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
    Route::get('/', function ($locale) {
        // 지원하지 않는 로케일이면 404 에러를 낼 수도 있고, 기본 언어로 처리할 수도 있습니다.
        if (!in_array($locale, ['ko', 'en', 'ja'])) {
            abort(404);
        }

        // 예시: PostController를 통하지 않고 직접 데이터를 가져오는 경우
        // 실제 프로젝트 구조에 맞게 컨트롤러를 호출하거나 데이터를 가져오세요.
        $posts = \App\Models\Post::with('photos')->get();

        return view('welcome', compact('posts'));
    })->name('home');

});

Route::prefix('api')->group(function () {
    Route::get('/photos/{photo}/reactions', [PostController::class, 'getReactions']);
    Route::post('/photos/{photo}/react', [PostController::class, 'toggleReaction']);
});

Route::get('/admin/logs/monitoring-dashboard', [MonitoringController::class, 'index'])->name('admin.monitoring');
