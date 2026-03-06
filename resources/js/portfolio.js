window.Portfolio = (() => {
    let state = {
        lang: 'ko', category: 'all', posts: window.PORTFOLIO_DATA || [], activePost: null, currentPhotoIndex: 0,
        viewMode: 'grid', mapInstance: null, mapLoaded: { kakao: false, google: false }, fingerprint: null
    };

    const i18n = {
        ko: { heroBioLead: 'Architecting Invisible Foundations', heroName: 'Park Kyoung Seo', toastCopy: '링크가 복사되었습니다.' },
        en: { heroBioLead: 'Architecting Invisible Foundations', heroName: 'Park Kyoung Seo', toastCopy: 'Link copied.' }
    };

    let DOM = {};
    let revealObserver;

    const initDOM = () => {
        DOM = {
            grid: document.getElementById('grid-container'), mapContainer: document.getElementById('map-container'),
            mapCanvas: document.getElementById('map-canvas'), mapLoader: document.getElementById('map-loader'),
            sectionTitle: document.getElementById('section-title'), modal: document.getElementById('photo-modal'),
            modalContainer: document.getElementById('modal-container'), modalIndicator: document.getElementById('modal-indicator'),
            modalTitleArea: document.getElementById('modal-title-area'), modalSliderContent: document.getElementById('modal-slider-content'),
            modalDots: document.getElementById('modal-dots'), langBtns: document.querySelectorAll('.lang-btn'),
            filterBtns: document.querySelectorAll('.filter-btn'), themeToggleBtn: document.getElementById('theme-toggle'),
            toast: document.getElementById('toast'), btnGrid: document.getElementById('btn-grid-view'), btnMap: document.getElementById('btn-map-view')
        };
    };

    const init = () => {
        initDOM();
        revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('active'); });
        }, { threshold: 0.1 });

        let id = localStorage.getItem('guest_fingerprint');
        if (!id) { id = 'fp_' + Math.random().toString(36).substring(2, 9) + Date.now(); localStorage.setItem('guest_fingerprint', id); }
        state.fingerprint = id;

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

    const updateLanguage = (lang) => {
        state.lang = lang;
        DOM.langBtns.forEach(btn => {
            btn.classList.toggle('theme-text', btn.dataset.lang === lang);
            btn.classList.toggle('font-bold', btn.dataset.lang === lang);
            btn.classList.toggle('theme-text-muted', btn.dataset.lang !== lang);
        });
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.dataset.i18n; if (i18n[lang] && i18n[lang][key]) el.innerHTML = i18n[lang][key];
        });
        if (state.viewMode === 'grid') renderGrid(); else renderMapMarkers();
        if (!DOM.modal.classList.contains('hidden') && state.activePost) renderActiveSlide();
    };

    const getTrans = (obj) => (typeof obj === 'string') ? obj : (obj[state.lang] || obj['ko'] || '');

    const switchView = (mode) => {
        if (state.viewMode === mode) return; state.viewMode = mode;
        if (mode === 'grid') {
            DOM.btnGrid.classList.add('view-toggle-active'); DOM.btnGrid.classList.remove('view-toggle-inactive');
            DOM.btnMap.classList.remove('view-toggle-active'); DOM.btnMap.classList.add('view-toggle-inactive');
            DOM.mapContainer.classList.add('opacity-0');
            setTimeout(() => { DOM.mapContainer.classList.add('hidden'); DOM.grid.classList.remove('hidden'); void DOM.grid.offsetWidth; DOM.grid.classList.add('opacity-100'); renderGrid(); }, 400);
        } else {
            DOM.btnMap.classList.add('view-toggle-active'); DOM.btnMap.classList.remove('view-toggle-inactive');
            DOM.btnGrid.classList.remove('view-toggle-active'); DOM.btnGrid.classList.add('view-toggle-inactive');
            DOM.grid.classList.remove('opacity-100'); DOM.grid.classList.add('opacity-0');
            setTimeout(() => { DOM.grid.classList.add('hidden'); DOM.mapContainer.classList.remove('hidden'); void DOM.mapContainer.offsetWidth; DOM.mapContainer.classList.add('opacity-100'); initHybridMap(); }, 400);
        }
    };

    const renderGrid = () => {
        DOM.grid.innerHTML = '';
        const filtered = state.category === 'all' ? state.posts : state.posts.filter(p => p.category === state.category);
        filtered.forEach((post, index) => {
            if (!post.photos || post.photos.length === 0) return;
            const card = document.createElement('div');
            card.className = 'photo-card cursor-pointer group reveal active w-full break-inside-avoid relative inline-block mb-6';
            card.style.transitionDelay = `${(index % 6) * 100}ms`;
            card.innerHTML = `
                <div class="rounded-xl theme-surface img-mask overflow-hidden w-full relative transition-colors duration-500" style="aspect-ratio: ${post.photos[0].aspect_ratio || 1.5};">
                    <img src="${post.photos[0].url}" loading="lazy" class="absolute inset-0 w-full h-full object-cover transform scale-100 group-hover:scale-105 transition-transform duration-1000 ease-spring opacity-90 group-hover:opacity-100">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-end p-6">
                        <span class="text-[10px] font-bold tracking-[0.2em] text-white/70 uppercase mb-2">${post.category}</span>
                        <h4 class="text-white font-medium text-lg leading-snug">${getTrans(post.title)}</h4>
                    </div>
                </div>
            `;
            // [문제 1 해결] 동적 요소 생성 시 openModal 이벤트 안전하게 바인딩
            card.addEventListener('click', () => window.Portfolio.openModal(post));
            DOM.grid.appendChild(card);
        });
    };

    // [문제 3 해결] 반응 데이터 GET API 연동
    const fetchReactions = async (photoId) => {
        try {
            const response = await fetch(`/api/photos/${photoId}/reactions`, { headers: { 'Accept': 'application/json' } });
            if (response.ok) {
                const data = await response.json();
                const photo = state.activePost.photos[state.currentPhotoIndex];
                if (photo && photo.id === photoId) {
                    photo.reaction_counts = data.counts; // 최신 카운트로 동기화
                    renderActiveSlide();
                }
            }
        } catch (e) {
            console.log('Reactions GET API not ready yet.');
        }
    };

    const handleReaction = async (type, photoId) => {
        const storageKey = `reacted_${photoId}_${type}`;
        const hasReacted = localStorage.getItem(storageKey);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const photo = state.activePost.photos[state.currentPhotoIndex];
        if (!photo.reaction_counts) photo.reaction_counts = {};

        // Optimistic UI 업데이트 (즉각적인 피드백)
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
                photo.reaction_counts = data.counts; // 서버 데이터로 보정
                renderActiveSlide();
            }
        } catch (e) { console.error('Reaction sync failed'); }
    };

    const openModal = (post) => {
        state.activePost = post; state.currentPhotoIndex = 0;
        renderActiveSlide();
        fetchReactions(post.photos[0].id); // 모달 열릴 때 최신 반응 로드

        DOM.modal.classList.remove('hidden'); document.body.style.overflow = 'hidden';
        void DOM.modal.offsetWidth; DOM.modal.classList.add('opacity-100'); DOM.modal.classList.remove('opacity-0');
        DOM.modalContainer.classList.remove('scale-95');
    };

    const closeModal = () => {
        DOM.modalContainer.classList.add('scale-95'); DOM.modal.classList.add('opacity-0');
        setTimeout(() => { DOM.modal.classList.add('hidden'); document.body.style.overflow = ''; state.activePost = null; }, 500);
    };

    const nextPhoto = () => {
        if (!state.activePost) return; state.currentPhotoIndex = (state.currentPhotoIndex + 1) % state.activePost.photos.length;
        renderActiveSlide(); fetchReactions(state.activePost.photos[state.currentPhotoIndex].id);
    };

    const prevPhoto = () => {
        if (!state.activePost) return; state.currentPhotoIndex = (state.currentPhotoIndex - 1 + state.activePost.photos.length) % state.activePost.photos.length;
        renderActiveSlide(); fetchReactions(state.activePost.photos[state.currentPhotoIndex].id);
    };

    const renderActiveSlide = () => {
        const post = state.activePost; const photo = post.photos[state.currentPhotoIndex]; if (!photo) return;
        DOM.modalIndicator.textContent = `${String(state.currentPhotoIndex + 1).padStart(2, '0')} / ${String(post.photos.length).padStart(2, '0')}`;

        const titleStr = getTrans(photo.title);
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
            <div class="w-full md:w-[65%] h-[50vh] md:h-auto flex items-center justify-center p-4 md:p-10 slide-fade-enter bg-black/5 dark:bg-black/20">
                <img src="${photo.url}" class="max-w-full max-h-full object-contain rounded-lg shadow-xl">
            </div>
            <div class="w-full md:w-[35%] flex flex-col justify-center p-8 md:p-10 space-y-6 slide-fade-enter overflow-y-auto">
                <div class="space-y-3">
                    <h3 class="text-xl md:text-2xl font-bold theme-text leading-tight">${titleStr}</h3>
                    <p class="theme-text-muted font-light text-sm leading-relaxed">${getTrans(photo.description)}</p>
                </div>
                <div class="flex flex-wrap gap-2 pt-2">
                    ${reactions.map(r => {
                        const count = (photo.reaction_counts && photo.reaction_counts[r.key]) || 0;
                        const isActive = localStorage.getItem(`reacted_${photo.id}_${r.key}`);
                        return `<button onclick="window.Portfolio.handleReaction('${r.key}', ${photo.id})" class="reaction-btn flex items-center space-x-2 px-3 py-1.5 rounded-full border theme-border theme-surface ${isActive ? 'active' : ''}"><span class="text-base">${r.icon}</span><span class="text-xs theme-text-muted transition-colors">${count}</span></button>`;
                    }).join('')}
                </div>
                <div class="pt-6 border-t theme-border grid grid-cols-2 gap-4">
                    <div class="flex flex-col"><span class="text-[9px] theme-text-muted uppercase font-bold mb-1">Camera</span><span class="theme-text text-xs font-medium truncate">${photo.exif_body || '-'}</span></div>
                    <div class="flex flex-col"><span class="text-[9px] theme-text-muted uppercase font-bold mb-1">Lens</span><span class="theme-text text-xs font-medium truncate">${photo.exif_lens || '-'}</span></div>
                    <div class="col-span-2 flex flex-wrap gap-1.5 pt-1">${exifArr.map(e => `<span class="px-2 py-0.5 bg-surface border theme-border rounded text-[9px] theme-text font-mono">${e}</span>`).join('')}</div>
                </div>
                <div class="flex gap-2 pt-2">
                    <!-- [문제 2 해결] action-btn 적용 -->
                    <button onclick="window.Portfolio.downloadFramedPhoto('${safePhotoData}', '${shareUrl}')" class="action-btn"><span>DOWNLOAD</span></button>
                    <button onclick="window.Portfolio.sharePhoto('${titleStr}', '${photo.url}', '${shareUrl}')" class="action-btn"><span>SHARE</span></button>
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
        if (!state.mapInstance) { state.mapInstance = new kakao.maps.Map(DOM.mapCanvas, { center: new kakao.maps.LatLng(first.lat, first.lng), level: 11 }); }
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

    const showToast = (message) => {
        DOM.toast.textContent = message; DOM.toast.classList.remove('opacity-0', 'translate-y-10');
        setTimeout(() => { DOM.toast.classList.add('opacity-0', 'translate-y-10'); }, 3000);
    };

    const downloadFramedPhoto = (photoDataStr, postUrl) => {
        showToast('준비 중입니다...');
        const photo = JSON.parse(photoDataStr); const img = new Image(); img.crossOrigin = "Anonymous"; img.src = photo.url;
        img.onload = () => {
            const canvas = document.createElement('canvas'); const ctx = canvas.getContext('2d');
            const px = 120, pt = 120, pb = 400; canvas.width = img.width + (px * 2); canvas.height = img.height + pt + pb;
            ctx.fillStyle = "#ffffff"; ctx.fillRect(0, 0, canvas.width, canvas.height); ctx.drawImage(img, px, pt, img.width, img.height);
            ctx.fillStyle = "#111111"; ctx.font = "bold 60px Pretendard, sans-serif"; ctx.fillText(getTrans(photo.title), px, pt + img.height + 100);
            const qr = new QRious({ value: postUrl, size: 180, level: 'H' }); const qrImg = new Image(); qrImg.src = qr.toDataURL();
            qrImg.onload = () => { ctx.drawImage(qrImg, canvas.width - px - 180, pt + img.height + 80, 180, 180); const link = document.createElement('a'); link.download = `photo.jpg`; link.href = canvas.toDataURL("image/jpeg", 0.95); link.click(); };
        };
    };

    const sharePhoto = (title, imageUrl, postUrl) => {
        if (navigator.share) { navigator.share({ title: `PHOTO - ${title}`, url: postUrl }); }
        else { navigator.clipboard.writeText(postUrl).then(() => showToast('Link Copied!')); }
    };

    // [문제 1 해결] 외부에서 함수 접근 가능하도록 Export
    return {
        init, switchView, openModal, closeModal, nextPhoto, prevPhoto, downloadFramedPhoto, sharePhoto, handleReaction
    };
})();
