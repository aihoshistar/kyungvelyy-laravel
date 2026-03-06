<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="selection:bg-gray-300 selection:text-black dark:selection:bg-white dark:selection:text-black">

    <script>
        window.PORTFOLIO_DATA = {!! json_encode($posts ?? []) !!};
        window.KAKAO_MAP_API_KEY = '{{ config('services.kakao.map_key') }}';
        window.GOOGLE_MAP_API_KEY = '{{ config('services.google.map_key') }}';

        window.I18N = {
            ko: @json(__('portfolio', [], 'ko')),
            en: @json(__('portfolio', [], 'en')),
            ja: @json(__('portfolio', [], 'ja'))
        };
        window.CURRENT_LOCALE = '{{ app()->getLocale() }}';

        if (typeof Kakao !== 'undefined' && !Kakao.isInitialized() && window.KAKAO_MAP_API_KEY) {
            Kakao.init(window.KAKAO_MAP_API_KEY);
        }
    </script>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-40 backdrop-blur-xl theme-nav border-b theme-border transition-all duration-500" id="navbar">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="#" class="theme-text font-bold tracking-widest text-sm uppercase">KKYOUNG.PHOTO</a>
            <div class="flex items-center space-x-6 md:space-x-8 text-sm font-medium">
                <a href="#gallery" class="theme-text-muted hover:text-opacity-80 transition-colors hidden sm:block" data-i18n="navGallery">{{ __('portfolio.navGallery') }}</a>
                <div class="flex space-x-3 text-xs theme-text-muted theme-surface px-3 py-1.5 rounded-full border theme-border">
                    <a href="/ko" class="lang-btn hover:text-current transition-colors {{ app()->getLocale() == 'ko' ? 'theme-text font-bold' : '' }}">KO</a>
                    <span class="opacity-30">·</span>
                    <a href="/en" class="lang-btn hover:text-current transition-colors {{ app()->getLocale() == 'en' ? 'theme-text font-bold' : '' }}">EN</a>
                    <span class="opacity-30">·</span>
                    <a href="/ja" class="lang-btn hover:text-current transition-colors {{ app()->getLocale() == 'ja' ? 'theme-text font-bold' : '' }}">JA</a>
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
                    <div class="flex items-center gap-5 mb-6">
                        <img src="https://photo.kkyungvelyy.com/photo/profile.jpg" alt="Park Kyoung Seo" class="w-24 h-24 md:w-32 md:h-32 rounded-full object-cover shadow-lg border theme-border">
                        <span class="bio-lead theme-text-muted font-bold text-sm md:text-base block uppercase tracking-widest" data-i18n="heroBioLead">{{ __('portfolio.heroBioLead') }}</span>
                    </div>
                    <h1 class="text-6xl md:text-8xl font-bold theme-text mb-8 tracking-tighter transition-colors duration-500" data-i18n="heroName">{{ __('portfolio.heroName') }}</h1>
                    <h2 class="text-2xl md:text-4xl font-medium leading-tight theme-text mb-8" data-i18n="heroCatchphrase">{!! __('portfolio.heroCatchphrase') !!}</h2>
                    <p class="text-xl theme-text-muted font-light max-w-xl" data-i18n="heroRole">{{ __('portfolio.heroRole') }}</p>
                </div>
                <div class="lg:col-span-5 space-y-6 text-base md:text-lg leading-relaxed theme-text-muted font-light border-l theme-border pl-8 lg:pl-12">
                    <p data-i18n="heroBioText1">{{ __('portfolio.heroBioText1') }}</p>
                    <p data-i18n="heroBioText2">{{ __('portfolio.heroBioText2') }}</p>
                    <p data-i18n="heroBioText3">{{ __('portfolio.heroBioText3') }}</p>

                    <div class="pt-8 flex flex-wrap gap-x-10 gap-y-6 border-t theme-border mt-8">
                        <div><span class="block text-xs md:text-sm font-bold theme-text uppercase tracking-widest mb-1.5" data-i18n="expertise">Expertise</span><span class="text-base font-medium">Blockchain Infra</span></div>
                        <div><span class="block text-xs md:text-sm font-bold theme-text uppercase tracking-widest mb-1.5" data-i18n="basedIn">Based in</span><span class="text-base font-medium">Seoul, Korea</span></div>
                        <div>
                            <span class="block text-xs md:text-sm font-bold theme-text uppercase tracking-widest mb-2.5">Connect</span>
                            <div class="flex items-center gap-4 text-sm font-medium">
                                <a href="https://github.com/aihoshistar" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-2 px-3 py-1.5 rounded-full border theme-border theme-surface hover:border-gray-400 transition-all duration-300 shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4 opacity-70 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path></svg>
                                    <span>GitHub</span>
                                </a>
                                <a href="https://www.instagram.com/kkyoung.photo" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-2 px-3 py-1.5 rounded-full border theme-border theme-surface hover:border-gray-400 transition-all duration-300 shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4 opacity-70 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"></path></svg>
                                    <span>Instagram</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2">
                        <span class="block text-xs md:text-sm font-bold theme-text uppercase tracking-widest mb-3">Projects & Websites</span>
                        <ul class="space-y-3">
                            <li>
                                <a href="https://ai-agent-photo-sky.vercel.app" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 text-sm font-medium hover:theme-text transition-colors w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-40 group-hover:opacity-100 transition-opacity"></span>
                                    AI Agent Photo Sky
                                    <svg class="w-3.5 h-3.5 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Gallery & Advanced Controls -->
        <div id="gallery-controls" class="flex flex-col mb-10 space-y-5 reveal">
            <div class="flex flex-col md:flex-row md:items-end justify-between space-y-4 md:space-y-0">
                <div class="flex items-center space-x-6">
                    <div>
                        <h3 class="text-xs uppercase tracking-[0.2em] theme-text-muted font-semibold mb-2">Selected Works</h3>
                        <h2 class="text-3xl font-bold theme-text tracking-tight transition-colors" id="section-title" data-i18n="galleryTitle">{{ __('portfolio.galleryTitle') }}</h2>
                    </div>
                    <div class="bg-surface border theme-border rounded-full p-1 flex shadow-sm">
                        <button id="btn-grid-view" class="p-2 rounded-full transition-all view-toggle-active" onclick="window.Portfolio.switchView('grid')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </button>
                        <button id="btn-map-view" class="p-2 rounded-full transition-all view-toggle-inactive" onclick="window.Portfolio.switchView('map')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- 필터 통합 바 (카테고리 + 고급 드롭다운) -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex space-x-2 overflow-x-auto pb-2 md:pb-0 hide-scrollbar" id="category-filters">
                        <button class="filter-btn active px-5 py-2 rounded-full text-sm font-semibold theme-filter-active border transition-colors" data-category="all" data-i18n="catAll">{{ __('portfolio.catAll') }}</button>
                        <button class="filter-btn px-5 py-2 rounded-full text-sm font-medium theme-filter-inactive border transition-colors" data-category="Landscape" data-i18n="catLandscape">{{ __('portfolio.catLandscape') }}</button>
                        <button class="filter-btn px-5 py-2 rounded-full text-sm font-medium theme-filter-inactive border transition-colors" data-category="Cityscape" data-i18n="catCityscape">{{ __('portfolio.catCityscape') }}</button>
                    </div>

                    <div class="hidden md:block w-px h-4 bg-gray-300 dark:bg-gray-700"></div>

                    <div class="flex space-x-2">
                        <div class="relative">
                            <select id="country-filter" class="appearance-none bg-surface border theme-border rounded-full pl-5 pr-10 py-2 text-sm font-medium theme-text focus:outline-none focus:ring-1 focus:ring-gray-400 transition-colors cursor-pointer">
                                <option value="all" data-i18n="filterAllCountries">모든 국가</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 theme-text-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        <div class="relative">
                            <select id="camera-filter" class="appearance-none bg-surface border theme-border rounded-full pl-5 pr-10 py-2 text-sm font-medium theme-text focus:outline-none focus:ring-1 focus:ring-gray-400 transition-colors cursor-pointer">
                                <option value="all" data-i18n="filterAllCameras">모든 기종</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 theme-text-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative w-full min-h-[500px]">
            <div id="grid-container" class="view-transition columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6 opacity-100 relative z-10"></div>

            <div id="map-container" class="view-transition hidden w-full h-[75vh] rounded-3xl overflow-hidden shadow-2xl opacity-0 border theme-border relative z-0 bg-[#e5e3df] dark:bg-[#242f3e]">
                <div id="map-canvas" class="w-full h-full"></div>
                <div id="map-loader" class="absolute inset-0 bg-black/50 backdrop-blur flex items-center justify-center text-white hidden z-10">
                    <div class="flex flex-col items-center">
                        <svg class="animate-spin h-8 w-8 mb-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span class="text-xs font-bold tracking-widest uppercase" data-i18n="loadingMap">{{ __('portfolio.loadingMap') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Photo Modal -->
    <div id="photo-modal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm overflow-hidden opacity-0 flex items-center justify-center select-none transition-opacity duration-500">
        <div id="modal-container" class="relative w-[95%] max-w-7xl h-[85vh] md:h-[80vh] theme-surface rounded-3xl shadow-2xl overflow-hidden flex flex-col transition-transform duration-500 scale-95">
            
            <!-- [해결] shrink-0를 주어 상단 헤더가 찌그러지지 않게 방지 -->
            <div class="w-full flex justify-between items-center px-6 py-5 border-b theme-border bg-opacity-50 z-[60] shrink-0">
                <div class="flex flex-col">
                    <span id="modal-indicator" class="text-xs md:text-sm font-bold tracking-[0.3em] theme-text-muted uppercase mb-1">01 / 05</span>
                    <div id="modal-title-area" class="theme-text font-medium transition-all duration-500 truncate max-w-[250px] md:max-w-md"></div>
                </div>
                <button onclick="window.Portfolio.closeModal()" class="p-2 theme-text hover:bg-black/10 dark:hover:bg-white/10 rounded-full cursor-pointer transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <!-- [해결] 모바일에서는 세로 스크롤(overflow-y-auto)을 허용하여 이미지가 글씨를 침범하지 않게 분리 -->
            <div class="flex-grow overflow-y-auto overflow-x-hidden md:overflow-hidden relative w-full">
                <button onclick="window.Portfolio.prevPhoto()" class="nav-btn absolute left-4 top-1/2 -translate-y-1/2 p-3 rounded-full theme-text hidden md:flex z-[70] items-center justify-center transition-all shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button onclick="window.Portfolio.nextPhoto()" class="nav-btn absolute right-4 top-1/2 -translate-y-1/2 p-3 rounded-full theme-text hidden md:flex z-[70] items-center justify-center transition-all shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                
                <!-- [해결] 모바일에서 높이가 자연스럽게 늘어나도록 h-full 대신 min-h-full 사용 -->
                <div id="modal-slider-content" class="w-full min-h-full md:h-full flex flex-col md:flex-row items-stretch"></div>
            </div>
            
            <div class="absolute bottom-6 w-full flex justify-center md:hidden pointer-events-none">
                <div class="flex space-x-2" id="modal-dots"></div>
            </div>
        </div>
    </div>

    <!-- [추가] Keyboard Shortcuts Overlay -->
    <div id="shortcut-overlay" class="fixed inset-0 z-[110] hidden bg-black/80 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-center justify-center">
        <div class="bg-surface border theme-border rounded-2xl p-8 max-w-sm w-full shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="flex justify-between items-center mb-6 border-b theme-border pb-4">
                <h3 class="text-lg font-bold theme-text tracking-wide flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    Pro Shortcuts
                </h3>
                <span class="text-xs font-bold px-2 py-1 bg-blue-500/20 text-blue-400 rounded tracking-widest">GEEK</span>
            </div>
            <ul class="space-y-4">
                <li class="flex justify-between items-center"><span class="theme-text-muted text-sm font-medium">Grid View</span><kbd class="px-2 py-1 bg-black/50 border theme-border rounded text-xs theme-text font-mono shadow-inner">G</kbd></li>
                <li class="flex justify-between items-center"><span class="theme-text-muted text-sm font-medium">Map View</span><kbd class="px-2 py-1 bg-black/50 border theme-border rounded text-xs theme-text font-mono shadow-inner">M</kbd></li>
                <li class="flex justify-between items-center"><span class="theme-text-muted text-sm font-medium">Dark Mode Toggle</span><kbd class="px-2 py-1 bg-black/50 border theme-border rounded text-xs theme-text font-mono shadow-inner">D</kbd></li>
                <li class="flex justify-between items-center"><span class="theme-text-muted text-sm font-medium">Fullscreen</span><kbd class="px-2 py-1 bg-black/50 border theme-border rounded text-xs theme-text font-mono shadow-inner">F</kbd></li>
                <li class="flex justify-between items-center"><span class="theme-text-muted text-sm font-medium">Show / Hide Help</span><kbd class="px-2 py-1 bg-black/50 border theme-border rounded text-xs theme-text font-mono shadow-inner">?</kbd></li>
            </ul>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-white text-black px-6 py-3 rounded-full shadow-2xl font-medium text-sm z-[100] transition-all duration-500 opacity-0 translate-y-10 pointer-events-none"></div>

</body>
</html>