<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KKYOUNG.PHOTO | @yield('title', 'ADMIN')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/exif-js"></script>
    <style>
        body { font-family: 'Pretendard', sans-serif; background-color: #050505; color: #e5e5e5; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        @yield('styles')
    </style>
</head>
<body class="min-h-screen flex flex-col h-screen overflow-hidden">

    <!-- Navbar -->
    <nav class="border-b border-white/10 bg-black px-6 py-4 flex justify-between items-center z-10 shrink-0">
        <div class="flex items-center gap-6">
            <h1 class="text-xl font-bold tracking-widest text-white">@yield('header_title', 'ADMIN AREA')</h1>
            <div class="hidden md:flex space-x-4 border-l border-white/20 pl-6">
                <a href="/admin/upload" class="text-sm font-medium transition {{ request()->is('admin/upload') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}">업로드</a>
                <a href="/admin/manage" class="text-sm font-medium transition {{ request()->is('admin/manage') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}">메타데이터 관리</a>
                <a href="/admin/collage" class="text-sm font-medium transition {{ request()->is('admin/collage') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}">콜라주 스튜디오</a>
                <a href="/admin/frame" class="text-sm font-medium transition {{ request()->is('admin/frame') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}">프레임 스튜디오</a>
                <a href="/admin/logs/monitoring-dashboard" class="text-sm font-medium transition {{ request()->is('admin/logs/monitoring-dashboard') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}">시스템 모니터링</a>
            </div>
        </div>
        <a href="{{ url('/') }}" class="text-sm font-medium text-gray-400 hover:text-white transition">사이트로 돌아가기</a>
    </nav>

    <!-- Main Content -->
    @yield('content')

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-10 right-10 transform bg-white text-black px-6 py-3 rounded-full shadow-2xl font-bold text-sm z-[100] transition-all duration-300 opacity-0 translate-y-10 pointer-events-none">
        성공적으로 처리되었습니다!
    </div>

    <script>
        window.showToast = (msg, isError = false) => {
            const toast = document.getElementById('toast');
            if(!toast) return;
            toast.textContent = msg;
            toast.className = `fixed bottom-10 right-10 transform px-6 py-3 rounded-full shadow-2xl font-bold text-sm z-[100] transition-all duration-300 ${isError ? 'bg-red-500 text-white' : 'bg-white text-black'}`;
            toast.classList.remove('opacity-0', 'translate-y-10');
            setTimeout(() => toast.classList.add('opacity-0', 'translate-y-10'), 3000);
        };
    </script>
    @yield('scripts')
</body>
</html>