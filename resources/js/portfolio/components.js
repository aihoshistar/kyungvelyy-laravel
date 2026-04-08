// resources/js/portfolio/components.js

import { State, DOM } from './core.js';
import { Utils } from './utils.js';
import { Interaction } from './interaction.js';
import { Exporter } from './exporter.js'; // 💡 [추가] Exporter 모듈 명시적 Import

export const Gallery = {
    initFilters: () => {
        const years = [...new Set(State.posts.map(p => p.taken_at ? p.taken_at.substring(0, 4) : null).filter(Boolean))].sort().reverse();
        const countries = [...new Set(State.posts.map(p => p.country).filter(Boolean))].sort();
        const cameras = [...new Set(State.posts.flatMap(p => p.photos.map(ph => ph.exif_body)).filter(Boolean))].sort();
        const lenses = [...new Set(State.posts.flatMap(p => p.photos.map(ph => ph.exif_lens)).filter(Boolean))].sort();

        const appendOptions = (selectEl, dataArray) => {
            if (!selectEl) return;
            dataArray.forEach(item => {
                const option = document.createElement('option');
                option.value = item; option.textContent = item;
                selectEl.appendChild(option);
            });
            selectEl.addEventListener('change', (e) => {
                State[selectEl.id.replace('-filter', '')] = e.target.value;
                if (State.viewMode === 'grid') Gallery.renderGrid(); else MapEngine.initHybridMap();
            });
        };

        appendOptions(DOM.yearFilter, years);
        appendOptions(DOM.countryFilter, countries);
        appendOptions(DOM.cameraFilter, cameras);
        appendOptions(DOM.lensFilter, lenses);
    },

    getFilteredPosts: () => {
        return State.posts.filter(p => {
            const matchCat = State.category === 'all' || p.category === State.category;
            const matchCountry = State.country === 'all' || p.country === State.country;
            const matchYear = State.year === 'all' || (p.taken_at && p.taken_at.startsWith(State.year));
            const matchCamera = State.camera === 'all' || (p.photos && p.photos.some(ph => ph.exif_body === State.camera));
            const matchLens = State.lens === 'all' || (p.photos && p.photos.some(ph => ph.exif_lens === State.lens));
            return matchCat && matchCountry && matchYear && matchCamera && matchLens;
        });
    },

    switchView: (mode) => {
        if (State.viewMode === mode) return; 
        State.viewMode = mode;
        if (mode === 'grid') {
            DOM.btnGrid.classList.add('view-toggle-active'); DOM.btnGrid.classList.remove('view-toggle-inactive');
            DOM.btnMap.classList.remove('view-toggle-active'); DOM.btnMap.classList.add('view-toggle-inactive');
            DOM.mapContainer.classList.add('opacity-0');
            setTimeout(() => { 
                DOM.mapContainer.classList.add('hidden'); DOM.grid.classList.remove('hidden'); 
                void DOM.grid.offsetWidth; DOM.grid.classList.add('opacity-100'); 
                Gallery.renderGrid(); 
            }, 400);
        } else {
            DOM.btnMap.classList.add('view-toggle-active'); DOM.btnMap.classList.remove('view-toggle-inactive');
            DOM.btnGrid.classList.remove('view-toggle-active'); DOM.btnGrid.classList.add('view-toggle-inactive');
            DOM.grid.classList.remove('opacity-100'); DOM.grid.classList.add('opacity-0');
            setTimeout(() => { 
                DOM.grid.classList.add('hidden'); DOM.mapContainer.classList.remove('hidden'); 
                void DOM.mapContainer.offsetWidth; DOM.mapContainer.classList.add('opacity-100'); 
                MapEngine.initHybridMap(); 
            }, 400);
        }
    },

    renderGrid: () => {
        DOM.grid.innerHTML = '';
        const filtered = Gallery.getFilteredPosts();
        filtered.forEach((post, index) => {
            if (!post.photos || post.photos.length === 0) return;
            const card = document.createElement('div');
            card.className = 'photo-card cursor-pointer group reveal active w-full break-inside-avoid relative inline-block mb-6';
            card.style.transitionDelay = `${(index % 6) * 100}ms`;
            const catTranslation = window.I18N[State.lang][`cat${post.category}`] || post.category;

            card.innerHTML = `
                <div class="rounded-xl theme-surface img-mask overflow-hidden w-full relative transition-colors duration-500 bg-gray-200 dark:bg-gray-800" style="aspect-ratio: ${post.photos[0].aspect_ratio || 1.5};">
                    <div class="skeleton-bg absolute inset-0 bg-white/5 animate-pulse z-0 transition-opacity duration-[1.5s]"></div>
                    <img crossorigin="anonymous" loading="lazy" class="grid-img absolute inset-0 w-full h-full object-cover transform scale-110 blur-xl opacity-0 transition-all duration-[1.2s] ease-out z-10 group-hover:scale-105">
                    
                    ${post.photos.length > 1 ? `<div class="absolute top-4 right-4 z-20 bg-black/50 backdrop-blur-md rounded-full px-3 py-1 text-white text-xs font-bold border border-white/20 flex items-center gap-1 shadow-lg"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>${post.photos.length}</div>` : ''}
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-end p-6 md:p-8 z-20">
                        <span class="text-xs md:text-sm font-bold tracking-[0.2em] text-white/80 uppercase mb-2">${catTranslation}</span>
                        <h4 class="text-white font-semibold text-xl md:text-2xl leading-snug drop-shadow-md">${Utils.getTrans(post.title)}</h4>
                    </div>
                </div>
            `;
            
            const imgEl = card.querySelector('.grid-img');
            const skeleton = card.querySelector('.skeleton-bg');

            const initGridVisuals = () => {
                imgEl.classList.remove('scale-110', 'blur-xl', 'opacity-0');
                imgEl.classList.add('scale-100', 'blur-0', 'opacity-90', 'group-hover:opacity-100');
                if(skeleton) skeleton.style.opacity = '0';
            };

            imgEl.onload = initGridVisuals;
            imgEl.src = post.photos[0].url; 
            if (imgEl.complete && imgEl.naturalWidth > 0) initGridVisuals();

            card.addEventListener('click', () => ModalViewer.open(post));
            DOM.grid.appendChild(card);
        });
    }
};

