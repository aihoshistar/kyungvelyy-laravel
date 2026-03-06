<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKYOUNG.PHOTO | Park Kyoung Seo</title>

    <!-- 테마 깜빡임 방지 스크립트 -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- 폰트 및 외부 라이브러리 -->
    <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script src="https://t1.kakaocdn.net/kakao_js_sdk/2.7.0/kakao.min.js"></script>

    <!-- Laravel Vite 연동 -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* 슬라이드 전환 애니메이션 */
        .slide-fade-enter { opacity: 0; transform: translateX(20px); }
        .slide-fade-active { opacity: 1; transform: translateX(0); transition: all 0.6s var(--ease-spring); }

        /* 모달 네비게이션 버튼 */
        .nav-btn { background-color: rgba(255, 255, 255, 0.05); backdrop-filter: blur(8px); }
        .dark .nav-btn { background-color: rgba(0, 0, 0, 0.2); }
        .nav-btn:hover { background-color: rgba(255, 255, 255, 0.15); transform: scale(1.1); }

        /* 뷰 전환 효과 */
        .view-transition { transition: opacity 0.4s var(--ease-spring), transform 0.4s var(--ease-spring); }
    </style>
</head>
<body class="selection:bg-gray-300 selection:text-black dark:selection:bg-white dark:selection:text-black relative">

    <script>
        // 데이터 주입
        window.PORTFOLIO_DATA = {!! json_encode($posts ?? []) !!};

        // API 키 할당 (오타 수정됨)
        window.KAKAO_MAP_API_KEY = '{{ config('services.kakao.map_key') }}';
        window.GOOGLE_MAP_API_KEY = '{{ config('services.google.map_key') }}';

        // SDK 초기화
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

                <div class="flex space-x-3 text-xs theme-text-muted theme-surface px-3 py-1.5 rounded-full border theme-border transition-colors duration-500">
                    <button class="lang-btn hover:text-current transition-colors" data-lang="ko">KO</button>
                    <span class="opacity-30">·</span>
                    <button class="lang-btn hover:text-current transition-colors" data-lang="en">EN</button>
                    <span class="opacity-30">·</span>
                    <button class="lang-btn hover:text-current transition-colors" data-lang="ja">JA</button>
                </div>

                <button id="theme-toggle" class="p-2 rounded-full theme-btn-hover theme-text transition-colors duration-300" aria-label="Toggle Dark Mode">
                    <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 pt-32 pb-24 relative">
        <!-- Profile Header -->
        <header class="mb-24 max-w-3xl reveal" id="profile-header">
            <h1 class="text-5xl md:text-6xl font-bold theme-text mb-4 tracking-tighter transition-colors duration-500" data-i18n="heroName">Park Kyoung Seo</h1>
            <p class="text-xl md:text-2xl theme-text-muted mb-10 font-light tracking-wide transition-colors duration-500" data-i18n="heroRole">Blockchain Infra Engineer & Photographer</p>
            <div class="space-y-6 border-l-2 theme-border pl-6 transition-colors duration-500">
                <h2 class="text-2xl md:text-3xl font-medium theme-text leading-tight transition-colors duration-500" data-i18n="heroCatchphrase">구조를 설계하고,<br>균형을 기록합니다.</h2>
                <p class="theme-text-muted leading-relaxed max-w-2xl font-light text-lg transition-colors duration-500" data-i18n="heroDesc">개발자로 일하며 구조와 흐름을 설계하고, 여행지와 도시에서는 빛과 공간의 균형을 기록합니다.</p>
            </div>
        </header>

        <!-- Gallery Section -->
        <section id="gallery" class="relative">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 space-y-6 md:space-y-0 reveal relative z-20">
                <div class="flex items-center space-x-6">
                    <div>
                        <h3 class="text-xs uppercase tracking-[0.2em] theme-text-muted font-semibold mb-2 transition-colors duration-500">Selected Works</h3>
                        <h2 class="text-3xl font-bold theme-text tracking-tight transition-colors duration-500" id="section-title" data-i18n="galleryTitle">Photography</h2>
                    </div>

                    <div class="bg-surface backdrop-blur border theme-border rounded-full p-1 flex">
                        <button id="btn-grid-view" class="p-2 rounded-full bg-black text-white dark:bg-white dark:text-black shadow-sm transition-all" onclick="Portfolio.switchView('grid')" aria-label="Grid View">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </button>
                        <button id="btn-map-view" class="p-2 rounded-full theme-text hover:bg-black/10 dark:hover:bg-white/10 transition-all" onclick="Portfolio.switchView('map')" aria-label="Map View">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="flex space-x-2 overflow-x-auto pb-2 md:pb-0 hide-scrollbar" id="category-filters">
                    <button class="filter-btn active px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300 ease-spring theme-filter-active border" data-category="all" data-i18n="catAll">전체</button>
                    <button class="filter-btn px-5 py-2 rounded-full text-sm font-medium transition-all duration-300 ease-spring theme-filter-inactive border" data-category="Landscape" data-i18n="catLandscape">풍경</button>
                    <button class="filter-btn px-5 py-2 rounded-full text-sm font-medium transition-all duration-300 ease-spring theme-filter-inactive border" data-category="Cityscape" data-i18n="catCityscape">도시</button>
                </div>
            </div>

            <div class="relative w-full">
                <!-- Grid View Container -->
                <div id="grid-container" class="view-transition columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6 opacity-100 relative z-10"></div>

                <!-- Map View Container -->
                <div id="map-container" class="view-transition hidden w-full h-[75vh] rounded-3xl overflow-hidden shadow-2xl opacity-0 border theme-border relative z-0">
                    <div id="map-canvas" class="w-full h-full bg-[#e5e3df] dark:bg-[#242f3e]"></div>
                    <!-- 로딩 오버레이 -->
                    <div id="map-loader" class="absolute inset-0 bg-black/50 backdrop-blur flex items-center justify-center text-white hidden z-10">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-8 w-8 mb-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span class="text-sm font-medium tracking-widest uppercase text-white">Loading Map...</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Photo Modal -->
    <div id="photo-modal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm overflow-hidden opacity-0 transition-opacity duration-500 ease-spring flex items-center justify-center select-none">
        <div id="modal-container" class="relative w-[95%] max-w-7xl h-[85vh] md:h-[80vh] theme-surface rounded-3xl shadow-2xl overflow-hidden flex flex-col transition-transform duration-500 scale-95">
            <!-- Modal Top Bar -->
            <div class="w-full flex justify-between items-center px-6 py-6 border-b theme-border z-[60]">
                <div class="flex flex-col">
                    <span id="modal-indicator" class="text-[10px] font-bold tracking-[0.3em] theme-text-muted uppercase mb-1">01 / 05</span>
                    <div id="modal-title-area" class="theme-text font-medium text-lg md:text-xl transition-all duration-500 truncate max-w-[250px] md:max-w-md"></div>
                </div>
                <button onclick="Portfolio.closeModal()" class="p-2.5 theme-text hover:bg-black/10 dark:hover:bg-white/10 rounded-full transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <!-- Slider Main -->
            <div class="flex-grow flex flex-col md:flex-row items-stretch overflow-hidden relative">
                <button onclick="Portfolio.prevPhoto()" class="nav-btn absolute left-4 top-1/2 -translate-y-1/2 p-3 rounded-full theme-text hidden md:flex z-[70] transition-all items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button onclick="Portfolio.nextPhoto()" class="nav-btn absolute right-4 top-1/2 -translate-y-1/2 p-3 rounded-full theme-text hidden md:flex z-[70] transition-all items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                <div id="modal-slider-content" class="w-full h-full flex flex-col md:flex-row items-stretch"></div>
            </div>
            <!-- Mobile Dots -->
            <div class="absolute bottom-6 w-full flex justify-center md:hidden pointer-events-none">
                <div class="flex space-x-2" id="modal-dots"></div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-white text-black px-6 py-3 rounded-full shadow-2xl font-medium text-sm z-[100] transition-all duration-500 opacity-0 translate-y-10 pointer-events-none"></div>

    <script>
        const Portfolio = (() => {
            let state = {
                lang: 'ko',
                category: 'all',
                posts: window.PORTFOLIO_DATA || [],
                activePost: null,
                currentPhotoIndex: 0,
                viewMode: 'grid',
                mapInstance: null,
                mapLoaded: { kakao: false, google: false },
                touchStartX: 0
            };

            const i18n = {
                ko: { navGallery: '갤러리', heroName: 'Park Kyoung Seo', heroRole: '블록체인 인프라 엔지니어 & 사진작가', heroCatchphrase: '구조를 설계하고,<br>균형을 기록합니다.', heroDesc: '개발자로 일하며 구조와 흐름을 설계하고, 여행지와 도시에서는 빛과 공간의 균형을 기록합니다.', galleryTitle: '사진 작업', mapTitle: '촬영 위치', catAll: '전체보기', catLandscape: '자연·풍경', catCityscape: '도시·건축', exifCamera: '카메라', exifLens: '렌즈', btnShare: '공유하기', btnDownload: '프레임 저장', toastCopy: '링크가 클립보드에 복사되었습니다.', toastWait: '이미지를 렌더링 중입니다...' },
                en: { navGallery: 'Gallery', heroName: 'Park Kyoung Seo', heroRole: 'Blockchain Infra Engineer & Photographer', heroCatchphrase: 'Architecting Systems,<br>Documenting Balance.', heroDesc: 'As a developer, I design structures and flows. In cities and nature, I record the balance of light and space.', galleryTitle: 'Selected Works', mapTitle: 'Locations', catAll: 'All', catLandscape: 'Landscape', catCityscape: 'Cityscape', exifCamera: 'Camera', exifLens: 'Lens', btnShare: 'Share', btnDownload: 'Save Frame', toastCopy: 'Link copied to clipboard.', toastWait: 'Generating image...' },
                ja: { navGallery: 'ギャラリー', heroName: 'Park Kyoung Seo', heroRole: 'ブロックチェーン인프라엔지니어 & 사진가', heroCatchphrase: '構造を設計し、<br>均衡を記録します。', heroDesc: '開発者として構造とフローを設計し、旅先や都市では光と空間のバランスを記録します。', galleryTitle: '사진 작품', mapTitle: '촬영 장소', catAll: 'すべて', catLandscape: '風景·自然', catCityscape: '都市·建築', exifCamera: '카메라', exifLens: '렌즈', btnShare: '공유하기', btnDownload: '저장', toastCopy: '링크가 클립보드에 복사되었습니다.', toastWait: '이미지를 렌더링 중입니다...' }
            };

            const DOM = {
                grid: document.getElementById('grid-container'),
                mapContainer: document.getElementById('map-container'),
                mapCanvas: document.getElementById('map-canvas'),
                mapLoader: document.getElementById('map-loader'),
                btnGrid: document.getElementById('btn-grid-view'),
                btnMap: document.getElementById('btn-map-view'),
                sectionTitle: document.getElementById('section-title'),
                profileHeader: document.getElementById('profile-header'),
                modal: document.getElementById('photo-modal'),
                modalContainer: document.getElementById('modal-container'),
                modalIndicator: document.getElementById('modal-indicator'),
                modalTitleArea: document.getElementById('modal-title-area'),
                modalSliderContent: document.getElementById('modal-slider-content'),
                modalDots: document.getElementById('modal-dots'),
                langBtns: document.querySelectorAll('.lang-btn'),
                filterBtns: document.querySelectorAll('.filter-btn'),
                themeToggleBtn: document.getElementById('theme-toggle'),
                darkIcon: document.getElementById('theme-toggle-dark-icon'),
                lightIcon: document.getElementById('theme-toggle-light-icon'),
                toast: document.getElementById('toast')
            };

            const init = () => {
                bindEvents();
                initTheme();
                updateLanguage(state.lang);
                document.querySelectorAll('.reveal').forEach(el => {
                    new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('active');
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.05 }).observe(el);
                });
            };

            const bindEvents = () => {
                DOM.themeToggleBtn.addEventListener('click', toggleTheme);
                DOM.langBtns.forEach(btn => btn.addEventListener('click', (e) => updateLanguage(e.target.dataset.lang)));
                DOM.filterBtns.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        DOM.filterBtns.forEach(b => { b.classList.remove('theme-filter-active'); b.classList.add('theme-filter-inactive'); });
                        e.target.classList.remove('theme-filter-inactive'); e.target.classList.add('theme-filter-active');
                        state.category = e.target.dataset.category;
                        if (state.viewMode === 'grid') renderGrid(); else renderMapMarkers();
                    });
                });
                DOM.modal.addEventListener('click', (e) => { if (e.target === DOM.modal) closeModal(); });
                DOM.modalContainer.addEventListener('touchstart', e => { state.touchStartX = e.changedTouches[0].screenX; }, {passive: true});
                DOM.modalContainer.addEventListener('touchend', e => {
                    const touchEndX = e.changedTouches[0].screenX;
                    if (state.touchStartX - touchEndX > 50) nextPhoto();
                    if (touchEndX - state.touchStartX > 50) prevPhoto();
                }, {passive: true});
                document.addEventListener('keydown', e => {
                    if (DOM.modal.classList.contains('hidden')) return;
                    if (e.key === 'ArrowRight') nextPhoto();
                    if (e.key === 'ArrowLeft') prevPhoto();
                    if (e.key === 'Escape') closeModal();
                });
            };

            const initTheme = () => {
                if (document.documentElement.classList.contains('dark')) DOM.lightIcon.classList.remove('hidden');
                else DOM.darkIcon.classList.remove('hidden');
            };

            const toggleTheme = () => {
                DOM.darkIcon.classList.toggle('hidden');
                DOM.lightIcon.classList.toggle('hidden');
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark');
                }
                if(state.viewMode === 'map') renderMapMarkers();
            };

            const updateLanguage = (lang) => {
                state.lang = lang;
                DOM.langBtns.forEach(btn => {
                    btn.classList.toggle('theme-text', btn.dataset.lang === lang);
                    btn.classList.toggle('font-bold', btn.dataset.lang === lang);
                    btn.classList.toggle('theme-text-muted', btn.dataset.lang !== lang);
                });
                document.querySelectorAll('[data-i18n]').forEach(el => {
                    const key = el.dataset.i18n;
                    if (i18n[lang][key]) el.innerHTML = i18n[lang][key];
                });
                DOM.sectionTitle.innerHTML = state.viewMode === 'grid' ? i18n[lang].galleryTitle : i18n[lang].mapTitle;
                if (state.viewMode === 'grid') renderGrid(); else renderMapMarkers();
                if (!DOM.modal.classList.contains('hidden') && state.activePost) renderActiveSlide();
            };

            const getTrans = (obj) => {
                if (!obj) return '';
                return (typeof obj === 'string') ? obj : (obj[state.lang] || obj['ko'] || '');
            };

            const formatDate = (dateString) => {
                if (!dateString) return '';
                const date = new Date(dateString);
                return isNaN(date) ? dateString : `${date.getFullYear()}.${String(date.getMonth() + 1).padStart(2, '0')}.${String(date.getDate()).padStart(2, '0')}`;
            };

            const switchView = (mode) => {
                if (state.viewMode === mode) return;
                state.viewMode = mode;

                if (mode === 'grid') {
                    DOM.btnGrid.className = 'p-2 rounded-full bg-black text-white dark:bg-white dark:text-black shadow-sm transition-all';
                    DOM.btnMap.className = 'p-2 rounded-full theme-text hover:bg-black/10 dark:hover:bg-white/10 transition-all';
                    DOM.sectionTitle.innerHTML = i18n[state.lang].galleryTitle;

                    DOM.mapContainer.classList.add('opacity-0');
                    setTimeout(() => {
                        DOM.mapContainer.classList.add('hidden');
                        DOM.grid.classList.remove('hidden');
                        void DOM.grid.offsetWidth;
                        DOM.grid.classList.add('opacity-100');
                        renderGrid();
                    }, 400);
                } else {
                    DOM.btnMap.className = 'p-2 rounded-full bg-black text-white dark:bg-white dark:text-black shadow-sm transition-all';
                    DOM.btnGrid.className = 'p-2 rounded-full theme-text hover:bg-black/10 dark:hover:bg-white/10 transition-all';
                    DOM.sectionTitle.innerHTML = i18n[state.lang].mapTitle;

                    DOM.grid.classList.remove('opacity-100');
                    DOM.grid.classList.add('opacity-0');
                    setTimeout(() => {
                        DOM.grid.classList.add('hidden');
                        DOM.mapContainer.classList.remove('hidden');
                        void DOM.mapContainer.offsetWidth;
                        DOM.mapContainer.classList.add('opacity-100');
                        initHybridMap();
                    }, 400);
                }
            };

            const renderGrid = () => {
                DOM.grid.innerHTML = '';
                const filtered = state.category === 'all' ? state.posts : state.posts.filter(p => p.category === state.category);
                filtered.forEach((post, index) => {
                    if (!post.photos?.length) return;
                    const card = document.createElement('div');
                    card.className = 'photo-card cursor-pointer group reveal w-full break-inside-avoid relative inline-block mb-6 active';
                    card.style.transitionDelay = `${(index % 6) * 100}ms`;
                    card.innerHTML = `
                        <div class="rounded-xl theme-surface img-mask overflow-hidden w-full relative transition-colors duration-500" style="aspect-ratio: ${post.photos[0].aspect_ratio || 1.5};">
                            <img src="${post.photos[0].url}" alt="${getTrans(post.title)}" loading="lazy" class="absolute inset-0 w-full h-full object-cover transform scale-100 group-hover:scale-105 transition-transform duration-1000 ease-spring opacity-90 group-hover:opacity-100">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-end p-6 md:p-8">
                                <span class="text-[10px] font-bold tracking-[0.2em] text-white/70 uppercase mb-2">${post.category}</span>
                                <h4 class="text-white font-medium text-xl md:text-2xl leading-snug drop-shadow-lg">${getTrans(post.title)}</h4>
                            </div>
                        </div>
                    `;
                    card.onclick = () => openModal(post);
                    DOM.grid.appendChild(card);
                });
            };

            const initHybridMap = () => {
                const filtered = state.category === 'all' ? state.posts : state.posts.filter(p => p.category === state.category);
                if (!filtered.length) return;
                const isKoreaMode = filtered.some(p => p.country === 'South Korea');
                DOM.mapLoader.classList.remove('hidden');
                if (isKoreaMode) {
                    if (state.mapLoaded.kakao) drawKakaoMap(filtered);
                    else {
                        const script = document.createElement('script');
                        script.src = `//dapi.kakao.com/v2/maps/sdk.js?appkey=${window.KAKAO_MAP_API_KEY}&autoload=false`;
                        script.onload = () => {
                            kakao.maps.load(() => { state.mapLoaded.kakao = true; drawKakaoMap(filtered); });
                        };
                        document.head.appendChild(script);
                    }
                } else {
                    if (state.mapLoaded.google) drawGoogleMap(filtered);
                    else {
                        const script = document.createElement('script');
                        script.src = `https://maps.googleapis.com/maps/api/js?key=${window.GOOGLE_MAP_API_KEY}&callback=initGoogleMapEngine`;
                        window.initGoogleMapEngine = () => { state.mapLoaded.google = true; drawGoogleMap(filtered); };
                        document.head.appendChild(script);
                    }
                }
            };

            const drawKakaoMap = (posts) => {
                DOM.mapLoader.classList.add('hidden');
                const first = posts.find(p => p.lat && p.lng) || {lat: 37.56, lng: 126.97};
                if (!state.mapInstance) {
                    state.mapInstance = new kakao.maps.Map(DOM.mapCanvas, { center: new kakao.maps.LatLng(first.lat, first.lng), level: 11 });
                } else {
                    state.mapInstance.relayout();
                    state.mapInstance.setCenter(new kakao.maps.LatLng(first.lat, first.lng));
                }

                posts.forEach(post => {
                    if (!post.lat || !post.photos?.length) return;
                    new kakao.maps.CustomOverlay({
                        position: new kakao.maps.LatLng(post.lat, post.lng),
                        content: `<div class="w-12 h-12 rounded-full border-2 border-white shadow-xl cursor-pointer bg-cover bg-center" style="background-image: url(${post.photos[0].url})" onclick="Portfolio.triggerOpenModal('${post.id}')"></div>`,
                        yAnchor: 1.2
                    }).setMap(state.mapInstance);
                });
            };

            const drawGoogleMap = (posts) => {
                DOM.mapLoader.classList.add('hidden');
                const first = posts.find(p => p.lat && p.lng) || {lat: 37.56, lng: 126.97};
                state.mapInstance = new google.maps.Map(DOM.mapCanvas, { zoom: 6, center: { lat: parseFloat(first.lat), lng: parseFloat(first.lng) }, mapTypeControl: false });
                posts.forEach(post => {
                    if (!post.lat || !post.photos?.length) return;
                    const marker = new google.maps.Marker({ position: { lat: parseFloat(post.lat), lng: parseFloat(post.lng) }, map: state.mapInstance });
                    marker.addListener("click", () => openModal(post));
                });
            };

            const renderMapMarkers = () => { if(state.viewMode === 'map') initHybridMap(); };
            const triggerOpenModal = (postId) => { const post = state.posts.find(p => p.id === postId); if(post) openModal(post); };
            const formatExif = (photo) => {
                const parts = [];
                if (photo.exif_focal_length) parts.push(`${photo.exif_focal_length}mm`);
                if (photo.exif_aperture) parts.push(`f/${parseFloat(photo.exif_aperture)}`);
                if (photo.exif_shutter_speed_num) parts.push(`${photo.exif_shutter_speed_num}/${photo.exif_shutter_speed_den}s`);
                if (photo.exif_iso) parts.push(`ISO ${photo.exif_iso}`);
                return parts;
            };

            const downloadFramedPhoto = (photoDataStr, postUrl) => {
                const photo = JSON.parse(photoDataStr.replace(/&quot;/g, '"'));
                const img = new Image(); img.crossOrigin = "Anonymous"; img.src = photo.url;
                img.onload = () => {
                    const canvas = document.createElement('canvas'); const ctx = canvas.getContext('2d');
                    const px = 120, pt = 120, pb = 400; canvas.width = img.width + (px * 2); canvas.height = img.height + pt + pb;
                    ctx.fillStyle = "#ffffff"; ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, px, pt, img.width, img.height);
                    ctx.fillStyle = "#111111"; ctx.textAlign = "left"; let sy = pt + img.height + 100;
                    ctx.font = "bold 60px Pretendard, sans-serif"; ctx.fillText(getTrans(photo.title), px, sy);
                    ctx.font = "30px Pretendard, monospace"; ctx.fillStyle = "#666666"; ctx.fillText(`${photo.exif_body || ''} · ${photo.exif_lens || ''} · ${formatExif(photo).join('  ')}`, px, sy + 70);
                    const qr = new QRious({ value: postUrl, size: 180, level: 'H' });
                    const qrImg = new Image(); qrImg.src = qr.toDataURL();
                    qrImg.onload = () => { ctx.drawImage(qrImg, canvas.width - px - 180, pt + img.height + 80, 180, 180); const link = document.createElement('a'); link.download = `photo.jpg`; link.href = canvas.toDataURL("image/jpeg", 0.95); link.click(); };
                };
            };
            const sharePhoto = (title, imageUrl, postUrl) => {
                const data = { title: `PHOTO - ${title}`, url: postUrl };
                if (navigator.share) navigator.share(data).catch(console.error);
                else { navigator.clipboard.writeText(postUrl).then(() => showToast(i18n[state.lang].toastCopy)); }
            };

            const openModal = (post) => {
                state.activePost = post; state.currentPhotoIndex = 0; renderActiveSlide();
                DOM.modal.classList.remove('hidden'); document.body.style.overflow = 'hidden';
                void DOM.modal.offsetWidth; DOM.modal.classList.remove('opacity-0'); DOM.modalContainer.classList.remove('scale-95');
            };
            const closeModal = () => {
                DOM.modalContainer.classList.add('scale-95'); DOM.modal.classList.add('opacity-0');
                setTimeout(() => { DOM.modal.classList.add('hidden'); document.body.style.overflow = ''; state.activePost = null; }, 500);
            };
            const nextPhoto = () => { if (!state.activePost) return; state.currentPhotoIndex = (state.currentPhotoIndex + 1) % state.activePost.photos.length; renderActiveSlide(); };
            const prevPhoto = () => { if (!state.activePost) return; state.currentPhotoIndex = (state.currentPhotoIndex - 1 + state.activePost.photos.length) % state.activePost.photos.length; renderActiveSlide(); };

            const renderActiveSlide = () => {
                const post = state.activePost; const photo = post.photos[state.currentPhotoIndex]; if (!photo) return;
                DOM.modalIndicator.textContent = `${String(state.currentPhotoIndex + 1).padStart(2, '0')} / ${String(post.photos.length).padStart(2, '0')}`;
                DOM.modalTitleArea.innerHTML = `<div class="flex items-center space-x-2"><span class="text-[9px] px-1.5 py-0.5 rounded border theme-border theme-text-muted font-bold uppercase">${post.category}</span><h2 class="text-sm md:text-base font-semibold truncate">${getTrans(post.title)}</h2></div>`;
                DOM.modalDots.innerHTML = post.photos.map((_, i) => `<div class="w-1 h-1 rounded-full transition-all duration-300 ${i === state.currentPhotoIndex ? 'bg-white w-3' : 'bg-white/30'}"></div>`).join('');
                const exifArr = formatExif(photo); const shareUrl = `${window.location.origin}${window.location.pathname}#post-${post.id}`;
                const shareTitle = getTrans(photo.title); const safePhotoData = JSON.stringify(photo).replace(/"/g, '&quot;');
                DOM.modalSliderContent.innerHTML = `<div class="w-full md:w-[65%] h-[50vh] md:h-auto flex items-center justify-center p-4 md:p-10 slide-fade-enter bg-black/5 dark:bg-black/20"><img src="${photo.url}" class="max-w-full max-h-full object-contain rounded-lg shadow-xl"></div><div class="w-full md:w-[35%] flex flex-col justify-center p-8 md:p-10 space-y-6 slide-fade-enter overflow-y-auto"><div class="space-y-3"><h3 class="text-xl md:text-2xl font-bold theme-text leading-tight">${shareTitle}</h3>${getTrans(photo.description) ? `<p class="theme-text-muted font-light text-sm">${getTrans(photo.description)}</p>` : ''}</div><div class="pt-6 border-t theme-border grid grid-cols-2 gap-4"><div class="flex flex-col"><span class="text-[9px] theme-text-muted uppercase font-bold mb-1">${i18n[state.lang].exifCamera}</span><span class="theme-text text-xs font-medium truncate">${photo.exif_body || '-'}</span></div><div class="flex flex-col"><span class="text-[9px] theme-text-muted uppercase font-bold mb-1">${i18n[state.lang].exifLens}</span><span class="theme-text text-xs font-medium truncate">${photo.exif_lens || '-'}</span></div><div class="col-span-2 flex flex-wrap gap-1.5 pt-1">${exifArr.map(e => `<span class="px-2 py-0.5 bg-surface border theme-border rounded text-[9px] theme-text font-mono">${e}</span>`).join('')}</div></div><div class="flex gap-2 pt-2"><button onclick="Portfolio.downloadFramedPhoto('${safePhotoData}', '${shareUrl}')" class="flex-1 flex items-center justify-center py-3 rounded-xl theme-surface border theme-border theme-text hover:bg-black transition-all text-[10px] font-bold"><span>${i18n[state.lang].btnDownload}</span></button><button onclick="Portfolio.sharePhoto('${shareTitle}', '${photo.url}', '${shareUrl}')" class="flex-1 flex items-center justify-center py-3 rounded-xl theme-surface border theme-border theme-text hover:bg-black transition-all text-[10px] font-bold"><span>${i18n[state.lang].btnShare}</span></button></div></div>`;
                requestAnimationFrame(() => { Array.from(DOM.modalSliderContent.children).forEach(child => { child.classList.remove('slide-fade-enter'); child.classList.add('slide-fade-active'); }); });
            };

            return { init, switchView, closeModal, nextPhoto, prevPhoto, downloadFramedPhoto, sharePhoto, triggerOpenModal };
        })();

        document.addEventListener('DOMContentLoaded', Portfolio.init);
    </script>
</body>
</html>
