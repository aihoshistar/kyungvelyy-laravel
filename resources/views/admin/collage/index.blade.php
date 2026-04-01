@extends('layouts.admin')

@section('title', 'INSTA STUDIO PRO')
@section('header_title', 'INSTA STUDIO PRO')

@section('styles')
<style>
    input[type=range] { -webkit-appearance: none; background: transparent; width: 100%; }
    input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; height: 16px; width: 16px; border-radius: 50%; background: #ffffff; cursor: pointer; margin-top: -6px; box-shadow: 0 0 10px rgba(0,0,0,0.5); transition: transform 0.1s; }
    input[type=range]::-webkit-slider-thumb:hover { transform: scale(1.2); }
    input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 4px; cursor: pointer; background: #333; border-radius: 2px; }
    input[type=range]:focus { outline: none; }

    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }

    .slice-divider {
        position: absolute;
        top: 0; bottom: 0;
        width: 1px;
        background-color: rgba(255, 255, 255, 0.3);
        border-right: 1px dashed rgba(59, 130, 246, 0.8);
        z-index: 50;
        pointer-events: none;
    }
    kbd {
        background-color: #333; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-family: monospace; border: 1px solid #555;
    }

    /* Active Queue Item Effect */
    .queue-item.active {
        border-color: #3b82f6;
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
@endsection

@section('content')

<!-- 💡 [추가] Global Drag & Drop Overlay -->
<div id="dnd-overlay" class="fixed inset-0 bg-blue-600/20 backdrop-blur-sm z-[100] hidden flex-col items-center justify-center border-4 border-blue-500 border-dashed m-4 rounded-3xl pointer-events-none transition-all duration-300 opacity-0">
    <div class="bg-black/50 p-8 rounded-2xl flex flex-col items-center shadow-2xl">
        <svg class="w-16 h-16 text-blue-400 animate-bounce mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
        <h2 class="text-3xl font-bold text-white tracking-widest uppercase">Drop Photos Here</h2>
        <p class="text-blue-200 mt-2">큐(Queue)에 자동으로 추가됩니다.</p>
    </div>
</div>

<div class="flex h-[calc(100vh-73px)] w-full overflow-hidden bg-[#050505]">

    <!-- Left Tool Palette -->
    <div class="w-24 md:w-32 border-r border-white/10 bg-[#0a0a0a] flex flex-col items-center py-6 shrink-0 space-y-8 z-10 overflow-y-auto hide-scroll">
        <button class="tool-btn active flex flex-col items-center gap-3 text-white hover:text-white transition group w-full" data-mode="single">
            <div class="w-12 h-12 rounded-xl bg-white/10 group-hover:bg-blue-500 flex items-center justify-center transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <span class="text-xs font-bold tracking-widest uppercase text-center leading-tight">Single<br>Export</span>
        </button>

        <button class="tool-btn flex flex-col items-center gap-3 text-gray-500 hover:text-white transition group w-full" data-mode="carousel">
            <div class="w-12 h-12 rounded-xl bg-transparent group-hover:bg-white/10 flex items-center justify-center transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
            </div>
            <span class="text-xs font-bold tracking-widest uppercase text-center leading-tight">Seamless<br>Slide</span>
        </button>
    </div>

    <!-- Center Workspace -->
    <div class="flex-1 flex flex-col relative overflow-hidden">

        <div class="flex-1 relative flex items-center justify-center p-6 md:p-10 overflow-hidden bg-black/50 overflow-x-auto custom-scrollbar" id="canvas-wrapper">

            <div class="relative flex items-center justify-center transition-all duration-300" id="single-wrapper">
                <canvas id="single-canvas" class="max-w-full max-h-[60vh] md:max-h-[70vh] shadow-2xl bg-white"></canvas>
                <div id="single-placeholder" class="absolute inset-0 flex items-center justify-center text-gray-500 text-sm font-bold tracking-widest uppercase bg-[#111] border border-white/10 rounded-xl w-[300px] h-[375px]">
                    아래에서 사진을 선택하세요
                </div>
            </div>

            <div class="relative transition-all duration-300 hidden" id="carousel-wrapper">
                <div id="carousel-workspace-container" class="relative border border-white/20 bg-[#ffffff] shadow-[0_20px_50px_rgba(0,0,0,0.5)]">
                    <canvas id="fabric-canvas"></canvas>
                    <div id="carousel-dividers" class="absolute inset-0 pointer-events-none"></div>
                </div>
            </div>

        </div>

        <!-- Bottom Queue Bar -->
        <div class="h-32 border-t border-white/10 bg-[#0a0a0a] px-6 py-4 flex flex-col justify-center shrink-0 z-10 relative">
            <div class="flex justify-between items-center mb-2">
                <div class="flex items-center gap-4">
                    <span class="text-xs font-bold text-white uppercase tracking-widest">Photo Queue</span>
                    <!-- 💡 [추가] Clear All 버튼 -->
                    <button id="btn-clear-queue" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-widest transition hidden">Clear All</button>
                </div>
                <div class="flex gap-2">
                    <label class="text-[10px] text-white hover:text-blue-300 font-bold uppercase tracking-widest px-4 py-1.5 border border-white/20 rounded-full cursor-pointer hover:bg-white/5 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Local Files
                        <input type="file" multiple accept="image/*" class="hidden" id="local-upload">
                    </label>
                    <button id="btn-load-posts" class="text-[10px] text-blue-400 hover:text-blue-300 font-bold uppercase tracking-widest px-4 py-1.5 bg-blue-500/10 rounded-full transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Load Albums
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-4 w-full">
                <div id="photo-queue" class="flex-1 flex gap-3 overflow-x-auto hide-scroll pb-2 items-center min-h-[5rem]">
                    <div class="text-xs text-gray-600 italic">사진을 업로드(Drag & Drop)하거나 앨범을 불러오세요.</div>
                </div>

                <div class="shrink-0 border-l border-white/10 pl-4">
                    <button id="btn-download" class="flex items-center gap-2 px-6 py-3 bg-white text-black font-bold tracking-widest text-xs uppercase rounded hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Detailed Settings Panel -->
    <div class="w-80 border-l border-white/10 bg-[#111] shrink-0 flex flex-col z-20 relative">

        <div class="p-6 border-b border-white/10 bg-[#0a0a0a]">
            <h2 id="panel-title" class="text-sm font-bold text-white tracking-[0.2em] uppercase">Single Export</h2>
            <p id="panel-desc" class="text-[10px] text-gray-500 mt-1">1장의 사진을 최적화된 테마로 내보냅니다.</p>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-8" id="settings-container">

            <div class="setting-group space-y-6" id="set-single">
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Export Ratio</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button class="single-ratio-btn active py-2.5 border border-blue-500 bg-blue-500/10 text-white rounded-lg text-xs font-bold transition" data-ratio="4:5">4:5 (세로형)</button>
                        <button class="single-ratio-btn py-2.5 border border-white/20 text-gray-400 hover:bg-white/5 rounded-lg text-xs font-bold transition" data-ratio="1:1">1:1 (정방형)</button>
                        <button class="single-ratio-btn py-2.5 border border-white/20 text-gray-400 hover:bg-white/5 rounded-lg text-xs font-bold transition" data-ratio="1.91:1">1.91:1 (가로형)</button>
                        <button class="single-ratio-btn py-2.5 border border-white/20 text-gray-400 hover:bg-white/5 rounded-lg text-xs font-bold transition" data-ratio="9:16">9:16 (스토리)</button>
                    </div>
                </div>

                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Author Info</span>
                    <input type="text" id="input-author" value="@kkyoung.photo" class="w-full bg-black border border-white/20 rounded px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                </div>
                <button id="btn-clear-single" class="w-full py-2 bg-red-500/10 text-red-400 text-xs font-bold rounded border border-red-500/20 hover:bg-red-500/20 transition hidden">
                    캔버스 비우기
                </button>
            </div>

            <div class="setting-group hidden space-y-8" id="set-carousel">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs font-bold text-gray-400 tracking-widest uppercase">Total Slides</span>
                        <span id="val-slides" class="text-xs font-mono text-white">2 장</span>
                    </div>
                    <input type="range" id="slider-slides" min="2" max="10" value="2" step="1">
                </div>

                <!-- 💡 [추가] Background Color -->
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Background Color</span>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="bg-color-carousel" value="#FFFFFF" class="w-8 h-8 rounded cursor-pointer border-0 p-0">
                        <button class="color-preset-c w-6 h-6 rounded-full border border-white/20 bg-white" data-color="#FFFFFF"></button>
                        <button class="color-preset-c w-6 h-6 rounded-full border border-white/20 bg-[#F9F8F6]" data-color="#F9F8F6"></button>
                        <button class="color-preset-c w-6 h-6 rounded-full border border-white/20 bg-[#1a1a1a]" data-color="#1a1a1a"></button>
                        <button class="color-preset-c w-6 h-6 rounded-full border border-white/20 bg-black" data-color="#000000"></button>
                    </div>
                </div>

                <div id="carousel-img-controls" class="p-4 bg-[#1a1a1a] border border-white/10 rounded-xl opacity-50 pointer-events-none transition-opacity">
                    <span class="text-xs font-bold text-blue-400 tracking-widest uppercase block mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        Selected Object
                    </span>

                    <div class="space-y-5">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] text-gray-500 uppercase">Scale</span>
                                <span id="val-scale" class="text-[10px] font-mono text-white">100%</span>
                            </div>
                            <input type="range" id="slider-scale" min="10" max="300" value="100">
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] text-gray-500 uppercase">Rotation</span>
                                <span id="val-rotation" class="text-[10px] font-mono text-white">0°</span>
                            </div>
                            <input type="range" id="slider-rotation" min="-180" max="180" value="0">
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2">
                            <button id="btn-img-fit" class="py-2 bg-blue-500/20 hover:bg-blue-500/40 text-blue-400 border border-blue-500/30 text-[10px] font-bold uppercase rounded transition">Fit Height</button>
                            <button id="btn-img-front" class="py-2 bg-white/10 hover:bg-white/20 text-white text-[10px] font-bold uppercase rounded transition">Bring Front</button>
                            <button id="btn-img-back" class="py-2 bg-white/10 hover:bg-white/20 text-white text-[10px] font-bold uppercase rounded transition">Send Back</button>
                            <button id="btn-img-remove" class="py-2 bg-red-500/20 hover:bg-red-500/40 text-red-400 text-[10px] font-bold uppercase rounded transition">Delete</button>
                        </div>
                    </div>
                </div>

                <div class="p-5 bg-gray-800/40 border border-white/10 rounded-xl text-xs text-gray-400 leading-relaxed shadow-inner">
                    <strong class="text-white block mb-2 text-[10px] tracking-widest uppercase">💡 Pro Tips & Shortcuts</strong>
                    <ul class="space-y-3 mt-3">
                        <li class="flex items-center justify-between"><span>마우스 드래그</span><span class="text-white">스마트 스냅 (자석)</span></li>
                        <li class="flex items-center justify-between"><span><kbd>↑</kbd> <kbd>↓</kbd> <kbd>←</kbd> <kbd>→</kbd></span><span class="text-white">1px 이동</span></li>
                        <li class="flex items-center justify-between"><span><kbd>Shift</kbd> + 방향키</span><span class="text-white">10px 이동</span></li>
                        <li class="flex items-center justify-between"><span><kbd>Del</kbd></span><span class="text-white">사진 삭제</span></li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal for Post Selection -->