export const ModalViewer = {
    toggleGrid: () => {
        State.isGridActive = !State.isGridActive;
        const gridOverlay = document.getElementById('grid-overlay');
        const btnIcon = document.getElementById('grid-btn-icon');
        const selectEl = document.getElementById('download-template-select');
        const currentStyle = selectEl ? selectEl.value : 'original';

        // 1. 아이콘 색상 변경
        if (State.isGridActive) btnIcon.classList.replace('text-white', 'text-blue-400');
        else btnIcon.classList.replace('text-blue-400', 'text-white');

        // 2. 프레임 유무에 따른 그리드 렌더링 분기처리
        if (currentStyle === 'original') {
            // 원본 사진일 경우 HTML CSS 오버레이를 켬
            if (gridOverlay) {
                gridOverlay.style.display = 'block';
                gridOverlay.classList.toggle('opacity-0', !State.isGridActive);
                gridOverlay.classList.toggle('opacity-100', State.isGridActive);
            }
        } else {
            // 프레임이 씌워진 경우 HTML 그리드는 끄고, Canvas 렌더링을 다시 트리거함
            if (gridOverlay) gridOverlay.style.display = 'none';
            if (selectEl) selectEl.dispatchEvent(new Event('change'));
        }
    },

    open: (post, initialIndex = 0) => {
        State.activePost = post;
        State.currentPhotoIndex = initialIndex;
        State.isGridActive = false;

        // 모달 열 때 기본 템플릿 유지 여부 확인 (최초는 original)
        if (!State.currentTemplate) State.currentTemplate = 'original';

        DOM.modalContainer.style.background = 'var(--color-surface)';
        ModalViewer.renderSlide();
        Interaction.fetchReactions(post.photos[State.currentPhotoIndex].id);

        DOM.modal.classList.remove('hidden'); document.body.style.overflow = 'hidden';
        void DOM.modal.offsetWidth; 
        DOM.modal.classList.add('opacity-100'); DOM.modal.classList.remove('opacity-0');
        
        // 💡 [수정] 모달 진입 애니메이션 (Scale + Translate Y)
        DOM.modalContainer.classList.remove('scale-95', 'translate-y-4'); 
        DOM.modalContainer.classList.add('scale-100', 'translate-y-0');
    },

    close: () => {
        DOM.modal.classList.remove('opacity-100'); DOM.modal.classList.add('opacity-0');
        history.replaceState(null, '', window.location.pathname + window.location.search);

        setTimeout(() => {
            DOM.modal.classList.add('hidden');
            document.body.style.overflow = '';
            State.activePost = null;
            DOM.modalContainer.style.background = 'var(--color-surface)';
            
            // 💡 [수정] 모달 종료 애니메이션 (Scale + Translate Y)
            DOM.modalContainer.classList.remove('scale-100', 'translate-y-0'); 
            DOM.modalContainer.classList.add('scale-95', 'translate-y-4');
        }, 300);
    },

    next: () => {
        if (!State.activePost || State.activePost.photos.length <= 1) return; 
        State.currentPhotoIndex = (State.currentPhotoIndex + 1) % State.activePost.photos.length;
        DOM.modalContainer.style.background = 'var(--color-surface)';
        ModalViewer.renderSlide(); Interaction.fetchReactions(State.activePost.photos[State.currentPhotoIndex].id);
    },

    prev: () => {
        if (!State.activePost || State.activePost.photos.length <= 1) return; 
        State.currentPhotoIndex = (State.currentPhotoIndex - 1 + State.activePost.photos.length) % State.activePost.photos.length;
        DOM.modalContainer.style.background = 'var(--color-surface)';
        ModalViewer.renderSlide(); Interaction.fetchReactions(State.activePost.photos[State.currentPhotoIndex].id);
    },

    renderSlide: () => {
        const post = State.activePost; const photo = post.photos[State.currentPhotoIndex]; if (!photo) return;
        const dict = window.I18N[State.lang];

        history.replaceState(null, '', `#post-${post.id}/photo-${State.currentPhotoIndex}`);
        DOM.modalIndicator.textContent = `${String(State.currentPhotoIndex + 1).padStart(2, '0')} / ${String(post.photos.length).padStart(2, '0')}`;
        
        const titleStr = Utils.getTrans(photo.title) || Utils.getTrans(post.title); 
        const catTranslation = dict[`cat${post.category}`] || post.category;

        DOM.modalTitleArea.innerHTML = `<div class="flex items-center space-x-3"><span class="text-xs px-2 py-1 rounded border theme-border theme-text-muted font-bold uppercase tracking-wider">${catTranslation}</span><h2 class="text-base md:text-lg font-semibold truncate max-w-[200px] md:max-w-md">${titleStr}</h2></div>`;
        DOM.modalDots.innerHTML = post.photos.map((_, i) => `<div class="w-1 h-1 rounded-full transition-all duration-300 ${i === State.currentPhotoIndex ? 'bg-white w-3' : 'bg-white/30'}"></div>`).join('');

        const exifArr = [];
        if (photo.exif_focal_length) exifArr.push(`${photo.exif_focal_length}mm`);
        if (photo.exif_aperture) exifArr.push(`f/${parseFloat(photo.exif_aperture)}`);
        if (photo.exif_shutter_speed_num && photo.exif_shutter_speed_den) exifArr.push(`${photo.exif_shutter_speed_num}/${photo.exif_shutter_speed_den}s`);
        if (photo.exif_iso) exifArr.push(`ISO ${photo.exif_iso}`);

        const targetIndex = State.currentPhotoIndex;
        if (State.currentPhotoIndex !== targetIndex) return;

        // 선택 메뉴 상태 유지
        const oldSelectEl = document.getElementById('download-template-select');
        const currentStyle = oldSelectEl ? oldSelectEl.value : (State.currentTemplate || 'original');
        State.currentTemplate = currentStyle;

        DOM.modalSliderContent.innerHTML = `
            <div class="w-full md:w-[65%] flex items-center justify-center p-4 md:p-6 relative shrink-0 h-[45vh] md:h-full min-h-0">
                <!-- 💡 [해결] 이미지가 바깥으로 삐져나가지 않도록 min-h-0, min-w-0 부여 및 내부 p-2 패딩 추가 -->
                <div class="relative flex items-center justify-center w-full h-full rounded-lg overflow-hidden bg-black/5 p-2 md:p-4 min-h-0" id="photo-loupe-container">
                    
                    <div class="skeleton-bg absolute inset-0 bg-white/5 animate-pulse z-0 transition-opacity duration-1000"></div>
                    
                    <div class="relative inline-flex items-center justify-center max-w-full max-h-full z-10 min-h-0 min-w-0">
                        <img id="active-photo-img" crossorigin="anonymous" class="block w-auto h-auto max-w-full max-h-full object-contain rounded-lg shadow-xl cursor-crosshair transform scale-[1.02] blur-lg opacity-0 transition-all duration-[0.8s] ease-out" style="max-height: 100%; max-width: 100%;">

                        <div id="grid-overlay" class="absolute inset-0 pointer-events-none rounded-lg overflow-hidden transition-opacity duration-300 ${State.isGridActive ? 'opacity-100' : 'opacity-0'}" style="display: ${currentStyle === 'original' ? 'block' : 'none'};">
                            <div class="absolute left-0 right-0 h-[1px] bg-white/50 shadow-[0_0_2px_rgba(0,0,0,0.8)]" style="top: 33.33%;"></div>
                            <div class="absolute left-0 right-0 h-[1px] bg-white/50 shadow-[0_0_2px_rgba(0,0,0,0.8)]" style="top: 66.66%;"></div>
                            <div class="absolute top-0 bottom-0 w-[1px] bg-white/50 shadow-[0_0_2px_rgba(0,0,0,0.8)]" style="left: 33.33%;"></div>
                            <div class="absolute top-0 bottom-0 w-[1px] bg-white/50 shadow-[0_0_2px_rgba(0,0,0,0.8)]" style="left: 66.66%;"></div>
                        </div>
                    </div>

                    <div id="photo-loupe" class="loupe hidden md:block overflow-hidden z-20">
                        <img id="loupe-inner-img" crossorigin="anonymous" class="absolute max-w-none" style="pointer-events: none; object-fit: contain;">
                    </div>

                    <button onclick="window.Portfolio.toggleGrid()" class="absolute top-3 right-3 p-2 bg-black/40 hover:bg-black/70 backdrop-blur-md rounded-full border border-white/20 transition-all shadow-lg z-40" title="3x3 구도 보기">
                        <svg id="grid-btn-icon" class="w-4 h-4 transition-colors duration-300 ${State.isGridActive ? 'text-blue-400' : 'text-white'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16M8 4v16M16 4v16"></path></svg>
                    </button>
                </div>
            </div>
            <div class="w-full md:w-[35%] flex flex-col justify-center p-6 md:p-10 space-y-8 overflow-y-auto">
                <div class="space-y-4">
                    <h3 class="text-2xl md:text-3xl font-bold theme-text leading-tight">${titleStr}</h3>
                    <p class="theme-text-muted font-light text-base md:text-lg leading-relaxed">${Utils.getTrans(photo.description) || ''}</p>
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

                <div id="reactions-container" class="flex flex-wrap gap-2 pt-2"></div>
                
                <div class="pt-6 border-t theme-border grid grid-cols-2 gap-6">
                    <div class="flex flex-col"><span class="text-xs theme-text-muted uppercase font-bold mb-1">${dict.exifCamera || 'Camera'}</span><span class="theme-text text-sm font-medium truncate">${photo.exif_body || '-'}</span></div>
                    <div class="flex flex-col"><span class="text-xs theme-text-muted uppercase font-bold mb-1">${dict.exifLens || 'Lens'}</span><span class="theme-text text-sm font-medium truncate">${photo.exif_lens || '-'}</span></div>
                    <div class="col-span-2 flex flex-wrap gap-2 pt-1">${exifArr.map(e => `<span class="px-3 py-1 bg-surface border theme-border rounded text-xs theme-text font-mono tracking-wide">${e}</span>`).join('')}</div>
                </div>
                <div class="flex gap-3 pt-4 pb-10 md:pb-0 items-center">
                    <div class="relative flex-grow h-12 bg-surface border theme-border rounded-xl flex items-center shadow-sm">
                        <select id="download-template-select" class="appearance-none bg-transparent w-full h-full pl-4 pr-10 text-xs md:text-sm font-bold uppercase tracking-widest theme-text focus:outline-none cursor-pointer z-10">
                            <option value="original" ${currentStyle === 'original' ? 'selected' : ''}>Original Photo</option>
                            <option value="polaroid" ${currentStyle === 'polaroid' ? 'selected' : ''}>Film Polaroid</option>
                            <option value="instagram" ${currentStyle === 'instagram' ? 'selected' : ''}>Clean White Gallery</option>
                            <option value="zf_viewfinder" ${currentStyle === 'zf_viewfinder' ? 'selected' : ''}>Nikon Zf Viewfinder</option>
                            <option value="nikon_lcd" ${currentStyle === 'nikon_lcd' ? 'selected' : ''}>Nikon LCD Display (세로/블랙)</option>
                            <option value="overlay" ${currentStyle === 'overlay' ? 'selected' : ''}>Cinematic Overlay</option>
                        </select>
                        <div class="pointer-events-none absolute right-3 top-1/2 transform -translate-y-1/2 theme-text-muted z-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <button onclick="window.Portfolio.processDownload()" class="h-12 px-6 flex items-center justify-center rounded-xl bg-black dark:bg-white text-white dark:text-black hover:opacity-80 transition-all font-bold shadow-md cursor-pointer ml-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    </button>
                    <button onclick="window.Portfolio.sharePhoto()" class="h-12 px-4 flex items-center justify-center rounded-xl border theme-border theme-surface hover:bg-gray-100 dark:hover:bg-gray-800 transition-all ml-2 cursor-pointer">
                        <svg class="w-5 h-5 theme-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                    </button>
                </div>
            </div>
        `;
        
        Interaction.updateReactionUI();

        requestAnimationFrame(() => {
            const imgEl = document.getElementById('active-photo-img');
            const loupeEl = document.getElementById('photo-loupe');
            const loupeInner = document.getElementById('loupe-inner-img');
            const skeleton = DOM.modalSliderContent.querySelector('.skeleton-bg');
            const selectEl = document.getElementById('download-template-select');

            if(imgEl && loupeEl && loupeInner) {
                const initVisuals = () => {
                    imgEl.classList.remove('scale-[1.02]', 'blur-lg', 'opacity-0', 'opacity-50');
                    imgEl.classList.add('scale-100', 'blur-0', 'opacity-100');
                    Utils.applyColorFeatures(imgEl);
                    if(skeleton) skeleton.classList.add('opacity-0');
                };

                const applyPreview = async (style) => {
                    const gridOverlay = document.getElementById('grid-overlay');
                    if (style === 'original') {
                        imgEl.src = photo.url;
                        loupeInner.src = photo.url;
                        if (imgEl.complete) initVisuals();
                        if (gridOverlay) {
                            gridOverlay.style.display = 'block';
                            gridOverlay.classList.toggle('opacity-0', !State.isGridActive);
                            gridOverlay.classList.toggle('opacity-100', State.isGridActive);
                        }
                    } else {
                        if (gridOverlay) gridOverlay.style.display = 'none';
                        try {
                            const dataUrl = await Exporter.getPreviewUrl(post, photo, style);
                            imgEl.src = dataUrl;
                            loupeInner.src = dataUrl;
                            if (imgEl.complete) initVisuals();
                        } catch (error) {
                            console.error('Live Preview Failed:', error);
                            imgEl.src = photo.url; 
                            if (imgEl.complete) initVisuals();
                        }
                    }
                };

                imgEl.onload = initVisuals;
                
                if (currentStyle !== 'original') {
                    imgEl.classList.remove('scale-100', 'blur-0', 'opacity-100');
                    imgEl.classList.add('scale-[1.02]', 'blur-lg', 'opacity-50');
                    if (skeleton) skeleton.classList.remove('opacity-0');
                    applyPreview(currentStyle);
                } else {
                    imgEl.src = photo.url; 
                    loupeInner.src = photo.url;
                    if (imgEl.complete && imgEl.naturalWidth > 0) initVisuals();
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

                if(selectEl) {
                    selectEl.addEventListener('change', async (e) => {
                        const style = e.target.value;
                        State.currentTemplate = style; 
                        
                        imgEl.classList.remove('scale-100', 'blur-0', 'opacity-100');
                        imgEl.classList.add('scale-[1.02]', 'blur-lg', 'opacity-50');
                        if (skeleton) skeleton.classList.remove('opacity-0');

                        await applyPreview(style);
                    });
                }
            }
            
            if (post.photos.length > 1) {
                const nextIdx = (targetIndex + 1) % post.photos.length;
                const prevIdx = (targetIndex - 1 + post.photos.length) % post.photos.length;
                const preloadNext = new Image(); preloadNext.crossOrigin = "Anonymous"; preloadNext.src = post.photos[nextIdx].url;
                const preloadPrev = new Image(); preloadPrev.crossOrigin = "Anonymous"; preloadPrev.src = post.photos[prevIdx].url;
            }
        });
    }
};

export const MapEngine = {
    clearMarkers: () => {
        if (State.mapMarkers && State.mapMarkers.length > 0) {
            State.mapMarkers.forEach(m => m.setMap(null));
        }
        State.mapMarkers = [];
    },

    initHybridMap: () => {
        const filtered = Gallery.getFilteredPosts();
        const isKoreaMode = State.posts.some(p => p.country === 'South Korea');
        if (DOM.mapLoader) DOM.mapLoader.classList.remove('hidden');
        if (isKoreaMode) {
            if (State.mapLoaded.kakao) MapEngine.drawKakaoMap(filtered);
            else {
                const script = document.createElement('script'); script.src = `//dapi.kakao.com/v2/maps/sdk.js?appkey=${window.KAKAO_MAP_API_KEY}&autoload=false`;
                script.onload = () => kakao.maps.load(() => { State.mapLoaded.kakao = true; MapEngine.drawKakaoMap(filtered); }); document.head.appendChild(script);
            }
        } else {
            if (State.mapLoaded.google) MapEngine.drawGoogleMap(filtered);
            else {
                const script = document.createElement('script'); script.src = `https://maps.googleapis.com/maps/api/js?key=${window.GOOGLE_MAP_API_KEY}&callback=initGoogleMapEngine`;
                window.initGoogleMapEngine = () => { State.mapLoaded.google = true; MapEngine.drawGoogleMap(filtered); }; document.head.appendChild(script);
            }
        }
    },

    drawKakaoMap: (posts) => {
        if (DOM.mapLoader) DOM.mapLoader.classList.add('hidden');
        const first = posts.find(p => p.lat && p.lng) || {lat: 37.56, lng: 126.97};
        if (!State.mapInstance) { State.mapInstance = new kakao.maps.Map(DOM.mapCanvas, { center: new kakao.maps.LatLng(first.lat, first.lng), level: 11 }); }
        else { State.mapInstance.relayout(); if(posts.length > 0) State.mapInstance.setCenter(new kakao.maps.LatLng(first.lat, first.lng)); }
        MapEngine.clearMarkers();
        posts.forEach(post => {
            if (!post.lat || !post.photos?.length) return;
            const content = document.createElement('div');
            content.className = 'w-12 h-12 rounded-full border-2 border-white shadow-xl bg-surface cursor-pointer transition-transform hover:scale-110 overflow-hidden relative';
            content.innerHTML = `<img src="${post.photos[0].url}" crossorigin="anonymous" class="w-full h-full object-cover">`;
            content.addEventListener('click', () => ModalViewer.open(post));
            const overlay = new kakao.maps.CustomOverlay({ position: new kakao.maps.LatLng(post.lat, post.lng), content: content, yAnchor: 1.2 });
            overlay.setMap(State.mapInstance); State.mapMarkers.push(overlay);
        });
    },

    drawGoogleMap: (posts) => {
        if (DOM.mapLoader) DOM.mapLoader.classList.add('hidden');
        const first = posts.find(p => p.lat && p.lng) || {lat: 37.56, lng: 126.97};
        if(!State.mapInstance) { State.mapInstance = new google.maps.Map(DOM.mapCanvas, { zoom: 6, center: { lat: parseFloat(first.lat), lng: parseFloat(first.lng) }, mapTypeControl: false }); }
        MapEngine.clearMarkers();
        posts.forEach(post => {
            if (!post.lat || !post.photos?.length) return;
            const marker = new google.maps.Marker({ position: { lat: parseFloat(post.lat), lng: parseFloat(post.lng) }, map: State.mapInstance });
            marker.addListener("click", () => ModalViewer.open(post)); State.mapMarkers.push(marker);
        });
    },

    renderMarkers: () => { if(State.viewMode === 'map') MapEngine.initHybridMap(); }
};