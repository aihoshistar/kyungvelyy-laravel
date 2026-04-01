@extends('layouts.admin')

@section('title', 'PICTURE CONTROL STUDIO')
@section('header_title', 'PICTURE CONTROL STUDIO')

@section('styles')
<style>
    /* Custom Range Slider for Tone Controls */
    input[type=range] { -webkit-appearance: none; background: transparent; width: 100%; }
    input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; height: 14px; width: 14px; border-radius: 50%; background: #3b82f6; cursor: pointer; margin-top: -5px; box-shadow: 0 0 10px rgba(0,0,0,0.8); border: 2px solid #fff; transition: transform 0.1s; }
    input[type=range]::-webkit-slider-thumb:hover { transform: scale(1.3); }
    input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 4px; cursor: pointer; background: #333; border-radius: 2px; }
    input[type=range]:focus { outline: none; }

    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }

    #preview-container {
        background-image:
            linear-gradient(45deg, #111 25%, transparent 25%),
            linear-gradient(-45deg, #111 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #111 75%),
            linear-gradient(-45deg, transparent 75%, #111 75%);
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    }

    /* Image Preview Styling */
    #image-preview {
        box-shadow: 0 20px 50px rgba(0,0,0,0.8);
        transition: filter 0.2s ease-out; /* 부드러운 필터 전환 */
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    /* Active Queue Item Effect */
    .queue-item.active {
        border-color: #3b82f6;
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
    }
</style>
@endsection

@section('content')

<!-- 💡 Global Drag & Drop Overlay -->
<div id="dnd-overlay" class="fixed inset-0 bg-blue-600/20 backdrop-blur-sm z-[100] hidden flex-col items-center justify-center border-4 border-blue-500 border-dashed m-4 rounded-3xl pointer-events-none transition-all duration-300 opacity-0">
    <div class="bg-black/50 p-8 rounded-2xl flex flex-col items-center shadow-2xl">
        <svg class="w-16 h-16 text-blue-400 animate-bounce mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
        <h2 class="text-3xl font-bold text-white tracking-widest uppercase">Drop Photos Here</h2>
        <p class="text-blue-200 mt-2">큐(Queue)에 자동으로 추가됩니다.</p>
    </div>
</div>

<div class="flex h-[calc(100vh-73px)] w-full overflow-hidden bg-[#050505]">

    <!-- Left Panel: Base Profiles & Upload -->
    <div class="w-80 border-r border-white/10 bg-[#111] shrink-0 flex flex-col z-20 overflow-y-auto custom-scrollbar">
        <div class="p-6 border-b border-white/10 bg-[#0a0a0a]">
            <h2 class="text-sm font-bold text-white tracking-[0.2em] uppercase">Base Profile</h2>
        </div>

        <div class="p-6 space-y-6">
            <div class="p-4 bg-blue-500/10 border border-blue-500/30 rounded-xl mb-4">
                <p class="text-xs text-blue-400 font-medium leading-relaxed">
                    하단 큐에서 사진을 불러온 뒤, 카메라 프로파일을 선택하세요.
                </p>
            </div>

            <!-- Base Picture Controls -->
            <div>
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
                        <input type="radio" name="preset" value="monochrome" class="hidden">
                        <span class="text-sm text-white font-medium">[MC] Monochrome</span>
                    </label>
                    <label class="flex items-center p-3 border border-white/10 rounded-lg cursor-pointer hover:bg-white/5 transition preset-option">
                        <input type="radio" name="preset" value="portrait" class="hidden">
                        <span class="text-sm text-white font-medium">[PT] Portrait</span>
                    </label>
                </div>
            </div>

            <div class="p-4 bg-gray-800/40 rounded-xl border border-white/10 text-xs text-gray-400 leading-relaxed">
                <strong>💡 팁:</strong> 우측 패널에서 조절한 설정값을 복사(Copy)하여 다른 사진에 일괄 적용(Paste)할 수 있습니다.
            </div>
        </div>
    </div>

    <!-- Center Workspace & Bottom Queue -->
    <div class="flex-1 flex flex-col relative overflow-hidden bg-black/80" id="preview-container">

        <!-- 💡 [추가] Before/After Compare Button -->
        <button id="btn-compare" class="absolute top-6 right-6 z-30 px-5 py-2.5 bg-black/60 hover:bg-black/80 backdrop-blur text-white text-[10px] font-bold uppercase tracking-widest rounded-full border border-white/20 transition-all opacity-0 pointer-events-none select-none flex items-center gap-2 shadow-xl">
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            Hold to Compare
        </button>

        <div class="flex-1 relative flex items-center justify-center p-10 overflow-hidden">
            <img id="image-preview" class="hidden" src="" crossorigin="anonymous" alt="Preview">

            <!-- Hidden Canvas for Actual Image Export -->
            <canvas id="export-canvas" class="hidden"></canvas>

            <div id="placeholder-text" class="absolute pointer-events-none text-gray-500 font-bold tracking-[0.3em] uppercase opacity-50">
                SELECT IMAGE FROM QUEUE
            </div>
        </div>

        <!-- Bottom Queue Bar -->
        <div class="h-32 border-t border-white/10 bg-[#0a0a0a] px-6 py-4 flex flex-col justify-center shrink-0 z-10 relative">
            <div class="flex justify-between items-center mb-2">
                <div class="flex items-center gap-4">
                    <span class="text-xs font-bold text-white uppercase tracking-widest">Photo Queue</span>
                    <!-- 💡 Clear All 버튼 -->
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
            <!-- 💡 [추가] Copy / Paste / Reset Controls -->
            <div class="flex gap-3">
                <button id="btn-copy-settings" class="text-[10px] text-blue-400 hover:text-white uppercase tracking-widest transition hidden" title="Copy Settings">Copy</button>
                <button id="btn-paste-settings" class="text-[10px] text-green-400 hover:text-white uppercase tracking-widest transition hidden" title="Paste Settings">Paste</button>
                <div class="w-px h-3 bg-gray-700 self-center"></div>
                <button id="btn-reset" class="text-[10px] text-gray-500 hover:text-white uppercase tracking-widest transition">Reset</button>
            </div>
        </div>

        <div class="p-6 space-y-6 flex-1">
            <!-- Sharpening -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Sharpening</span>
                    <span id="val-sharpen" class="text-[10px] font-mono text-white">3</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-600 font-mono">-3</span>
                    <input type="range" class="control-slider" id="slider-sharpen" data-type="sharpen" min="-3" max="9" value="3" step="1">
                    <span class="text-[10px] text-gray-600 font-mono">+9</span>
                </div>
            </div>

            <!-- Clarity -->
            <div class="border-t border-white/10 pt-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Clarity</span>
                    <span id="val-clarity" class="text-[10px] font-mono text-white">0.00</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-600 font-mono">-5</span>
                    <input type="range" class="control-slider" id="slider-clarity" data-type="clarity" min="-5" max="5" value="0" step="0.25">
                    <span class="text-[10px] text-gray-600 font-mono">+5</span>
                </div>
            </div>

            <!-- Contrast -->
            <div class="border-t border-white/10 pt-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Contrast</span>
                    <span id="val-contrast" class="text-[10px] font-mono text-white">0.00</span>
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
                    <span id="val-brightness" class="text-[10px] font-mono text-white">0.00</span>
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
                    <span id="val-saturation" class="text-[10px] font-mono text-white">0.00</span>
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
                    <span id="val-hue" class="text-[10px] font-mono text-white">0.00</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-600 font-mono">-3</span>
                    <input type="range" class="control-slider" id="slider-hue" data-type="hue" min="-3" max="3" value="0" step="0.25">
                    <span class="text-[10px] text-gray-600 font-mono">+3</span>
                </div>
            </div>

        </div>

        <div class="p-6 border-t border-white/10 bg-[#0a0a0a] space-y-3">
            <button id="btn-export-image" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-white text-black font-bold tracking-widest text-xs uppercase rounded-lg hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export Web Image
            </button>
            <button id="btn-export-preset" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white font-bold tracking-widest text-xs uppercase rounded-lg hover:bg-blue-500 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Download Preset (.NCP)
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
        imageSrc: null,
        baseProfile: 'standard',
        loadedPosts: [],
        photoQueueData: [],
        activeQueueId: null,

        params: {
            sharpen: 3.0,
            clarity: 0.0,
            contrast: 0.0,
            brightness: 0.0,
            saturation: 0.0,
            hue: 0.0
        },

        copiedSettings: null // 💡 [추가] 복사된 설정을 저장할 공간
    };

    const ProfileOffsets = {
        standard: { contrast: 0, saturation: 0 },
        neutral: { contrast: -1, saturation: -1 },
        vivid: { contrast: 1.5, saturation: 1.5 },
        monochrome: { contrast: 1, saturation: -100 },
        portrait: { contrast: -0.5, saturation: -0.5 }
    };

    // --- DOM Elements ---
    const DOM = {
        preview: document.getElementById('image-preview'),
        placeholder: document.getElementById('placeholder-text'),
        exportCanvas: document.getElementById('export-canvas'),
        presetOptions: document.querySelectorAll('input[name="preset"]'),
        sliders: document.querySelectorAll('.control-slider'),
        btnReset: document.getElementById('btn-reset'),

        btnCopySettings: document.getElementById('btn-copy-settings'),
        btnPasteSettings: document.getElementById('btn-paste-settings'),
        btnCompare: document.getElementById('btn-compare'),

        btnExportImage: document.getElementById('btn-export-image'),
        btnExportPreset: document.getElementById('btn-export-preset'),

        localUpload: document.getElementById('local-upload'),
        photoQueue: document.getElementById('photo-queue'),
        btnLoad: document.getElementById('btn-load-posts'),
        btnClearQueue: document.getElementById('btn-clear-queue'),

        modal: document.getElementById('modal-posts'),
        modalSearch: document.getElementById('modal-search'),
        modalList: document.getElementById('modal-list'),

        dndOverlay: document.getElementById('dnd-overlay')
    };

    // --- Global Drag & Drop ---
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
                State.photoQueueData.push({ id: 'local_' + Date.now() + Math.random(), url: ev.target.result });
                renderQueue();
            };
            reader.readAsDataURL(file);
        });
    });

    // --- Unified Queue Management ---
    DOM.localUpload.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        if(!files.length) return;
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (ev) => {
                State.photoQueueData.push({ id: 'local_' + Date.now() + Math.random(), url: ev.target.result });
                renderQueue();
            };
            reader.readAsDataURL(file);
        });
        DOM.localUpload.value = '';
    });

    DOM.btnClearQueue.addEventListener('click', () => {
        State.photoQueueData = [];
        State.activeQueueId = null;
        State.imageSrc = null;
        DOM.preview.src = '';
        DOM.preview.classList.add('hidden');
        DOM.placeholder.classList.remove('hidden');
        DOM.btnExportImage.disabled = true;
        DOM.btnExportPreset.disabled = true;
        DOM.btnCompare.classList.add('opacity-0', 'pointer-events-none');
        DOM.btnCopySettings.classList.add('hidden');
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
            photos.forEach(p => {
                if(!State.photoQueueData.find(q => q.id === `server_${p.id}`)) { State.photoQueueData.push({ id: `server_${p.id}`, url: p.url }); }
            });
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
                DOM.preview.src = State.imageSrc;
                DOM.preview.classList.remove('hidden'); DOM.placeholder.classList.add('hidden');
                DOM.btnExportImage.disabled = false; DOM.btnExportPreset.disabled = false;

                // Show tools
                DOM.btnCompare.classList.remove('opacity-0', 'pointer-events-none');
                DOM.btnCopySettings.classList.remove('hidden');

                applyFilters();
                renderQueue(); // Update active border
            });
        });

        document.querySelectorAll('.btn-remove-queue').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const removeId = btn.dataset.id;
                State.photoQueueData = State.photoQueueData.filter(q => q.id !== removeId);
                if(State.activeQueueId === removeId) {
                    State.activeQueueId = null;
                    State.imageSrc = null;
                    DOM.preview.src = '';
                    DOM.preview.classList.add('hidden');
                    DOM.placeholder.classList.remove('hidden');
                    DOM.btnExportImage.disabled = true;
                    DOM.btnExportPreset.disabled = true;
                    DOM.btnCompare.classList.add('opacity-0', 'pointer-events-none');
                    DOM.btnCopySettings.classList.add('hidden');
                }
                renderQueue();
            });
        });
    };


    // --- Compare Before / After ---
    const toggleCompare = (isComparing) => {
        if(!State.imageSrc) return;
        if(isComparing) {
            DOM.preview.style.filter = 'none';
        } else {
            applyFilters();
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
            params: { ...State.params }
        };
        DOM.btnPasteSettings.classList.remove('hidden');
        if(window.showToast) window.showToast('보정 설정이 복사되었습니다.');
    });

    DOM.btnPasteSettings.addEventListener('click', () => {
        if(!State.copiedSettings) return;

        State.baseProfile = State.copiedSettings.profile;
        State.params = { ...State.copiedSettings.params };

        // Update Preset UI
        document.querySelectorAll('.preset-option').forEach(el => el.classList.remove('border-blue-500', 'bg-blue-500/10'));
        document.querySelector(`input[name="preset"][value="${State.baseProfile}"]`).closest('.preset-option').classList.add('border-blue-500', 'bg-blue-500/10');

        // Update Sliders UI
        DOM.sliders.forEach(slider => {
            const type = slider.dataset.type;
            slider.value = State.params[type];
            const textVal = State.params[type] > 0 ? `+${State.params[type].toFixed(2)}` : State.params[type].toFixed(2);
            document.getElementById(`val-${type}`).textContent = (type === 'sharpen') ? State.params[type].toString() : textVal;

            if(type === 'saturation' || type === 'hue') {
                slider.disabled = (State.baseProfile === 'monochrome');
                slider.parentElement.style.opacity = slider.disabled ? '0.3' : '1';
            }
        });

        applyFilters();
        if(window.showToast) window.showToast('보정 설정이 붙여넣기 되었습니다.');
    });


    // --- Base Profile & Sliders ---
    DOM.presetOptions.forEach(opt => {
        opt.addEventListener('change', (e) => {
            document.querySelectorAll('.preset-option').forEach(el => el.classList.remove('border-blue-500', 'bg-blue-500/10'));
            e.target.closest('.preset-option').classList.add('border-blue-500', 'bg-blue-500/10');
            State.baseProfile = e.target.value;
            DOM.sliders.forEach(slider => {
                if(slider.dataset.type === 'saturation' || slider.dataset.type === 'hue') {
                    slider.disabled = (State.baseProfile === 'monochrome');
                    slider.parentElement.style.opacity = slider.disabled ? '0.3' : '1';
                }
            });
            applyFilters();
        });
    });

    DOM.sliders.forEach(slider => {
        slider.addEventListener('input', (e) => {
            const type = e.target.dataset.type; const val = parseFloat(e.target.value); State.params[type] = val;
            const textVal = val > 0 ? `+${val.toFixed(2)}` : val.toFixed(2);
            document.getElementById(`val-${type}`).textContent = (type === 'sharpen') ? val.toString() : textVal;
            applyFilters();
        });
    });

    DOM.btnReset.addEventListener('click', () => {
        State.params = { sharpen: 3.0, clarity: 0.0, contrast: 0.0, brightness: 0.0, saturation: 0.0, hue: 0.0 };
        DOM.sliders.forEach(slider => {
            const type = slider.dataset.type; slider.value = State.params[type];
            const textVal = State.params[type] > 0 ? `+${State.params[type].toFixed(2)}` : State.params[type].toFixed(2);
            document.getElementById(`val-${type}`).textContent = (type === 'sharpen') ? State.params[type].toString() : textVal;
        });
        applyFilters();
    });


    // --- Rendering Engine ---
    const applyFilters = () => {
        const p = State.params; const offset = ProfileOffsets[State.baseProfile];
        const cssBrightness = 100 + (p.brightness * 10);
        let totalContrast = p.contrast + offset.contrast + (p.clarity * 0.5);
        const cssContrast = 100 + (totalContrast * 10);
        let cssSaturate = (State.baseProfile === 'monochrome') ? 0 : 100 + ((p.saturation + offset.saturation) * 15);
        const cssHue = p.hue * 5;

        DOM.preview.style.filter = `brightness(${cssBrightness}%) contrast(${cssContrast}%) saturate(${cssSaturate}%) hue-rotate(${cssHue}deg)`;
    };


    // --- Export ---
    DOM.btnExportImage.addEventListener('click', () => {
        if(!State.imageSrc) return;
        DOM.btnExportImage.disabled = true; DOM.btnExportImage.textContent = 'Processing...';

        const img = new Image(); img.crossOrigin = "Anonymous";
        img.onload = () => {
            const canvas = DOM.exportCanvas; const ctx = canvas.getContext('2d'); canvas.width = img.width; canvas.height = img.height;
            const p = State.params; const offset = ProfileOffsets[State.baseProfile];
            const cssBrightness = 100 + (p.brightness * 10);
            let totalContrast = p.contrast + offset.contrast + (p.clarity * 0.5);
            const cssContrast = 100 + (totalContrast * 10);
            let cssSaturate = (State.baseProfile === 'monochrome') ? 0 : 100 + ((p.saturation + offset.saturation) * 15);
            const cssHue = p.hue * 5;

            ctx.filter = `brightness(${cssBrightness}%) contrast(${cssContrast}%) saturate(${cssSaturate}%) hue-rotate(${cssHue}deg)`;
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            const link = document.createElement('a'); link.download = `kkyoung_pic_ctrl_${Date.now()}.jpg`;
            link.href = canvas.toDataURL("image/jpeg", 1.0); link.click();
            DOM.btnExportImage.textContent = 'EXPORT WEB IMAGE'; DOM.btnExportImage.disabled = false;
            if(window.showToast) window.showToast('색감이 적용된 이미지가 저장되었습니다!');
        };
        img.src = State.imageSrc;
    });

    DOM.btnExportPreset.addEventListener('click', () => {
        const presetData = {
            "version": "1.0", "type": "KKYOUNG_WEB_NCP", "base_profile": State.baseProfile.toUpperCase(),
            "parameters": { "sharpening": State.params.sharpen, "clarity": State.params.clarity, "contrast": State.params.contrast, "brightness": State.params.brightness, "saturation": State.params.saturation, "hue": State.params.hue },
            "created_at": new Date().toISOString()
        };
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(presetData, null, 4));
        const link = document.createElement('a'); link.href = dataStr; link.download = `CUSTOM_${State.baseProfile.toUpperCase()}_PRESET.json`; link.click();
        if(window.showToast) window.showToast('프리셋 파일이 다운로드되었습니다.');
    });
});
</script>
@endsection
