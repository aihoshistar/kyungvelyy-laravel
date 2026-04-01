@extends('layouts.admin')

@section('title', 'FRAME STUDIO')
@section('header_title', 'FRAME STUDIO')

@section('styles')
<style>
    input[type=range] { -webkit-appearance: none; background: transparent; width: 100%; }
    input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; height: 16px; width: 16px; border-radius: 50%; background: #ffffff; cursor: pointer; margin-top: -6px; box-shadow: 0 0 10px rgba(0,0,0,0.5); transition: transform 0.1s; }
    input[type=range]::-webkit-slider-thumb:hover { transform: scale(1.2); }
    input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 4px; cursor: pointer; background: #333; border-radius: 2px; }
    input[type=range]:focus { outline: none; }
    .custom-checkbox:checked + div { background-color: #3b82f6; border-color: #3b82f6; }
    .custom-checkbox:checked + div svg { opacity: 1; }
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }

    #canvas-container {
        background-image: linear-gradient(45deg, #111 25%, transparent 25%), linear-gradient(-45deg, #111 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #111 75%), linear-gradient(-45deg, transparent 75%, #111 75%);
        background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    }
    canvas { box-shadow: 0 20px 50px rgba(0,0,0,0.5); cursor: grab; }
    canvas:active { cursor: grabbing; }

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

    <!-- Left Panel: Metadata Info -->
    <div class="w-80 border-r border-white/10 bg-[#111] shrink-0 flex flex-col z-20 overflow-y-auto custom-scrollbar">
        <div class="p-6 border-b border-white/10 bg-[#0a0a0a]">
            <h2 class="text-sm font-bold text-white tracking-[0.2em] uppercase">Photo Info</h2>
        </div>

        <div class="p-6 space-y-6">
            <div class="p-4 bg-blue-500/10 border border-blue-500/30 rounded-xl">
                <p class="text-xs text-blue-400 font-medium leading-relaxed">
                    하단 큐에서 사진을 선택하면 메타데이터가 자동으로 추출됩니다. 필요시 직접 수정하세요.
                </p>
            </div>

            <!-- Metadata Inputs -->
            <div id="metadata-form" class="space-y-4 opacity-50 pointer-events-none transition-opacity">
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Title / Caption</span>
                    <input type="text" id="meta-title" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500 transition" placeholder="사진 제목">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Camera</span>
                        <input type="text" id="meta-camera" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500 transition" placeholder="예: NIKON Z f">
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Lens</span>
                        <input type="text" id="meta-lens" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500 transition" placeholder="예: 40mm f/2">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">F-Stop</span>
                        <input type="text" id="meta-fstop" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500 transition" placeholder="F2.8">
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Shutter</span>
                        <input type="text" id="meta-shutter" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500 transition" placeholder="1/125s">
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">ISO</span>
                        <input type="text" id="meta-iso" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500 transition" placeholder="100">
                    </div>
                </div>
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Author Text</span>
                    <input type="text" id="meta-author" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500 transition" value="@kkyoung.photo">
                </div>
                <!-- 💡 [추가] Custom Watermark Upload -->
                <div class="border-t border-white/10 pt-4 mt-4">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Custom Watermark (Optional)</span>
                    <input type="file" id="watermark-upload" accept="image/png, image/jpeg" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500 transition">
                    <p class="text-[10px] text-gray-500 mt-1">텍스트 서명 대신 이미지를 사용합니다.</p>
                </div>
            </div>
            <div class="p-4 bg-gray-800/40 rounded-xl border border-white/10 text-[10px] text-gray-400 leading-relaxed">
                <strong>💡 조작 팁:</strong><br>사진을 캔버스 위에서 <strong>마우스로 드래그</strong>하면 원본 비율을 유지하며 이동(Pan)할 수 있습니다.
            </div>
        </div>
    </div>

    <!-- Center Workspace & Bottom Queue -->
    <div class="flex-1 flex flex-col relative overflow-hidden bg-black/80" id="canvas-container">
        <div class="flex-1 relative flex items-center justify-center p-10 overflow-hidden">
            <canvas id="editor-canvas" class="max-w-full max-h-full transition-transform duration-300"></canvas>
            <div id="placeholder-text" class="absolute pointer-events-none text-gray-500 font-bold tracking-[0.3em] uppercase opacity-50">
                SELECT IMAGE FROM QUEUE
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

    <!-- Right Panel: Style & Export -->
    <div class="w-80 border-l border-white/10 bg-[#111] shrink-0 flex flex-col z-20 overflow-y-auto custom-scrollbar">
        <div class="p-6 border-b border-white/10 bg-[#0a0a0a]">
            <h2 class="text-sm font-bold text-white tracking-[0.2em] uppercase">Canvas Style</h2>
        </div>

        <div class="p-6 space-y-8 flex-1">
            <div>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Canvas Ratio</span>
                <div class="grid grid-cols-4 gap-2">
                    <button class="ratio-btn active py-2 border border-blue-500 bg-blue-500/10 text-white rounded-lg text-[10px] font-bold transition" data-ratio="4:5">4:5</button>
                    <button class="ratio-btn py-2 border border-white/20 text-gray-400 hover:bg-white/5 rounded-lg text-[10px] font-bold transition" data-ratio="1:1">1:1</button>
                    <button class="ratio-btn py-2 border border-white/20 text-gray-400 hover:bg-white/5 rounded-lg text-[10px] font-bold transition" data-ratio="3:2">3:2</button>
                    <button class="ratio-btn py-2 border border-white/20 text-gray-400 hover:bg-white/5 rounded-lg text-[10px] font-bold transition" data-ratio="16:9">16:9</button>
                </div>
            </div>

            <div>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Background Color</span>
                <div class="flex gap-2 items-center">
                    <input type="color" id="bg-color" value="#FFFFFF" class="w-8 h-8 rounded cursor-pointer border-0 p-0">
                    <button class="color-preset w-6 h-6 rounded-full border border-white/20 bg-white" data-color="#FFFFFF"></button>
                    <button class="color-preset w-6 h-6 rounded-full border border-white/20 bg-[#F9F8F6]" data-color="#F9F8F6"></button>
                    <button class="color-preset w-6 h-6 rounded-full border border-white/20 bg-[#1a1a1a]" data-color="#1a1a1a"></button>
                </div>
            </div>

            <div class="border-t border-white/10 pt-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Image Scale</span>
                    <!-- 💡 [추가] Fit / Fill Mode Toggle -->
                    <button id="btn-mode-toggle" class="text-[10px] bg-white/10 hover:bg-white/20 text-white px-2 py-0.5 rounded transition uppercase font-bold tracking-widest">FIT</button>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] text-gray-500">Manual Adjust</span>
                    <span id="val-scale" class="text-[10px] font-mono text-white">100%</span>
                </div>
                <input type="range" id="slider-scale" min="10" max="300" value="100">
            </div>

            <div class="border-t border-white/10 pt-6">
                <label class="flex items-center cursor-pointer">
                    <div class="relative flex items-center">
                        <input type="checkbox" id="check-grid" class="hidden custom-checkbox">
                        <div class="w-5 h-5 border-2 border-gray-500 rounded flex items-center justify-center transition-colors">
                            <svg class="w-3 h-3 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </div>
                    <span class="ml-3 text-sm font-bold text-white uppercase tracking-widest">3x3 Grid Guide</span>
                </label>
            </div>

            <div class="border-t border-white/10 pt-6">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-4">Pro Frames</span>
                <div class="space-y-2">
                    <label class="flex items-center p-3 border border-white/10 rounded-lg cursor-pointer hover:bg-white/5 transition overlay-option border-blue-500 bg-blue-500/10">
                        <input type="radio" name="overlay" value="none" class="hidden" checked>
                        <span class="text-sm text-white font-medium">None (Original)</span>
                    </label>
                    <label class="flex items-center p-3 border border-white/10 rounded-lg cursor-pointer hover:bg-white/5 transition overlay-option">
                        <input type="radio" name="overlay" value="polaroid" class="hidden">
                        <span class="text-sm text-white font-medium">Film Polaroid</span>
                    </label>
                    <label class="flex items-center p-3 border border-white/10 rounded-lg cursor-pointer hover:bg-white/5 transition overlay-option">
                        <input type="radio" name="overlay" value="instagram" class="hidden">
                        <span class="text-sm text-white font-medium">Instagram Gallery</span>
                    </label>
                    <label class="flex items-center p-3 border border-white/10 rounded-lg cursor-pointer hover:bg-white/5 transition overlay-option">
                        <input type="radio" name="overlay" value="viewfinder" class="hidden">
                        <span class="text-sm text-white font-medium">Nikon ZF Viewfinder</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="p-6 border-t border-white/10 bg-[#0a0a0a]">
            <button id="btn-export" class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-white text-black font-bold tracking-widest text-sm uppercase rounded-xl hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export High-Res
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

    // --- State ---
    const State = {
        image: null,
        ratio: '4:5',
        bgColor: '#FFFFFF',
        scale: 1.0,
        offsetX: 0,
        offsetY: 0,
        showGrid: false,
        overlay: 'none',
        canvasBaseWidth: 1080,
        metadata: { title: '', camera: '', lens: '', fstop: '', shutter: '', iso: '', author: '@kkyoung.photo' },

        photoQueueData: [],
        loadedPosts: [],
        activeQueueId: null,

        imageMode: 'fit', // 'fit' or 'fill'
        watermarkImg: null // Custom watermark image object
    };

    // --- DOM ---
    const DOM = {
        canvas: document.getElementById('editor-canvas'), ctx: document.getElementById('editor-canvas').getContext('2d'),
        placeholder: document.getElementById('placeholder-text'), metadataForm: document.getElementById('metadata-form'),

        metaTitle: document.getElementById('meta-title'), metaCamera: document.getElementById('meta-camera'), metaLens: document.getElementById('meta-lens'),
        metaFstop: document.getElementById('meta-fstop'), metaShutter: document.getElementById('meta-shutter'), metaIso: document.getElementById('meta-iso'), metaAuthor: document.getElementById('meta-author'),
        watermarkUpload: document.getElementById('watermark-upload'),

        ratioBtns: document.querySelectorAll('.ratio-btn'), bgColor: document.getElementById('bg-color'), colorPresets: document.querySelectorAll('.color-preset'),
        sliderScale: document.getElementById('slider-scale'), valScale: document.getElementById('val-scale'), btnModeToggle: document.getElementById('btn-mode-toggle'),
        checkGrid: document.getElementById('check-grid'), overlayOptions: document.querySelectorAll('input[name="overlay"]'), btnExport: document.getElementById('btn-export'),

        localUpload: document.getElementById('local-upload'), photoQueue: document.getElementById('photo-queue'), btnLoad: document.getElementById('btn-load-posts'), btnClearQueue: document.getElementById('btn-clear-queue'),
        modal: document.getElementById('modal-posts'), modalSearch: document.getElementById('modal-search'), modalList: document.getElementById('modal-list'),
        dndOverlay: document.getElementById('dnd-overlay')
    };

    const formatFraction = (exifValue) => {
        if (!exifValue) return '';
        if (exifValue.numerator && exifValue.denominator) {
            if (exifValue.numerator === 1 && exifValue.denominator > 1) return `1/${exifValue.denominator}s`;
            if (exifValue.denominator === 1) return `${exifValue.numerator}s`;
            return `${(exifValue.numerator / exifValue.denominator).toFixed(1)}s`;
        }
        return `${exifValue}s`;
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
                State.photoQueueData.push({ id: 'local_' + Date.now() + Math.random(), url: ev.target.result, file: file, isServer: false });
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
                State.photoQueueData.push({ id: 'local_' + Date.now() + Math.random(), url: ev.target.result, file: file, isServer: false });
                renderQueue();
            };
            reader.readAsDataURL(file);
        });
        DOM.localUpload.value = '';
    });

    DOM.btnClearQueue.addEventListener('click', () => {
        State.photoQueueData = [];
        State.activeQueueId = null;
        State.image = null;
        DOM.canvas.width = 0; DOM.canvas.height = 0;
        DOM.placeholder.classList.remove('hidden'); DOM.metadataForm.classList.add('opacity-50', 'pointer-events-none');
        DOM.btnExport.disabled = true;
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
                if(!State.photoQueueData.find(q => q.id === `server_${p.id}`)) {
                    State.photoQueueData.push({ id: `server_${p.id}`, url: p.url, photo: p, isServer: true });
                }
            });
            renderQueue();
        } catch(e) { alert('실패'); }
    };

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
                loadToCanvas(item);
                renderQueue(); // Update active border
            });
        });

        document.querySelectorAll('.btn-remove-queue').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const removeId = btn.dataset.id;
                State.photoQueueData = State.photoQueueData.filter(q => q.id !== removeId);
                if(State.activeQueueId === removeId) {
                    State.activeQueueId = null; State.image = null; DOM.canvas.width = 0; DOM.canvas.height = 0;
                    DOM.placeholder.classList.remove('hidden'); DOM.metadataForm.classList.add('opacity-50', 'pointer-events-none');
                    DOM.btnExport.disabled = true;
                }
                renderQueue();
            });
        });
    };

    // Load Item to Canvas & Extract EXIF
    const loadToCanvas = (item) => {
        DOM.placeholder.textContent = "LOADING..."; DOM.placeholder.classList.remove('hidden');
        const img = new Image(); img.crossOrigin = "Anonymous";
        img.onload = () => {
            State.image = img; State.scale = 1.0; State.offsetX = 0; State.offsetY = 0; DOM.sliderScale.value = 100; DOM.valScale.textContent = '100%';

            if(item.isServer) {
                const p = item.photo;
                State.metadata.camera = p.exif_body || ''; State.metadata.lens = p.exif_lens || '';
                State.metadata.fstop = p.exif_aperture ? `F${parseFloat(p.exif_aperture)}` : '';
                State.metadata.shutter = (p.exif_shutter_speed_num && p.exif_shutter_speed_den) ? `${p.exif_shutter_speed_num}/${p.exif_shutter_speed_den}s` : '';
                State.metadata.iso = p.exif_iso ? `ISO ${p.exif_iso}` : '';
                State.metadata.title = p.title?.ko || 'Untitiled';
                updateMetaUIAndRender();
            } else {
                EXIF.getData(item.file, function() {
                    const make = EXIF.getTag(this, "Make"); const model = EXIF.getTag(this, "Model"); const lens = EXIF.getTag(this, "LensModel") || EXIF.getTag(this, "undefined");
                    State.metadata.camera = (make && model) ? `${make.replace('CORPORATION', '').trim()} ${model}` : '';
                    State.metadata.lens = lens || '';
                    State.metadata.fstop = EXIF.getTag(this, "FNumber") ? `F${parseFloat(EXIF.getTag(this, "FNumber"))}` : '';
                    State.metadata.shutter = EXIF.getTag(this, "ExposureTime") ? formatFraction(EXIF.getTag(this, "ExposureTime")) : '';
                    State.metadata.iso = EXIF.getTag(this, "ISOSpeedRatings") ? `ISO ${EXIF.getTag(this, "ISOSpeedRatings")}` : '';
                    State.metadata.title = item.file.name.split('.')[0];
                    updateMetaUIAndRender();
                });
            }
        };
        img.src = item.url;
    };

    const updateMetaUIAndRender = () => {
        DOM.metaTitle.value = State.metadata.title; DOM.metaCamera.value = State.metadata.camera; DOM.metaLens.value = State.metadata.lens;
        DOM.metaFstop.value = State.metadata.fstop; DOM.metaShutter.value = State.metadata.shutter; DOM.metaIso.value = State.metadata.iso;
        DOM.metadataForm.classList.remove('opacity-50', 'pointer-events-none');
        DOM.placeholder.classList.add('hidden'); DOM.btnExport.disabled = false;
        render();
    };

    // --- Inputs & Controls ---
    ['metaTitle', 'metaCamera', 'metaLens', 'metaFstop', 'metaShutter', 'metaIso', 'metaAuthor'].forEach(id => {
        DOM[id].addEventListener('input', (e) => { State.metadata[id.replace('meta', '').toLowerCase()] = e.target.value; render(); });
    });

    // Custom Watermark Upload
    DOM.watermarkUpload.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if(!file) { State.watermarkImg = null; render(); return; }
        const reader = new FileReader();
        reader.onload = (ev) => {
            const wm = new Image();
            wm.onload = () => { State.watermarkImg = wm; render(); };
            wm.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });

    // Fit/Fill Toggle
    DOM.btnModeToggle.addEventListener('click', () => {
        State.imageMode = State.imageMode === 'fit' ? 'fill' : 'fit';
        DOM.btnModeToggle.textContent = State.imageMode.toUpperCase();
        DOM.btnModeToggle.classList.toggle('bg-blue-500', State.imageMode === 'fill');
        DOM.btnModeToggle.classList.toggle('bg-white/10', State.imageMode === 'fit');
        render();
    });

    DOM.ratioBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            DOM.ratioBtns.forEach(b => { b.classList.remove('border-blue-500', 'bg-blue-500/10', 'text-white'); b.classList.add('border-white/20', 'text-gray-400'); });
            btn.classList.add('border-blue-500', 'bg-blue-500/10', 'text-white'); btn.classList.remove('border-white/20', 'text-gray-400');
            State.ratio = btn.dataset.ratio; render();
        });
    });
    DOM.bgColor.addEventListener('input', (e) => { State.bgColor = e.target.value; render(); });
    DOM.colorPresets.forEach(btn => { btn.addEventListener('click', () => { State.bgColor = btn.dataset.color; DOM.bgColor.value = State.bgColor; render(); }); });
    DOM.sliderScale.addEventListener('input', (e) => { State.scale = e.target.value / 100; DOM.valScale.textContent = `${e.target.value}%`; render(); });
    DOM.checkGrid.addEventListener('change', (e) => { State.showGrid = e.target.checked; render(); });
    DOM.overlayOptions.forEach(opt => {
        opt.addEventListener('change', (e) => {
            document.querySelectorAll('.overlay-option').forEach(el => el.classList.remove('border-blue-500', 'bg-blue-500/10'));
            e.target.closest('.overlay-option').classList.add('border-blue-500', 'bg-blue-500/10');
            State.overlay = e.target.value;
            if(State.overlay === 'polaroid') State.bgColor = '#F9F8F6'; else State.bgColor = '#FFFFFF';
            DOM.bgColor.value = State.bgColor; render();
        });
    });

    // Panning
    let isDragging = false; let startX, startY;
    DOM.canvas.addEventListener('mousedown', (e) => { if(!State.image) return; isDragging = true; startX = e.clientX - State.offsetX; startY = e.clientY - State.offsetY; });
    window.addEventListener('mousemove', (e) => { if(!isDragging) return; State.offsetX = e.clientX - startX; State.offsetY = e.clientY - startY; render(); });
    window.addEventListener('mouseup', () => { isDragging = false; });
    DOM.canvas.addEventListener('mouseleave', () => { isDragging = false; });

    // --- Core Render Engine ---
    const render = () => {
        if(!State.image) return;
        let w = State.canvasBaseWidth; let h = w;
        if(State.ratio === '4:5') h = w * (5/4); if(State.ratio === '3:2') h = w * (2/3); if(State.ratio === '16:9') h = w * (9/16);
        DOM.canvas.width = w; DOM.canvas.height = h; DOM.canvas.style.aspectRatio = `${w} / ${h}`;

        const ctx = DOM.ctx;
        ctx.fillStyle = State.bgColor;
        ctx.fillRect(0, 0, w, h);

        const img = State.image;
        let drawX, drawY, drawW, drawH;
        let boxW = w, boxH = h, boxX = 0, boxY = 0; // The actual box where image should go

        // Determine Box per Overlay
        if (State.overlay === 'polaroid') {
            const mX = w * 0.04; const mT = mX * 1.2;
            boxW = w - (mX * 2); boxH = h - (mT + mX * 5.0);
            boxX = mX; boxY = mT;
        } else if (State.overlay === 'instagram') {
             const mX = w * 0.05; const mT = mX;
             boxW = w - (mX * 2); boxH = h - (mT + w * 0.15);
             boxX = mX; boxY = mT;
             ctx.shadowColor = "rgba(0, 0, 0, 0.15)"; ctx.shadowBlur = 20; ctx.shadowOffsetY = 10;
        }

        // Calculate Scale (Fit vs Fill) based on Box
        let baseScale;
        if(State.imageMode === 'fill') {
            baseScale = Math.max(boxW / img.width, boxH / img.height);
        } else { // default 'fit'
            baseScale = Math.min(boxW / img.width, boxH / img.height);
        }

        const finalW = img.width * baseScale * State.scale;
        const finalH = img.height * baseScale * State.scale;

        const panX = State.offsetX * (w / DOM.canvas.clientWidth);
        const panY = State.offsetY * (h / DOM.canvas.clientHeight);

        drawW = finalW; drawH = finalH;
        drawX = boxX + (boxW - drawW) / 2 + panX;
        drawY = boxY + (boxH - drawH) / 2 + panY;

        // Draw Image (with Clipping to prevent Fill bleeding)
        ctx.save();
        ctx.beginPath();
        ctx.rect(boxX, boxY, boxW, boxH);
        ctx.clip();
        ctx.drawImage(img, drawX, drawY, drawW, drawH);
        ctx.restore();
        ctx.shadowColor = "transparent";

        const m = State.metadata;
        if(State.overlay === 'polaroid') drawPolaroidOverlay(ctx, w, h, m.title, `${m.camera} • ${m.lens} • ${m.fstop} • ${m.shutter} • ${m.iso}`);
        else if(State.overlay === 'instagram') drawInstagramOverlay(ctx, w, h, [m.camera, m.lens].filter(Boolean).join(' | '), [m.fstop, m.shutter, m.iso].filter(Boolean).join(' | '));
        else if(State.overlay === 'viewfinder') drawViewfinderOverlay(ctx, w, h, m.fstop, m.shutter, m.iso.replace('ISO ', ''));

        // Draw Custom Watermark or Author Text (Only if not viewfinder which has its own layout)
        if(State.overlay !== 'viewfinder') {
            drawWatermark(ctx, w, h, m.author, State.overlay);
        }

        if(State.showGrid) drawGrid(ctx, boxX, boxY, boxW, boxH);
    };

    const drawWatermark = (ctx, w, h, authorText, overlayType) => {
        let wmX, wmY, maxSize;
        if (overlayType === 'polaroid') {
            const mB = w * 0.04 * 5.0; // Bottom margin
            wmX = w / 2; wmY = h - mB + (mB * 0.35) + Math.max(30, w * 0.025)*1.4 + Math.max(18, w * 0.014)*1.5;
            maxSize = mB * 0.4;
        } else if (overlayType === 'instagram') {
            const mX = w * 0.05; const mB = w * 0.15;
            const fS = Math.max(18, w * 0.016);
            wmX = mX; wmY = h - mB + (mB * 0.4) + fS * 1.5;
            maxSize = mB * 0.4;
        } else {
            // Default bottom right
            const pad = w * 0.05;
            wmX = w - pad; wmY = h - pad;
            maxSize = w * 0.1;
        }

        if (State.watermarkImg) {
            // Draw custom image watermark
            const img = State.watermarkImg;
            const scale = Math.min(maxSize / img.width, maxSize / img.height);
            const drawW = img.width * scale; const drawH = img.height * scale;

            ctx.globalAlpha = 0.8;
            if(overlayType === 'polaroid') {
                ctx.drawImage(img, wmX - drawW/2, wmY - drawH, drawW, drawH);
            } else if (overlayType === 'instagram') {
                ctx.drawImage(img, wmX, wmY - drawH/2, drawW, drawH);
            } else {
                ctx.drawImage(img, wmX - drawW, wmY - drawH, drawW, drawH);
            }
            ctx.globalAlpha = 1.0;
        } else if (authorText) {
            // Draw text
            ctx.fillStyle = overlayType === 'polaroid' || overlayType === 'instagram' ? "#888888" : "rgba(255,255,255,0.7)";
            ctx.font = `italic 400 ${Math.max(14, w * 0.012)}px Pretendard, serif`;
            if(overlayType === 'polaroid') { ctx.textAlign = "center"; ctx.fillText(authorText, wmX, wmY); }
            else if (overlayType === 'instagram') { ctx.textAlign = "left"; ctx.fillText(authorText, wmX, wmY); }
            else { ctx.textAlign = "right"; ctx.fillText(authorText, wmX, wmY); }
        }
    };

    const drawGrid = (ctx, x, y, w, h) => {
        ctx.strokeStyle = "rgba(255, 255, 255, 0.6)"; ctx.lineWidth = Math.max(1.5, w * 0.002); ctx.shadowColor = "transparent";
        ctx.beginPath(); ctx.moveTo(x + w / 3, y); ctx.lineTo(x + w / 3, y + h); ctx.moveTo(x + (w * 2) / 3, y); ctx.lineTo(x + (w * 2) / 3, y + h);
        ctx.moveTo(x, y + h / 3); ctx.lineTo(x + w, y + h / 3); ctx.moveTo(x, y + (h * 2) / 3); ctx.lineTo(x + w, y + (h * 2) / 3); ctx.stroke();
    };

    const drawPolaroidOverlay = (ctx, w, h, title, meta) => {
        const mX = w * 0.04; const mT = mX * 1.2; const mB = mX * 5.0;
        ctx.strokeStyle = "rgba(0,0,0,0.08)"; ctx.lineWidth = 1; ctx.strokeRect(mX, mT, w - mX*2, h - mT - mB);
        const tY = h - mB + (mB * 0.35); const tF = Math.max(30, w * 0.025); const mF = Math.max(18, w * 0.014);
        ctx.textAlign = "center"; ctx.fillStyle = "#222222"; ctx.font = `bold ${tF}px Pretendard, serif`; ctx.fillText(title || 'Untitled', w / 2, tY);
        ctx.fillStyle = "#777777"; ctx.font = `400 ${mF}px Pretendard, monospace`; ctx.fillText(meta, w / 2, tY + tF * 1.4);
    };

    const drawInstagramOverlay = (ctx, w, h, leftText, rightText) => {
        const mX = w * 0.05; const mB = w * 0.15; const tY = h - mB + (mB * 0.4); const fS = Math.max(18, w * 0.016);
        ctx.textBaseline = "middle"; ctx.fillStyle = "#333333"; ctx.textAlign = "left"; ctx.font = `600 ${fS}px Pretendard, sans-serif`; ctx.fillText(leftText || 'Camera Info', mX, tY);
        const qS = mB * 0.5; const qX = w - mX - qS; const qY = h - mB + (mB - qS) / 2;
        ctx.fillStyle = "#333333"; ctx.textAlign = "right"; ctx.font = `400 ${fS}px Pretendard, sans-serif`; ctx.fillText(rightText || 'Settings Info', qX - (mX * 0.5), qY + (qS / 2));
        const qr = new QRious({ value: window.location.origin, size: qS, level: 'M' }); const qrImg = new Image(); qrImg.src = qr.toDataURL();
        ctx.globalAlpha = 0.8; ctx.drawImage(qrImg, qX, qY, qS, qS); ctx.globalAlpha = 1.0;
    };

    const drawViewfinderOverlay = (ctx, w, h, fstop, shutter, iso) => {
        const pX = w * 0.03; const pY = h * 0.04; const fS = Math.max(16, w * 0.018); const fSm = fS * 0.7; const fLg = fS * 1.4;
        ctx.shadowColor = "rgba(0, 0, 0, 0.9)"; ctx.shadowBlur = Math.max(3, w * 0.003); ctx.shadowOffsetX = 1; ctx.shadowOffsetY = 1;
        ctx.fillStyle = "#FFFFFF"; ctx.strokeStyle = "#FFFFFF"; ctx.lineWidth = Math.max(1.5, w * 0.0015);
        ctx.textAlign = "left"; ctx.textBaseline = "middle"; const tY = pY + fLg * 0.5;
        ctx.font = `bold ${fLg}px Arial, sans-serif`; ctx.fillText("P", pX, tY);
        ctx.font = `bold ${fS}px Arial, sans-serif`; const sBoxX = pX + fLg * 2.0; ctx.strokeRect(sBoxX, tY - fS * 0.75, fS * 1.5, fS * 1.5); ctx.fillText("S", sBoxX + fS * 0.35, tY);
        const afcX = sBoxX + fS * 2.8; ctx.fillText("AF-C", afcX, tY); const meterX = afcX + fS * 4.5; ctx.strokeRect(meterX, tY - fS * 0.6, fS * 1.8, fS * 1.2); ctx.beginPath(); ctx.arc(meterX + fS * 0.9, tY, fS * 0.3, 0, Math.PI * 2); ctx.stroke();
        ctx.textAlign = "right"; const rX = w - pX; ctx.font = `bold ${fS}px Arial, sans-serif`; ctx.fillText("FX", rX, tY);
        ctx.font = `bold ${fSm}px Arial, sans-serif`; const nX = rX - fS * 2.8; ctx.fillText("NORM", nX, tY + fS * 0.15);
        ctx.font = `bold ${fS}px Arial, sans-serif`; const aX = nX - fS * 3.0; ctx.fillText("AUTO", aX, tY);
        ctx.font = `bold ${fSm}px Arial, sans-serif`; const wX = aX - fS * 3.8; ctx.fillText("WB", wX, tY + fS * 0.15);
        ctx.textBaseline = "alphabetic"; const bY = h - pY; const cx = w / 2; ctx.textAlign = "left"; const iH = fS * 1.5; const iY = bY - iH + (fS * 0.2);
        ctx.strokeRect(pX, iY, fS * 2.0, iH); ctx.beginPath(); ctx.arc(pX + fS * 1.0, iY + iH/2, fS * 0.4, 0, Math.PI * 2); ctx.fill();
        ctx.textAlign = "center"; ctx.font = `bold ${fS * 1.2}px Arial, sans-serif`; ctx.fillText(shutter || '---', cx - fS * 6.5, bY); ctx.fillText(fstop || 'F--', cx - fS * 1.0, bY);
        ctx.shadowColor = "transparent"; const isoX = cx + fS * 3.5; const isoH = fS * 1.8; const isoY = bY - isoH + (fS * 0.2); const isoW = fS * 6.0;
        ctx.fillStyle = "rgba(20, 20, 20, 0.85)"; ctx.fillRect(isoX, isoY, isoW, isoH); ctx.fillStyle = "#FFFFFF"; ctx.textAlign = "left"; ctx.font = `bold ${fS * 0.6}px Arial, sans-serif`; ctx.textBaseline = "middle";
        ctx.fillText("ISO", isoX + fS * 0.5, isoY + isoH * 0.35); ctx.fillText("AUTO", isoX + fS * 0.5, isoY + isoH * 0.7); ctx.font = `bold ${fS * 1.2}px Arial, sans-serif`; ctx.fillText(iso || '---', isoX + fS * 2.2, isoY + isoH * 0.55);
        ctx.textBaseline = "alphabetic"; ctx.shadowColor = "rgba(0, 0, 0, 0.9)"; ctx.textAlign = "right"; ctx.font = `bold ${fS}px Arial, sans-serif`; ctx.fillText("[ 8.0k ]", rX, bY);
        const ibY = bY - fS * 5.0; ctx.font = `italic bold ${fS * 1.2}px "Times New Roman", serif`; ctx.strokeRect(rX - fS * 1.5, ibY, fS * 1.5, fS * 1.5); ctx.textBaseline = "middle"; ctx.fillText("i", rX - fS * 0.5, ibY + fS * 0.75); ctx.textBaseline = "alphabetic";
        const batY = bY - fS * 3.0; ctx.lineWidth = Math.max(1.5, w * 0.0015); ctx.strokeRect(rX - fS * 2.0, batY, fS * 1.8, fS * 0.8); ctx.fillRect(rX - fS * 1.8, batY + fS * 0.15, fS * 0.8, fS * 0.5); ctx.fillRect(rX - fS * 0.2, batY + fS * 0.2, fS * 0.2, fS * 0.4);

        // Viewfinder Author Text (Watermark Override)
        ctx.globalAlpha = 0.8; ctx.textAlign = "right"; ctx.textBaseline = "top";

        if(State.watermarkImg) {
            const wmImg = State.watermarkImg;
            const wmSize = fontLarge * 1.5;
            const wmScale = Math.min(wmSize / wmImg.width, wmSize / wmImg.height);
            ctx.drawImage(wmImg, rX - wmImg.width*wmScale, pY + fontLarge * 1.8, wmImg.width*wmScale, wmImg.height*wmScale);
        } else {
            ctx.font = `italic 300 ${fSm * 1.2}px "Times New Roman", serif`;
            ctx.fillText(State.metadata.author, rX, pY + fontLarge * 1.8);
        }
        ctx.globalAlpha = 1.0;
    };

    DOM.btnExport.addEventListener('click', () => {
        const link = document.createElement('a'); link.download = `kkyoung_frame_${State.metadata.title || 'export'}_${Date.now()}.jpg`;
        link.href = DOM.canvas.toDataURL("image/jpeg", 1.0); link.click();
        if(window.showToast) window.showToast('이미지가 성공적으로 저장되었습니다!');
    });

});
</script>
@endsection
