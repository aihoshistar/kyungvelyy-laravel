@extends('layouts.admin')

@section('title', 'INSTA STUDIO PRO')
@section('header_title', 'INSTA STUDIO PRO')

@section('styles')
<style>
    /* Custom Range Slider Styling */
    input[type=range] { -webkit-appearance: none; background: transparent; width: 100%; }
    input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; height: 16px; width: 16px; border-radius: 50%; background: #ffffff; cursor: pointer; margin-top: -6px; box-shadow: 0 0 10px rgba(0,0,0,0.5); transition: transform 0.1s; }
    input[type=range]::-webkit-slider-thumb:hover { transform: scale(1.2); }
    input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 4px; cursor: pointer; background: #333; border-radius: 2px; }
    input[type=range]:focus { outline: none; }
    
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }

    /* Carousel Workspace Overlay Styles */
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
</style>
<!-- 💡 핵심: Fabric.js 라이브러리 로드 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
@endsection

@section('content')
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
            
            <!-- Single Mode Canvas -->
            <div class="relative flex items-center justify-center transition-all duration-300" id="single-wrapper">
                <canvas id="single-canvas" class="max-w-full max-h-[60vh] md:max-h-[70vh] shadow-2xl bg-white"></canvas>
                <div id="single-placeholder" class="absolute inset-0 flex items-center justify-center text-gray-500 text-sm font-bold tracking-widest uppercase bg-[#111] border border-white/10 rounded-xl w-[300px] h-[375px]">
                    아래에서 사진을 선택하세요
                </div>
            </div>

            <!-- Carousel Mode Workspace (Fabric.js Container) -->
            <div class="relative transition-all duration-300 hidden" id="carousel-wrapper">
                <div id="carousel-workspace-container" class="relative border border-white/20 bg-[#ffffff] shadow-[0_20px_50px_rgba(0,0,0,0.5)]">
                    <!-- Fabric Canvas Object -->
                    <canvas id="fabric-canvas"></canvas>
                    <!-- CSS Guide Lines (Slice Dividers) -->
                    <div id="carousel-dividers" class="absolute inset-0 pointer-events-none"></div>
                </div>
            </div>

        </div>

        <!-- Bottom Queue Bar -->
        <div class="h-32 border-t border-white/10 bg-[#0a0a0a] px-6 py-4 flex flex-col justify-center shrink-0 z-10 relative">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-bold text-white uppercase tracking-widest">Photo Queue</span>
                <button id="btn-load-posts" class="text-xs text-blue-400 hover:text-blue-300 font-bold uppercase tracking-widest px-4 py-1 bg-blue-500/10 rounded-full transition">Load Albums</button>
            </div>
            
            <div class="flex items-center gap-4 w-full">
                <div id="photo-queue" class="flex-1 flex gap-3 overflow-x-auto hide-scroll pb-2 items-center">
                    <div class="text-xs text-gray-600 italic">우측 상단 'Load Albums'를 눌러 앨범을 불러오세요.</div>
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
            
            <!-- Single Mode Info -->
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
                    선택 취소
                </button>
            </div>

            <!-- Carousel Mode Settings -->
            <div class="setting-group hidden space-y-8" id="set-carousel">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs font-bold text-gray-400 tracking-widest uppercase">Total Slides</span>
                        <span id="val-slides" class="text-xs font-mono text-white">2 장</span>
                    </div>
                    <input type="range" id="slider-slides" min="2" max="10" value="2" step="1">
                </div>

                <div id="carousel-img-controls" class="p-4 bg-[#1a1a1a] border border-white/10 rounded-xl opacity-50 pointer-events-none transition-opacity">
                    <span class="text-xs font-bold text-blue-400 tracking-widest uppercase block mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        Selected Object
                    </span>
                    
                    <div class="space-y-5">
                        <!-- Scale Control -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] text-gray-500 uppercase">Scale</span>
                                <span id="val-scale" class="text-[10px] font-mono text-white">100%</span>
                            </div>
                            <input type="range" id="slider-scale" min="10" max="300" value="100">
                        </div>
                        
                        <!-- Rotation Control -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] text-gray-500 uppercase">Rotation</span>
                                <span id="val-rotation" class="text-[10px] font-mono text-white">0°</span>
                            </div>
                            <input type="range" id="slider-rotation" min="-180" max="180" value="0">
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-2 pt-2">
                            <button id="btn-img-fit" class="py-2 bg-blue-500/20 hover:bg-blue-500/40 text-blue-400 border border-blue-500/30 text-[10px] font-bold uppercase rounded transition">Fit Height</button>
                            <button id="btn-img-front" class="py-2 bg-white/10 hover:bg-white/20 text-white text-[10px] font-bold uppercase rounded transition">Bring Front</button>
                            <button id="btn-img-back" class="py-2 bg-white/10 hover:bg-white/20 text-white text-[10px] font-bold uppercase rounded transition">Send Back</button>
                            <button id="btn-img-remove" class="py-2 bg-red-500/20 hover:bg-red-500/40 text-red-400 text-[10px] font-bold uppercase rounded transition">Delete</button>
                        </div>
                    </div>
                </div>
                
                <!-- Pro Tips -->
                <div class="p-5 bg-gray-800/40 border border-white/10 rounded-xl text-xs text-gray-400 leading-relaxed shadow-inner">
                    <strong class="text-white block mb-2 text-[10px] tracking-widest uppercase">💡 Pro Tips & Shortcuts</strong>
                    <ul class="space-y-3 mt-3">
                        <li class="flex items-center justify-between"><span>마우스 드래그</span><span class="text-white">자유 변형</span></li>
                        <li class="flex items-center justify-between"><span><kbd>↑</kbd> <kbd>↓</kbd> <kbd>←</kbd> <kbd>→</kbd></span><span class="text-white">1px 이동</span></li>
                        <li class="flex items-center justify-between"><span><kbd>Shift</kbd> + 방향키</span><span class="text-white">10px 이동</span></li>
                        <li class="flex items-center justify-between"><span><kbd>[</kbd> <kbd>]</kbd></span><span class="text-white">위/아래 순서</span></li>
                        <li class="flex items-center justify-between"><span><kbd>Del</kbd> / <kbd>Back</kbd></span><span class="text-white">사진 삭제</span></li>
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
        
        // Single
        singlePhoto: null, 
        authorInfo: '@kkyoung.photo',
        singleRatio: '4:5', 
        
        // Carousel (Fabric.js)
        slides: 2,
        baseWidth: 300,  // Slide Preview Width
        baseHeight: 375, // Slide Preview Height
    };

    const EXPORT_RATIO = 3.6; // 1080 / 300 = 3.6 (Export 고화질 스케일링)

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
        
        imgControls: document.getElementById('carousel-img-controls'),
        sliderScale: document.getElementById('slider-scale'),
        valScale: document.getElementById('val-scale'),
        sliderRotation: document.getElementById('slider-rotation'),
        valRotation: document.getElementById('val-rotation'),
        
        btnFit: document.getElementById('btn-img-fit'),
        btnFront: document.getElementById('btn-img-front'),
        btnBack: document.getElementById('btn-img-back'),
        btnRemove: document.getElementById('btn-img-remove'),
        
        photoQueue: document.getElementById('photo-queue'),
        btnLoad: document.getElementById('btn-load-posts'),
        btnDownload: document.getElementById('btn-download'),
        modal: document.getElementById('modal-posts'),
        modalSearch: document.getElementById('modal-search'),
        modalList: document.getElementById('modal-list')
    };

    // --- 0. Fabric.js Initialization ---
    let fCanvas = new fabric.Canvas('fabric-canvas', {
        preserveObjectStacking: true, // 선택된 객체가 무조건 위로 오지 않고 순서 유지
        backgroundColor: '#FFFFFF'
    });
    
    // 글로벌 컨트롤 테마 (포토샵 스타일의 깔끔한 블루 바운딩 박스)
    fabric.Object.prototype.set({
        transparentCorners: false,
        cornerColor: '#ffffff',
        cornerStrokeColor: '#3b82f6',
        borderColor: '#3b82f6',
        cornerSize: 10,
        padding: 5,
        cornerStyle: 'circle',
        borderDashArray: [4, 4]
    });

    // Fabric 이벤트 리스너 바인딩 (UI 연동)
    fCanvas.on('selection:created', handleSelection);
    fCanvas.on('selection:updated', handleSelection);
    fCanvas.on('selection:cleared', () => {
        DOM.imgControls.classList.add('opacity-50', 'pointer-events-none');
    });

    // 객체 변형 중 슬라이더 UI 실시간 업데이트
    fCanvas.on('object:modified', syncSliders);
    fCanvas.on('object:scaling', syncSliders);
    fCanvas.on('object:rotating', syncSliders);

    function handleSelection(e) {
        const obj = e.selected[0];
        if(obj) {
            DOM.imgControls.classList.remove('opacity-50', 'pointer-events-none');
            syncSliders({ target: obj });
        }
    }

    function syncSliders(e) {
        const obj = e.target;
        if(obj) {
            const scalePercent = Math.round(obj.scaleX * 100);
            DOM.sliderScale.value = scalePercent;
            DOM.valScale.textContent = `${scalePercent}%`;

            const angle = Math.round(obj.angle || 0);
            DOM.sliderRotation.value = angle;
            DOM.valRotation.textContent = `${angle}°`;
        }
    }


    // --- 1. Mode Switching Logic ---
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
                DOM.panelTitle.textContent = 'Single Export';
                DOM.panelDesc.textContent = '1장의 사진을 갤러리 테마로 내보냅니다.';
                DOM.setCarousel.classList.add('hidden'); DOM.setSingle.classList.remove('hidden');
                DOM.carouselWrapper.classList.add('hidden'); DOM.singleWrapper.classList.remove('hidden');
                updateSingleUI();
            } else {
                DOM.panelTitle.textContent = 'Seamless Slide';
                DOM.panelDesc.textContent = '사진을 이어붙여 인스타 파노라마를 만듭니다.';
                DOM.setSingle.classList.add('hidden'); DOM.setCarousel.classList.remove('hidden');
                DOM.singleWrapper.classList.add('hidden'); DOM.carouselWrapper.classList.remove('hidden');
                updateCarouselUI();
            }
            updateDownloadBtn();
        });
    });

    // --- 2. Single Mode Logic ---
    DOM.inputAuthor.addEventListener('input', (e) => { State.authorInfo = e.target.value; renderSingleCanvas(); });
    
    DOM.singleRatioBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            DOM.singleRatioBtns.forEach(b => {
                b.classList.remove('border-blue-500', 'bg-blue-500/10', 'text-white');
                b.classList.add('border-white/20', 'text-gray-400');
            });
            btn.classList.add('border-blue-500', 'bg-blue-500/10', 'text-white');
            btn.classList.remove('border-white/20', 'text-gray-400');
            State.singleRatio = btn.dataset.ratio;
            renderSingleCanvas();
        });
    });

    DOM.btnClearSingle.addEventListener('click', () => {
        State.singlePhoto = null;
        updateSingleUI();
        updateDownloadBtn();
    });

    const updateSingleUI = () => {
        if(State.singlePhoto) {
            DOM.singlePlaceholder.classList.add('hidden');
            DOM.singleCanvas.classList.remove('hidden');
            DOM.btnClearSingle.classList.remove('hidden');
            renderSingleCanvas();
        } else {
            DOM.singlePlaceholder.classList.remove('hidden');
            DOM.singleCanvas.classList.add('hidden');
            DOM.btnClearSingle.classList.add('hidden');
            DOM.singleCanvas.style.width = '0px'; 
            DOM.singleCanvas.style.height = '0px';
        }
    };

    const renderSingleCanvas = () => {
        if(!State.singlePhoto) return;
        const { post, photo, imgElement } = State.singlePhoto;
        const canvas = DOM.singleCanvas;
        const ctx = canvas.getContext('2d');
        const img = imgElement;

        let w = 1080;
        let h = 1350; 

        if (State.singleRatio === '1:1') h = 1080;
        else if (State.singleRatio === '1.91:1') h = 566;
        else if (State.singleRatio === '9:16') h = 1920;

        canvas.width = w; canvas.height = h;
        canvas.style.aspectRatio = `${w} / ${h}`;

        ctx.fillStyle = "#FFFFFF"; 
        ctx.fillRect(0, 0, w, h);

        const marginX = w * 0.05; 
        const marginBottom = w * 0.15; 
        const marginTop = marginX;

        const availW = w - (marginX * 2);
        const availH = h - marginTop - marginBottom;

        const scale = Math.min(availW / img.width, availH / img.height);
        const drawW = img.width * scale;
        const drawH = img.height * scale;
        const drawX = (w - drawW) / 2; 
        const drawY = marginTop + (availH - drawH) / 2; 

        ctx.shadowColor = "rgba(0, 0, 0, 0.15)";
        ctx.shadowBlur = Math.max(20, w * 0.015);
        ctx.shadowOffsetY = Math.max(10, w * 0.008);
        ctx.drawImage(img, drawX, drawY, drawW, drawH);
        ctx.shadowColor = "transparent";

        const leftTextArr = [];
        if (photo.exif_body) leftTextArr.push(photo.exif_body);
        if (photo.exif_lens) leftTextArr.push(photo.exif_lens);
        const leftText = leftTextArr.join(' | ');

        const rightTextArr = [];
        if (photo.exif_focal_length) rightTextArr.push(`${photo.exif_focal_length}mm`);
        if (photo.exif_aperture) rightTextArr.push(`F${parseFloat(photo.exif_aperture)}`);
        if (photo.exif_shutter_speed_num && photo.exif_shutter_speed_den) rightTextArr.push(`${photo.exif_shutter_speed_num}/${photo.exif_shutter_speed_den}s`);
        if (photo.exif_iso) rightTextArr.push(`ISO ${photo.exif_iso}`);
        const rightText = rightTextArr.join(' | ');

        const textY = drawY + drawH + (marginBottom * 0.4);
        const fontSize = Math.max(18, w * 0.016);

        ctx.textBaseline = "middle";
        ctx.fillStyle = "#333333";
        ctx.textAlign = "left";
        ctx.font = `600 ${fontSize}px Pretendard, sans-serif`;
        ctx.fillText(leftText, marginX, textY);

        ctx.fillStyle = "#888888";
        ctx.font = `400 ${fontSize * 0.85}px Pretendard, sans-serif`;
        ctx.fillText(State.authorInfo, marginX, textY + fontSize * 1.5);

        const qrSize = marginBottom * 0.5;
        const qrX = w - marginX - qrSize;
        const qrY = drawY + drawH + (marginBottom - qrSize) / 2;

        ctx.fillStyle = "#333333";
        ctx.textAlign = "right";
        ctx.font = `400 ${fontSize}px Pretendard, sans-serif`;
        ctx.fillText(rightText, qrX - (marginX * 0.5), qrY + (qrSize / 2));

        const postUrl = `${window.location.origin}/ko#post-${post.id}/photo-${photo.id}`;
        const qr = new QRious({ value: postUrl, size: qrSize, level: 'M' });
        const qrImg = new Image();
        qrImg.src = qr.toDataURL();
        qrImg.onload = () => {
            ctx.globalAlpha = 0.8;
            ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);
            ctx.globalAlpha = 1.0;
        };
    };


    // --- 3. Seamless Carousel Logic (Fabric) ---
    DOM.sliderSlides.addEventListener('input', (e) => {
        State.slides = parseInt(e.target.value);
        DOM.valSlides.textContent = `${State.slides} 장`;
        updateCarouselUI();
    });

    const updateCarouselUI = () => {
        const totalWidth = State.slides * State.baseWidth;
        
        // CSS Container Width Update
        DOM.carouselWorkspaceContainer.style.width = `${totalWidth}px`;
        DOM.carouselWorkspaceContainer.style.height = `${State.baseHeight}px`;
        
        // Fabric Canvas Resize
        fCanvas.setWidth(totalWidth);
        fCanvas.setHeight(State.baseHeight);
        fCanvas.requestRenderAll();
        
        // Update Guide Dividers
        const dividersContainer = document.getElementById('carousel-dividers');
        dividersContainer.innerHTML = '';
        for(let i = 1; i < State.slides; i++) {
            const divider = document.createElement('div');
            divider.className = 'slice-divider';
            divider.style.left = `${i * State.baseWidth}px`;
            dividersContainer.appendChild(divider);
        }
        updateDownloadBtn();
    };

    const addCarouselItem = (url) => {
        fabric.Image.fromURL(url, function(img) {
            // 기본 크기를 캔버스 높이(375px)에 맞춤
            const scale = State.baseHeight / img.height;
            img.set({
                scaleX: scale,
                scaleY: scale,
                left: (State.baseWidth - (img.width * scale)) / 2, // 1번 슬라이드 중앙
                top: 0
            });
            fCanvas.add(img);
            fCanvas.setActiveObject(img);
            fCanvas.requestRenderAll();
            updateDownloadBtn();
        }, { crossOrigin: 'anonymous' }); // CORS 우회용
    };


    // --- Fabric Controls via Panel ---
    DOM.sliderScale.addEventListener('input', (e) => {
        const obj = fCanvas.getActiveObject();
        if(obj) {
            const val = e.target.value / 100;
            obj.set({ scaleX: val, scaleY: val });
            fCanvas.requestRenderAll();
            DOM.valScale.textContent = `${e.target.value}%`;
        }
    });

    DOM.sliderRotation.addEventListener('input', (e) => {
        const obj = fCanvas.getActiveObject();
        if(obj) {
            obj.rotate(parseFloat(e.target.value));
            fCanvas.requestRenderAll();
            DOM.valRotation.textContent = `${e.target.value}°`;
        }
    });

    DOM.btnFit.addEventListener('click', () => {
        const obj = fCanvas.getActiveObject();
        if(obj) {
            const scale = State.baseHeight / obj.height;
            obj.set({ scaleX: scale, scaleY: scale, top: 0, angle: 0 });
            fCanvas.centerObjectH(obj); // 가로 기준 가운데 정렬
            obj.setCoords();
            fCanvas.requestRenderAll();
            syncSliders({ target: obj });
        }
    });

    DOM.btnFront.addEventListener('click', () => {
        const obj = fCanvas.getActiveObject();
        if(obj) fCanvas.bringForward(obj);
    });

    DOM.btnBack.addEventListener('click', () => {
        const obj = fCanvas.getActiveObject();
        if(obj) fCanvas.sendBackwards(obj);
    });

    DOM.btnRemove.addEventListener('click', () => {
        const obj = fCanvas.getActiveObject();
        if(obj) {
            fCanvas.remove(obj);
            fCanvas.discardActiveObject();
            fCanvas.requestRenderAll();
            updateDownloadBtn();
        }
    });

    // --- Keyboard Controls (Professional Detail) ---
    window.addEventListener('keydown', (e) => {
        if(State.mode !== 'carousel' || !fCanvas) return;
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        const obj = fCanvas.getActiveObject();
        if (!obj) return;

        const moveStep = e.shiftKey ? 10 : 1;

        switch(e.key) {
            case 'ArrowUp': obj.top -= moveStep; obj.setCoords(); fCanvas.requestRenderAll(); e.preventDefault(); break;
            case 'ArrowDown': obj.top += moveStep; obj.setCoords(); fCanvas.requestRenderAll(); e.preventDefault(); break;
            case 'ArrowLeft': obj.left -= moveStep; obj.setCoords(); fCanvas.requestRenderAll(); e.preventDefault(); break;
            case 'ArrowRight': obj.left += moveStep; obj.setCoords(); fCanvas.requestRenderAll(); e.preventDefault(); break;
            case 'Delete':
            case 'Backspace': fCanvas.remove(obj); fCanvas.discardActiveObject(); fCanvas.requestRenderAll(); updateDownloadBtn(); break;
            case '[': fCanvas.sendBackwards(obj); break;
            case ']': fCanvas.bringForward(obj); break;
        }
    });


    // --- 4. Queue and Data Fetching ---
    DOM.btnLoad.addEventListener('click', async () => {
        DOM.modal.classList.remove('hidden');
        setTimeout(() => DOM.modal.classList.remove('opacity-0'), 10);
        DOM.modalList.innerHTML = '<div class="text-center text-gray-500 py-10 text-sm">앨범 목록을 불러오는 중...</div>';
        try {
            const res = await fetch('/admin/collage/api/posts');
            State.loadedPosts = await res.json();
            renderModalList(State.loadedPosts);
        } catch(e) { DOM.modalList.innerHTML = '<div class="text-red-500 text-center py-10">오류가 발생했습니다.</div>'; }
    });

    const renderModalList = (posts) => {
        DOM.modalList.innerHTML = posts.map(p => `
            <div class="flex justify-between items-center p-4 bg-black border border-white/10 rounded-xl hover:bg-white/5 transition cursor-pointer group" onclick="loadPostPhotos(${p.id})">
                <div>
                    <div class="font-bold text-white text-sm group-hover:text-blue-400 transition">${p.title?.ko || p.title?.en || 'Album #'+p.id}</div>
                    <div class="text-xs text-gray-500 font-mono mt-1">${p.taken_at}</div>
                </div>
                <button class="px-4 py-2 bg-white/10 text-white text-xs font-bold rounded hover:bg-blue-600 transition">선택</button>
            </div>
        `).join('');
    };

    window.loadPostPhotos = async (id) => {
        DOM.modalList.innerHTML = '<div class="text-center text-gray-500 py-10 text-sm">사진을 불러오는 중...</div>';
        try {
            const res = await fetch(`/admin/collage/api/posts/${id}/photos`);
            const photos = await res.json();
            const post = State.loadedPosts.find(p => p.id === id);
            
            DOM.modal.classList.add('opacity-0');
            setTimeout(() => DOM.modal.classList.add('hidden'), 300);

            if(photos.length === 0) {
                DOM.photoQueue.innerHTML = '<div class="text-xs text-red-400 italic">사진이 없는 앨범입니다.</div>';
                return;
            }

            DOM.photoQueue.innerHTML = photos.map(p => `
                <div class="h-20 w-20 shrink-0 rounded-lg overflow-hidden border-2 border-transparent hover:border-blue-500 cursor-pointer transition relative group queue-item" data-id="${p.id}" data-url="${p.url}">
                    <img src="${p.url}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-blue-500/0 group-hover:bg-blue-500/20 transition flex items-center justify-center">
                        <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                </div>
            `).join('');

            document.querySelectorAll('.queue-item').forEach(el => {
                el.addEventListener('click', () => {
                    const photoData = photos.find(ph => ph.id == el.dataset.id);
                    if(State.mode === 'single') {
                        const imgElement = new Image();
                        imgElement.crossOrigin = "Anonymous";
                        imgElement.src = photoData.url;
                        imgElement.onload = () => {
                            State.singlePhoto = { post, photo: photoData, imgElement };
                            updateSingleUI();
                            updateDownloadBtn();
                        };
                    } else {
                        addCarouselItem(photoData.url);
                    }
                });
            });
        } catch(e) { alert('사진을 불러오는데 실패했습니다.'); }
    };

    // --- 5. Export Logic ---
    const updateDownloadBtn = () => {
        if(State.mode === 'single') {
            DOM.btnDownload.disabled = !State.singlePhoto;
        } else {
            DOM.btnDownload.disabled = fCanvas.getObjects().length === 0;
        }
    };

    DOM.btnDownload.addEventListener('click', async () => {
        if(State.mode === 'single') {
            const link = document.createElement('a');
            link.download = `insta_single_${Date.now()}.jpg`;
            link.href = DOM.singleCanvas.toDataURL("image/jpeg", 1.0);
            link.click();
            if(window.showToast) window.showToast('단일 사진이 성공적으로 저장되었습니다!');
        } 
        else {
            DOM.btnDownload.disabled = true;
            DOM.btnDownload.textContent = '처리중...';

            // 촬영 시 방해되는 컨트롤 박스(파란 테두리) 비활성화
            fCanvas.discardActiveObject(); 
            fCanvas.requestRenderAll();

            // 💡 최고 화질 추출: Fabric.js의 multiplier 옵션을 이용해 해상도를 증폭시킵니다.
            const dataUrl = fCanvas.toDataURL({
                format: 'jpeg',
                quality: 1.0,
                multiplier: EXPORT_RATIO // (3.6 곱하기 -> 높이가 1350px 됨)
            });

            // 거대한 파노라마 캔버스를 메모리에 올려서 자르기 루프 시작
            const bigImg = new Image();
            bigImg.onload = async () => {
                const sliceWidth = 1080;
                const sliceHeight = 1350;

                for(let i = 0; i < State.slides; i++) {
                    const sliceCanvas = document.createElement('canvas');
                    sliceCanvas.width = sliceWidth;
                    sliceCanvas.height = sliceHeight;
                    const sCtx = sliceCanvas.getContext('2d');
                    
                    sCtx.drawImage(
                        bigImg, 
                        i * sliceWidth, 0, sliceWidth, sliceHeight, // 잘라낼 Source
                        0, 0, sliceWidth, sliceHeight               // 렌더링 Destination
                    );

                    const link = document.createElement('a');
                    link.download = `insta_panorama_slide_${i+1}_of_${State.slides}.jpg`;
                    link.href = sliceCanvas.toDataURL("image/jpeg", 1.0);
                    link.click();
                    
                    // 브라우저 탭 멈춤 방지용 딜레이
                    await new Promise(r => setTimeout(r, 400)); 
                }

                DOM.btnDownload.textContent = 'EXPORT';
                DOM.btnDownload.disabled = false;
                if(window.showToast) window.showToast('파노라마 슬라이드 분할 저장이 완료되었습니다!');
            };
            bigImg.src = dataUrl;
        }
    });

    // Init
    updateCarouselUI();
});
</script>
@endsection