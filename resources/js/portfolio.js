window.Portfolio = (() => {
    let state = {
        lang: window.CURRENT_LOCALE || 'ko',
        category: 'all',
        country: 'all',
        camera: 'all',
        posts: window.PORTFOLIO_DATA || [],
        activePost: null,
        currentPhotoIndex: 0,
        viewMode: 'grid',
        mapInstance: null,
        mapLoaded: { kakao: false, google: false },
        fingerprint: null,
        isGridActive: false,
        mapMarkers: [],
        lenis: null
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
            toast: document.getElementById('toast'), btnGrid: document.getElementById('btn-grid-view'), btnMap: document.getElementById('btn-map-view'),
            countryFilter: document.getElementById('country-filter'), cameraFilter: document.getElementById('camera-filter'),
            shortcutOverlay: document.getElementById('shortcut-overlay')
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

        const i18nFallback = {
            ko: { filterAllCountries: '모든 국가', filterAllCameras: '모든 기종', colorPalette: 'Color Palette' },
            en: { filterAllCountries: 'All Countries', filterAllCameras: 'All Cameras', colorPalette: 'Color Palette' },
            ja: { filterAllCountries: 'すべての国', filterAllCameras: 'すべてのカメラ', colorPalette: 'Color Palette' }
        };
        ['ko', 'en', 'ja'].forEach(l => {
            window.I18N[l] = { ...i18nFallback[l], ...window.I18N[l] };
        });

        initLenis();
        initFilters();
        bindEvents();
        bindShortcuts();
        initTheme();
        updateLanguage(state.lang);
        document.querySelectorAll('.reveal').forEach(el => { el.classList.remove('active'); revealObserver.observe(el); });

        checkUrlHash();
    };

    const initLenis = () => {
        if (typeof Lenis !== 'undefined') {
            state.lenis = new Lenis({
                duration: 1.2,
                easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
                direction: 'vertical',
                gestureDirection: 'vertical',
                smooth: true,
                smoothTouch: false,
                touchMultiplier: 2,
            });

            function raf(time) {
                state.lenis.raf(time);
                requestAnimationFrame(raf);
            }
            requestAnimationFrame(raf);
        }
    };

    const bindShortcuts = () => {
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            const key = e.key.toLowerCase();
            const isModalOpen = !DOM.modal.classList.contains('hidden');

            if (key === '?') {
                toggleShortcutHelp();
            } else if (key === 'd') {
                toggleTheme();
            } else if (key === 'f') {
                if (!document.fullscreenElement) document.documentElement.requestFullscreen().catch(() => {});
                else document.exitFullscreen();
            } else if (!isModalOpen) {
                if (key === 'g') switchView('grid');
                else if (key === 'm') switchView('map');
            }
        });

        if (DOM.shortcutOverlay) {
            DOM.shortcutOverlay.addEventListener('click', (e) => {
                if (e.target === DOM.shortcutOverlay) toggleShortcutHelp();
            });
        }
    };

    const toggleShortcutHelp = () => {
        if (!DOM.shortcutOverlay) return;
        const isHidden = DOM.shortcutOverlay.classList.contains('hidden');
        if (isHidden) {
            DOM.shortcutOverlay.classList.remove('hidden');
            void DOM.shortcutOverlay.offsetWidth;
            DOM.shortcutOverlay.classList.remove('opacity-0');
            DOM.shortcutOverlay.children[0].classList.remove('scale-95');
        } else {
            DOM.shortcutOverlay.classList.add('opacity-0');
            DOM.shortcutOverlay.children[0].classList.add('scale-95');
            setTimeout(() => DOM.shortcutOverlay.classList.add('hidden'), 300);
        }
    };

    const initFilters = () => {
        const countries = [...new Set(state.posts.map(p => p.country).filter(Boolean))].sort();
        const cameras = [...new Set(state.posts.flatMap(p => p.photos.map(ph => ph.exif_body)).filter(Boolean))].sort();

        if (DOM.countryFilter) {
            countries.forEach(c => {
                const option = document.createElement('option');
                option.value = c; option.textContent = c;
                DOM.countryFilter.appendChild(option);
            });
            DOM.countryFilter.addEventListener('change', (e) => {
                state.country = e.target.value;
                if (state.viewMode === 'grid') renderGrid(); else initHybridMap();
            });
        }

        if (DOM.cameraFilter) {
            cameras.forEach(c => {
                const option = document.createElement('option');
                option.value = c; option.textContent = c;
                DOM.cameraFilter.appendChild(option);
            });
            DOM.cameraFilter.addEventListener('change', (e) => {
                state.camera = e.target.value;
                if (state.viewMode === 'grid') renderGrid(); else initHybridMap();
            });
        }
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
            if (e.key === 'Escape') {
                if (DOM.shortcutOverlay && !DOM.shortcutOverlay.classList.contains('hidden')) {
                    toggleShortcutHelp();
                } else if (!DOM.modal.classList.contains('hidden')) {
                    closeModal();
                }
            }
            if (DOM.modal.classList.contains('hidden')) return;
            if (e.key === 'ArrowRight') nextPhoto(); 
            if (e.key === 'ArrowLeft') prevPhoto(); 
        });

        let touchStartX = 0;
        let touchStartY = 0;

        DOM.modalContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });

        DOM.modalContainer.addEventListener('touchend', (e) => {
            if (DOM.modal.classList.contains('hidden') || !state.activePost) return;
            const touchEndX = e.changedTouches[0].screenX;
            const touchEndY = e.changedTouches[0].screenY;
            const xDiff = touchStartX - touchEndX;
            const yDiff = touchStartY - touchEndY;

            if (Math.abs(xDiff) > Math.abs(yDiff) && Math.abs(xDiff) > 50) {
                if (xDiff > 0) nextPhoto();
                else prevPhoto();
            }
        }, { passive: true });
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

        const currentDictionary = window.I18N[lang];
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.dataset.i18n;
            if (currentDictionary && currentDictionary[key]) el.innerHTML = currentDictionary[key];
        });

        if (state.viewMode === 'grid') renderGrid(); else renderMapMarkers();
        if (!DOM.modal.classList.contains('hidden') && state.activePost) renderActiveSlide();
    };

    const getTrans = (obj) => (typeof obj === 'string') ? obj : (obj[state.lang] || obj['ko'] || '');

    const getFilteredPosts = () => {
        return state.posts.filter(p => {
            const matchCat = state.category === 'all' || p.category === state.category;
            const matchCountry = state.country === 'all' || p.country === state.country;
            const matchCamera = state.camera === 'all' || (p.photos && p.photos.some(ph => ph.exif_body === state.camera));
            return matchCat && matchCountry && matchCamera;
        });
    };

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
        const filtered = getFilteredPosts();
        filtered.forEach((post, index) => {
            if (!post.photos || post.photos.length === 0) return;
            const card = document.createElement('div');
            card.className = 'photo-card cursor-pointer group reveal active w-full break-inside-avoid relative inline-block mb-6';
            card.style.transitionDelay = `${(index % 6) * 100}ms`;
            const catTranslation = window.I18N[state.lang][`cat${post.category}`] || post.category;

            // [추가] Blur-up 효과: 처음엔 블러된 상태로 시작, 로드가 완료되면 JS로 블러를 품
            card.innerHTML = `
                <div class="rounded-xl theme-surface img-mask overflow-hidden w-full relative transition-colors duration-500 bg-gray-200 dark:bg-gray-800" style="aspect-ratio: ${post.photos[0].aspect_ratio || 1.5};">
                    <!-- Blur Placeholder Skeleton -->
                    <div class="skeleton-bg absolute inset-0 bg-white/5 animate-pulse z-0 transition-opacity duration-[1.5s]"></div>
                    <!-- Actual Image with progressive fade-in -->
                    <img crossorigin="anonymous" loading="lazy" class="grid-img absolute inset-0 w-full h-full object-cover transform scale-110 blur-xl opacity-0 transition-all duration-[1.2s] ease-out z-10 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-end p-6 md:p-8 z-20">
                        <span class="text-xs md:text-sm font-bold tracking-[0.2em] text-white/80 uppercase mb-2">${catTranslation}</span>
                        <h4 class="text-white font-semibold text-xl md:text-2xl leading-snug drop-shadow-md">${getTrans(post.title)}</h4>
                    </div>
                </div>
            `;
            
            const imgEl = card.querySelector('.grid-img');
            const skeleton = card.querySelector('.skeleton-bg');

            // [추가] 이미지가 로딩 완료되었을 때 실행되는 Blur-up 해제 로직
            imgEl.onload = () => {
                imgEl.classList.remove('scale-110', 'blur-xl', 'opacity-0');
                imgEl.classList.add('scale-100', 'blur-0', 'opacity-90', 'group-hover:opacity-100');
                if(skeleton) skeleton.style.opacity = '0';
            };
            
            // src를 나중에 할당하여 브라우저가 캐싱된 이미지라도 onload 이벤트를 확실히 태우도록 함
            imgEl.src = post.photos[0].url;

            card.addEventListener('click', () => window.Portfolio.openModal(post));
            DOM.grid.appendChild(card);
        });
    };

    const extractPaletteFromImage = (imgEl) => {
        const defaultColors = [
            { raw: '#4B5563', glow: 'rgba(75, 85, 99, 0.2)' }, { raw: '#374151', glow: 'rgba(55, 65, 81, 0.2)' },
            { raw: '#1F2937', glow: 'rgba(31, 41, 55, 0.2)' }, { raw: '#111827', glow: 'rgba(17, 24, 39, 0.2)' }
        ];

        if (!imgEl.complete || imgEl.naturalWidth === 0) return defaultColors;

        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            canvas.width = 50; canvas.height = 50;
            ctx.drawImage(imgEl, 0, 0, 50, 50);

            const data = ctx.getImageData(0, 0, 50, 50).data;
            const colorCounts = {};

            for (let i = 0; i < data.length; i += 16) {
                const r = Math.round(data[i] / 10) * 10;
                const g = Math.round(data[i+1] / 10) * 10;
                const b = Math.round(data[i+2] / 10) * 10;
                if(data[i+3] < 255) continue;
                const rgb = `${r},${g},${b}`;
                colorCounts[rgb] = (colorCounts[rgb] || 0) + 1;
            }

            const sortedColors = Object.entries(colorCounts)
                .sort((a, b) => b[1] - a[1])
                .map(entry => {
                    const [r, g, b] = entry[0].split(',').map(Number);
                    return { r, g, b };
                });

            const finalColors = [];
            const minDistance = 50;
            const getDistance = (c1, c2) => Math.sqrt(Math.pow(c1.r - c2.r, 2) + Math.pow(c1.g - c2.g, 2) + Math.pow(c1.b - c2.b, 2));

            for (const color of sortedColors) {
                if (finalColors.length >= 4) break;
                let isDistinct = true;
                for (const existingColor of finalColors) {
                    if (getDistance(color, existingColor) < minDistance) {
                        isDistinct = false;
                        break;
                    }
                }
                if (isDistinct) finalColors.push(color);
            }

            if (finalColors.length < 4) {
                for (const color of sortedColors) {
                    if (finalColors.length >= 4) break;
                    if (!finalColors.some(c => c.r === color.r && c.g === color.g && c.b === color.b)) {
                        finalColors.push(color);
                    }
                }
            }

            return finalColors.map(c => ({
                raw: `rgb(${c.r}, ${c.g}, ${c.b})`,
                glow: `rgba(${c.r}, ${c.g}, ${c.b}, 0.25)`
            }));

        } catch(e) {
            console.error("Color extraction failed", e);
            return defaultColors;
        }
    };

    const applyColorFeatures = (imgEl) => {
        const colors = extractPaletteFromImage(imgEl);

        const paletteContainer = document.getElementById('color-palette-container');
        if (paletteContainer && colors && colors.length > 0) {
            paletteContainer.innerHTML = colors.map(c =>
                `<div class="w-8 h-4 rounded-sm shadow-sm border border-white/10 transition-colors duration-500" style="background-color: ${c.raw};"></div>`
            ).join('');

            DOM.modalContainer.style.background = `
                radial-gradient(circle at 10% 10%, ${colors[0]?.glow || 'transparent'} 0%, transparent 50%),
                radial-gradient(circle at 90% 10%, ${colors[1]?.glow || 'transparent'} 0%, transparent 50%),
                radial-gradient(circle at 10% 90%, ${colors[2]?.glow || 'transparent'} 0%, transparent 50%),
                radial-gradient(circle at 90% 90%, ${colors[3]?.glow || 'transparent'} 0%, transparent 50%),
                var(--color-surface)
            `;
        }
    };

    const checkUrlHash = () => {
        const hash = window.location.hash;
        if (hash.startsWith('#post-')) {
            const parts = hash.replace('#post-', '').split('/photo-');
            const postId = parseInt(parts[0], 10);
            const photoIndex = parts.length > 1 ? parseInt(parts[1], 10) : 0;

            const targetPost = state.posts.find(p => p.id == postId);
            if (targetPost) setTimeout(() => openModal(targetPost, photoIndex), 500);
        }
    };

    const fetchReactions = async (photoId) => {
        try {
            const response = await fetch(`/api/photos/${photoId}/reactions`, { headers: { 'Accept': 'application/json' } });
            if (response.ok) {
                const data = await response.json();
                const photo = state.activePost.photos[state.currentPhotoIndex];
                if (photo && photo.id === photoId) { photo.reaction_counts = data.counts; renderActiveSlide(); }
            }
        } catch (e) { console.log('Reactions fetch skipped'); }
    };

    const handleReaction = async (type, photoId) => {
        const storageKey = `reacted_${photoId}_${type}`;
        const hasReacted = localStorage.getItem(storageKey);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const photo = state.activePost.photos[state.currentPhotoIndex];
        if (!photo.reaction_counts) photo.reaction_counts = {};

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
                const data = await response.json(); photo.reaction_counts = data.counts; renderActiveSlide();
            }
        } catch (e) { console.error('Reaction sync failed'); }
    };

    const toggleGrid = () => {
        state.isGridActive = !state.isGridActive;
        const gridOverlay = document.getElementById('grid-overlay');
        const btnIcon = document.getElementById('grid-btn-icon');
        if (gridOverlay) {
            gridOverlay.classList.toggle('opacity-0', !state.isGridActive);
            gridOverlay.classList.toggle('opacity-100', state.isGridActive);
            if(state.isGridActive) btnIcon.classList.replace('text-white', 'text-blue-400');
            else btnIcon.classList.replace('text-blue-400', 'text-white');
        }
    };

    const openModal = (post, initialIndex = 0) => {
        if (state.lenis) state.lenis.stop();
        state.activePost = post;
        state.currentPhotoIndex = initialIndex;
        state.isGridActive = false;

        DOM.modalContainer.style.background = 'var(--color-surface)';
        renderActiveSlide();
        fetchReactions(post.photos[state.currentPhotoIndex].id);

        DOM.modal.classList.remove('hidden'); document.body.style.overflow = 'hidden';
        void DOM.modal.offsetWidth; DOM.modal.classList.add('opacity-100'); DOM.modal.classList.remove('opacity-0');
        DOM.modalContainer.classList.remove('scale-95');
    };

    const closeModal = () => {
        DOM.modalContainer.classList.add('scale-95'); DOM.modal.classList.add('opacity-0');
        history.replaceState(null, '', window.location.pathname + window.location.search);

        setTimeout(() => {
            DOM.modal.classList.add('hidden');
            document.body.style.overflow = '';
            state.activePost = null;
            DOM.modalContainer.style.background = 'var(--color-surface)';
            if (state.lenis) state.lenis.start();
        }, 500);
    };

    const nextPhoto = () => {
        if (!state.activePost || state.activePost.photos.length <= 1) return; 
        state.currentPhotoIndex = (state.currentPhotoIndex + 1) % state.activePost.photos.length;
        DOM.modalContainer.style.background = 'var(--color-surface)';
        renderActiveSlide(); fetchReactions(state.activePost.photos[state.currentPhotoIndex].id);
    };

    const prevPhoto = () => {
        if (!state.activePost || state.activePost.photos.length <= 1) return; 
        state.currentPhotoIndex = (state.currentPhotoIndex - 1 + state.activePost.photos.length) % state.activePost.photos.length;
        DOM.modalContainer.style.background = 'var(--color-surface)';
        renderActiveSlide(); fetchReactions(state.activePost.photos[state.currentPhotoIndex].id);
    };

    const renderActiveSlide = () => {
        const post = state.activePost; const photo = post.photos[state.currentPhotoIndex]; if (!photo) return;
        const dict = window.I18N[state.lang];

        history.replaceState(null, '', `#post-${post.id}/photo-${state.currentPhotoIndex}`);

        DOM.modalIndicator.textContent = `${String(state.currentPhotoIndex + 1).padStart(2, '0')} / ${String(post.photos.length).padStart(2, '0')}`;
        const titleStr = getTrans(photo.title);
        const catTranslation = dict[`cat${post.category}`] || post.category;

        DOM.modalTitleArea.innerHTML = `<div class="flex items-center space-x-3"><span class="text-xs px-2 py-1 rounded border theme-border theme-text-muted font-bold uppercase tracking-wider">${catTranslation}</span><h2 class="text-base md:text-lg font-semibold truncate max-w-[200px] md:max-w-md">${titleStr}</h2></div>`;
        DOM.modalDots.innerHTML = post.photos.map((_, i) => `<div class="w-1 h-1 rounded-full transition-all duration-300 ${i === state.currentPhotoIndex ? 'bg-white w-3' : 'bg-white/30'}"></div>`).join('');

        const exifArr = [];
        if (photo.exif_focal_length) exifArr.push(`${photo.exif_focal_length}mm`);
        if (photo.exif_aperture) exifArr.push(`f/${parseFloat(photo.exif_aperture)}`);
        if (photo.exif_iso) exifArr.push(`ISO ${photo.exif_iso}`);

        const reactions = [{ key: 'love', icon: '❤️' }, { key: 'fire', icon: '🔥' }, { key: 'inspired', icon: '✨' }, { key: 'calm', icon: '🧘' }];
        
        DOM.modalSliderContent.style.transition = 'opacity 0.2s ease-in-out';
        DOM.modalSliderContent.style.opacity = '0';
        
        const targetIndex = state.currentPhotoIndex;
        
        const performRender = () => {
            if (state.currentPhotoIndex !== targetIndex) return;

            // [추가] 모달창 안에서도 Blur-up 적용. 로딩 스피너 대신 배경의 부드러운 스켈레톤과 블러 이미지를 사용
            DOM.modalSliderContent.innerHTML = `
                <div class="w-full md:w-[65%] flex items-center justify-center p-4 md:p-10 relative shrink-0">
                    <div class="relative flex items-center justify-center w-full min-h-[40vh] md:min-h-[60vh] rounded-lg overflow-hidden bg-black/10" id="photo-loupe-container">
                        
                        <div class="skeleton-bg absolute inset-0 bg-white/5 animate-pulse z-0 transition-opacity duration-1000"></div>
                        
                        <!-- Blur-up Image -->
                        <img id="active-photo-img" crossorigin="anonymous" class="max-w-full max-h-[50vh] md:max-h-[70vh] object-contain rounded-lg shadow-xl block cursor-crosshair z-10 relative transform scale-[1.02] blur-lg opacity-0 transition-all duration-[1.2s] ease-out">

                        <div id="photo-loupe" class="loupe hidden md:block overflow-hidden z-20">
                            <img id="loupe-inner-img" crossorigin="anonymous" class="absolute max-w-none" style="pointer-events: none; object-fit: contain;">
                        </div>

                        <div id="grid-overlay" class="absolute inset-0 pointer-events-none rounded-lg overflow-hidden transition-opacity duration-300 z-30 ${state.isGridActive ? 'opacity-100' : 'opacity-0'}">
                            <div class="absolute left-0 right-0 h-[1px] bg-white/50 shadow-[0_0_2px_rgba(0,0,0,0.8)]" style="top: 33.33%;"></div>
                            <div class="absolute left-0 right-0 h-[1px] bg-white/50 shadow-[0_0_2px_rgba(0,0,0,0.8)]" style="top: 66.66%;"></div>
                            <div class="absolute top-0 bottom-0 w-[1px] bg-white/50 shadow-[0_0_2px_rgba(0,0,0,0.8)]" style="left: 33.33%;"></div>
                            <div class="absolute top-0 bottom-0 w-[1px] bg-white/50 shadow-[0_0_2px_rgba(0,0,0,0.8)]" style="left: 66.66%;"></div>
                        </div>
                        <button onclick="window.Portfolio.toggleGrid()" class="absolute top-3 right-3 p-2 bg-black/40 hover:bg-black/70 backdrop-blur-md rounded-full border border-white/20 transition-all shadow-lg z-40" title="3x3 구도 보기">
                            <svg id="grid-btn-icon" class="w-4 h-4 transition-colors duration-300 ${state.isGridActive ? 'text-blue-400' : 'text-white'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16M8 4v16M16 4v16"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="w-full md:w-[35%] flex flex-col justify-center p-6 md:p-10 space-y-8">
                    <div class="space-y-4">
                        <h3 class="text-2xl md:text-3xl font-bold theme-text leading-tight">${titleStr}</h3>
                        <p class="theme-text-muted font-light text-base md:text-lg leading-relaxed">${getTrans(photo.description) || ''}</p>
                    </div>

                    <div class="space-y-3">
                        <span class="block text-xs md:text-sm theme-text-muted uppercase font-bold tracking-widest">${dict.colorPalette || 'Color Palette'}</span>
                        <div id="color-palette-container" class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-800 animate-pulse"></div>
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-800 animate-pulse"></div>
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-800 animate-pulse"></div>
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-800 animate-pulse"></div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        ${reactions.map(r => {
                            const count = (photo.reaction_counts && photo.reaction_counts[r.key]) || 0;
                            const isActive = localStorage.getItem(`reacted_${photo.id}_${r.key}`);
                            return `<button onclick="window.Portfolio.handleReaction('${r.key}', ${photo.id})" class="reaction-btn flex items-center space-x-2 px-4 py-2 rounded-full border theme-border theme-surface ${isActive ? 'active' : ''}"><span class="text-lg">${r.icon}</span><span class="text-sm font-medium theme-text-muted transition-colors">${count}</span></button>`;
                        }).join('')}
                    </div>
                    <div class="pt-6 border-t theme-border grid grid-cols-2 gap-6">
                        <div class="flex flex-col"><span class="text-xs theme-text-muted uppercase font-bold mb-1">${dict.exifCamera || 'Camera'}</span><span class="theme-text text-sm font-medium truncate">${photo.exif_body || '-'}</span></div>
                        <div class="flex flex-col"><span class="text-xs theme-text-muted uppercase font-bold mb-1">${dict.exifLens || 'Lens'}</span><span class="theme-text text-sm font-medium truncate">${photo.exif_lens || '-'}</span></div>
                        <div class="col-span-2 flex flex-wrap gap-2 pt-1">${exifArr.map(e => `<span class="px-3 py-1 bg-surface border theme-border rounded text-xs theme-text font-mono tracking-wide">${e}</span>`).join('')}</div>
                    </div>
                    <div class="flex gap-3 pt-4 pb-10 md:pb-0">
                        <button onclick="window.Portfolio.downloadFramedPhoto()" class="action-btn"><span>${dict.btnDownload || 'DOWNLOAD'}</span></button>
                        <button onclick="window.Portfolio.sharePhoto()" class="action-btn"><span>${dict.btnShare || 'SHARE'}</span></button>
                    </div>
                </div>
            `;

            requestAnimationFrame(() => {
                void DOM.modalSliderContent.offsetWidth;
                DOM.modalSliderContent.style.opacity = '1';

                const imgEl = document.getElementById('active-photo-img');
                const loupeEl = document.getElementById('photo-loupe');
                const loupeInner = document.getElementById('loupe-inner-img');
                const skeleton = DOM.modalSliderContent.querySelector('.skeleton-bg');

                if(imgEl && loupeEl && loupeInner) {
                    // 블러 업을 완성하는 트리거
                    const initVisuals = () => {
                        imgEl.classList.remove('scale-[1.02]', 'blur-lg', 'opacity-0');
                        imgEl.classList.add('scale-100', 'blur-0', 'opacity-100');
                        applyColorFeatures(imgEl);
                        if(skeleton) skeleton.classList.add('opacity-0');
                    };
                    
                    if (imgEl.complete) {
                        initVisuals();
                    } else {
                        imgEl.addEventListener('load', initVisuals);
                    }

                    imgEl.addEventListener('mouseenter', () => loupeEl.classList.add('active'));
                    imgEl.addEventListener('mouseleave', () => loupeEl.classList.remove('active'));
                    imgEl.addEventListener('mousemove', (e) => {
                        const rect = imgEl.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const y = e.clientY - rect.top;

                        loupeEl.style.left = `${x}px`;
                        loupeEl.style.top = `${y}px`;

                        const zoom = 2.5;
                        loupeInner.style.width = `${rect.width * zoom}px`;
                        loupeInner.style.height = `${rect.height * zoom}px`;
                        loupeInner.style.left = `${90 - (x * zoom)}px`;
                        loupeInner.style.top = `${90 - (y * zoom)}px`;
                    });
                }
                
                // 실제 이미지 URL 삽입 (여기서 로딩 시작)
                if (imgEl) {
                    imgEl.src = photo.url;
                    if (loupeInner) loupeInner.src = photo.url;
                }
                
                if (post.photos.length > 1) {
                    const nextIdx = (targetIndex + 1) % post.photos.length;
                    const prevIdx = (targetIndex - 1 + post.photos.length) % post.photos.length;
                    new Image().src = post.photos[nextIdx].url;
                    new Image().src = post.photos[prevIdx].url;
                }
            });
        };

        const preloader = new Image();
        preloader.src = photo.url;
        
        if (preloader.complete) {
            setTimeout(performRender, 50);
        } else {
            // 프리로더 대신 화면부터 그려서 블러-업 효과를 바로 볼 수 있게 변경
            performRender();
        }
    };

    const clearMapMarkers = () => {
        if (state.mapMarkers && state.mapMarkers.length > 0) {
            state.mapMarkers.forEach(m => m.setMap(null));
        }
        state.mapMarkers = [];
    };

    const initHybridMap = () => {
        const filtered = getFilteredPosts();
        const isKoreaMode = state.posts.some(p => p.country === 'South Korea');
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
        else { state.mapInstance.relayout(); if(posts.length > 0) state.mapInstance.setCenter(new kakao.maps.LatLng(first.lat, first.lng)); }
        clearMapMarkers();
        posts.forEach(post => {
            if (!post.lat || !post.photos?.length) return;
            const content = document.createElement('div');
            content.className = 'w-12 h-12 rounded-full border-2 border-white shadow-xl bg-surface cursor-pointer transition-transform hover:scale-110 overflow-hidden relative';
            content.innerHTML = `<img src="${post.photos[0].url}" crossorigin="anonymous" class="w-full h-full object-cover">`;
            content.addEventListener('click', () => openModal(post));
            const overlay = new kakao.maps.CustomOverlay({ position: new kakao.maps.LatLng(post.lat, post.lng), content: content, yAnchor: 1.2 });
            overlay.setMap(state.mapInstance); state.mapMarkers.push(overlay);
        });
    };

    const drawGoogleMap = (posts) => {
        if (DOM.mapLoader) DOM.mapLoader.classList.add('hidden');
        const first = posts.find(p => p.lat && p.lng) || {lat: 37.56, lng: 126.97};
        if(!state.mapInstance) { state.mapInstance = new google.maps.Map(DOM.mapCanvas, { zoom: 6, center: { lat: parseFloat(first.lat), lng: parseFloat(first.lng) }, mapTypeControl: false }); }
        clearMapMarkers();
        posts.forEach(post => {
            if (!post.lat || !post.photos?.length) return;
            const marker = new google.maps.Marker({ position: { lat: parseFloat(post.lat), lng: parseFloat(post.lng) }, map: state.mapInstance });
            marker.addListener("click", () => openModal(post)); state.mapMarkers.push(marker);
        });
    };

    const renderMapMarkers = () => { if(state.viewMode === 'map') initHybridMap(); };

    const showToast = (message) => {
        DOM.toast.textContent = message; DOM.toast.classList.remove('opacity-0', 'translate-y-10');
        setTimeout(() => { DOM.toast.classList.add('opacity-0', 'translate-y-10'); }, 3000);
    };

    const downloadFramedPhoto = () => {
        const post = state.activePost;
        const photo = post.photos[state.currentPhotoIndex];
        const postUrl = `${window.location.origin}${window.location.pathname}#post-${post.id}/photo-${state.currentPhotoIndex}`;
        
        showToast(window.I18N[state.lang].toastWait || '프레임을 생성하는 중입니다...');
        const img = new Image();
        img.crossOrigin = "Anonymous";
        img.src = photo.url;

        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            const margin = Math.max(120, img.width * 0.05);
            const pt = margin;
            const px = margin;
            const pb = margin * 3.5;

            canvas.width = img.width + (px * 2);
            canvas.height = img.height + pt + pb;

            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, px, pt, img.width, img.height);

            if (state.isGridActive) {
                ctx.strokeStyle = "rgba(255, 255, 255, 0.7)";
                ctx.lineWidth = Math.max(2, img.width * 0.002);
                ctx.beginPath();
                ctx.moveTo(px + img.width / 3, pt); ctx.lineTo(px + img.width / 3, pt + img.height);
                ctx.moveTo(px + (img.width * 2) / 3, pt); ctx.lineTo(px + (img.width * 2) / 3, pt + img.height);
                ctx.moveTo(px, pt + img.height / 3); ctx.lineTo(px + img.width, pt + img.height / 3);
                ctx.moveTo(px, pt + (img.height * 2) / 3); ctx.lineTo(px + img.width, pt + (img.height * 2) / 3);
                ctx.stroke();
            }

            const titleFontSize = Math.max(40, img.width * 0.025);
            const exifFontSize = Math.max(24, img.width * 0.015);
            const textYOffset = pt + img.height + margin * 1.2;

            ctx.fillStyle = "#111111";
            ctx.font = `bold ${titleFontSize}px Pretendard, -apple-system, sans-serif`;
            const title = typeof photo.title === 'string' ? photo.title : (photo.title[state.lang] || photo.title['ko'] || '');
            ctx.fillText(title, px, textYOffset);

            const exifArr = [];
            if (photo.exif_body) exifArr.push(photo.exif_body);
            if (photo.exif_lens) exifArr.push(photo.exif_lens);
            if (photo.exif_focal_length) exifArr.push(`${photo.exif_focal_length}mm`);
            if (photo.exif_aperture) exifArr.push(`f/${parseFloat(photo.exif_aperture)}`);
            if (photo.exif_iso) exifArr.push(`ISO ${photo.exif_iso}`);

            ctx.fillStyle = "#666666";
            ctx.font = `400 ${exifFontSize}px Pretendard, -apple-system, sans-serif`;
            ctx.fillText(exifArr.join('  |  '), px, textYOffset + titleFontSize * 1.5);

            const qrSize = margin * 1.8;
            const qr = new QRious({ value: postUrl, size: qrSize, level: 'H' });
            const qrImg = new Image();
            qrImg.src = qr.toDataURL();

            qrImg.onload = () => {
                ctx.drawImage(qrImg, canvas.width - px - qrSize, pt + img.height + margin * 0.6, qrSize, qrSize);
                const link = document.createElement('a');
                const cleanSlug = post.slug ? post.slug.replace(/[^a-zA-Z0-9-]/g, '') : 'post';
                link.download = state.isGridActive ? `kkyung_photo_grid_${cleanSlug}_${photo.id}.jpg` : `kkyung_photo_${cleanSlug}_${photo.id}.jpg`;
                link.href = canvas.toDataURL("image/jpeg", 0.95);
                link.click();
            };
        };
    };

    const sharePhoto = () => {
        const photo = state.activePost.photos[state.currentPhotoIndex];
        const title = typeof photo.title === 'string' ? photo.title : (photo.title[state.lang] || photo.title['ko'] || '');
        const postUrl = `${window.location.origin}${window.location.pathname}#post-${state.activePost.id}/photo-${state.currentPhotoIndex}`;

        if (navigator.share) { navigator.share({ title: `PHOTO - ${title}`, url: postUrl }); }
        else { navigator.clipboard.writeText(postUrl).then(() => showToast(window.I18N[state.lang].toastCopy || 'Link Copied!')); }
    };

    return { init, switchView, openModal, closeModal, nextPhoto, prevPhoto, toggleGrid, downloadFramedPhoto, sharePhoto, handleReaction };
})();