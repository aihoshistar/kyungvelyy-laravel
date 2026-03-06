// 글로벌 환경(window)에 바인딩하여 Blade의 인라인 onclick 이벤트에서 접근 가능하게 설정
window.Portfolio = (() => {
    let state = {
        lang: 'ko', category: 'all', posts: window.PORTFOLIO_DATA || [], activePost: null, currentPhotoIndex: 0,
        viewMode: 'grid', mapInstance: null, mapLoaded: { kakao: false, google: false }, fingerprint: null
    };

    const i18n = {
        ko: {
            navGallery: '갤러리', heroBioLead: 'Architecting Invisible Foundations', heroName: 'Park Kyoung Seo',
            heroRole: 'Blockchain Infrastructure Engineer & Documentary Photographer',
            heroCatchphrase: '보이지 않는 구조를 설계하고,<br class="hidden md:block">보이는 찰나의 균형을 기록합니다.',
            heroBioText1: '분산 네트워크의 견고한 합의를 이끌어내는 인프라 엔지니어로서, 저는 무너지지 않는 시스템의 기반을 설계합니다. 보이지 않는 곳에서 노드 간의 질서를 구축하는 일은 저에게 단순한 직업 이상의 \'구조적 완성\'에 대한 탐구입니다.',
            heroBioText2: '모니터 밖으로 걸어 나올 때, 저의 시선은 도시의 복잡함 속에 숨겨진 정적과 빛의 조화를 향합니다. 시스템 설계에서의 치밀함은 카메라 렌즈 너머로 완벽한 수평과 수직의 균형을 찾는 미학적 집착으로 이어집니다.',
            heroBioText3: '기술은 세상을 지탱하는 골격이고, 예술은 그 공간을 채우는 빛입니다. 저는 이 두 세계의 접점에서 기록하는 사람으로 살아가고 있습니다.',
            galleryTitle: 'Photography', mapTitle: 'Locations', catAll: '전체보기', catLandscape: 'Landscape', catCityscape: 'Cityscape',
            exifCamera: '카메라', exifLens: '렌즈', btnShare: '공유하기', btnDownload: '프레임 저장', toastCopy: '링크가 복사되었습니다.', toastWait: '이미지를 생성 중입니다...'
        },
        en: {
            navGallery: 'Gallery', heroBioLead: 'Architecting Invisible Foundations', heroName: 'Park Kyoung Seo',
            heroRole: 'Blockchain Infrastructure Engineer & Documentary Photographer',
            heroCatchphrase: 'Architecting Invisible Foundations,<br class="hidden md:block">Capturing Visible Balance.',
            heroBioText1: 'As an infrastructure engineer building robust consensus in decentralized networks, I design foundations that never fail. Building order among nodes is more than a job—it is a pursuit of structural perfection.',
            heroBioText2: 'When I step away from the monitor, my eyes seek the stillness and harmony of light hidden within urban complexity. The precision in system design translates into an aesthetic obsession with finding perfect balance behind the lens.',
            heroBioText3: 'Technology is the skeleton that supports the world, and art is the light that fills its spaces. I live as a chronicler at the intersection of these two worlds.',
            galleryTitle: 'Selected Works', mapTitle: 'Photo Map', catAll: 'All Works', catLandscape: 'Landscape', catCityscape: 'Cityscape',
            exifCamera: 'Camera', exifLens: 'Lens', btnShare: 'Share', btnDownload: 'Save Frame', toastCopy: 'Link copied.', toastWait: 'Generating...'
        },
        ja: {
            navGallery: 'ギャラリー', heroBioLead: 'Architecting Invisible Foundations', heroName: 'Park Kyoung Seo',
            heroRole: 'Blockchain Infrastructure Engineer & Documentary Photographer',
            heroCatchphrase: '目に見えない構造を設計し、<br class="hidden md:block">目に見える刹那の均衡を記録します。',
            heroBioText1: '分散型ネットワークの堅固な合意を導き出すインフラエンジニアとして、私は崩れないシステムの基盤を設計します。目に見えない場所でノード間の秩序を構築することは、私にとって単なる職業以上の「構造的完成」への探求です。',
            heroBioText2: 'モニターの外へ歩み出す時、私の視線は都市の複雑さの中に隠された静寂と光の調和に向かいます。システム設計における緻密さは、カメラレンズの向こう側で完璧な水平と垂直の均衡を探す美的な執着へと繋がります。',
            heroBioText3: '技術は世界を支える骨格であり、芸術はその空間を満たす光です。私はこれら二つの世界の接点で記録する者として生きています。',
            galleryTitle: '写真作品', mapTitle: '撮影場所', catAll: 'すべて', catLandscape: 'Landscape', catCityscape: 'Cityscape',
            exifCamera: 'カメラ', exifLens: 'レンズ', btnShare: '共有', btnDownload: '保存', toastCopy: 'リンクをコピーしました。', toastWait: '準備中...'
        }
    };

    let DOM = {};
    let revealObserver;

    const initDOM = () => {
        DOM = {
            grid: document.getElementById('grid-container'), mapContainer: document.getElementById('map-container'),
            mapCanvas: document.getElementById('map-canvas'), sectionTitle: document.getElementById('section-title'),
            modal: document.getElementById('photo-modal'), modalContainer: document.getElementById('modal-container'),
            modalIndicator: document.getElementById('modal-indicator'), modalTitleArea: document.getElementById('modal-title-area'),
            modalSliderContent: document.getElementById('modal-slider-content'), modalDots: document.getElementById('modal-dots'),
            langBtns: document.querySelectorAll('.lang-btn'), filterBtns: document.querySelectorAll('.filter-btn'),
            themeToggleBtn: document.getElementById('theme-toggle'), toast: document.getElementById('toast'),
            profileHeader: document.getElementById('profile-header'),
            mapLoader: document.getElementById('map-loader')
        };
    };

    const init = () => {
        initDOM();
        revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('active'); } });
        }, { threshold: 0.1 });

        let id = localStorage.getItem('guest_fingerprint') || ('fp_' + Math.random().toString(36).substr(2, 9) + Date.now());
        localStorage.setItem('guest_fingerprint', id); state.fingerprint = id;

        bindEvents(); initTheme(); updateLanguage(state.lang);
        document.querySelectorAll('.reveal').forEach(el => { el.classList.remove('active'); revealObserver.observe(el); });
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
        document.addEventListener('keydown', e => {
            if (DOM.modal.classList.contains('hidden')) return;
            if (e.key === 'ArrowRight') nextPhoto(); if (e.key === 'ArrowLeft') prevPhoto(); if (e.key === 'Escape') closeModal();
        });
    };

    const initTheme = () => {
        const isDark = document.documentElement.classList.contains('dark');
        document.getElementById('theme-toggle-light-icon').classList.toggle('hidden', !isDark);
        document.getElementById('theme-toggle-dark-icon').classList.toggle('hidden', isDark);
    };

    const toggleTheme = () => {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        initTheme(); if(state.viewMode === 'map') renderMapMarkers();
    };

    const showToast = (message) => {
        DOM.toast.textContent = message; DOM.toast.classList.remove('opacity-0', 'translate-y-10');
        setTimeout(() => DOM.toast.classList.add('opacity-0', 'translate-y-10'), 3000);
    };

    const updateLanguage = (lang) => {
        state.lang = lang;
        DOM.langBtns.forEach(btn => {
            btn.classList.toggle('theme-text', btn.dataset.lang === lang);
            btn.classList.toggle('font-bold', btn.dataset.lang === lang);
            btn.classList.toggle('theme-text-muted', btn.dataset.lang !== lang);
        });
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.dataset.i18n; if (i18n[lang][key]) el.innerHTML = i18n[lang][key];
        });
        DOM.sectionTitle.innerHTML = state.viewMode === 'grid' ? i18n[lang].galleryTitle : i18n[lang].mapTitle;
        if (state.viewMode === 'grid') renderGrid(); else renderMapMarkers();
        if (!DOM.modal.classList.contains('hidden') && state.activePost) renderActiveSlide();
    };

    const getTrans = (obj) => (typeof obj === 'string') ? obj : (obj[state.lang] || obj['ko'] || '');

    const switchView = (mode) => {
        if (state.viewMode === mode) return; state.viewMode = mode;
        if (mode === 'grid') {
            document.getElementById('btn-grid-view').classList.add('bg-black', 'text-white');
            document.getElementById('btn-map-view').classList.remove('bg-black', 'text-white');
            DOM.mapContainer.classList.add('hidden', 'opacity-0'); DOM.grid.classList.remove('hidden'); void DOM.grid.offsetWidth; DOM.grid.classList.add('opacity-100'); renderGrid();
        } else {
            document.getElementById('btn-map-view').classList.add('bg-black', 'text-white');
            document.getElementById('btn-grid-view').classList.remove('bg-black', 'text-white');
            DOM.grid.classList.add('hidden', 'opacity-0'); DOM.mapContainer.classList.remove('hidden'); void DOM.mapContainer.offsetWidth; DOM.mapContainer.classList.add('opacity-100'); initHybridMap();
        }
    };

    const renderGrid = () => {
        DOM.grid.innerHTML = '';
        const filtered = state.category === 'all' ? state.posts : state.posts.filter(p => p.category === state.category);
        filtered.forEach((post, index) => {
            if (!post.photos?.length) return;
            const card = document.createElement('div');
            card.className = 'photo-card cursor-pointer group reveal active w-full break-inside-avoid relative inline-block mb-6';
            card.style.transitionDelay = `${(index % 6) * 100}ms`;
            card.innerHTML = `
                <div class="rounded-xl theme-surface img-mask overflow-hidden w-full relative transition-colors duration-500" style="aspect-ratio: ${post.photos[0].aspect_ratio || 1.5};">
                    <img src="${post.photos[0].url}" alt="${getTrans(post.title)}" loading="lazy" class="absolute inset-0 w-full h-full object-cover transform scale-100 group-hover:scale-105 transition-transform duration-1000 ease-spring opacity-90 group-hover:opacity-100">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-end p-6">
                        <span class="text-[10px] font-bold tracking-[0.2em] text-white/70 uppercase mb-2">${post.category}</span>
                        <h4 class="text-white font-medium text-lg leading-snug drop-shadow-lg">${getTrans(post.title)}</h4>
                    </div>
                </div>
            `;
            card.addEventListener('click', () => openModal(post));
            DOM.grid.appendChild(card);
        });
    };

    const fetchReactions = async (photoId) => {
        try {
            const response = await fetch(`/api/photos/${photoId}/reactions`, { headers: { 'Accept': 'application/json' } });
            if (response.ok) {
                const data = await response.json();
                const photo = state.activePost.photos[state.currentPhotoIndex];
                if (photo && photo.id === photoId) {
                    photo.reaction_counts = data.counts;
                    renderActiveSlide();
                }
            }
        } catch (e) { console.log('Reactions fetch skipped (API not ready)'); }
    };

    const handleReaction = async (type, photoId) => {
        const storageKey = `reacted_${photoId}_${type}`;
        const hasReacted = localStorage.getItem(storageKey);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const photo = state.activePost.photos[state.currentPhotoIndex];
        if (!photo.reaction_counts) photo.reaction_counts = {};

        // Optimistic UI Update
        if (hasReacted) { photo.reaction_counts[type] = Math.max(0, (photo.reaction_counts[type] || 1) - 1); localStorage.removeItem(storageKey); }
        else { photo.reaction_counts[type] = (photo.reaction_counts[type] || 0) + 1; localStorage.setItem(storageKey, 'true'); }
        renderActiveSlide();

        try {
            const response = await fetch(`/api/photos/${photoId}/react`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ type: type, fingerprint: state.fingerprint })
            });
            if (response.ok) {
                const data = await response.json();
                photo.reaction_counts = data.counts; // Server Sync
                renderActiveSlide();
            }
        } catch (e) { console.error('Reaction sync failed'); }
    };

    const openModal = (post) => {
        state.activePost = post; state.currentPhotoIndex = 0; renderActiveSlide();
        fetchReactions(post.photos[0].id);

        DOM.modal.classList.remove('hidden'); document.body.style.overflow = 'hidden';
        void DOM.modal.offsetWidth; DOM.modal.classList.add('opacity-100'); DOM.modal.classList.remove('opacity-0');
        DOM.modalContainer.classList.remove('scale-95');
    };

    const closeModal = () => {
        DOM.modalContainer.classList.add('scale-95'); DOM.modal.classList.add('opacity-0');
        setTimeout(() => { DOM.modal.classList.add('hidden'); document.body.style.overflow = ''; state.activePost = null; }, 500);
    };

    const nextPhoto = () => {
        if (!state.activePost) return;
        state.currentPhotoIndex = (state.currentPhotoIndex + 1) % state.activePost.photos.length;
        renderActiveSlide(); fetchReactions(state.activePost.photos[state.currentPhotoIndex].id);
    };

    const prevPhoto = () => {
        if (!state.activePost) return;
        state.currentPhotoIndex = (state.currentPhotoIndex - 1 + state.activePost.photos.length) % state.activePost.photos.length;
        renderActiveSlide(); fetchReactions(state.activePost.photos[state.currentPhotoIndex].id);
    };

    const renderActiveSlide = () => {
        const post = state.activePost; const photo = post.photos[state.currentPhotoIndex]; if (!photo) return;
        DOM.modalIndicator.textContent = `${String(state.currentPhotoIndex + 1).padStart(2, '0')} / ${String(post.photos.length).padStart(2, '0')}`;

        const titleStr = typeof photo.title === 'string' ? photo.title : (photo.title[state.lang] || photo.title['ko'] || '');
        DOM.modalTitleArea.innerHTML = `<div class="flex items-center space-x-2"><span class="text-[9px] px-1.5 py-0.5 rounded border theme-border theme-text-muted font-bold uppercase">${post.category}</span><h2 class="text-sm md:text-base font-semibold truncate max-w-[200px]">${titleStr}</h2></div>`;
        DOM.modalDots.innerHTML = post.photos.map((_, i) => `<div class="w-1 h-1 rounded-full transition-all duration-300 ${i === state.currentPhotoIndex ? 'bg-white w-3' : 'bg-white/30'}"></div>`).join('');

        const exifArr = [];
        if (photo.exif_focal_length) exifArr.push(`${photo.exif_focal_length}mm`);
        if (photo.exif_aperture) exifArr.push(`f/${parseFloat(photo.exif_aperture)}`);
        if (photo.exif_iso) exifArr.push(`ISO ${photo.exif_iso}`);

        const reactions = [{ key: 'love', icon: '❤️' }, { key: 'fire', icon: '🔥' }, { key: 'inspired', icon: '✨' }, { key: 'calm', icon: '🧘' }];
        const shareUrl = `${window.location.origin}${window.location.pathname}#post-${post.id}`;
        const safePhotoData = JSON.stringify(photo).replace(/"/g, '&quot;');

        DOM.modalSliderContent.innerHTML = `
            <div class="w-full md:w-[65%] h-[50vh] md:h-auto flex items-center justify-center p-4 md:p-10 slide-fade-enter bg-black/5 dark:bg-black/20"><img src="${photo.url}" class="max-w-full max-h-full object-contain rounded-lg shadow-xl"></div>
            <div class="w-full md:w-[35%] flex flex-col justify-center p-8 md:p-10 space-y-6 slide-fade-enter overflow-y-auto">
                <div class="space-y-3">
                    <h3 class="text-xl md:text-2xl font-bold theme-text leading-tight">${titleStr}</h3>
                    <p class="theme-text-muted font-light text-sm leading-relaxed">${(typeof photo.description === 'string' ? photo.description : (photo.description ? photo.description[state.lang] : '')) || ''}</p>
                </div>
                <div class="flex flex-wrap gap-2 pt-2">
                    ${reactions.map(r => {
                        const count = (photo.reaction_counts && photo.reaction_counts[r.key]) || 0;
                        const isActive = localStorage.getItem(`reacted_${photo.id}_${r.key}`);
                        return `<button onclick="window.Portfolio.handleReaction('${r.key}', ${photo.id})" class="reaction-btn flex items-center space-x-2 px-3 py-1.5 rounded-full border theme-border theme-surface ${isActive ? 'active' : ''}"><span class="text-base">${r.icon}</span><span class="text-xs theme-text-muted transition-colors">${count}</span></button>`;
                    }).join('')}
                </div>
                <div class="pt-6 border-t theme-border grid grid-cols-2 gap-4">
                    <div class="flex flex-col"><span class="text-[9px] theme-text-muted uppercase font-bold mb-1">${i18n[state.lang].exifCamera}</span><span class="theme-text text-xs font-medium truncate">${photo.exif_body || '-'}</span></div>
                    <div class="flex flex-col"><span class="text-[9px] theme-text-muted uppercase font-bold mb-1">${i18n[state.lang].exifLens}</span><span class="theme-text text-xs font-medium truncate">${photo.exif_lens || '-'}</span></div>
                    <div class="col-span-2 flex flex-wrap gap-1.5 pt-1">${exifArr.map(e => `<span class="px-2 py-0.5 bg-surface border theme-border rounded text-[9px] theme-text font-mono">${e}</span>`).join('')}</div>
                </div>
                <div class="flex gap-2 pt-2">
                    <button onclick="window.Portfolio.downloadFramedPhoto('${safePhotoData}', '${shareUrl}')" class="action-btn">
                        <span>${i18n[state.lang].btnDownload || 'DOWNLOAD'}</span>
                    </button>
                    <button onclick="window.Portfolio.sharePhoto('${titleStr}', '${photo.url}', '${shareUrl}')" class="action-btn">
                        <span>${i18n[state.lang].btnShare || 'SHARE'}</span>
                    </button>
                </div>
            </div>
        `;
        requestAnimationFrame(() => { Array.from(DOM.modalSliderContent.children).forEach(child => { child.classList.remove('slide-fade-enter'); child.classList.add('slide-fade-active'); }); });
    };

    const initHybridMap = () => {
        const filtered = state.category === 'all' ? state.posts : state.posts.filter(p => p.category === state.category); if (!filtered.length) return;
        const isKoreaMode = filtered.some(p => p.country === 'South Korea');
        if (DOM.mapLoader) DOM.mapLoader.classList.remove('hidden');
        if (isKoreaMode) {
            if (state.mapLoaded.kakao) drawKakaoMap(filtered);
            else {
                const script = document.createElement('script'); script.src = `//dapi.kakao.com/v2/maps/sdk.js?appkey=${window.KAKAO_MAP_API_KEY}&autoload=false`;
                script.onload = () => kakao.maps.load(() => { state.mapLoaded.kakao = true; drawKakaoMap(filtered); }); document.head.appendChild(script);
            }
        } else {
            if (state.mapLoaded.google) drawGoogleMap(filtered);
            else {
                const script = document.createElement('script'); script.src = `https://maps.googleapis.com/maps/api/js?key=${window.GOOGLE_MAP_API_KEY}&callback=initGoogleMapEngine`;
                window.initGoogleMapEngine = () => { state.mapLoaded.google = true; drawGoogleMap(filtered); }; document.head.appendChild(script);
            }
        }
    };

    const drawKakaoMap = (posts) => {
        if (DOM.mapLoader) DOM.mapLoader.classList.add('hidden');
        const first = posts.find(p => p.lat && p.lng) || {lat: 37.56, lng: 126.97};
        if (!state.mapInstance) state.mapInstance = new kakao.maps.Map(DOM.mapCanvas, { center: new kakao.maps.LatLng(first.lat, first.lng), level: 11 });
        else { state.mapInstance.relayout(); state.mapInstance.setCenter(new kakao.maps.LatLng(first.lat, first.lng)); }
        posts.forEach(post => {
            if (!post.lat || !post.photos?.length) return;
            const content = document.createElement('div'); content.className = 'w-12 h-12 rounded-full border-2 border-white shadow-xl bg-cover bg-center cursor-pointer transition-transform hover:scale-110';
            content.style.backgroundImage = `url(${post.photos[0].url})`;
            content.addEventListener('click', () => openModal(post));
            new kakao.maps.CustomOverlay({ position: new kakao.maps.LatLng(post.lat, post.lng), content: content, yAnchor: 1.2 }).setMap(state.mapInstance);
        });
    };

    const drawGoogleMap = (posts) => {
        if (DOM.mapLoader) DOM.mapLoader.classList.add('hidden');
        const first = posts.find(p => p.lat && p.lng) || {lat: 37.56, lng: 126.97};
        state.mapInstance = new google.maps.Map(DOM.mapCanvas, { zoom: 6, center: { lat: parseFloat(first.lat), lng: parseFloat(first.lng) }, mapTypeControl: false });
        posts.forEach(post => {
            if (!post.lat || !post.photos?.length) return;
            new google.maps.Marker({ position: { lat: parseFloat(post.lat), lng: parseFloat(post.lng) }, map: state.mapInstance }).addListener("click", () => openModal(post));
        });
    };

    const renderMapMarkers = () => { if(state.viewMode === 'map') initHybridMap(); };

    const downloadFramedPhoto = (photoDataStr, postUrl) => {
        showToast(i18n[state.lang].toastWait || '준비 중입니다...');
        const photo = JSON.parse(photoDataStr); const img = new Image(); img.crossOrigin = "Anonymous"; img.src = photo.url;
        img.onload = () => {
            const canvas = document.createElement('canvas'); const ctx = canvas.getContext('2d');
            const px = 120, pt = 120, pb = 400; canvas.width = img.width + (px * 2); canvas.height = img.height + pt + pb;
            ctx.fillStyle = "#ffffff"; ctx.fillRect(0, 0, canvas.width, canvas.height); ctx.drawImage(img, px, pt, img.width, img.height);
            ctx.fillStyle = "#111111"; ctx.font = "bold 60px Pretendard, sans-serif"; ctx.fillText((typeof photo.title === 'string' ? photo.title : photo.title['ko']), px, pt + img.height + 100);
            const qr = new QRious({ value: postUrl, size: 180, level: 'H' }); const qrImg = new Image(); qrImg.src = qr.toDataURL();
            qrImg.onload = () => { ctx.drawImage(qrImg, canvas.width - px - 180, pt + img.height + 80, 180, 180); const link = document.createElement('a'); link.download = `photo.jpg`; link.href = canvas.toDataURL("image/jpeg", 0.95); link.click(); };
        };
    };

    const sharePhoto = (title, imageUrl, postUrl) => {
        if (navigator.share) navigator.share({ title: `PHOTO - ${title}`, url: postUrl });
        else { navigator.clipboard.writeText(postUrl).then(() => showToast(i18n[state.lang].toastCopy || 'Link Copied!')); }
    };

    // Public API 반환
    return { init, switchView, closeModal, openModal, nextPhoto, prevPhoto, downloadFramedPhoto, sharePhoto, handleReaction };
})();
