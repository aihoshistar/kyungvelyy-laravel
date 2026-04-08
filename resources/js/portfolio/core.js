// resources/js/portfolio/core.js

import { Utils } from './utils.js';
import { Gallery, ModalViewer, MapEngine } from './components.js';
import { Exporter } from './exporter.js';
import { Interaction } from './interaction.js';

export const State = {
    lang: window.CURRENT_LOCALE || 'ko',
    category: 'all',
    year: 'all',      
    country: 'all',
    camera: 'all',
    lens: 'all',      
    posts: window.PORTFOLIO_DATA || [],
    activePost: null,
    currentPhotoIndex: 0,
    viewMode: 'grid',
    mapInstance: null,
    mapLoaded: { kakao: false, google: false },
    fingerprint: null,
    isGridActive: false,
    mapMarkers: [],
    revealObserver: null
};

export let DOM = {};

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
        yearFilter: document.getElementById('year-filter'), countryFilter: document.getElementById('country-filter'), 
        cameraFilter: document.getElementById('camera-filter'), lensFilter: document.getElementById('lens-filter'), 
        shortcutOverlay: document.getElementById('shortcut-overlay')
    };
};

const bindEvents = () => {
    DOM.themeToggleBtn.addEventListener('click', toggleTheme);
    DOM.langBtns.forEach(btn => btn.addEventListener('click', (e) => updateLanguage(e.target.dataset.lang)));
    DOM.filterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            DOM.filterBtns.forEach(b => { b.classList.remove('theme-filter-active'); b.classList.add('theme-filter-inactive'); });
            e.target.classList.remove('theme-filter-inactive'); e.target.classList.add('theme-filter-active');
            State.category = e.target.dataset.category;
            if (State.viewMode === 'grid') Gallery.renderGrid(); else MapEngine.renderMarkers();
        });
    });
    
    DOM.modal.addEventListener('click', (e) => { if (e.target === DOM.modal) ModalViewer.close(); });
    
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            if (DOM.shortcutOverlay && !DOM.shortcutOverlay.classList.contains('hidden')) toggleShortcutHelp();
            else if (!DOM.modal.classList.contains('hidden')) ModalViewer.close();
        }
        if (DOM.modal.classList.contains('hidden')) return;
        if (e.key === 'ArrowRight') ModalViewer.next(); 
        if (e.key === 'ArrowLeft') ModalViewer.prev(); 
    });

    let touchStartX = 0;
    let touchStartY = 0;

    DOM.modalContainer.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    DOM.modalContainer.addEventListener('touchend', (e) => {
        if (DOM.modal.classList.contains('hidden') || !State.activePost) return;
        const xDiff = touchStartX - e.changedTouches[0].screenX;
        const yDiff = touchStartY - e.changedTouches[0].screenY;

        if (Math.abs(xDiff) > Math.abs(yDiff) && Math.abs(xDiff) > 50) {
            if (xDiff > 0) ModalViewer.next();
            else ModalViewer.prev();
        }
    }, { passive: true });
};

const bindShortcuts = () => {
    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        const key = e.key.toLowerCase();
        const isModalOpen = !DOM.modal.classList.contains('hidden');

        if (key === '?') toggleShortcutHelp();
        else if (key === 'd') toggleTheme();
        else if (key === 'f') {
            if (!document.fullscreenElement) document.documentElement.requestFullscreen().catch(() => {});
            else document.exitFullscreen();
        } else if (!isModalOpen) {
            if (key === 'g') Gallery.switchView('grid');
            else if (key === 'm') Gallery.switchView('map');
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

const initTheme = () => {
    const isDark = document.documentElement.classList.contains('dark');
    document.getElementById('theme-toggle-light-icon').classList.toggle('hidden', !isDark);
    document.getElementById('theme-toggle-dark-icon').classList.toggle('hidden', isDark);
};

const toggleTheme = () => {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    initTheme(); if(State.viewMode === 'map') MapEngine.renderMarkers();
};

const initI18n = () => {
    const i18nFallback = {
        ko: { filterAllYears: '모든 연도', filterAllCountries: '모든 국가', filterAllCameras: '모든 기종', filterAllLenses: '모든 렌즈', colorPalette: 'Color Palette' },
        en: { filterAllYears: 'All Years', filterAllCountries: 'All Countries', filterAllCameras: 'All Cameras', filterAllLenses: 'All Lenses', colorPalette: 'Color Palette' },
        ja: { filterAllYears: 'すべての年', filterAllCountries: 'すべての国', filterAllCameras: 'すべてのカメラ', filterAllLenses: 'すべてのレンズ', colorPalette: 'Color Palette' }
    };
    ['ko', 'en', 'ja'].forEach(l => { window.I18N[l] = { ...i18nFallback[l], ...window.I18N[l] }; });
};

const updateLanguage = (lang) => {
    State.lang = lang;
    DOM.langBtns.forEach(btn => {
        btn.classList.toggle('theme-text', btn.dataset.lang === lang);
        btn.classList.toggle('font-bold', btn.dataset.lang === lang);
        btn.classList.toggle('theme-text-muted', btn.dataset.lang !== lang);
    });

    const currentDict = window.I18N[lang];
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.dataset.i18n;
        if (currentDict && currentDict[key]) el.innerHTML = currentDict[key];
    });

    if (State.viewMode === 'grid') Gallery.renderGrid(); else MapEngine.renderMarkers();
    if (!DOM.modal.classList.contains('hidden') && State.activePost) ModalViewer.renderSlide();
};

export const init = () => {
    initDOM();
    initI18n();
    Gallery.initFilters();
    bindEvents();
    bindShortcuts();
    initTheme();
    updateLanguage(State.lang);

    State.revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('active'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => { el.classList.remove('active'); State.revealObserver.observe(el); });

    let id = localStorage.getItem('guest_fingerprint');
    if (!id) { id = 'fp_' + Math.random().toString(36).substring(2, 9) + Date.now(); localStorage.setItem('guest_fingerprint', id); }
    State.fingerprint = id;

    // 💡 [수정] 해시 대신 딥링크(Clean URL) 파서를 호출합니다.
    Utils.checkDeepLink();
};

window.Portfolio = { 
    init, 
    switchView: Gallery.switchView, 
    openModal: ModalViewer.open, 
    closeModal: ModalViewer.close, 
    nextPhoto: ModalViewer.next, 
    prevPhoto: ModalViewer.prev, 
    toggleGrid: ModalViewer.toggleGrid, 
    processDownload: Exporter.processDownload, 
    sharePhoto: Interaction.sharePhoto, 
    handleReaction: Interaction.handleReaction 
};