<div id="modal-posts" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-[#111] border border-white/10 rounded-2xl w-full max-w-2xl max-h-[80vh] flex flex-col shadow-2xl">
        <div class="p-6 border-b border-white/10 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white tracking-widest uppercase">Load from Album</h3>
            <button onclick="document.getElementById('modal-posts').classList.add('hidden')" class="text-gray-500 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <div class="p-4 border-b border-white/10 bg-[#0a0a0a]">
            <input type="text" id="modal-search" placeholder="Search by title..." class="w-full bg-black border border-white/20 rounded-lg px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500">
        </div>
        <div id="modal-list" class="flex-1 overflow-y-auto p-4 space-y-2 custom-scrollbar"></div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // --- State Management ---
    const State = {
        mode: 'single',
        loadedPosts: [],
        photoQueueData: [],
        activeQueueId: null, // 💡 [추가] Active State 추적

        singlePhoto: null,
        authorInfo: '@kkyoung.photo',
        singleRatio: '4:5',

        slides: 2,
        baseWidth: 300,
        baseHeight: 375,
        carouselBgColor: '#FFFFFF'
    };

    const EXPORT_RATIO = 3.6;

    // --- DOM Elements ---
    const DOM = {
        toolBtns: document.querySelectorAll('.tool-btn'),
        setSingle: document.getElementById('set-single'),
        setCarousel: document.getElementById('set-carousel'),
        panelTitle: document.getElementById('panel-title'),
        panelDesc: document.getElementById('panel-desc'),

        singleWrapper: document.getElementById('single-wrapper'),
        carouselWrapper: document.getElementById('carousel-wrapper'),

        singleCanvas: document.getElementById('single-canvas'),
        singlePlaceholder: document.getElementById('single-placeholder'),
        inputAuthor: document.getElementById('input-author'),
        btnClearSingle: document.getElementById('btn-clear-single'),
        singleRatioBtns: document.querySelectorAll('.single-ratio-btn'),

        carouselWorkspaceContainer: document.getElementById('carousel-workspace-container'),
        sliderSlides: document.getElementById('slider-slides'),
        valSlides: document.getElementById('val-slides'),
        bgColorCarousel: document.getElementById('bg-color-carousel'),
        colorPresetsC: document.querySelectorAll('.color-preset-c'),

        imgControls: document.getElementById('carousel-img-controls'),
        sliderScale: document.getElementById('slider-scale'),
        valScale: document.getElementById('val-scale'),
        sliderRotation: document.getElementById('slider-rotation'),
        valRotation: document.getElementById('val-rotation'),

        btnFit: document.getElementById('btn-img-fit'),
        btnFront: document.getElementById('btn-img-front'),
        btnBack: document.getElementById('btn-img-back'),
        btnRemove: document.getElementById('btn-img-remove'),

        localUpload: document.getElementById('local-upload'),
        photoQueue: document.getElementById('photo-queue'),
        btnClearQueue: document.getElementById('btn-clear-queue'),
        btnLoad: document.getElementById('btn-load-posts'),
        btnDownload: document.getElementById('btn-download'),

        modal: document.getElementById('modal-posts'),
        modalSearch: document.getElementById('modal-search'),
        modalList: document.getElementById('modal-list'),

        dndOverlay: document.getElementById('dnd-overlay')
    };

    // --- Fabric.js Initialization ---
    let fCanvas = new fabric.Canvas('fabric-canvas', {
        preserveObjectStacking: true,
        backgroundColor: '#FFFFFF'
    });

    fabric.Object.prototype.set({
        transparentCorners: false, cornerColor: '#ffffff', cornerStrokeColor: '#3b82f6',
        borderColor: '#3b82f6', cornerSize: 10, padding: 5, cornerStyle: 'circle', borderDashArray: [4, 4]
    });

    fCanvas.on('selection:created', handleSelection);
    fCanvas.on('selection:updated', handleSelection);
    fCanvas.on('selection:cleared', () => { DOM.imgControls.classList.add('opacity-50', 'pointer-events-none'); });
    fCanvas.on('object:modified', syncSliders);
    fCanvas.on('object:scaling', syncSliders);
    fCanvas.on('object:rotating', syncSliders);

    function handleSelection(e) {
        const obj = e.selected[0];
        if(obj) { DOM.imgControls.classList.remove('opacity-50', 'pointer-events-none'); syncSliders({ target: obj }); }
    }

    function syncSliders(e) {
        const obj = e.target;
        if(obj) {
            const scalePercent = Math.round(obj.scaleX * 100);
            DOM.sliderScale.value = scalePercent; DOM.valScale.textContent = `${scalePercent}%`;
            const angle = Math.round(obj.angle || 0);
            DOM.sliderRotation.value = angle; DOM.valRotation.textContent = `${angle}°`;
        }
    }

    // 💡 [추가] Fabric.js Smart Snapping (자석 가이드라인)
    let snappingLines = [];
    fCanvas.on('object:moving', (e) => {
        const obj = e.target;
        const canvasW = fCanvas.getWidth();
        const canvasH = fCanvas.getHeight();
        const objW = obj.getScaledWidth();
        const objH = obj.getScaledHeight();
        const snapZone = 15;
        snappingLines = [];

        // 1. 수직 중앙 스냅 (가로선)
        const objCenterY = obj.top + objH/2;
        if (Math.abs(objCenterY - canvasH/2) < snapZone) {
            obj.set('top', canvasH/2 - objH/2);
            snappingLines.push({ p1: {x: 0, y: canvasH/2}, p2: {x: canvasW, y: canvasH/2} });
        }

        // 2. 캔버스 상단/하단 스냅
        if (Math.abs(obj.top) < snapZone) { obj.set('top', 0); snappingLines.push({ p1: {x: 0, y: 0}, p2: {x: canvasW, y: 0} }); }
        if (Math.abs((obj.top + objH) - canvasH) < snapZone) { obj.set('top', canvasH - objH); snappingLines.push({ p1: {x: 0, y: canvasH}, p2: {x: canvasW, y: canvasH} }); }

        // 3. 슬라이드 컷(Center) 스냅 (세로선)
        const objCenterX = obj.left + objW/2;
        for (let i = 0; i < State.slides; i++) {
            const slideCenterX = (i * State.baseWidth) + (State.baseWidth / 2);
            if (Math.abs(objCenterX - slideCenterX) < snapZone) {
                obj.set('left', slideCenterX - objW/2);
                snappingLines.push({ p1: {x: slideCenterX, y: 0}, p2: {x: slideCenterX, y: canvasH} });
                break;
            }
        }
    });

    fCanvas.on('after:render', () => {
        if (snappingLines.length > 0) {
            const ctx = fCanvas.contextContainer;
            ctx.save();
            ctx.strokeStyle = '#ec4899'; // Pink color for guides
            ctx.lineWidth = 1;
            snappingLines.forEach(line => {
                ctx.beginPath(); ctx.moveTo(line.p1.x, line.p1.y); ctx.lineTo(line.p2.x, line.p2.y); ctx.stroke();
            });
            ctx.restore();
        }
    });
    fCanvas.on('mouse:up', () => { snappingLines = []; fCanvas.requestRenderAll(); });


    // --- Mode Switching ---
    DOM.toolBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if(State.mode === btn.dataset.mode) return;
            DOM.toolBtns.forEach(b => {
                b.classList.remove('active', 'text-white'); b.classList.add('text-gray-500');
                b.querySelector('div').classList.remove('bg-blue-500'); b.querySelector('div').classList.add('bg-transparent');
            });
            btn.classList.add('active', 'text-white'); btn.classList.remove('text-gray-500');
            btn.querySelector('div').classList.add('bg-blue-500'); btn.querySelector('div').classList.remove('bg-transparent');

            State.mode = btn.dataset.mode;

            if(State.mode === 'single') {
                DOM.panelTitle.textContent = 'Single Export'; DOM.panelDesc.textContent = '1장의 사진을 갤러리 테마로 내보냅니다.';
                DOM.setCarousel.classList.add('hidden'); DOM.setSingle.classList.remove('hidden');
                DOM.carouselWrapper.classList.add('hidden'); DOM.singleWrapper.classList.remove('hidden');
                updateSingleUI();
            } else {
                DOM.panelTitle.textContent = 'Seamless Slide'; DOM.panelDesc.textContent = '사진을 이어붙여 인스타 파노라마를 만듭니다.';
                DOM.setSingle.classList.add('hidden'); DOM.setCarousel.classList.remove('hidden');
                DOM.singleWrapper.classList.add('hidden'); DOM.carouselWrapper.classList.remove('hidden');
                updateCarouselUI();
            }
            updateDownloadBtn();
            renderQueue(); // Update active states
        });
    });

    // --- Single Mode Logic ---
    DOM.inputAuthor.addEventListener('input', (e) => { State.authorInfo = e.target.value; renderSingleCanvas(); });
    DOM.singleRatioBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            DOM.singleRatioBtns.forEach(b => { b.classList.remove('border-blue-500', 'bg-blue-500/10', 'text-white'); b.classList.add('border-white/20', 'text-gray-400'); });
            btn.classList.add('border-blue-500', 'bg-blue-500/10', 'text-white'); btn.classList.remove('border-white/20', 'text-gray-400');
            State.singleRatio = btn.dataset.ratio; renderSingleCanvas();
        });
    });

    DOM.btnClearSingle.addEventListener('click', () => {
        State.singlePhoto = null; State.activeQueueId = null;
        updateSingleUI(); updateDownloadBtn(); renderQueue();
    });

    const updateSingleUI = () => {
        if(State.singlePhoto) {
            DOM.singlePlaceholder.classList.add('hidden'); DOM.singleCanvas.classList.remove('hidden'); DOM.btnClearSingle.classList.remove('hidden');
            renderSingleCanvas();
        } else {
            DOM.singlePlaceholder.classList.remove('hidden'); DOM.singleCanvas.classList.add('hidden'); DOM.btnClearSingle.classList.add('hidden');
            DOM.singleCanvas.style.width = '0px'; DOM.singleCanvas.style.height = '0px';
        }
    };

    const renderSingleCanvas = () => {
        if(!State.singlePhoto) return;
        const { photo, imgElement } = State.singlePhoto;
        const canvas = DOM.singleCanvas; const ctx = canvas.getContext('2d'); const img = imgElement;

        let w = 1080; let h = 1350;
        if (State.singleRatio === '1:1') h = 1080;
        else if (State.singleRatio === '1.91:1') h = 566;
        else if (State.singleRatio === '9:16') h = 1920;

        canvas.width = w; canvas.height = h; canvas.style.aspectRatio = `${w} / ${h}`;
        ctx.fillStyle = "#FFFFFF"; ctx.fillRect(0, 0, w, h);

        const marginX = w * 0.05; const marginBottom = w * 0.15; const marginTop = marginX;
        const availW = w - (marginX * 2); const availH = h - marginTop - marginBottom;

        const scale = Math.min(availW / img.width, availH / img.height);
        const drawW = img.width * scale; const drawH = img.height * scale;
        const drawX = (w - drawW) / 2; const drawY = marginTop + (availH - drawH) / 2;

        ctx.shadowColor = "rgba(0, 0, 0, 0.15)"; ctx.shadowBlur = Math.max(20, w * 0.015); ctx.shadowOffsetY = Math.max(10, w * 0.008);
        ctx.drawImage(img, drawX, drawY, drawW, drawH); ctx.shadowColor = "transparent";

        const leftTextArr = [];
        if (photo?.exif_body) leftTextArr.push(photo.exif_body);
        if (photo?.exif_lens) leftTextArr.push(photo.exif_lens);
        const leftText = leftTextArr.join(' | ');

        const rightTextArr = [];
        if (photo?.exif_focal_length) rightTextArr.push(`${photo.exif_focal_length}mm`);
        if (photo?.exif_aperture) rightTextArr.push(`F${parseFloat(photo.exif_aperture)}`);
        if (photo?.exif_shutter_speed_num && photo?.exif_shutter_speed_den) rightTextArr.push(`${photo.exif_shutter_speed_num}/${photo.exif_shutter_speed_den}s`);
        if (photo?.exif_iso) rightTextArr.push(`ISO ${photo.exif_iso}`);
        const rightText = rightTextArr.join(' | ');

        const textY = drawY + drawH + (marginBottom * 0.4); const fontSize = Math.max(18, w * 0.016);
        ctx.textBaseline = "middle";
        ctx.fillStyle = "#333333"; ctx.textAlign = "left"; ctx.font = `600 ${fontSize}px Pretendard, sans-serif`; ctx.fillText(leftText, marginX, textY);
        ctx.fillStyle = "#888888"; ctx.font = `400 ${fontSize * 0.85}px Pretendard, sans-serif`; ctx.fillText(State.authorInfo, marginX, textY + fontSize * 1.5);

        const qrSize = marginBottom * 0.5; const qrX = w - marginX - qrSize; const qrY = drawY + drawH + (marginBottom - qrSize) / 2;
        ctx.fillStyle = "#333333"; ctx.textAlign = "right"; ctx.font = `400 ${fontSize}px Pretendard, sans-serif`; ctx.fillText(rightText, qrX - (marginX * 0.5), qrY + (qrSize / 2));

        const postUrl = photo?.id ? `${window.location.origin}/ko#photo-${photo.id}` : window.location.origin;
        const qr = new QRious({ value: postUrl, size: qrSize, level: 'M' });
        const qrImg = new Image(); qrImg.src = qr.toDataURL();
        qrImg.onload = () => { ctx.globalAlpha = 0.8; ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize); ctx.globalAlpha = 1.0; };
    };

    // --- Carousel Logic (Fabric) ---
    DOM.sliderSlides.addEventListener('input', (e) => { State.slides = parseInt(e.target.value); DOM.valSlides.textContent = `${State.slides} 장`; updateCarouselUI(); });

    // 💡 [추가] Background Color
    DOM.bgColorCarousel.addEventListener('input', (e) => { State.carouselBgColor = e.target.value; fCanvas.backgroundColor = State.carouselBgColor; fCanvas.requestRenderAll(); });
    DOM.colorPresetsC.forEach(btn => {
        btn.addEventListener('click', () => {
            State.carouselBgColor = btn.dataset.color; DOM.bgColorCarousel.value = State.carouselBgColor;
            fCanvas.backgroundColor = State.carouselBgColor; fCanvas.requestRenderAll();
        });
    });

    const updateCarouselUI = () => {
        const totalWidth = State.slides * State.baseWidth;
        DOM.carouselWorkspaceContainer.style.width = `${totalWidth}px`; DOM.carouselWorkspaceContainer.style.height = `${State.baseHeight}px`;
        fCanvas.setWidth(totalWidth); fCanvas.setHeight(State.baseHeight);
        fCanvas.backgroundColor = State.carouselBgColor;
        fCanvas.requestRenderAll();

        const dividersContainer = document.getElementById('carousel-dividers'); dividersContainer.innerHTML = '';
        for(let i = 1; i < State.slides; i++) {
            const divider = document.createElement('div'); divider.className = 'slice-divider'; divider.style.left = `${i * State.baseWidth}px`; dividersContainer.appendChild(divider);
        }
        updateDownloadBtn();
    };

    const addCarouselItem = (url) => {
        fabric.Image.fromURL(url, function(img) {
            const scale = State.baseHeight / img.height;
            img.set({ scaleX: scale, scaleY: scale, left: (State.baseWidth - (img.width * scale)) / 2, top: 0 });
            fCanvas.add(img); fCanvas.setActiveObject(img); fCanvas.requestRenderAll(); updateDownloadBtn();
        }, { crossOrigin: 'anonymous' });
    };

    DOM.sliderScale.addEventListener('input', (e) => {
        const obj = fCanvas.getActiveObject();
        if(obj) { const val = e.target.value / 100; obj.set({ scaleX: val, scaleY: val }); fCanvas.requestRenderAll(); DOM.valScale.textContent = `${e.target.value}%`; }
    });
    DOM.sliderRotation.addEventListener('input', (e) => {
        const obj = fCanvas.getActiveObject();
        if(obj) { obj.rotate(parseFloat(e.target.value)); fCanvas.requestRenderAll(); DOM.valRotation.textContent = `${e.target.value}°`; }
    });
    DOM.btnFit.addEventListener('click', () => {
        const obj = fCanvas.getActiveObject();
        if(obj) {
            const scale = State.baseHeight / obj.height;
            obj.set({ scaleX: scale, scaleY: scale, top: 0, angle: 0 });
            fCanvas.centerObjectH(obj); obj.setCoords(); fCanvas.requestRenderAll(); syncSliders({ target: obj });
        }
    });
    DOM.btnFront.addEventListener('click', () => { const obj = fCanvas.getActiveObject(); if(obj) fCanvas.bringForward(obj); });
    DOM.btnBack.addEventListener('click', () => { const obj = fCanvas.getActiveObject(); if(obj) fCanvas.sendBackwards(obj); });
    DOM.btnRemove.addEventListener('click', () => {
        const obj = fCanvas.getActiveObject();
        if(obj) { fCanvas.remove(obj); fCanvas.discardActiveObject(); fCanvas.requestRenderAll(); updateDownloadBtn(); }
    });
    window.addEventListener('keydown', (e) => {
        if(State.mode !== 'carousel' || !fCanvas) return;
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        const obj = fCanvas.getActiveObject(); if (!obj) return;
        const moveStep = e.shiftKey ? 10 : 1;
        switch(e.key) {
            case 'ArrowUp': obj.top -= moveStep; obj.setCoords(); fCanvas.requestRenderAll(); e.preventDefault(); break;
            case 'ArrowDown': obj.top += moveStep; obj.setCoords(); fCanvas.requestRenderAll(); e.preventDefault(); break;
            case 'ArrowLeft': obj.left -= moveStep; obj.setCoords(); fCanvas.requestRenderAll(); e.preventDefault(); break;
            case 'ArrowRight': obj.left += moveStep; obj.setCoords(); fCanvas.requestRenderAll(); e.preventDefault(); break;
            case 'Delete': case 'Backspace': fCanvas.remove(obj); fCanvas.discardActiveObject(); fCanvas.requestRenderAll(); updateDownloadBtn(); break;
            case '[': fCanvas.sendBackwards(obj); break; case ']': fCanvas.bringForward(obj); break;
        }
    });


    // --- 4. Unified Queue Management & Drag Drop ---

    // 💡 [추가] Global Drag & Drop Events
    window.addEventListener('dragover', (e) => {
        e.preventDefault(); DOM.dndOverlay.classList.remove('hidden'); DOM.dndOverlay.classList.remove('opacity-0');
    });
    window.addEventListener('dragleave', (e) => {
        if(e.clientX === 0 || e.clientY === 0) {
            DOM.dndOverlay.classList.add('opacity-0'); setTimeout(() => DOM.dndOverlay.classList.add('hidden'), 300);
        }
    });
    window.addEventListener('drop', (e) => {
        e.preventDefault();
        DOM.dndOverlay.classList.add('opacity-0'); setTimeout(() => DOM.dndOverlay.classList.add('hidden'), 300);

        const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
        if(!files.length) return;

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (ev) => {
                State.photoQueueData.push({ id: 'local_' + Date.now() + Math.random(), url: ev.target.result, photo: null });
                renderQueue();
            };
            reader.readAsDataURL(file);
        });
    });

    // 로컬 파일 버튼 업로드
    DOM.localUpload.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        if(!files.length) return;
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (ev) => {
                State.photoQueueData.push({ id: 'local_' + Date.now() + Math.random(), url: ev.target.result, photo: null });
                renderQueue();
            };
            reader.readAsDataURL(file);
        });
        DOM.localUpload.value = '';
    });

    // 💡 [추가] 큐 전체 비우기
    DOM.btnClearQueue.addEventListener('click', () => {
        State.photoQueueData = [];
        State.activeQueueId = null;
        if(State.mode === 'single') { State.singlePhoto = null; updateSingleUI(); }
        renderQueue(); updateDownloadBtn();
    });

    // 모달을 통한 서버 파일 로드
    DOM.btnLoad.addEventListener('click', async () => {
        DOM.modal.classList.remove('hidden'); setTimeout(() => DOM.modal.classList.remove('opacity-0'), 10);
        DOM.modalList.innerHTML = '<div class="text-center text-gray-500 py-10 text-sm">앨범 목록을 불러오는 중...</div>';
        try {
            const res = await fetch('/admin/collage/api/posts'); State.loadedPosts = await res.json(); renderModalList(State.loadedPosts);
        } catch(e) { DOM.modalList.innerHTML = '<div class="text-red-500 text-center py-10">오류 발생</div>'; }
    });

    const renderModalList = (posts) => {
        DOM.modalList.innerHTML = posts.map(p => `
            <div class="flex justify-between items-center p-4 bg-black border border-white/10 rounded-xl hover:bg-white/5 transition cursor-pointer group" onclick="loadPostPhotos(${p.id})">
                <div><div class="font-bold text-white text-sm group-hover:text-blue-400">${p.title?.ko || 'Album #'+p.id}</div><div class="text-xs text-gray-500 font-mono mt-1">${p.taken_at}</div></div>
                <button class="px-4 py-2 bg-white/10 text-white text-xs font-bold rounded hover:bg-blue-600">선택</button>
            </div>
        `).join('');
    };

    window.loadPostPhotos = async (id) => {
        DOM.modalList.innerHTML = '<div class="text-center text-gray-500 py-10 text-sm">사진을 불러오는 중...</div>';
        try {
            const res = await fetch(`/admin/collage/api/posts/${id}/photos`); const photos = await res.json();
            DOM.modal.classList.add('opacity-0'); setTimeout(() => DOM.modal.classList.add('hidden'), 300);
            if(photos.length === 0) return;
            photos.forEach(p => {
                if(!State.photoQueueData.find(q => q.id === `server_${p.id}`)) {
                    State.photoQueueData.push({ id: `server_${p.id}`, url: p.url, photo: p });
                }
            });
            renderQueue();
        } catch(e) { alert('실패'); }
    };

    // 큐 UI 렌더링
    const renderQueue = () => {
        if(State.photoQueueData.length === 0) {
            DOM.photoQueue.innerHTML = '<div class="text-xs text-gray-600 italic">사진을 업로드(Drag & Drop)하거나 앨범을 불러오세요.</div>';
            DOM.btnClearQueue.classList.add('hidden');
            return;
        }

        DOM.btnClearQueue.classList.remove('hidden');

        DOM.photoQueue.innerHTML = State.photoQueueData.map(item => `
            <div class="h-20 w-20 shrink-0 rounded-lg overflow-hidden border-2 transition relative group queue-item ${State.activeQueueId === item.id ? 'active' : 'border-transparent hover:border-blue-500'}" data-id="${item.id}">
                <img src="${item.url}" class="w-full h-full object-cover">
                <!-- 💡 [추가] 개별 삭제 버튼 -->
                <button class="absolute top-1 right-1 w-5 h-5 bg-black/60 hover:bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition z-10 btn-remove-queue" data-id="${item.id}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        `).join('');

        // 큐 아이템 클릭 (로드)
        document.querySelectorAll('.queue-item').forEach(el => {
            el.addEventListener('click', (e) => {
                // 삭제 버튼 클릭 시 무시
                if(e.target.closest('.btn-remove-queue')) return;

                const itemId = el.dataset.id;
                State.activeQueueId = itemId;
                const item = State.photoQueueData.find(q => q.id === itemId);

                if(State.mode === 'single') {
                    const imgElement = new Image();
                    imgElement.crossOrigin = "Anonymous";
                    imgElement.src = item.url;
                    imgElement.onload = () => {
                        State.singlePhoto = { photo: item.photo, imgElement };
                        updateSingleUI(); updateDownloadBtn(); renderQueue(); // 렌더링을 다시 해서 Active 테두리 업데이트
                    };
                } else {
                    addCarouselItem(item.url);
                    renderQueue();
                }
            });
        });

        // 큐 아이템 삭제
        document.querySelectorAll('.btn-remove-queue').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const removeId = btn.dataset.id;
                State.photoQueueData = State.photoQueueData.filter(q => q.id !== removeId);
                if(State.activeQueueId === removeId) {
                    State.activeQueueId = null;
                    if(State.mode === 'single') { State.singlePhoto = null; updateSingleUI(); }
                }
                renderQueue(); updateDownloadBtn();
            });
        });
    };

    // --- 5. Export Logic ---
    const updateDownloadBtn = () => {
        if(State.mode === 'single') { DOM.btnDownload.disabled = !State.singlePhoto; }
        else { DOM.btnDownload.disabled = fCanvas.getObjects().length === 0; }
    };

    DOM.btnDownload.addEventListener('click', async () => {
        if(State.mode === 'single') {
            const link = document.createElement('a'); link.download = `insta_single_${Date.now()}.jpg`;
            link.href = DOM.singleCanvas.toDataURL("image/jpeg", 1.0); link.click();
            if(window.showToast) window.showToast('단일 사진이 성공적으로 저장되었습니다!');
        }
        else {
            DOM.btnDownload.disabled = true; DOM.btnDownload.textContent = '처리중...';
            fCanvas.discardActiveObject(); fCanvas.requestRenderAll();

            const dataUrl = fCanvas.toDataURL({ format: 'jpeg', quality: 1.0, multiplier: EXPORT_RATIO });
            const bigImg = new Image();
            bigImg.onload = async () => {
                const sliceWidth = 1080; const sliceHeight = 1350;
                for(let i = 0; i < State.slides; i++) {
                    const sliceCanvas = document.createElement('canvas');
                    sliceCanvas.width = sliceWidth; sliceCanvas.height = sliceHeight;
                    const sCtx = sliceCanvas.getContext('2d');
                    sCtx.drawImage(bigImg, i * sliceWidth, 0, sliceWidth, sliceHeight, 0, 0, sliceWidth, sliceHeight);
                    const link = document.createElement('a');
                    link.download = `insta_panorama_slide_${i+1}_of_${State.slides}.jpg`;
                    link.href = sliceCanvas.toDataURL("image/jpeg", 1.0); link.click();
                    await new Promise(r => setTimeout(r, 400));
                }
                DOM.btnDownload.textContent = 'EXPORT'; DOM.btnDownload.disabled = false;
                if(window.showToast) window.showToast('파노라마 분할 저장이 완료되었습니다!');
            };
            bigImg.src = dataUrl;
        }
    });

    updateCarouselUI();
});
</script>
@endsection
