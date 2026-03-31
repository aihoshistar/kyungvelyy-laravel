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

    #canvas-container {
        background-image: 
            linear-gradient(45deg, #111 25%, transparent 25%), 
            linear-gradient(-45deg, #111 25%, transparent 25%), 
            linear-gradient(45deg, transparent 75%, #111 75%), 
            linear-gradient(-45deg, transparent 75%, #111 75%);
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    }
    canvas {
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        cursor: grab;
    }
    canvas:active { cursor: grabbing; }
</style>
@endsection

@section('content')
<div class="flex h-[calc(100vh-73px)] w-full overflow-hidden bg-[#050505]">
    
    <!-- Left Panel: Source & Metadata -->
    <div class="w-80 border-r border-white/10 bg-[#111] shrink-0 flex flex-col z-20 overflow-y-auto custom-scrollbar">
        <div class="p-6 border-b border-white/10 bg-[#0a0a0a]">
            <h2 class="text-sm font-bold text-white tracking-[0.2em] uppercase">Source & Info</h2>
        </div>

        <div class="p-6 space-y-8">
            <!-- Upload -->
            <div>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Upload Image</span>
                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-white/20 rounded-xl hover:bg-white/5 hover:border-blue-500 transition cursor-pointer group">
                    <svg class="w-8 h-8 text-gray-500 group-hover:text-blue-400 mb-2 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span class="text-xs text-gray-400 font-medium">Click to extract EXIF</span>
                    <input type="file" id="image-upload" accept="image/*" class="hidden">
                </label>
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
                        <input type="text" id="meta-iso" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500 transition" placeholder="ISO 100">
                    </div>
                </div>
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Author Info</span>
                    <input type="text" id="meta-author" class="w-full bg-[#1a1a1a] border border-white/10 rounded px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500 transition" value="@kkyoung.photo">
                </div>
            </div>
            
        </div>
    </div>

    <!-- Center Workspace -->
    <div class="flex-1 flex flex-col relative overflow-hidden bg-black/80" id="canvas-container">
        <div class="flex-1 relative flex items-center justify-center p-10 overflow-hidden">
            <canvas id="editor-canvas" class="max-w-full max-h-full transition-transform duration-300"></canvas>
            <div id="placeholder-text" class="absolute pointer-events-none text-gray-500 font-bold tracking-[0.3em] uppercase opacity-50">
                LOAD IMAGE FIRST
            </div>
        </div>
    </div>

    <!-- Right Panel: Style & Export -->
    <div class="w-80 border-l border-white/10 bg-[#111] shrink-0 flex flex-col z-20 overflow-y-auto custom-scrollbar">
        <div class="p-6 border-b border-white/10 bg-[#0a0a0a]">
            <h2 class="text-sm font-bold text-white tracking-[0.2em] uppercase">Canvas Style</h2>
        </div>

        <div class="p-6 space-y-8 flex-1">
            <!-- Canvas Ratio -->
            <div>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Canvas Ratio</span>
                <div class="grid grid-cols-4 gap-2">
                    <button class="ratio-btn active py-2 border border-blue-500 bg-blue-500/10 text-white rounded-lg text-[10px] font-bold transition" data-ratio="4:5">4:5</button>
                    <button class="ratio-btn py-2 border border-white/20 text-gray-400 hover:bg-white/5 rounded-lg text-[10px] font-bold transition" data-ratio="1:1">1:1</button>
                    <button class="ratio-btn py-2 border border-white/20 text-gray-400 hover:bg-white/5 rounded-lg text-[10px] font-bold transition" data-ratio="3:2">3:2</button>
                    <button class="ratio-btn py-2 border border-white/20 text-gray-400 hover:bg-white/5 rounded-lg text-[10px] font-bold transition" data-ratio="16:9">16:9</button>
                </div>
            </div>

            <!-- Background Color -->
            <div>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Background Color</span>
                <div class="flex gap-2 items-center">
                    <input type="color" id="bg-color" value="#FFFFFF" class="w-8 h-8 rounded cursor-pointer border-0 p-0">
                    <button class="color-preset w-6 h-6 rounded-full border border-white/20 bg-white" data-color="#FFFFFF"></button>
                    <button class="color-preset w-6 h-6 rounded-full border border-white/20 bg-[#F9F8F6]" data-color="#F9F8F6"></button>
                    <button class="color-preset w-6 h-6 rounded-full border border-white/20 bg-[#1a1a1a]" data-color="#1a1a1a"></button>
                </div>
            </div>
            
            <!-- Image Scale -->
            <div class="border-t border-white/10 pt-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Image Scale</span>
                    <span id="val-scale" class="text-[10px] font-mono text-white">100%</span>
                </div>
                <input type="range" id="slider-scale" min="10" max="300" value="100">
            </div>

            <!-- Grid Overlay -->
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

            <!-- Overlays -->
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
        canvasBaseWidth: 1080, // 고해상도 기준
        metadata: {
            title: '', camera: '', lens: '', fstop: '', shutter: '', iso: '', author: '@kkyoung.photo'
        }
    };

    // --- DOM ---
    const DOM = {
        upload: document.getElementById('image-upload'),
        canvas: document.getElementById('editor-canvas'),
        ctx: document.getElementById('editor-canvas').getContext('2d'),
        placeholder: document.getElementById('placeholder-text'),
        metadataForm: document.getElementById('metadata-form'),
        
        // Meta Inputs
        metaTitle: document.getElementById('meta-title'),
        metaCamera: document.getElementById('meta-camera'),
        metaLens: document.getElementById('meta-lens'),
        metaFstop: document.getElementById('meta-fstop'),
        metaShutter: document.getElementById('meta-shutter'),
        metaIso: document.getElementById('meta-iso'),
        metaAuthor: document.getElementById('meta-author'),

        // Controls
        ratioBtns: document.querySelectorAll('.ratio-btn'),
        bgColor: document.getElementById('bg-color'),
        colorPresets: document.querySelectorAll('.color-preset'),
        sliderScale: document.getElementById('slider-scale'),
        valScale: document.getElementById('val-scale'),
        checkGrid: document.getElementById('check-grid'),
        overlayOptions: document.querySelectorAll('input[name="overlay"]'),
        btnExport: document.getElementById('btn-export')
    };

    // --- Helper: EXIF Fraction to String ---
    const formatFraction = (exifValue) => {
        if (!exifValue) return '';
        if (exifValue.numerator && exifValue.denominator) {
            if (exifValue.numerator === 1 && exifValue.denominator > 1) return `1/${exifValue.denominator}s`;
            if (exifValue.denominator === 1) return `${exifValue.numerator}s`;
            return `${(exifValue.numerator / exifValue.denominator).toFixed(1)}s`;
        }
        return `${exifValue}s`;
    };

    // --- Event Listeners ---
    
    // 1. Upload & EXIF Extraction
    DOM.upload.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if(!file) return;
        
        DOM.placeholder.textContent = "EXTRACTING METADATA...";

        const reader = new FileReader();
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => {
                State.image = img;
                State.scale = 1.0; State.offsetX = 0; State.offsetY = 0;
                DOM.sliderScale.value = 100; DOM.valScale.textContent = '100%';
                
                // 💡 [핵심] EXIF.js를 이용한 메타데이터 추출
                EXIF.getData(img, function() {
                    const make = EXIF.getTag(this, "Make");
                    const model = EXIF.getTag(this, "Model");
                    const lens = EXIF.getTag(this, "LensModel") || EXIF.getTag(this, "undefined");
                    const fnumber = EXIF.getTag(this, "FNumber");
                    const exposure = EXIF.getTag(this, "ExposureTime");
                    const iso = EXIF.getTag(this, "ISOSpeedRatings");

                    // 데이터 포맷팅 및 상태 업데이트
                    State.metadata.camera = (make && model) ? `${make.replace('CORPORATION', '').trim()} ${model}` : '';
                    State.metadata.lens = lens || '';
                    State.metadata.fstop = fnumber ? `F${parseFloat(fnumber)}` : '';
                    State.metadata.shutter = exposure ? formatFraction(exposure) : '';
                    State.metadata.iso = iso ? `ISO ${iso}` : '';
                    State.metadata.title = file.name.split('.')[0]; // 파일명을 기본 제목으로

                    // UI 업데이트
                    DOM.metaTitle.value = State.metadata.title;
                    DOM.metaCamera.value = State.metadata.camera;
                    DOM.metaLens.value = State.metadata.lens;
                    DOM.metaFstop.value = State.metadata.fstop;
                    DOM.metaShutter.value = State.metadata.shutter;
                    DOM.metaIso.value = State.metadata.iso;
                    
                    DOM.metadataForm.classList.remove('opacity-50', 'pointer-events-none');
                    DOM.placeholder.classList.add('hidden');
                    DOM.btnExport.disabled = false;
                    render();

                    if(window.showToast) window.showToast('메타데이터 추출 완료!');
                });
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });

    // 2. Metadata Inputs Sync
    ['metaTitle', 'metaCamera', 'metaLens', 'metaFstop', 'metaShutter', 'metaIso', 'metaAuthor'].forEach(id => {
        DOM[id].addEventListener('input', (e) => {
            const key = id.replace('meta', '').toLowerCase();
            State.metadata[key] = e.target.value;
            render();
        });
    });

    // 3. Canvas Controls
    DOM.ratioBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            DOM.ratioBtns.forEach(b => { b.classList.remove('border-blue-500', 'bg-blue-500/10', 'text-white'); b.classList.add('border-white/20', 'text-gray-400'); });
            btn.classList.add('border-blue-500', 'bg-blue-500/10', 'text-white'); btn.classList.remove('border-white/20', 'text-gray-400');
            State.ratio = btn.dataset.ratio;
            render();
        });
    });
    DOM.bgColor.addEventListener('input', (e) => { State.bgColor = e.target.value; render(); });
    DOM.colorPresets.forEach(btn => {
        btn.addEventListener('click', () => {
            State.bgColor = btn.dataset.color; DOM.bgColor.value = State.bgColor; render();
        });
    });
    DOM.sliderScale.addEventListener('input', (e) => {
        State.scale = e.target.value / 100; DOM.valScale.textContent = `${e.target.value}%`; render();
    });
    DOM.checkGrid.addEventListener('change', (e) => { State.showGrid = e.target.checked; render(); });
    DOM.overlayOptions.forEach(opt => {
        opt.addEventListener('change', (e) => {
            document.querySelectorAll('.overlay-option').forEach(el => el.classList.remove('border-blue-500', 'bg-blue-500/10'));
            e.target.closest('.overlay-option').classList.add('border-blue-500', 'bg-blue-500/10');
            State.overlay = e.target.value;
            // 오버레이에 따라 추천 배경색 자동 설정
            if(State.overlay === 'polaroid') State.bgColor = '#F9F8F6';
            else if(State.overlay === 'instagram') State.bgColor = '#FFFFFF';
            else State.bgColor = '#FFFFFF';
            DOM.bgColor.value = State.bgColor;
            render();
        });
    });

    // 4. Panning
    let isDragging = false; let startX, startY;
    DOM.canvas.addEventListener('mousedown', (e) => { if(!State.image) return; isDragging = true; startX = e.clientX - State.offsetX; startY = e.clientY - State.offsetY; });
    window.addEventListener('mousemove', (e) => { if(!isDragging) return; State.offsetX = e.clientX - startX; State.offsetY = e.clientY - startY; render(); });
    window.addEventListener('mouseup', () => { isDragging = false; });


    // --- Rendering Engine ---
    const render = () => {
        if(!State.image) return;

        let w = State.canvasBaseWidth;
        let h = w;
        if(State.ratio === '4:5') h = w * (5/4);
        if(State.ratio === '3:2') h = w * (2/3);
        if(State.ratio === '16:9') h = w * (9/16);

        DOM.canvas.width = w; DOM.canvas.height = h;
        DOM.canvas.style.aspectRatio = `${w} / ${h}`;

        const ctx = DOM.ctx;
        ctx.fillStyle = State.bgColor;
        ctx.fillRect(0, 0, w, h);

        const img = State.image;
        const baseScale = Math.min(w / img.width, h / img.height);
        const finalW = img.width * baseScale * State.scale;
        const finalH = img.height * baseScale * State.scale;
        
        const panX = State.offsetX * (w / DOM.canvas.clientWidth); 
        const panY = State.offsetY * (h / DOM.canvas.clientHeight);

        // 오버레이별 이미지 그리기 영역 계산
        let drawX, drawY, drawW, drawH;

        if (State.overlay === 'polaroid') {
            const marginX = w * 0.04; const marginTop = marginX * 1.2;
            drawW = w - (marginX * 2); drawH = h - (marginTop + marginX * 5.0); // 하단 여백 제외
            const contentScale = Math.min(drawW / img.width, drawH / img.height) * State.scale;
            drawX = marginX + (drawW - img.width * contentScale) / 2 + panX;
            drawY = marginTop + (drawH - img.height * contentScale) / 2 + panY;
            drawW = img.width * contentScale; drawH = img.height * contentScale;
        } else if (State.overlay === 'instagram') {
             const marginX = w * 0.05; const marginTop = marginX;
             drawW = w - (marginX * 2); drawH = h - (marginTop + w * 0.15);
             const contentScale = Math.min(drawW / img.width, drawH / img.height) * State.scale;
             drawX = marginX + (drawW - img.width * contentScale) / 2 + panX;
             drawY = marginTop + (drawH - img.height * contentScale) / 2 + panY;
             drawW = img.width * contentScale; drawH = img.height * contentScale;
             
             // 인스타 모드는 그림자 효과 추가
             ctx.shadowColor = "rgba(0, 0, 0, 0.15)"; ctx.shadowBlur = 20; ctx.shadowOffsetY = 10;
        } else {
            // 기본 & 뷰파인더
            drawX = (w - finalW) / 2 + panX;
            drawY = (h - finalH) / 2 + panY;
            drawW = finalW; drawH = finalH;
        }
        
        // 이미지 그리기 (클리핑 처리)
        ctx.save();
        if(State.overlay === 'polaroid' || State.overlay === 'instagram') {
            ctx.beginPath();
            // 실제 이미지가 들어갈 영역만 클리핑
            let clipY = (State.overlay === 'polaroid') ? w * 0.04 * 1.2 : w * 0.05;
            let clipH = h - clipY - ((State.overlay === 'polaroid') ? w * 0.04 * 5.0 : w * 0.15);
            ctx.rect(0, clipY, w, clipH);
            ctx.clip();
        }
        ctx.drawImage(img, drawX, drawY, drawW, drawH);
        ctx.restore();
        ctx.shadowColor = "transparent";

        // 3. Overlays (High-Fidelity Port)
        const m = State.metadata;
        if(State.overlay === 'polaroid') {
            drawPolaroidOverlay(ctx, w, h, m.title, `${m.camera} • ${m.lens} • ${m.fstop} • ${m.shutter} • ${m.iso}`, m.author);
        } else if(State.overlay === 'instagram') {
            const left = [m.camera, m.lens].filter(Boolean).join(' | ');
            const right = [m.fstop, m.shutter, m.iso].filter(Boolean).join(' | ');
            drawInstagramOverlay(ctx, w, h, left, right, m.author);
        } else if(State.overlay === 'viewfinder') {
            drawViewfinderOverlay(ctx, w, h, m.fstop, m.shutter, m.iso.replace('ISO ', ''), m.author);
        }

        // 4. Grid (이제 오버레이보다 나중에, 정확한 이미지 영역에 맞춰 그려집니다)
        if(State.showGrid) {
            drawGrid(ctx, drawX, drawY, drawW, drawH);
        }
    };


    // --- High-Fidelity Overlay Drawing Functions ---
    
    // 💡 이미지 영역(x, y, w, h)에 딱 맞춰 그려지도록 수정
    const drawGrid = (ctx, x, y, w, h) => {
        ctx.strokeStyle = "rgba(255, 255, 255, 0.6)";
        ctx.lineWidth = Math.max(1.5, w * 0.002);
        ctx.shadowColor = "transparent";
        ctx.beginPath();
        ctx.moveTo(x + w / 3, y); ctx.lineTo(x + w / 3, y + h);
        ctx.moveTo(x + (w * 2) / 3, y); ctx.lineTo(x + (w * 2) / 3, y + h);
        ctx.moveTo(x, y + h / 3); ctx.lineTo(x + w, y + h / 3);
        ctx.moveTo(x, y + (h * 2) / 3); ctx.lineTo(x + w, y + (h * 2) / 3);
        ctx.stroke();
    };

    const drawPolaroidOverlay = (ctx, w, h, title, meta, author) => {
        const marginX = w * 0.04; const marginTop = marginX * 1.2; const marginBottom = marginX * 5.0;
        
        // 테두리 라인
        ctx.strokeStyle = "rgba(0,0,0,0.08)"; ctx.lineWidth = 1;
        ctx.strokeRect(marginX, marginTop, w - marginX*2, h - marginTop - marginBottom);

        const textY = h - marginBottom + (marginBottom * 0.35);
        const titleFontSize = Math.max(30, w * 0.025);
        const metaFontSize = Math.max(18, w * 0.014);
        
        ctx.textAlign = "center"; ctx.fillStyle = "#222222";
        ctx.font = `bold ${titleFontSize}px Pretendard, serif`;
        ctx.fillText(title || 'Untitled', w / 2, textY);

        ctx.fillStyle = "#777777"; ctx.font = `400 ${metaFontSize}px Pretendard, monospace`;
        ctx.fillText(meta, w / 2, textY + titleFontSize * 1.4);

        ctx.fillStyle = "#999999"; ctx.font = `italic 300 ${metaFontSize * 0.8}px Pretendard, serif`;
        ctx.fillText(author, w / 2, textY + titleFontSize * 1.4 + metaFontSize * 1.5);

        // Dummy QR for Preview
        const qrSize = marginX * 1.5;
        const qr = new QRious({ value: window.location.origin, size: qrSize, level: 'M' });
        const qrImg = new Image(); qrImg.src = qr.toDataURL();
        ctx.globalAlpha = 0.8;
        ctx.drawImage(qrImg, w - marginX - qrSize, h - marginX - qrSize, qrSize, qrSize);
        ctx.globalAlpha = 1.0;
    };

    const drawInstagramOverlay = (ctx, w, h, leftText, rightText, author) => {
        const marginX = w * 0.05; const marginBottom = w * 0.15;
        const textY = h - marginBottom + (marginBottom * 0.4);
        const fontSize = Math.max(18, w * 0.016);

        ctx.textBaseline = "middle";
        ctx.fillStyle = "#333333"; ctx.textAlign = "left";
        ctx.font = `600 ${fontSize}px Pretendard, sans-serif`;
        ctx.fillText(leftText || 'Camera Info', marginX, textY);

        ctx.fillStyle = "#888888"; ctx.font = `400 ${fontSize * 0.85}px Pretendard, sans-serif`;
        ctx.fillText(author, marginX, textY + fontSize * 1.5);

        const qrSize = marginBottom * 0.5;
        const qrX = w - marginX - qrSize;
        const qrY = h - marginBottom + (marginBottom - qrSize) / 2;

        ctx.fillStyle = "#333333"; ctx.textAlign = "right";
        ctx.font = `400 ${fontSize}px Pretendard, sans-serif`;
        ctx.fillText(rightText || 'Settings Info', qrX - (marginX * 0.5), qrY + (qrSize / 2));

        const qr = new QRious({ value: window.location.origin, size: qrSize, level: 'M' });
        const qrImg = new Image(); qrImg.src = qr.toDataURL();
        ctx.globalAlpha = 0.8;
        ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);
        ctx.globalAlpha = 1.0;
    };

    const drawViewfinderOverlay = (ctx, w, h, fstop, shutter, iso, author) => {
        const padX = w * 0.03; const padY = h * 0.04;
        const fontSize = Math.max(16, w * 0.018); const fontSmall = fontSize * 0.7; const fontLarge = fontSize * 1.4;
        
        ctx.shadowColor = "rgba(0, 0, 0, 0.9)"; ctx.shadowBlur = Math.max(3, w * 0.003); ctx.shadowOffsetX = 1; ctx.shadowOffsetY = 1;
        ctx.fillStyle = "#FFFFFF"; ctx.strokeStyle = "#FFFFFF"; ctx.lineWidth = Math.max(1.5, w * 0.0015);
        
        // Top Row
        ctx.textAlign = "left"; ctx.textBaseline = "middle"; const topY = padY + fontLarge * 0.5;
        ctx.font = `bold ${fontLarge}px Arial, sans-serif`; ctx.fillText("P", padX, topY);
        ctx.font = `bold ${fontSize}px Arial, sans-serif`;
        const sBoxX = padX + fontLarge * 2.0; ctx.strokeRect(sBoxX, topY - fontSize * 0.75, fontSize * 1.5, fontSize * 1.5);
        ctx.fillText("S", sBoxX + fontSize * 0.35, topY);
        const afcX = sBoxX + fontSize * 2.8; ctx.fillText("AF-C", afcX, topY);
        const meterX = afcX + fontSize * 4.5; ctx.strokeRect(meterX, topY - fontSize * 0.6, fontSize * 1.8, fontSize * 1.2);
        ctx.beginPath(); ctx.arc(meterX + fontSize * 0.9, topY, fontSize * 0.3, 0, Math.PI * 2); ctx.stroke();

        ctx.textAlign = "right"; const rightX = w - padX;
        ctx.font = `bold ${fontSize}px Arial, sans-serif`; ctx.fillText("FX", rightX, topY);
        ctx.font = `bold ${fontSmall}px Arial, sans-serif`; const normX = rightX - fontSize * 2.8; ctx.fillText("NORM", normX, topY + fontSize * 0.15);
        ctx.font = `bold ${fontSize}px Arial, sans-serif`; const autoX = normX - fontSize * 3.0; ctx.fillText("AUTO", autoX, topY);
        ctx.font = `bold ${fontSmall}px Arial, sans-serif`; const wbX = autoX - fontSize * 3.8; ctx.fillText("WB", wbX, topY + fontSize * 0.15);

        // Bottom Row
        ctx.textBaseline = "alphabetic"; const bottomY = h - padY; const cx = w / 2;
        ctx.textAlign = "left"; const iconH = fontSize * 1.5; const iconY = bottomY - iconH + (fontSize * 0.2);
        ctx.strokeRect(padX, iconY, fontSize * 2.0, iconH); ctx.beginPath(); ctx.arc(padX + fontSize * 1.0, iconY + iconH/2, fontSize * 0.4, 0, Math.PI * 2); ctx.fill();
        
        ctx.textAlign = "center"; ctx.font = `bold ${fontSize * 1.2}px Arial, sans-serif`;
        ctx.fillText(shutter || '---', cx - fontSize * 6.5, bottomY);
        ctx.fillText(fstop || 'F--', cx - fontSize * 1.0, bottomY);
        
        ctx.shadowColor = "transparent";
        const isoBoxX = cx + fontSize * 3.5; const isoBoxH = fontSize * 1.8; const isoBoxY = bottomY - isoBoxH + (fontSize * 0.2); const isoBoxW = fontSize * 6.0;
        ctx.fillStyle = "rgba(20, 20, 20, 0.85)"; ctx.fillRect(isoBoxX, isoBoxY, isoBoxW, isoBoxH);
        ctx.fillStyle = "#FFFFFF"; ctx.textAlign = "left"; ctx.font = `bold ${fontSize * 0.6}px Arial, sans-serif`; ctx.textBaseline = "middle";
        ctx.fillText("ISO", isoBoxX + fontSize * 0.5, isoBoxY + isoBoxH * 0.35); ctx.fillText("AUTO", isoBoxX + fontSize * 0.5, isoBoxY + isoBoxH * 0.7);
        ctx.font = `bold ${fontSize * 1.2}px Arial, sans-serif`; ctx.fillText(iso || '---', isoBoxX + fontSize * 2.2, isoBoxY + isoBoxH * 0.55);

        ctx.textBaseline = "alphabetic"; ctx.shadowColor = "rgba(0, 0, 0, 0.9)"; ctx.textAlign = "right";
        ctx.font = `bold ${fontSize}px Arial, sans-serif`; ctx.fillText("[ 8.0k ]", rightX, bottomY);

        const iBoxY = bottomY - fontSize * 5.0;
        ctx.font = `italic bold ${fontSize * 1.2}px "Times New Roman", serif`; ctx.strokeRect(rightX - fontSize * 1.5, iBoxY, fontSize * 1.5, fontSize * 1.5);
        ctx.textBaseline = "middle"; ctx.fillText("i", rightX - fontSize * 0.5, iBoxY + fontSize * 0.75); ctx.textBaseline = "alphabetic";
        
        const batY = bottomY - fontSize * 3.0; ctx.lineWidth = Math.max(1.5, w * 0.0015);
        ctx.strokeRect(rightX - fontSize * 2.0, batY, fontSize * 1.8, fontSize * 0.8);
        ctx.fillRect(rightX - fontSize * 1.8, batY + fontSize * 0.15, fontSize * 0.8, fontSize * 0.5); ctx.fillRect(rightX - fontSize * 0.2, batY + fontSize * 0.2, fontSize * 0.2, fontSize * 0.4);

        ctx.globalAlpha = 0.8; ctx.textAlign = "right"; ctx.textBaseline = "top";
        ctx.font = `italic 300 ${fontSmall * 1.2}px "Times New Roman", serif`;
        ctx.fillText(author, rightX, padY + fontLarge * 1.8); ctx.globalAlpha = 1.0;
    };

    // --- Export ---
    DOM.btnExport.addEventListener('click', () => {
        const link = document.createElement('a');
        link.download = `kkyoung_frame_${State.metadata.title || 'export'}_${Date.now()}.jpg`;
        link.href = DOM.canvas.toDataURL("image/jpeg", 1.0);
        link.click();
        if(window.showToast) window.showToast('이미지가 성공적으로 저장되었습니다!');
    });

});
</script>
@endsection