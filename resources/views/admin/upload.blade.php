<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KKYOUNG.PHOTO | STUDIO UPLOAD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" />
    <style>
        body { font-family: 'Pretendard', sans-serif; background-color: #050505; color: #e5e5e5; }
        #log-container::-webkit-scrollbar { width: 6px; }
        #log-container::-webkit-scrollbar-thumb { background: #333; border-radius: 3px; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <nav class="border-b border-white/10 bg-black px-6 py-4 flex justify-between items-center z-10 relative">
        <h1 class="text-xl font-bold tracking-widest">STUDIO UPLOAD</h1>
        <a href="{{ url('/') }}" class="text-sm font-medium text-gray-400 hover:text-white transition">Exit to Site</a>
    </nav>

    <div class="flex-grow flex flex-col md:flex-row max-w-7xl mx-auto w-full p-6 gap-8">
        
        <!-- Left: Upload Controller -->
        <div class="w-full md:w-1/3 flex flex-col gap-6">
            <div class="bg-[#111] border border-white/10 rounded-2xl p-6">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Location Hint</label>
                <input type="text" id="location-hint" value="서울특별시 중구 을지로 (힙지로)" class="w-full bg-black border border-white/20 rounded-lg px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500 transition">
                <p class="text-xs text-gray-500 mt-2">Gemini AI가 이 텍스트를 기반으로 위치와 설명을 작성합니다.</p>
            </div>

            <div class="bg-[#111] border border-white/10 rounded-2xl p-6 flex-grow flex flex-col">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Select Photos (JPEG/RAW)</label>
                <div id="drop-zone" class="border-2 border-dashed border-white/20 rounded-xl flex-grow flex flex-col items-center justify-center p-8 text-center hover:bg-white/5 hover:border-white/40 transition cursor-pointer">
                    <svg class="w-10 h-10 text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span class="text-sm font-medium text-gray-300">Click or Drop photos here</span>
                    <input type="file" id="file-input" multiple accept="image/jpeg, image/png, image/jpg" class="hidden">
                </div>
                <button id="upload-btn" class="w-full mt-4 bg-white text-black font-bold py-3 rounded-lg opacity-50 cursor-not-allowed transition" disabled>START BATCH UPLOAD</button>
            </div>
        </div>

        <!-- Right: Terminal Logger -->
        <div class="w-full md:w-2/3 bg-black border border-white/10 rounded-2xl flex flex-col overflow-hidden shadow-2xl">
            <div class="bg-[#111] border-b border-white/10 px-4 py-3 flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-red-500/20"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-500/20"></div>
                <div class="w-3 h-3 rounded-full bg-green-500/20"></div>
                <span class="ml-2 text-xs font-mono text-gray-500 tracking-widest">Process Terminal</span>
            </div>
            <div id="log-container" class="p-6 font-mono text-sm h-[600px] overflow-y-auto space-y-2">
                <div class="text-gray-500">System Ready. Waiting for files...</div>
            </div>
        </div>

    </div>

    <script>
        const fileInput = document.getElementById('file-input');
        const dropZone = document.getElementById('drop-zone');
        const uploadBtn = document.getElementById('upload-btn');
        const logContainer = document.getElementById('log-container');
        const locationHint = document.getElementById('location-hint');
        let selectedFiles = [];

        let currentPostId = null;

        const log = (msg, type = 'info') => {
            const div = document.createElement('div');
            const time = new Date().toLocaleTimeString();
            let color = 'text-gray-300';
            if(type === 'success') color = 'text-green-400';
            if(type === 'error') color = 'text-red-400';
            if(type === 'warn') color = 'text-yellow-400';
            
            div.innerHTML = `<span class="text-gray-600 mr-2">[${time}]</span><span class="${color}">${msg}</span>`;
            logContainer.appendChild(div);
            logContainer.scrollTop = logContainer.scrollHeight;
        };

        const updateUI = () => {
            if (selectedFiles.length > 0) {
                dropZone.querySelector('span').textContent = `${selectedFiles.length} files selected`;
                uploadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                uploadBtn.removeAttribute('disabled');
                uploadBtn.classList.add('hover:bg-gray-200');
            } else {
                dropZone.querySelector('span').textContent = 'Click or Drop photos here';
                uploadBtn.classList.add('opacity-50', 'cursor-not-allowed');
                uploadBtn.setAttribute('disabled', 'true');
            }
        };

        dropZone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', (e) => { selectedFiles = Array.from(e.target.files); updateUI(); });
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('border-blue-500'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-blue-500'));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault(); dropZone.classList.remove('border-blue-500');
            selectedFiles = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            updateUI();
        });

        uploadBtn.addEventListener('click', async () => {
            uploadBtn.setAttribute('disabled', 'true');
            uploadBtn.textContent = 'PROCESSING...';
            
            log(`\n🚀 Starting batch upload process (${selectedFiles.length} files)...`, 'info');

            currentPostId = null;

            for (let i = 0; i < selectedFiles.length; i++) {
                const file = selectedFiles[i];
                log(`[${i+1}/${selectedFiles.length}] Uploading ${file.name}...`);
                
                let uploadSuccess = false;
                let retryCount = 0;
                let currentWaitTime = 30000; // 초기 대기 시간: 30초

                // 💡 [수정] maxRetries를 없애고 성공할 때까지 무한 반복
                while (!uploadSuccess) {
                    const formData = new FormData();
                    formData.append('photo', file);
                    formData.append('location_hint', locationHint.value);
                    
                    if (currentPostId) {
                        formData.append('post_id', currentPostId);
                    }

                    try {
                        const response = await fetch('/admin/upload-process', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            if (!currentPostId) {
                                currentPostId = data.post_id;
                                log(`✅ Album Created: [Post ID: ${currentPostId}]`, 'success');
                            } else {
                                log(`✅ Photo attached to Album [Post ID: ${currentPostId}]`, 'success');
                            }
                            log(`   -> Title: ${data.data.title.ko}`);
                            uploadSuccess = true; // 성공 시 while 루프 탈출
                        } else {
                            const errorMsg = data.error || 'Unknown error';
                            
                            if (errorMsg.includes('Quota') || errorMsg.includes('retry') || errorMsg.includes('429')) {
                                retryCount++;
                                log(`⚠️ Rate Limit Hit! API 허용량 초과. ${currentWaitTime / 1000}초 대기 후 재시도합니다... (${retryCount}번째 재시도)`, 'warn');
                                
                                // 현재 설정된 시간만큼 대기
                                await new Promise(r => setTimeout(r, currentWaitTime));
                                
                                // 💡 [핵심] 다음 실패를 대비해 대기 시간을 2배로 증가 (최대 30분까지만 증가하도록 제한)
                                currentWaitTime = Math.min(currentWaitTime * 2, 1800000); 
                            } else {
                                log(`❌ Error: ${errorMsg}`, 'error');
                                break; // API 제한이 아닌 다른 심각한 에러(S3 용량 초과 등)는 루프 탈출
                            }
                        }
                    } catch (error) {
                        log(`❌ Request failed: ${error.message}`, 'error');
                        break;
                    }
                }

                // 성공적으로 업로드되었고, 마지막 사진이 아니라면 기본 대기 시간(20초) 적용
                if (uploadSuccess && i < selectedFiles.length - 1) {
                    log('⏳ Waiting 20s to avoid Rate Limit...', 'warn');
                    await new Promise(r => setTimeout(r, 20000));
                }
            }

            log('\n✨ Batch processing completed.', 'success');
            
            currentPostId = null; 
            
            uploadBtn.textContent = 'UPLOAD COMPLETED';
            setTimeout(() => { uploadBtn.textContent = 'START BATCH UPLOAD'; updateUI(); }, 3000);
            selectedFiles = [];
            fileInput.value = '';
        });
    </script>
</body>
</html>