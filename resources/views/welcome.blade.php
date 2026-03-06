<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKYOUNG.PHOTO | Park Kyoung Seo</title>

    <!-- 테마 깜빡임(FOUC) 방지용 스크립트 -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- 프리텐다드 폰트 -->
    <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" />

    <!-- QR Code 생성 라이브러리 (qrious) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

    <!-- 카카오톡 공유 API SDK -->
    <script src="https://t1.kakaocdn.net/kakao_js_sdk/2.7.0/kakao.min.js" xintegrity="sha384-l+xbElFSnPZ2rOaGxg++s3mKqOuzZwqF8353G//aJjw03usrNjgGfRcv/aO8E9ua" crossorigin="anonymous"></script>

    <!-- Laravel Vite 연동 -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="selection:bg-gray-300 selection:text-black dark:selection:bg-white dark:selection:text-black relative">

    <script>
        // DB 데이터 주입
        window.PORTFOLIO_DATA = {!! json_encode($posts ?? []) !!};

        // [중요] 카카오 API 키 초기화 (발급받은 JavaScript 키로 변경하세요)
        // https://developers.kakao.com 에서 발급 가능
        if (!Kakao.isInitialized()) {
            Kakao.init('YOUR_KAKAO_JAVASCRIPT_KEY');
        }
    </script>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-40 backdrop-blur-xl theme-nav border-b theme-border transition-all duration-500" id="navbar">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="#" class="theme-text font-bold tracking-widest text-sm uppercase">KKYOUNG.PHOTO</a>

            <div class="flex items-center space-x-6 md:space-x-8 text-sm font-medium">
                <a href="#gallery" class="theme-text-muted hover:text-opacity-80 transition-colors hidden sm:block" data-i18n="navGallery">Gallery</a>

                <!-- 언어 토글 -->
                <div class="flex space-x-3 text-xs theme-text-muted theme-surface px-3 py-1.5 rounded-full border theme-border transition-colors duration-500">
                    <button class="lang-btn hover:text-current transition-colors" data-lang="ko">KO</button>
                    <span class="opacity-30">·</span>
                    <button class="lang-btn hover:text-current transition-colors" data-lang="en">EN</button>
                    <span class="opacity-30">·</span>
                    <button class="lang-btn hover:text-current transition-colors" data-lang="ja">JA</button>
                </div>

                <!-- 다크모드 토글 버튼 -->
                <button id="theme-toggle" class="p-2 rounded-full theme-btn-hover theme-text transition-colors duration-300" aria-label="Toggle Dark Mode">
                    <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 pt-32 pb-24">
        <!-- Profile Header -->
        <header class="mb-32 max-w-3xl reveal">
            <h1 class="text-5xl md:text-6xl font-bold theme-text mb-4 tracking-tighter transition-colors duration-500" data-i18n="heroName">Park Kyoung Seo</h1>
            <p class="text-xl md:text-2xl theme-text-muted mb-10 font-light tracking-wide transition-colors duration-500" data-i18n="heroRole">Blockchain Infra Engineer & Photographer</p>
            <div class="space-y-6 border-l-2 theme-border pl-6 transition-colors duration-500">
                <h2 class="text-2xl md:text-3xl font-medium theme-text leading-tight transition-colors duration-500" data-i18n="heroCatchphrase">구조를 설계하고,<br>균형을 기록합니다.</h2>
                <p class="theme-text-muted leading-relaxed max-w-2xl font-light text-lg transition-colors duration-500" data-i18n="heroDesc">개발자로 일하며 구조와 흐름을 설계하고, 여행지와 도시에서는 빛과 공간의 균형을 기록합니다.</p>
            </div>
        </header>

        <!-- Gallery Section -->
        <section id="gallery">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 space-y-6 md:space-y-0 reveal">
                <div>
                    <h3 class="text-xs uppercase tracking-[0.2em] theme-text-muted font-semibold mb-2 transition-colors duration-500">Selected Works</h3>
                    <h2 class="text-3xl font-bold theme-text tracking-tight transition-colors duration-500" data-i18n="galleryTitle">Photography</h2>
                </div>
                <!-- Category Filter -->
                <div class="flex space-x-2 overflow-x-auto pb-2 md:pb-0 hide-scrollbar" id="category-filters">
                    <button class="filter-btn active px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300 ease-spring theme-filter-active border" data-category="all" data-i18n="catAll">전체</button>
                    <button class="filter-btn px-5 py-2 rounded-full text-sm font-medium transition-all duration-300 ease-spring theme-filter-inactive border" data-category="Landscape" data-i18n="catLandscape">풍경</button>
                    <button class="filter-btn px-5 py-2 rounded-full text-sm font-medium transition-all duration-300 ease-spring theme-filter-inactive border" data-category="Cityscape" data-i18n="catCityscape">도시</button>
                </div>
            </div>
            <!-- Grid Layout -->
            <div id="grid-container" class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6"></div>
        </section>
    </main>

    <!-- Photo Modal -->
    <div id="photo-modal" class="fixed inset-0 z-50 hidden theme-modal backdrop-blur-2xl overflow-y-auto opacity-0 transition-opacity duration-500 ease-spring">
        <div class="fixed top-0 w-full z-50 flex justify-end items-center px-6 py-6 pointer-events-none">
            <button onclick="Portfolio.closeModal()" class="p-3 theme-text hover:scale-110 transition-transform duration-300 ease-spring theme-surface border theme-border rounded-full backdrop-blur-md cursor-pointer pointer-events-auto shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="min-h-screen pt-24 pb-32 px-4 flex flex-col items-center w-full">
            <div id="modal-header" class="w-full max-w-4xl mb-20 text-center reveal"></div>
            <div id="modal-content" class="w-full max-w-6xl space-y-32"></div>
        </div>
    </div>

    <!-- Toast Notification (복사 완료 등 알림용) -->
    <div id="toast" class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-white text-black px-6 py-3 rounded-full shadow-2xl font-medium text-sm z-[60] transition-all duration-500 opacity-0 translate-y-10 pointer-events-none">
        알림 메시지
    </div>

    <!-- Application Logic -->
    <script>
        const Portfolio = (() => {
            let state = {
                lang: 'ko',
                category: 'all',
                posts: window.PORTFOLIO_DATA || [],
                activePost: null
            };

            const i18n = {
                ko: { navGallery: '갤러리', heroName: 'Park Kyoung Seo', heroRole: '블록체인 인프라 엔지니어 & 사진작가', heroCatchphrase: '구조를 설계하고,<br>균형을 기록합니다.', heroDesc: '개발자로 일하며 구조와 흐름을 설계하고, 여행지와 도시에서는 빛과 공간의 균형을 기록합니다.', galleryTitle: '사진 작업', catAll: '전체보기', catLandscape: '자연·풍경', catCityscape: '도시·건축', exifCamera: '카메라', exifLens: '렌즈', btnShare: '공유하기', btnDownload: '프레임 저장', toastCopy: '링크가 클립보드에 복사되었습니다.', toastWait: '이미지를 생성 중입니다...' },
                en: { navGallery: 'Gallery', heroName: 'Park Kyoung Seo', heroRole: 'Blockchain Infra Engineer & Photographer', heroCatchphrase: 'Architecting Systems,<br>Documenting Balance.', heroDesc: 'As a developer, I design structures and flows. In cities and nature, I record the balance of light and space.', galleryTitle: 'Selected Photography', catAll: 'All Works', catLandscape: 'Landscape', catCityscape: 'Cityscape', exifCamera: 'Camera', exifLens: 'Lens', btnShare: 'Share', btnDownload: 'Save Frame', toastCopy: 'Link copied to clipboard.', toastWait: 'Generating image...' },
                ja: { navGallery: 'ギャラリー', heroName: 'Park Kyoung Seo', heroRole: 'ブロックチェーンインフラエンジニア & 写真家', heroCatchphrase: '構造を設計し、<br>均衡を記録します。', heroDesc: '開発者として構造とフローを設計し、旅先や都市では光と空間のバランスを記録します。', galleryTitle: '写真作品', catAll: 'すべて', catLandscape: '風景・自然', catCityscape: '都市・建築', exifCamera: 'カメラ', exifLens: 'レンズ', btnShare: '共有する', btnDownload: '保存', toastCopy: 'リンクをコピーしました。', toastWait: '画像を生成しています...' }
            };

            const DOM = {
                grid: document.getElementById('grid-container'),
                modal: document.getElementById('photo-modal'),
                modalHeader: document.getElementById('modal-header'),
                modalContent: document.getElementById('modal-content'),
                langBtns: document.querySelectorAll('.lang-btn'),
                filterBtns: document.querySelectorAll('.filter-btn'),
                themeToggleBtn: document.getElementById('theme-toggle'),
                darkIcon: document.getElementById('theme-toggle-dark-icon'),
                lightIcon: document.getElementById('theme-toggle-light-icon'),
                toast: document.getElementById('toast')
            };

            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        observer.unobserve(entry.target);
                    }
                });
            }, { root: null, rootMargin: '0px', threshold: 0.05 });

            const init = () => {
                bindEvents();
                initTheme();
                updateLanguage(state.lang);
                document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
            };

            const initTheme = () => {
                if (document.documentElement.classList.contains('dark')) {
                    DOM.lightIcon.classList.remove('hidden');
                } else {
                    DOM.darkIcon.classList.remove('hidden');
                }
            };

            const toggleTheme = () => {
                DOM.darkIcon.classList.toggle('hidden');
                DOM.lightIcon.classList.toggle('hidden');

                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            };

            const bindEvents = () => {
                DOM.themeToggleBtn.addEventListener('click', toggleTheme);
                DOM.langBtns.forEach(btn => btn.addEventListener('click', (e) => updateLanguage(e.target.dataset.lang)));
                DOM.filterBtns.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        DOM.filterBtns.forEach(b => {
                            b.classList.remove('theme-filter-active');
                            b.classList.add('theme-filter-inactive');
                        });
                        e.target.classList.remove('theme-filter-inactive');
                        e.target.classList.add('theme-filter-active');
                        state.category = e.target.dataset.category;
                        renderGrid();
                    });
                });
                DOM.modal.addEventListener('click', (e) => {
                    if (e.target === DOM.modal || e.target.closest('.min-h-screen') === e.target) closeModal();
                });
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !DOM.modal.classList.contains('hidden')) closeModal();
                });
            };

            const showToast = (message) => {
                DOM.toast.textContent = message;
                DOM.toast.classList.remove('opacity-0', 'translate-y-10');
                setTimeout(() => {
                    DOM.toast.classList.add('opacity-0', 'translate-y-10');
                }, 3000);
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
                renderGrid();
                if (!DOM.modal.classList.contains('hidden') && state.activePost) renderModal(state.activePost);
            };

            const getTrans = (obj) => {
                if (!obj) return '';
                if (typeof obj === 'string') return obj;
                return obj[state.lang] || obj['ko'] || '';
            };

            const formatDate = (dateString) => {
                if (!dateString) return '';
                const date = new Date(dateString);
                if (isNaN(date)) return dateString;
                return `${date.getFullYear()}.${String(date.getMonth() + 1).padStart(2, '0')}.${String(date.getDate()).padStart(2, '0')}`;
            };

            const formatExif = (photo) => {
                const parts = [];
                if (photo.exif_focal_length) parts.push(`${photo.exif_focal_length}mm`);
                if (photo.exif_aperture) parts.push(`f/${parseFloat(photo.exif_aperture)}`);
                if (photo.exif_shutter_speed_num && photo.exif_shutter_speed_den) parts.push(`${photo.exif_shutter_speed_num}/${photo.exif_shutter_speed_den}s`);
                if (photo.exif_iso) parts.push(`ISO ${photo.exif_iso}`);
                return parts;
            };

            const renderGrid = () => {
                DOM.grid.innerHTML = '';
                const filtered = state.category === 'all' ? state.posts : state.posts.filter(p => p.category === state.category);

                filtered.forEach((post, index) => {
                    if (!post.photos || post.photos.length === 0) return;
                    const firstImage = post.photos[0];
                    const aspectRatio = firstImage.aspect_ratio ? parseFloat(firstImage.aspect_ratio) : 1.5;

                    const card = document.createElement('div');
                    card.className = 'photo-card cursor-pointer group reveal w-full break-inside-avoid relative inline-block';
                    card.style.transitionDelay = `${(index % 6) * 100}ms`;

                    card.innerHTML = `
                        <div class="rounded-xl theme-surface img-mask overflow-hidden w-full relative transition-colors duration-500" style="aspect-ratio: ${aspectRatio};">
                            <img src="${firstImage.url}" alt="${getTrans(post.title)}" loading="lazy"
                                 class="absolute inset-0 w-full h-full object-cover transform scale-100 group-hover:scale-105 transition-transform duration-1000 ease-spring opacity-90 group-hover:opacity-100">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-end p-6 md:p-8">
                                <span class="text-[10px] font-bold tracking-[0.2em] text-white/70 uppercase mb-2">${post.category}</span>
                                <h4 class="text-white font-medium text-xl md:text-2xl leading-snug drop-shadow-lg">${getTrans(post.title)}</h4>
                            </div>
                        </div>
                    `;
                    card.onclick = () => openModal(post);
                    DOM.grid.appendChild(card);
                    revealObserver.observe(card);
                });
            };

            const openModal = (post) => {
                state.activePost = post;
                renderModal(post);
                DOM.modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                void DOM.modal.offsetWidth;
                DOM.modal.classList.remove('opacity-0');
            };

            const renderModal = (post) => {
                DOM.modalHeader.innerHTML = `
                    <h2 class="text-3xl md:text-5xl lg:text-6xl font-bold theme-text mb-6 tracking-tight transition-colors duration-500">${getTrans(post.title)}</h2>
                    <div class="flex flex-wrap items-center justify-center gap-3 theme-text-muted text-sm md:text-base tracking-wide transition-colors duration-500">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            ${getTrans(post.location)}
                        </span>
                        <span class="opacity-50 hidden md:inline">|</span>
                        <span>${formatDate(post.taken_at)}</span>
                    </div>
                `;
                revealObserver.observe(DOM.modalHeader);
                DOM.modalContent.innerHTML = '';

                if (post.photos && post.photos.length > 0) {
                    post.photos.forEach((photo) => {
                        const exifArr = formatExif(photo);
                        const photoDiv = document.createElement('div');
                        photoDiv.className = 'flex flex-col md:flex-row gap-8 lg:gap-16 items-start reveal w-full';

                        // Sharing Data
                        const shareUrl = window.location.origin + window.location.pathname + '#post-' + post.id;
                        const shareTitle = getTrans(photo.title);
                        const safePhotoData = JSON.stringify(photo).replace(/"/g, '&quot;');

                        photoDiv.innerHTML = `
                            <div class="w-full md:w-2/3 lg:w-3/4 flex-shrink-0">
                                <img src="${photo.url}" alt="${shareTitle}" class="w-full rounded-lg shadow-2xl">
                            </div>
                            <div class="w-full md:w-1/3 lg:w-1/4 md:sticky md:top-32 space-y-6 flex-grow">
                                <h3 class="text-2xl font-bold theme-text leading-snug transition-colors duration-500">${shareTitle}</h3>
                                ${getTrans(photo.description) ? `<p class="theme-text-muted font-light leading-relaxed text-base transition-colors duration-500">${getTrans(photo.description)}</p>` : ''}

                                <div class="pt-6 border-t theme-border space-y-3 transition-colors duration-500">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] theme-text-muted uppercase tracking-widest font-bold mb-1">${i18n[state.lang].exifCamera}</span>
                                        <span class="theme-text text-sm font-medium transition-colors duration-500">${photo.exif_body || '-'}</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] theme-text-muted uppercase tracking-widest font-bold mb-1">${i18n[state.lang].exifLens}</span>
                                        <span class="theme-text text-sm font-medium transition-colors duration-500">${photo.exif_lens || '-'}</span>
                                    </div>
                                    ${exifArr.length > 0 ? `
                                        <div class="flex flex-wrap gap-2 pt-3">
                                            ${exifArr.map(e => `<span class="px-2.5 py-1 theme-surface border theme-border rounded-md text-xs theme-text font-mono tracking-tight transition-colors duration-500">${e}</span>`).join('')}
                                        </div>
                                    ` : ''}
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-3 pt-6 border-t theme-border">
                                    <button onclick="Portfolio.downloadFramedPhoto('${safePhotoData}', '${shareUrl}')" class="flex-1 flex items-center justify-center py-3 rounded-lg theme-surface border theme-border theme-text hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-colors text-sm font-semibold">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span data-i18n="btnDownload">${i18n[state.lang].btnDownload}</span>
                                    </button>
                                    <button onclick="Portfolio.sharePhoto('${shareTitle}', '${photo.url}', '${shareUrl}')" class="flex-1 flex items-center justify-center py-3 rounded-lg theme-surface border theme-border theme-text hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-colors text-sm font-semibold">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-5.368m0 5.368l5.657 5.657m-5.657-5.657l5.657-5.657"></path></svg>
                                        <span data-i18n="btnShare">${i18n[state.lang].btnShare}</span>
                                    </button>
                                </div>
                            </div>
                        `;
                        DOM.modalContent.appendChild(photoDiv);
                        revealObserver.observe(photoDiv);
                    });
                }
            };

            const closeModal = () => {
                DOM.modal.classList.add('opacity-0');
                setTimeout(() => {
                    DOM.modal.classList.add('hidden');
                    document.body.style.overflow = '';
                    state.activePost = null;
                }, 500);
            };

            // --- 신규 기능: 하이엔드 액자 이미지 다운로드 ---
            const downloadFramedPhoto = (photoDataStr, postUrl) => {
                showToast(i18n[state.lang].toastWait);

                const photo = JSON.parse(photoDataStr.replace(/&quot;/g, '"'));
                const img = new Image();
                img.crossOrigin = "Anonymous"; // 외부 URL(CORS) 이미지 로딩 지원
                img.src = photo.url;

                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    // 갤러리 액자(Gallery Frame) 스타일 설정
                    const paddingX = 120;
                    const paddingTop = 120;
                    const paddingBottom = 400; // 하단 메타데이터 및 QR 코드가 들어갈 넓은 공간

                    canvas.width = img.width + (paddingX * 2);
                    canvas.height = img.height + paddingTop + paddingBottom;

                    // 프레임 배경 (고급스러운 갤러리 화이트)
                    ctx.fillStyle = "#ffffff";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    // 사진 그리기 (미세한 그림자 효과 추가)
                    ctx.shadowColor = "rgba(0, 0, 0, 0.1)";
                    ctx.shadowBlur = 40;
                    ctx.shadowOffsetY = 20;
                    ctx.drawImage(img, paddingX, paddingTop, img.width, img.height);

                    // 그림자 초기화
                    ctx.shadowColor = "transparent";

                    // 하단 메타데이터 텍스트 설정
                    ctx.fillStyle = "#111111";
                    ctx.textAlign = "left";

                    const textStartX = paddingX;
                    let textStartY = paddingTop + img.height + 100;

                    // 제목 그리기
                    ctx.font = "bold 60px Pretendard, sans-serif";
                    ctx.fillText(getTrans(photo.title), textStartX, textStartY);

                    // EXIF 데이터 렌더링
                    const exifArr = formatExif(photo);
                    let exifString = `${photo.exif_body || ''} · ${photo.exif_lens || ''}`;
                    if(exifArr.length > 0) exifString += ` · ${exifArr.join('  ')}`;

                    ctx.font = "30px Pretendard, monospace";
                    ctx.fillStyle = "#666666";
                    ctx.fillText(exifString, textStartX, textStartY + 70);

                    // 워터마크 및 사이트 URL
                    ctx.font = "bold 30px Pretendard, sans-serif";
                    ctx.fillStyle = "#000000";
                    ctx.fillText("KKYOUNG.PHOTO", textStartX, textStartY + 150);

                    // QR 코드 생성 및 그리기 (우측 하단)
                    const qr = new QRious({
                        value: postUrl,
                        size: 200,
                        level: 'H',
                        background: 'white',
                        foreground: 'black'
                    });

                    const qrImg = new Image();
                    qrImg.src = qr.toDataURL();
                    qrImg.onload = () => {
                        const qrX = canvas.width - paddingX - 200;
                        const qrY = paddingTop + img.height + 80;
                        ctx.drawImage(qrImg, qrX, qrY, 200, 200);

                        // 캔버스를 이미지 파일로 다운로드
                        const link = document.createElement('a');
                        link.download = `kkyoung_photo_${Date.now()}.jpg`;
                        link.href = canvas.toDataURL("image/jpeg", 0.95); // 95% 퀄리티 JPG
                        link.click();
                    };
                };

                img.onerror = () => {
                    alert('이미지를 로드하는 중 오류가 발생했습니다. (CORS 권한 문제일 수 있습니다)');
                };
            };

            // --- 신규 기능: 하이브리드 공유하기 ---
            const sharePhoto = (title, imageUrl, postUrl) => {
                const shareData = {
                    title: `KKYOUNG.PHOTO - ${title}`,
                    text: '아름다운 빛과 공간의 기록을 감상해 보세요.',
                    url: postUrl
                };

                // 1. Web Share API (모바일 네이티브 공유창 - 인스타그램, 카톡 등 자동 지원)
                if (navigator.share) {
                    navigator.share(shareData).catch(console.error);
                }
                // 2. 카카오톡 전용 공유 (데스크톱/모바일 대체)
                else if (typeof Kakao !== 'undefined' && Kakao.isInitialized()) {
                    Kakao.Share.sendDefault({
                        objectType: 'feed',
                        content: {
                            title: shareData.title,
                            description: shareData.text,
                            imageUrl: imageUrl, // 카카오톡 썸네일
                            link: { mobileWebUrl: postUrl, webUrl: postUrl },
                        },
                        buttons: [{ title: '작품 감상하기', link: { mobileWebUrl: postUrl, webUrl: postUrl } }]
                    });
                }
                // 3. Fallback: 링크 복사
                else {
                    navigator.clipboard.writeText(postUrl).then(() => {
                        showToast(i18n[state.lang].toastCopy);
                    }).catch(() => {
                        // 클립보드 복사 실패 시 구형 방식
                        const textArea = document.createElement("textarea");
                        textArea.value = postUrl;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand("copy");
                        textArea.remove();
                        showToast(i18n[state.lang].toastCopy);
                    });
                }
            };

            return { init, closeModal, downloadFramedPhoto, sharePhoto };
        })();

        document.addEventListener('DOMContentLoaded', Portfolio.init);
    </script>
</body>
</html>
