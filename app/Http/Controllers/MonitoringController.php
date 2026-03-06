<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MonitoringController extends Controller
{
    /**
     * 로그 파일을 파싱하여 대시보드 뷰를 반환합니다.
     */
    public function index(Request $request)
    {
        // 1. 보안 체크 (간이 비밀번호 설정 권장)
        // .env에 MONITORING_PASSWORD를 설정하여 접근을 제한할 수 있습니다.
        $password = config('services.monitoring.password');
        if ($password && $request->get('key') !== $password) {
            abort(403, 'Unauthorized access.');
        }

        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logPath)) {
            // 파일을 읽어서 역순으로 정렬 (최신 로그가 위로)
            $fileContent = File::get($logPath);
            $rawLogs = explode("\n", trim($fileContent));

            // 최근 100줄만 추출하여 파싱
            $latestLogs = array_reverse(array_slice($rawLogs, -100));

            foreach ($latestLogs as $line) {
                if (empty($line)) continue;

                // 로그 레벨 추출 (ERROR, INFO, WARNING 등)
                preg_match('/^\[(?P<date>.*)\] (?P<env>\w+)\.(?P<level>\w+): (?P<message>.*)/', $line, $matches);

                $logs[] = [
                    'timestamp' => $matches['date'] ?? '-',
                    'level'     => $matches['level'] ?? 'DEBUG',
                    'message'   => $matches['message'] ?? $line,
                    'class'     => $this->getLevelClass($matches['level'] ?? ''),
                ];
            }
        }

        return view('monitoring', compact('logs'));
    }

    /**
     * 로그 레벨에 따른 CSS 클래스 반환
     */
    private function getLevelClass($level)
    {
        return match (strtoupper($level)) {
            'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'text-red-400 border-red-900/30 bg-red-900/10',
            'WARNING' => 'text-yellow-400 border-yellow-900/30 bg-yellow-900/10',
            'INFO'    => 'text-blue-400 border-blue-900/30 bg-blue-900/10',
            default   => 'text-gray-400 border-gray-800 bg-gray-900/20',
        };
    }
}
