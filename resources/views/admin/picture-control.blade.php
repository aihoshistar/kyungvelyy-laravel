@extends('layouts.admin')

@section('title', 'PICTURE CONTROL STUDIO')
@section('header_title', 'PICTURE CONTROL STUDIO')

@section('styles')
<style>
    /* Range Sliders */
    input[type=range] { -webkit-appearance: none; background: transparent; width: 100%; }
    input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; height: 14px; width: 14px; border-radius: 50%; background: #3b82f6; cursor: pointer; margin-top: -5px; box-shadow: 0 0 10px rgba(0,0,0,0.8); border: 2px solid #fff; transition: transform 0.1s; }
    input[type=range]::-webkit-slider-thumb:hover { transform: scale(1.3); }
    input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 4px; cursor: pointer; background: #333; border-radius: 2px; }
    input[type=range]:focus { outline: none; }
    input[type=range]:disabled::-webkit-slider-thumb { background: #555; border-color: #777; cursor: not-allowed; }

    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }

    #preview-container {
        background-image: linear-gradient(45deg, #111 25%, transparent 25%), linear-gradient(-45deg, #111 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #111 75%), linear-gradient(-45deg, transparent 75%, #111 75%);
        background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    }

    /* 💡 [수정] 캔버스로 직접 렌더링되므로 캔버스 태그에 스타일 적용 */
    #render-canvas {
        box-shadow: 0 20px 50px rgba(0,0,0,0.8);
        max-width: 100%; max-height: 100%; object-fit: contain;
    }

    .queue-item.active { border-color: #3b82f6; box-shadow: 0 0 15px rgba(59, 130, 246, 0.5); }
</style>
@endsection

@section('content')

<!-- Global Drag & Drop Overlay -->
<div id="dnd-overlay" class="fixed inset-0 bg-blue-600/20 backdrop-blur-sm z-[100] hidden flex-col items-center justify-center border-4 border-blue-500 border-dashed m-4 rounded-3xl pointer-events-none transition-all duration-300 opacity-0">
    <div class="bg-black/50 p-8 rounded-2xl flex flex-col items-center shadow-2xl">
        <svg class="w-16 h-16 text-blue-400 animate-bounce mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
        <h2 class="text-3xl font-bold text-white tracking-widest uppercase">Drop Photos Here</h2>
    </div>
</div>

<div class="flex h-[calc(100vh-73px)] w-full overflow-hidden bg-[#050505]">

    <!-- Left Panel: Base Profiles & Advanced Tone Curve -->
    <div class="w-80 border-r border-white/10 bg-[#111] shrink-0 flex flex-col z-20 overflow-y-auto custom-scrollbar">
        <div class="p-6 border-b border-white/10 bg-[#0a0a0a]">
            <h2 class="text-sm font-bold text-white tracking-[0.2em] uppercase">Base Profile</h2>
        </div>

        <div class="p-6 space-y-6">
            <!-- Base Picture Controls -->
            <div>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-4">Picture Control</span>
                <div class="space-y-2">
                    <label class="flex items-center p-3 border border-white/10 rounded-lg cursor-pointer hover:bg-white/5 transition preset-option border-blue-500 bg-blue-500/10">
                        <input type="radio" name="preset" value="standard" class="hidden" checked>
                        <span class="text-sm text-white font-medium">[SD] Standard</span>
                    </label>
                    <label class="flex items-center p-3 border border-white/10 rounded-lg cursor-pointer hover:bg-white/5 transition preset-option">
                        <input type="radio" name="preset" value="neutral" class="hidden">
                        <span class="text-sm text-white font-medium">[NL] Neutral</span>
                    </label>
                    <label class="flex items-center p-3 border border-white/10 rounded-lg cursor-pointer hover:bg-white/5 transition preset-option">
                        <input type="radio" name="preset" value="vivid" class="hidden">
                        <span class="text-sm text-white font-medium">[VI] Vivid</span>
                    </label>
                    <label class="flex items-center p-3 border border-white/10 rounded-lg cursor-pointer hover:bg-white/5 transition preset-option">
                        <input type="radio" name="preset" value="portrait" class="hidden">
                        <span class="text-sm text-white font-medium">[PT] Portrait</span>
                    </label>
                    <label class="flex items-center p-3 border border-white/10 rounded-lg cursor-pointer hover:bg-white/5 transition preset-option">
                        <input type="radio" name="preset" value="monochrome" class="hidden">
                        <span class="text-sm text-white font-medium">[MC] Monochrome</span>
                    </label>
                </div>
            </div>

            <!-- 💡 [신규] Monochrome Settings (Hidden by default) -->
            <div id="mono-settings" class="hidden space-y-4 border-t border-white/10 pt-4">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block">Monochrome Filters</span>
                <div class="grid grid-cols-2 gap-2">
                    <select id="mono-filter" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500">
                        <option value="none">Filter: OFF</option>
                        <option value="yellow">Y (Yellow)</option>
                        <option value="orange">O (Orange)</option>
                        <option value="red">R (Red)</option>
                        <option value="green">G (Green)</option>
                    </select>
                    <select id="mono-toning" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500">
                        <option value="none">Toning: OFF</option>
                        <option value="sepia">Sepia</option>
                        <option value="cyanotype">Cyanotype</option>
                    </select>
                </div>
            </div>

            <!-- 💡 [신규] Tone Curve (Bezier LUT) -->
            <div class="border-t border-white/10 pt-6">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Tone Curve</span>
                </div>

                <!-- 미니 커브 시각화 캔버스 -->
                <div class="w-full h-24 bg-[#1a1a1a] rounded-lg border border-white/10 mb-4 relative overflow-hidden">
                    <!-- 격자 -->
                    <div class="absolute inset-0 pointer-events-none grid grid-cols-3 grid-rows-3 opacity-20">
                        <div class="border-r border-b border-white"></div><div class="border-r border-b border-white"></div><div class="border-b border-white"></div>
                        <div class="border-r border-b border-white"></div><div class="border-r border-b border-white"></div><div class="border-b border-white"></div>
                        <div class="border-r border-white"></div><div class="border-r border-white"></div><div></div>
                    </div>
                    <canvas id="curve-graph" class="absolute inset-0 w-full h-full"></canvas>
                </div>

                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-1"><span class="text-[10px] text-gray-400">Highlights</span><span id="val-hl" class="text-[10px] text-white">0</span></div>
                        <input type="range" class="curve-slider" data-type="highlights" min="-50" max="50" value="0">
                    </div>
                    <div>
                        <div class="flex justify-between mb-1"><span class="text-[10px] text-gray-400">Midtones</span><span id="val-mid" class="text-[10px] text-white">0</span></div>
                        <input type="range" class="curve-slider" data-type="midtones" min="-50" max="50" value="0">
                    </div>
                    <div>
                        <div class="flex justify-between mb-1"><span class="text-[10px] text-gray-400">Shadows</span><span id="val-sh" class="text-[10px] text-white">0</span></div>
                        <input type="range" class="curve-slider" data-type="shadows" min="-50" max="50" value="0">
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Center Workspace & Bottom Queue -->
    <div class="flex-1 flex flex-col relative overflow-hidden bg-black/80" id="preview-container">

        <button id="btn-compare" class="absolute top-6 right-6 z-30 px-5 py-2.5 bg-black/60 hover:bg-black/80 backdrop-blur text-white text-[10px] font-bold uppercase tracking-widest rounded-full border border-white/20 transition-all opacity-0 pointer-events-none select-none flex items-center gap-2 shadow-xl">
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            Hold to Compare
        </button>

        <div class="flex-1 relative flex items-center justify-center p-10 overflow-hidden">
            <!-- 💡 원본 데이터를 보관할 숨김 이미지와 오프스크린 캔버스 -->
            <img id="source-image" class="hidden" src="" crossorigin="anonymous" alt="Source">
            <canvas id="offscreen-canvas" class="hidden"></canvas>

            <!-- 화면에 보여질 렌더링 캔버스 -->
            <canvas id="render-canvas" class="hidden"></canvas>

            <div id="placeholder-text" class="absolute pointer-events-none text-gray-500 font-bold tracking-[0.3em] uppercase opacity-50">
                SELECT IMAGE FROM QUEUE
            </div>
            <div id="processing-overlay" class="absolute inset-0 bg-black/50 backdrop-blur flex items-center justify-center z-40 hidden">
                <div class="text-white font-bold tracking-widest animate-pulse">PROCESSING PIXELS...</div>
            </div>
        </div>

        <!-- Bottom Queue Bar -->
        <div class="h-32 border-t border-white/10 bg-[#0a0a0a] px-6 py-4 flex flex-col justify-center shrink-0 z-10 relative">
            <div class="flex justify-between items-center mb-2">
                <div class="flex items-center gap-4">
                    <span class="text-xs font-bold text-white uppercase tracking-widest">Photo Queue</span>
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
                    <div class="text-xs text-gray-600 italic">사진을 업로드(Drag & Drop)하거나 서버 앨범을 불러오세요.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Tone Adjustments -->
    <div class="w-80 border-l border-white/10 bg-[#111] shrink-0 flex flex-col z-20 overflow-y-auto custom-scrollbar">
        <div class="p-6 border-b border-white/10 bg-[#0a0a0a] flex justify-between items-center">
            <h2 class="text-sm font-bold text-white tracking-[0.2em] uppercase">Adjustments</h2>
            <div class="flex gap-3">
                <button id="btn-copy-settings" class="text-[10px] text-blue-400 hover:text-white uppercase tracking-widest transition hidden" title="Copy Settings">Copy</button>
                <button id="btn-paste-settings" class="text-[10px] text-green-400 hover:text-white uppercase tracking-widest transition hidden" title="Paste Settings">Paste</button>
                <div class="w-px h-3 bg-gray-700 self-center"></div>
                <button id="btn-reset" class="text-[10px] text-gray-500 hover:text-white uppercase tracking-widest transition">Reset</button>
            </div>
        </div>

        <div class="p-6 space-y-6 flex-1">
            <!-- 💡 Sharpening (이제 진짜 3x3 컨볼루션 연산 적용) -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Sharpening</span>
                    <span id="val-sharpen" class="text-[10px] font-mono text-white">0</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-600 font-mono">0</span>
                    <input type="range" class="control-slider" id="slider-sharpen" data-type="sharpen" min="0" max="9" value="0" step="1">
                    <span class="text-[10px] text-gray-600 font-mono">9</span>
                </div>
            </div>

            <!-- 💡 Clarity (미드톤 로컬 컨트라스트 연산 적용) -->
            <div class="border-t border-white/10 pt-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Clarity</span>
                    <span id="val-clarity" class="text-[10px] font-mono text-white">0.0</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-600 font-mono">-5</span>
                    <input type="range" class="control-slider" id="slider-clarity" data-type="clarity" min="-5" max="5" value="0" step="0.5">
                    <span class="text-[10px] text-gray-600 font-mono">+5</span>
                </div>
            </div>

            <!-- Contrast -->
            <div class="border-t border-white/10 pt-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Contrast</span>
                    <span id="val-contrast" class="text-[10px] font-mono text-white">0.0</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-600 font-mono">-3</span>
                    <input type="range" class="control-slider" id="slider-contrast" data-type="contrast" min="-3" max="3" value="0" step="0.25">
                    <span class="text-[10px] text-gray-600 font-mono">+3</span>
                </div>
            </div>

            <!-- Brightness -->
            <div class="border-t border-white/10 pt-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Brightness</span>
                    <span id="val-brightness" class="text-[10px] font-mono text-white">0.0</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-600 font-mono">-1.5</span>
                    <input type="range" class="control-slider" id="slider-brightness" data-type="brightness" min="-1.5" max="1.5" value="0" step="0.25">
                    <span class="text-[10px] text-gray-600 font-mono">+1.5</span>
                </div>
            </div>

            <!-- Saturation -->
            <div class="border-t border-white/10 pt-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Saturation</span>
                    <span id="val-saturation" class="text-[10px] font-mono text-white">0.0</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-600 font-mono">-3</span>
                    <input type="range" class="control-slider" id="slider-saturation" data-type="saturation" min="-3" max="3" value="0" step="0.25">
                    <span class="text-[10px] text-gray-600 font-mono">+3</span>
                </div>
            </div>

            <!-- Hue -->
            <div class="border-t border-white/10 pt-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Hue</span>
                    <span id="val-hue" class="text-[10px] font-mono text-white">0.0</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-600 font-mono">-5</span>
                    <input type="range" class="control-slider" id="slider-hue" data-type="hue" min="-5" max="5" value="0" step="0.5">
                    <span class="text-[10px] text-gray-600 font-mono">+5</span>
                </div>
            </div>

        </div>

        <div class="p-6 border-t border-white/10 bg-[#0a0a0a] space-y-3">
            <button id="btn-export-image" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-white text-black font-bold tracking-widest text-xs uppercase rounded-lg hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export Processed Image
            </button>
            <button id="btn-export-preset" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white font-bold tracking-widest text-xs uppercase rounded-lg hover:bg-blue-500 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Download Preset (.JSON)
            </button>
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
        imageSrc: null, loadedPosts: [], photoQueueData: [], activeQueueId: null, copiedSettings: null,
        baseProfile: 'standard',
        monoFilter: 'none', monoToning: 'none',
        curve: { highlights: 0, midtones: 0, shadows: 0 },
        params: { sharpen: 0, clarity: 0, contrast: 0, brightness: 0, saturation: 0, hue: 0 },
        // 렌더링 최적화를 위한 픽셀 버퍼
        cachedImageData: null,
        curveLUT: new Uint8Array(256),
        isRendering: false
    };

    // 프로파일 베이스 오프셋 (픽셀 연산용 변환값)
    const ProfileOffsets = {
        standard: { c: 0, s: 1.0 },
        neutral: { c: -10, s: 0.8 },
        vivid: { c: 15, s: 1.3 },
        portrait: { c: -5, s: 0.95 },
        monochrome: { c: 10, s: 0 }
    };

    // --- DOM Elements ---
    const DOM = {
        sourceImg: document.getElementById('source-image'),
        offCanvas: document.getElementById('offscreen-canvas'),
        renderCanvas: document.getElementById('render-canvas'),
        ctxRender: document.getElementById('render-canvas').getContext('2d', { willReadFrequently: true }),
        curveCanvas: document.getElementById('curve-graph'),
        ctxCurve: document.getElementById('curve-graph').getContext('2d'),

        placeholder: document.getElementById('placeholder-text'),
        processingOverlay: document.getElementById('processing-overlay'),

        presetOptions: document.querySelectorAll('input[name="preset"]'),
        monoSettings: document.getElementById('mono-settings'),
        monoFilter: document.getElementById('mono-filter'),
        monoToning: document.getElementById('mono-toning'),

        curveSliders: document.querySelectorAll('.curve-slider'),
        sliders: document.querySelectorAll('.control-slider'),
        btnReset: document.getElementById('btn-reset'),
        btnCopySettings: document.getElementById('btn-copy-settings'), btnPasteSettings: document.getElementById('btn-paste-settings'),
        btnCompare: document.getElementById('btn-compare'),
        btnExportImage: document.getElementById('btn-export-image'), btnExportPreset: document.getElementById('btn-export-preset'),

        localUpload: document.getElementById('local-upload'), photoQueue: document.getElementById('photo-queue'),
        btnLoad: document.getElementById('btn-load-posts'), btnClearQueue: document.getElementById('btn-clear-queue'),
        modal: document.getElementById('modal-posts'), modalSearch: document.getElementById('modal-search'), modalList: document.getElementById('modal-list'),
        dndOverlay: document.getElementById('dnd-overlay')
    };

    // --- CORE ENGINE: 1. Tone Curve LUT Generator ---
    const updateCurveLUT = () => {
        const { shadows, midtones, highlights } = State.curve;
        // -50 ~ +50 범위를 픽셀 단위 증감으로 변환
        const s = shadows * 1.5; const m = midtones * 1.2; const h = highlights * 1.5;

        for (let i = 0; i < 256; i++) {
            let val = i;
            // Shadows curve (하위 50%에 영향)
            if (i < 128) val += s * (1 - (i / 128));
            // Midtones curve (가우시안 분포처럼 중간에 영향)
            val += m * (1 - Math.abs(i - 128) / 128);
            // Highlights curve (상위 50%에 영향)
            if (i >= 128) val += h * ((i - 128) / 128);

            State.curveLUT[i] = Math.max(0, Math.min(255, val));
        }
        drawCurveGraph();
    };

    const drawCurveGraph = () => {
        const ctx = DOM.ctxCurve; const w = DOM.curveCanvas.width; const h = DOM.curveCanvas.height;
        ctx.clearRect(0, 0, w, h);
        ctx.beginPath(); ctx.strokeStyle = "#3b82f6"; ctx.lineWidth = 3;
        for (let i = 0; i < 256; i++) {
            const x = (i / 255) * w;
            const y = h - (State.curveLUT[i] / 255) * h;
            if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
        }
        ctx.stroke();
    };

    // Init Curve Canvas size
    DOM.curveCanvas.width = DOM.curveCanvas.parentElement.clientWidth;
    DOM.curveCanvas.height = DOM.curveCanvas.parentElement.clientHeight;
    updateCurveLUT();

    // --- CORE ENGINE: 2. Pixel Manipulation Pipeline ---
    // 디바운싱: 슬라이더 조절 시 렉을 방지하기 위해 렌더링 요청을 그룹화합니다.
    let renderTimeout;
    const requestRender = (isExport = false) => {
        if(isExport) { executePixelPipeline(true); return; }
        if(renderTimeout) cancelAnimationFrame(renderTimeout);
        renderTimeout = requestAnimationFrame(() => executePixelPipeline(false));
    };

    const executePixelPipeline = (isExport) => {
        if (!State.cachedImageData) return;
        State.isRendering = true;

        const w = DOM.renderCanvas.width;
        const h = DOM.renderCanvas.height;

        // 1. 버퍼 이미지 가져오기
        const imgData = new ImageData(
            new Uint8ClampedArray(State.cachedImageData.data), w, h
        );
        const data = imgData.data;
        const len = data.length;

        // 2. 연산을 위한 파라미터 캐싱
        const p = State.params; const offset = ProfileOffsets[State.baseProfile];

        // 밝기/대비 팩터 (-255 ~ 255)
        const bright = p.brightness * 30;
        const cont = (p.contrast * 15) + offset.c;
        const factor = (259 * (cont + 255)) / (255 * (259 - cont));

        // 채도 팩터
        let sat = (State.baseProfile === 'monochrome') ? 0 : offset.s * (1 + p.saturation * 0.3);

        // 흑백 컬러 필터 가중치 설정
        let bwWeights = [0.299, 0.587, 0.114]; // 기본 루마
        if (State.baseProfile === 'monochrome') {
            if(State.monoFilter === 'yellow') bwWeights = [0.6, 0.28, 0.12];
            else if(State.monoFilter === 'orange') bwWeights = [0.7, 0.2, 0.1];
            else if(State.monoFilter === 'red') bwWeights = [0.8, 0.1, 0.1];
            else if(State.monoFilter === 'green') bwWeights = [0.1, 0.7, 0.2];
        }

        const clarityAmt = p.clarity * 0.5; // 명료도 강도

        // 3. Main Pixel Loop
        for (let i = 0; i < len; i += 4) {
            let r = data[i], g = data[i+1], b = data[i+2];

            // A. Tone Curve LUT 적용
            r = State.curveLUT[r]; g = State.curveLUT[g]; b = State.curveLUT[b];

            // B. Brightness & Contrast
            r = factor * (r - 128) + 128 + bright;
            g = factor * (g - 128) + 128 + bright;
            b = factor * (b - 128) + 128 + bright;

            // C. Clarity (미드톤 로컬 컨트라스트 공식)
            if (clarityAmt !== 0) {
                const luma = 0.299*r + 0.587*g + 0.114*b;
                const weight = 1.0 - Math.abs(luma - 128) / 128.0; // 미드톤일수록 weight가 1에 가까움
                r += (r - 128) * clarityAmt * weight;
                g += (g - 128) * clarityAmt * weight;
                b += (b - 128) * clarityAmt * weight;
            }

            // D. Saturation & Color Profiles
            if (State.baseProfile === 'monochrome') {
                // 흑백 + 렌즈 필터
                let gray = (r * bwWeights[0]) + (g * bwWeights[1]) + (b * bwWeights[2]);

                // 토닝 (Toning)
                if (State.monoToning === 'sepia') {
                    r = gray + 40; g = gray + 15; b = gray - 20;
                } else if (State.monoToning === 'cyanotype') {
                    r = gray - 20; g = gray + 15; b = gray + 40;
                } else {
                    r = gray; g = gray; b = gray;
                }
            } else {
                // 컬러 채도 조절
                const luma = 0.299*r + 0.587*g + 0.114*b;
                r = luma + (r - luma) * sat;
                g = luma + (g - luma) * sat;
                b = luma + (b - luma) * sat;
            }

            // 클램핑
            data[i] = r; data[i+1] = g; data[i+2] = b;
        }

        // 4. Sharpening (Convolution Matrix 3x3)
        // 실시간 미리보기에서는 가볍게, Export에서는 강하게 적용하거나 무시할 수 있지만 여기선 동일하게 적용
        if (p.sharpen > 0) {
            applySharpening(imgData, w, h, p.sharpen * 0.15);
        }

        // 5. 캔버스에 그리기
        DOM.ctxRender.putImageData(imgData, 0, 0);
        State.isRendering = false;
    };

    // 샤프닝 매트릭스 연산 함수
    const applySharpening = (imgData, w, h, amount) => {
        const d = imgData.data;
        const copy = new Uint8ClampedArray(d);
        const mix = amount;

        // 3x3 라플라시안 베이스 매트릭스 (가운데 강조, 십자 방향 감산)
        for (let y = 1; y < h - 1; y++) {
            for (let x = 1; x < w - 1; x++) {
                const i = (y * w + x) * 4;
                for (let c = 0; c < 3; c++) {
                    const top = copy[((y - 1) * w + x) * 4 + c];
                    const left = copy[(y * w + (x - 1)) * 4 + c];
                    const right = copy[(y * w + (x + 1)) * 4 + c];
                    const bottom = copy[((y + 1) * w + x) * 4 + c];
                    const center = copy[i + c];

                    // 윤곽선 추출 후 원본에 더함
                    const edge = (center * 4) - top - left - right - bottom;
                    d[i + c] = center + (edge * mix);
                }
            }
        }
    };


    // --- Global Drag & Drop ---
    window.addEventListener('dragover', (e) => { e.preventDefault(); DOM.dndOverlay.classList.remove('hidden', 'opacity-0'); });
    window.addEventListener('dragleave', (e) => { if(e.clientX === 0 || e.clientY === 0) { DOM.dndOverlay.classList.add('opacity-0'); setTimeout(() => DOM.dndOverlay.classList.add('hidden'), 300); } });
    window.addEventListener('drop', (e) => {
        e.preventDefault(); DOM.dndOverlay.classList.add('opacity-0'); setTimeout(() => DOM.dndOverlay.classList.add('hidden'), 300);
        const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
        if(!files.length) return;
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (ev) => { State.photoQueueData.push({ id: 'local_' + Date.now() + Math.random(), url: ev.target.result }); renderQueue(); };
            reader.readAsDataURL(file);
        });
    });

    // --- Unified Queue Management ---
    DOM.localUpload.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        if(!files.length) return;
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (ev) => { State.photoQueueData.push({ id: 'local_' + Date.now() + Math.random(), url: ev.target.result }); renderQueue(); };
            reader.readAsDataURL(file);
        });
        DOM.localUpload.value = '';
    });

    DOM.btnClearQueue.addEventListener('click', () => {
        State.photoQueueData = []; State.activeQueueId = null; State.imageSrc = null;
        DOM.renderCanvas.classList.add('hidden'); DOM.placeholder.classList.remove('hidden');
        DOM.btnExportImage.disabled = true; DOM.btnExportPreset.disabled = true;
        DOM.btnCompare.classList.add('opacity-0', 'pointer-events-none'); DOM.btnCopySettings.classList.add('hidden');
        renderQueue();
    });

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
            photos.forEach(p => { if(!State.photoQueueData.find(q => q.id === `server_${p.id}`)) { State.photoQueueData.push({ id: `server_${p.id}`, url: p.url }); } });
            renderQueue();
        } catch(e) { alert('실패'); }
    };

    const renderQueue = () => {
        if(State.photoQueueData.length === 0) {
            DOM.photoQueue.innerHTML = '<div class="text-xs text-gray-600 italic">사진을 업로드(Drag & Drop)하거나 서버 앨범을 불러오세요.</div>';
            DOM.btnClearQueue.classList.add('hidden');
            return;
        }

        DOM.btnClearQueue.classList.remove('hidden');
        DOM.photoQueue.innerHTML = State.photoQueueData.map(item => `
            <div class="h-20 w-20 shrink-0 rounded-lg overflow-hidden border-2 transition relative group queue-item ${State.activeQueueId === item.id ? 'active' : 'border-transparent hover:border-blue-500'}" data-id="${item.id}">
                <img src="${item.url}" class="w-full h-full object-cover">
                <button class="absolute top-1 right-1 w-5 h-5 bg-black/60 hover:bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition z-10 btn-remove-queue" data-id="${item.id}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        `).join('');

        document.querySelectorAll('.queue-item').forEach(el => {
            el.addEventListener('click', (e) => {
                if(e.target.closest('.btn-remove-queue')) return;
                const itemId = el.dataset.id;
                State.activeQueueId = itemId;
                const item = State.photoQueueData.find(q => q.id === itemId);

                State.imageSrc = item.url;
                loadImageForProcessing(State.imageSrc);
                renderQueue(); // Update active border
            });
        });

        document.querySelectorAll('.btn-remove-queue').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const removeId = btn.dataset.id;
                State.photoQueueData = State.photoQueueData.filter(q => q.id !== removeId);
                if(State.activeQueueId === removeId) {
                    State.activeQueueId = null; State.imageSrc = null;
                    DOM.renderCanvas.classList.add('hidden'); DOM.placeholder.classList.remove('hidden');
                    DOM.btnExportImage.disabled = true; DOM.btnExportPreset.disabled = true;
                    DOM.btnCompare.classList.add('opacity-0', 'pointer-events-none'); DOM.btnCopySettings.classList.add('hidden');
                }
                renderQueue();
            });
        });
    };

    // 이미지 로드 & 픽셀 버퍼 생성 (최적화)
    const loadImageForProcessing = (url) => {
        DOM.placeholder.classList.add('hidden');
        DOM.processingOverlay.classList.remove('hidden');

        DOM.sourceImg.onload = () => {
            // 미리보기용 캔버스 사이즈 최적화 (가로세로 최대 1080px)
            const MAX_DIM = 1080;
            let w = DOM.sourceImg.width; let h = DOM.sourceImg.height;
            if(w > MAX_DIM || h > MAX_DIM) {
                const ratio = Math.min(MAX_DIM/w, MAX_DIM/h);
                w = Math.floor(w * ratio); h = Math.floor(h * ratio);
            }

            DOM.renderCanvas.width = w; DOM.renderCanvas.height = h;
            const ctx = DOM.ctxRender;
            ctx.drawImage(DOM.sourceImg, 0, 0, w, h);

            // 원본 픽셀 데이터 캐싱 (연산의 기준점)
            State.cachedImageData = ctx.getImageData(0, 0, w, h);

            DOM.renderCanvas.classList.remove('hidden');
            DOM.processingOverlay.classList.add('hidden');
            DOM.btnExportImage.disabled = false; DOM.btnExportPreset.disabled = false;
            DOM.btnCompare.classList.remove('opacity-0', 'pointer-events-none');
            DOM.btnCopySettings.classList.remove('hidden');

            requestRender();
        };
        DOM.sourceImg.src = url;
    };


    // --- Compare Before / After ---
    const toggleCompare = (isComparing) => {
        if(!State.cachedImageData) return;
        if(isComparing) {
            DOM.ctxRender.putImageData(State.cachedImageData, 0, 0); // 원본 복원
        } else {
            requestRender(); // 보정본 다시 연산
        }
    };

    DOM.btnCompare.addEventListener('mousedown', () => toggleCompare(true));
    DOM.btnCompare.addEventListener('mouseup', () => toggleCompare(false));
    DOM.btnCompare.addEventListener('mouseleave', () => toggleCompare(false));
    DOM.btnCompare.addEventListener('touchstart', (e) => { e.preventDefault(); toggleCompare(true); });
    DOM.btnCompare.addEventListener('touchend', (e) => { e.preventDefault(); toggleCompare(false); });


    // --- Copy / Paste Settings ---
    DOM.btnCopySettings.addEventListener('click', () => {
        State.copiedSettings = {
            profile: State.baseProfile,
            params: { ...State.params },
            curve: { ...State.curve },
            monoFilter: State.monoFilter,
            monoToning: State.monoToning
        };
        DOM.btnPasteSettings.classList.remove('hidden');
        if(window.showToast) window.showToast('보정 설정이 복사되었습니다.');
    });

    DOM.btnPasteSettings.addEventListener('click', () => {
        if(!State.copiedSettings) return;

        State.baseProfile = State.copiedSettings.profile;
        State.params = { ...State.copiedSettings.params };
        State.curve = { ...State.copiedSettings.curve };
        State.monoFilter = State.copiedSettings.monoFilter;
        State.monoToning = State.copiedSettings.monoToning;

        // Update UI
        document.querySelectorAll('.preset-option').forEach(el => el.classList.remove('border-blue-500', 'bg-blue-500/10'));
        document.querySelector(`input[name="preset"][value="${State.baseProfile}"]`).closest('.preset-option').classList.add('border-blue-500', 'bg-blue-500/10');

        if (State.baseProfile === 'monochrome') DOM.monoSettings.classList.remove('hidden');
        else DOM.monoSettings.classList.add('hidden');

        DOM.monoFilter.value = State.monoFilter; DOM.monoToning.value = State.monoToning;

        DOM.curveSliders.forEach(slider => { slider.value = State.curve[slider.dataset.type]; document.getElementById(`val-${slider.dataset.type === 'shadows' ? 'sh' : (slider.dataset.type === 'midtones' ? 'mid' : 'hl')}`).textContent = State.curve[slider.dataset.type]; });
        DOM.sliders.forEach(slider => {
            const type = slider.dataset.type; slider.value = State.params[type];
            const textVal = State.params[type] > 0 ? `+${State.params[type].toFixed(2)}` : State.params[type].toFixed(2);
            document.getElementById(`val-${type}`).textContent = (type === 'sharpen') ? State.params[type].toString() : textVal;
            if(type === 'saturation' || type === 'hue') { slider.disabled = (State.baseProfile === 'monochrome'); slider.parentElement.style.opacity = slider.disabled ? '0.3' : '1'; }
        });

        updateCurveLUT();
        requestRender();
        if(window.showToast) window.showToast('보정 설정이 붙여넣기 되었습니다.');
    });


    // --- UI Listeners (Base Profile, Sliders, Curve) ---
    DOM.presetOptions.forEach(opt => {
        opt.addEventListener('change', (e) => {
            document.querySelectorAll('.preset-option').forEach(el => el.classList.remove('border-blue-500', 'bg-blue-500/10'));
            e.target.closest('.preset-option').classList.add('border-blue-500', 'bg-blue-500/10');
            State.baseProfile = e.target.value;

            if (State.baseProfile === 'monochrome') DOM.monoSettings.classList.remove('hidden');
            else DOM.monoSettings.classList.add('hidden');

            DOM.sliders.forEach(slider => {
                if(slider.dataset.type === 'saturation' || slider.dataset.type === 'hue') {
                    slider.disabled = (State.baseProfile === 'monochrome');
                    slider.parentElement.style.opacity = slider.disabled ? '0.3' : '1';
                }
            });
            requestRender();
        });
    });

    DOM.monoFilter.addEventListener('change', (e) => { State.monoFilter = e.target.value; requestRender(); });
    DOM.monoToning.addEventListener('change', (e) => { State.monoToning = e.target.value; requestRender(); });

    DOM.sliders.forEach(slider => {
        slider.addEventListener('input', (e) => {
            const type = e.target.dataset.type; const val = parseFloat(e.target.value); State.params[type] = val;
            const textVal = val > 0 ? `+${val.toFixed(2)}` : val.toFixed(2);
            document.getElementById(`val-${type}`).textContent = (type === 'sharpen') ? val.toString() : textVal;
            requestRender();
        });
    });

    DOM.curveSliders.forEach(slider => {
        slider.addEventListener('input', (e) => {
            const type = e.target.dataset.type; State.curve[type] = parseInt(e.target.value);
            document.getElementById(`val-${type === 'shadows' ? 'sh' : (type === 'midtones' ? 'mid' : 'hl')}`).textContent = e.target.value;
            updateCurveLUT(); requestRender();
        });
    });

    DOM.btnReset.addEventListener('click', () => {
        State.params = { sharpen: 0, clarity: 0, contrast: 0, brightness: 0, saturation: 0, hue: 0 };
        State.curve = { highlights: 0, midtones: 0, shadows: 0 };
        State.monoFilter = 'none'; State.monoToning = 'none';

        DOM.monoFilter.value = 'none'; DOM.monoToning.value = 'none';
        DOM.curveSliders.forEach(s => { s.value = 0; document.getElementById(`val-${s.dataset.type === 'shadows' ? 'sh' : (s.dataset.type === 'midtones' ? 'mid' : 'hl')}`).textContent = '0'; });
        DOM.sliders.forEach(slider => {
            const type = slider.dataset.type; slider.value = State.params[type];
            document.getElementById(`val-${type}`).textContent = (type === 'sharpen') ? "0" : "0.00";
        });
        updateCurveLUT(); requestRender();
    });


    // --- Export High-Res ---
    DOM.btnExportImage.addEventListener('click', () => {
        if(!State.imageSrc || !State.cachedImageData) return;
        DOM.btnExportImage.disabled = true; DOM.btnExportImage.textContent = 'EXPORTING...';
        DOM.processingOverlay.classList.remove('hidden');

        // Export는 UI 쓰레드를 멈추지 않게 setTimeout으로 지연 실행 (또는 WebWorker 사용 권장)
        setTimeout(() => {
            const canvas = DOM.exportCanvas; const ctx = canvas.getContext('2d');
            const img = DOM.sourceImg;
            canvas.width = img.width; canvas.height = img.height;
            ctx.drawImage(img, 0, 0, img.width, img.height);

            // 원본 해상도 픽셀 버퍼 획득
            const imgData = ctx.getImageData(0, 0, img.width, img.height);
            const data = imgData.data; const len = data.length;

            const p = State.params; const offset = ProfileOffsets[State.baseProfile];
            const bright = p.brightness * 30; const cont = (p.contrast * 15) + offset.c;
            const factor = (259 * (cont + 255)) / (255 * (259 - cont));
            let sat = (State.baseProfile === 'monochrome') ? 0 : offset.s * (1 + p.saturation * 0.3);
            let bwWeights = [0.299, 0.587, 0.114];
            if (State.baseProfile === 'monochrome') {
                if(State.monoFilter === 'yellow') bwWeights = [0.6, 0.28, 0.12];
                else if(State.monoFilter === 'orange') bwWeights = [0.7, 0.2, 0.1];
                else if(State.monoFilter === 'red') bwWeights = [0.8, 0.1, 0.1];
                else if(State.monoFilter === 'green') bwWeights = [0.1, 0.7, 0.2];
            }
            const clarityAmt = p.clarity * 0.5;

            // 동일한 연산 파이프라인 수행 (고해상도)
            for (let i = 0; i < len; i += 4) {
                let r = data[i], g = data[i+1], b = data[i+2];
                r = State.curveLUT[r]; g = State.curveLUT[g]; b = State.curveLUT[b];
                r = factor * (r - 128) + 128 + bright; g = factor * (g - 128) + 128 + bright; b = factor * (b - 128) + 128 + bright;
                if (clarityAmt !== 0) {
                    const luma = 0.299*r + 0.587*g + 0.114*b; const weight = 1.0 - Math.abs(luma - 128) / 128.0;
                    r += (r - 128) * clarityAmt * weight; g += (g - 128) * clarityAmt * weight; b += (b - 128) * clarityAmt * weight;
                }
                if (State.baseProfile === 'monochrome') {
                    let gray = (r * bwWeights[0]) + (g * bwWeights[1]) + (b * bwWeights[2]);
                    if (State.monoToning === 'sepia') { r = gray + 40; g = gray + 15; b = gray - 20; }
                    else if (State.monoToning === 'cyanotype') { r = gray - 20; g = gray + 15; b = gray + 40; }
                    else { r = gray; g = gray; b = gray; }
                } else {
                    const luma = 0.299*r + 0.587*g + 0.114*b;
                    r = luma + (r - luma) * sat; g = luma + (g - luma) * sat; b = luma + (b - luma) * sat;
                }
                data[i] = r; data[i+1] = g; data[i+2] = b;
            }

            if (p.sharpen > 0) applySharpening(imgData, img.width, img.height, p.sharpen * 0.15);
            ctx.putImageData(imgData, 0, 0);

            const link = document.createElement('a'); link.download = `kkyoung_pic_ctrl_${Date.now()}.jpg`;
            link.href = canvas.toDataURL("image/jpeg", 1.0); link.click();

            DOM.processingOverlay.classList.add('hidden');
            DOM.btnExportImage.textContent = 'EXPORT PROCESSED IMAGE'; DOM.btnExportImage.disabled = false;
            if(window.showToast) window.showToast('고해상도 보정본이 저장되었습니다!');
        }, 50);
    });

    DOM.btnExportPreset.addEventListener('click', () => {
        const presetData = {
            "version": "2.0_PIXEL_ENGINE", "type": "KKYOUNG_WEB_NCP", "base_profile": State.baseProfile.toUpperCase(),
            "curve": State.curve,
            "monochrome": { filter: State.monoFilter, toning: State.monoToning },
            "parameters": State.params,
            "created_at": new Date().toISOString()
        };
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(presetData, null, 4));
        const link = document.createElement('a'); link.href = dataStr; link.download = `CUSTOM_${State.baseProfile.toUpperCase()}_PRESET.json`; link.click();
        if(window.showToast) window.showToast('프리셋 파일이 다운로드되었습니다.');
    });
});
</script>
@endsection
