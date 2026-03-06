<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KKYOUNG.PHOTO | Park Kyoung Seo</title>

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script src="https://t1.kakaocdn.net/kakao_js_sdk/2.7.0/kakao.min.js"></script>

    <!-- 분리된 CSS와 JS 로드 -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="selection:bg-gray-300 selection:text-black dark:selection:bg-white dark:selection:text-black">

    <script>
        window.PORTFOLIO_DATA = {!! json_encode($posts ?? []) !!};
        window.KAKAO_MAP_API_KEY = '{{ config('services.kakao.map_key') }}';
        window.GOOGLE_MAP_API_KEY = '{{ config('services.google.map_key') }}';
        if (typeof Kakao !== 'undefined' && !Kakao.isInitialized() && window.KAKAO_MAP_API_KEY) {
            Kakao.init(window.KAKAO_MAP_API_KEY);
        }
    </script>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-40 backdrop-blur-xl theme-nav border-b theme-border transition-all duration-500" id="navbar">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="#" class="theme-text font-bold tracking-widest text-sm uppercase">KKYOUNG.PHOTO</a>
            <div class="flex items-center space-x-6 md:space-x-8 text-sm font-medium">
                <a href="#gallery" class="theme-text-muted hover:text-opacity-80 transition-colors hidden sm:block" data-i18n="navGallery">Gallery</a>
                <div class="flex space-x-3 text-xs theme-text-muted theme-surface px-3 py-1.5 rounded-full border theme-border">
                    <button class="lang-btn hover:text-current transition-colors" data-lang="ko">KO</button>
                    <span class="opacity-30">·</span>
                    <button class="lang-btn hover:text-current transition-colors" data-lang="en">EN</button>
                    <span class="opacity-30">·</span>
                    <button class="lang-btn hover:text-current transition-colors" data-lang="ja">JA</button>
                </div>
                <button id="theme-toggle" class="p-2 rounded-full theme-btn-hover theme-text transition-colors duration-300">
                    <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 pt-32 pb-24 relative min-h-screen">

        <!-- Profile Header -->
        <header class="mb-32 reveal" id="profile-header">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-24 items-start">
                <div class="lg:col-span-7">
                    <span class="bio-lead theme-text-muted font-bold text-xs mb-4 block uppercase tracking-widest" data-i18n="heroBioLead">Architecting Invisible Foundations</span>
                    <h1 class="text-6xl md:text-8xl font-bold theme-text mb-8 tracking-tighter transition-colors duration-500" data-i18n="heroName">Park Kyoung Seo</h1>
                    <h2 class="text-2xl md:text-4xl font-medium leading-tight theme-text mb-8" data-i18n="heroCatchphrase">보이지 않는 구조를 설계하고,<br>보이는 찰나의 균형을 기록합니다.</h2>
                    <p class="text-xl theme-text-muted font-light max-w-xl" data-i18n="heroRole">Blockchain Infrastructure Engineer & Documentary Photographer</p>
                </div>
                <div class="lg:col-span-5 space-y-6 text-base md:text-lg leading-relaxed theme-text-muted font-light border-l theme-border pl-8 lg:pl-12">
                    <p data-i18n="heroBioText1">분산 네트워크의 견고한 합의를 이끌어내는 인프라 엔지니어로서, 저는 무너지지 않는 시스템의 기반을 설계합니다. 보이지 않는 곳에서 노드 간의 질서를 구축하는 일은 저에게 단순한 직업 이상의 '구조적 완성'에 대한 탐구입니다.</p>
                    <p data-i18n="heroBioText2">모니터 밖으로 걸어 나올 때, 저의 시선은 도시의 복잡함 속에 숨겨진 정적과 빛의 조화를 향합니다. 시스템 설계에서의 치밀함은 카메라 렌즈 너머로 완벽한 수평과 수직의 균형을 찾는 미학적 집착으로 이어집니다.</p>
                    <p data-i18n="heroBioText3">기술은 세상을 지탱하는 골격이고, 예술은 그 공간을 채우는 빛입니다. 저는 이 두 세계의 접점에서 기록하는 사람으로 살아가고 있습니다.</p>
                    <div class="pt-8 flex gap-8">
                        <div><span class="block text-xs font-bold theme-text uppercase tracking-widest mb-1">Expertise</span><span class="text-sm">Blockchain Infra</span></div>
                        <div><span class="block text-xs font-bold theme-text uppercase tracking-widest mb-1">Based in</span><span class="text-sm">Seoul, Korea</span></div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Gallery Controls -->
        <div id="gallery-controls" class="flex flex-col md:flex-row md:items-end justify-between mb-12 space-y-6 md:space-y-0 reveal">
            <div class="flex items-center space-x-6">
                <div>
                    <h3 class="text-xs uppercase tracking-[0.2em] theme-text-muted font-semibold mb-2">Selected Works</h3>
                    <h2 class="text-3xl font-bold theme-text tracking-tight" id="section-title" data-i18n="galleryTitle">Photography</h2>
                </div>
                <div class="bg-surface border theme-border rounded-full p-1 flex shadow-sm">
                    <button id="btn-grid-view" class="p-2 rounded-full bg-black text-white dark:bg-white dark:text-black shadow-sm transition-all" onclick="window.Portfolio.switchView('grid')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </button>
                    <button id="btn-map-view" class="p-2 rounded-full theme-text hover:bg-black/10 dark:hover:bg-white/10 transition-all" onclick="window.Portfolio.switchView('map')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    </button>
                </div>
            </div>
            <div class="flex space-x-2 overflow-x-auto pb-2 md:pb-0 hide-scrollbar" id="category-filters">
                <button class="filter-btn active px-5 py-2 rounded-full text-sm font-semibold theme-filter-active border" data-category="all" data-i18n="catAll">전체</button>
                <button class="filter-btn px-5 py-2 rounded-full text-sm font-medium theme-filter-inactive border" data-category="Landscape" data-i18n="catLandscape">Landscape</button>
                <button class="filter-btn px-5 py-2 rounded-full text-sm font-medium theme-filter-inactive border" data-category="Cityscape" data-i18n="catCityscape">Cityscape</button>
            </div>
        </div>

        <div class="relative w-full min-h-[400px]">
            <div id="grid-container" class="view-transition columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6 opacity-100"></div>
            <div id="map-container" class="view-transition hidden w-full h-[75vh] rounded-3xl overflow-hidden shadow-2xl opacity-0 border theme-border">
                <div id="map-canvas" class="w-full h-full bg-gray-100 dark:bg-gray-900"></div>
                <div id="map-loader" class="absolute inset-0 bg-black/50 backdrop-blur flex items-center justify-center text-white hidden z-10">
                    <div class="flex flex-col items-center">
                        <svg class="animate-spin h-8 w-8 mb-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span class="text-xs font-bold tracking-widest">LOADING MAP...</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Photo Modal -->
    <div id="photo-modal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm overflow-hidden opacity-0 flex items-center justify-center select-none transition-opacity duration-500">
        <div id="modal-container" class="relative w-[95%] max-w-7xl h-[85vh] md:h-[80vh] theme-surface rounded-3xl shadow-2xl overflow-hidden flex flex-col transition-transform duration-500 scale-95">
            <div class="w-full flex justify-between items-center px-6 py-5 border-b theme-border bg-opacity-50 z-[60]">
                <div class="flex flex-col">
                    <span id="modal-indicator" class="text-[10px] font-bold tracking-[0.3em] theme-text-muted uppercase mb-1">01 / 05</span>
                    <div id="modal-title-area" class="theme-text font-medium transition-all duration-500 truncate max-w-[250px] md:max-w-md"></div>
                </div>
                <button onclick="window.Portfolio.closeModal()" class="p-2 theme-text hover:bg-black/10 dark:hover:bg-white/10 rounded-full cursor-pointer transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="flex-grow flex flex-col md:flex-row items-stretch overflow-hidden relative">
                <button onclick="window.Portfolio.prevPhoto()" class="nav-btn absolute left-4 top-1/2 -translate-y-1/2 p-3 rounded-full theme-text hidden md:flex z-[70] items-center justify-center transition-all shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button onclick="window.Portfolio.nextPhoto()" class="nav-btn absolute right-4 top-1/2 -translate-y-1/2 p-3 rounded-full theme-text hidden md:flex z-[70] items-center justify-center transition-all shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                <div id="modal-slider-content" class="w-full h-full flex flex-col md:flex-row items-stretch"></div>
            </div>
            <div class="absolute bottom-6 w-full flex justify-center md:hidden pointer-events-none">
                <div class="flex space-x-2" id="modal-dots"></div>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-white text-black px-6 py-3 rounded-full shadow-2xl font-medium text-sm z-[100] transition-all duration-500 opacity-0 translate-y-10 pointer-events-none"></div>

</body>
</html>
