<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. URL의 첫 번째 세그먼트를 가져옵니다. (예: /en/gallery -> 'en')
        $locale = $request->segment(1);

        // 2. 지원하는 언어 목록을 정의합니다.
        $supportedLocales = ['ko', 'en', 'ja'];

        // 3. 가져온 세그먼트가 지원하는 언어 목록에 있는지 확인합니다.
        if (in_array($locale, $supportedLocales)) {
            // 지원하는 언어라면 애플리케이션 로케일을 설정합니다.
            App::setLocale($locale);
        } else {
            // 지원하지 않는 언어이거나 생략된 경우 기본 언어(config/app.php의 locale)를 사용합니다.
            // 필요에 따라 기본 언어 URL로 리다이렉트 시킬 수도 있습니다.
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
