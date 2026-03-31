<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminBasicAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // .env 파일에서 설정한 관리자 아이디/비밀번호를 가져옵니다. 
        // (기본값: admin / secret1234)
        $expectedUsername = env('ADMIN_USERNAME', 'admin');
        $expectedPassword = env('ADMIN_PASSWORD', 'password');

        // 사용자가 입력한 정보와 일치하지 않으면 401 Unauthorized와 함께 Basic Auth 창을 띄웁니다.
        if ($request->getUser() !== $expectedUsername || $request->getPassword() !== $expectedPassword) {
            $headers = ['WWW-Authenticate' => 'Basic realm="KKYOUNG.PHOTO Admin Area"'];
            return response('관리자 권한이 필요합니다.', 401, $headers);
        }

        // 인증 성공 시 세션에 관리자 여부를 기록 (기존 컨트롤러 호환용)
        session(['is_admin' => true]);

        return $next($request);
    }
}