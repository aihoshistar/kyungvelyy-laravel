{{-- resources/views/admin/upload.blade.php --}}

@extends('layouts.admin')

@section('title', 'STUDIO UPLOAD')
@section('header_title', 'STUDIO UPLOAD')

@section('styles')
<style>
    #log-container::-webkit-scrollbar { width: 6px; }
    #log-container::-webkit-scrollbar-thumb { background: #333; border-radius: 3px; }
</style>
@endsection

@section('content')
<!-- Main Content -->
<div class="flex-grow flex flex-col md:flex-row w-full max-w-7xl mx-auto p-6 md:p-10 gap-8 overflow-hidden bg-[#050505]">

    <!-- Left: Upload Controller -->
    <!-- h-full과 overflow-y-auto를 주어 왼쪽 패널이 길어져도 자체적으로 스크롤되게 합니다 -->
    <div class="w-full md:w-1/3 flex flex-col gap-6 overflow-y-auto custom-scrollbar pr-2 pb-10 md:pb-0 h-full">

        <!-- [추가] 기존 앨범에 이어 올리기 위한 Post ID 입력란 -->
        <div class="bg-[#111] border border-white/10 rounded-2xl p-6 shrink-0">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Attach to Existing Album (Optional)</label>
            <input type="number" id="existing-post-id" placeholder="e.g. 10" class="w-full bg-black border border-white/20 rounded-lg px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500 transition placeholder-gray-700">
            <p class="text-xs text-gray-500 mt-2">끊겼던 작업이나 기존 앨범에 사진을 추가하려면 해당 Post ID를 입력하세요. 비워두면 새 앨범이 생성됩니다.</p>
        </div>

        <div class="bg-[#111] border border-white/10 rounded-2xl p-6 shrink-0">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Location Hint</label>
            <input type="text" id="location-hint" value="서울특별시 중구 을지로 (힙지로)" class="w-full bg-black border border-white/20 rounded-lg px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500 transition">
            <p class="text-xs text-gray-500 mt-2">Gemini AI가 이 텍스트를 기반으로 위치와 설명을 작성합니다.</p>
        </div>

        <div class="bg-[#111] border border-white/10 rounded-2xl p-6 flex-grow flex flex-col min-h-[300px]">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Select Photos (JPEG/RAW)</label>
            <div id="drop-zone" class="border-2 border-dashed border-white/20 rounded-xl flex-grow flex flex-col items-center justify-center p-8 text-center hover:bg-white/5 hover:border-blue-500/50 transition cursor-pointer min-h-[150px]">
                <svg class="w-10 h-10 text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                <span class="text-sm font-medium text-gray-300">Click or Drop photos here</span>
                <input type="file" id="file-input" multiple accept="image/jpeg, image/png, image/jpg" class="hidden">
            </div>
            <button id="upload-btn" class="w-full mt-4 bg-white text-black font-bold py-3 rounded-lg opacity-50 cursor-not-allowed transition" disabled>START BATCH UPLOAD</button>
        </div>
    </div>

    <!-- Right: Terminal Logger -->
    <!-- flex-grow와 h-full로 남은 공간을 꽉 채웁니다 -->
    <div class="w-full md:w-2/3 bg-[#0a0a0a] border border-white/10 rounded-2xl flex flex-col overflow-hidden shadow-2xl h-full">
        <div class="bg-[#111] border-b border-white/10 px-4 py-3 flex items-center gap-2 shrink-0">
            <div class="w-3 h-3 rounded-full bg-red-500/20"></div>
            <div class="w-3 h-3 rounded-full bg-yellow-500/20"></div>
            <div class="w-3 h-3 rounded-full bg-green-500/20"></div>
            <span class="ml-2 text-[10px] font-mono text-gray-500 tracking-widest uppercase">Process Terminal</span>
        </div>
        <!-- 로그 컨테이너가 자체적으로 스크롤되도록 flex-grow 설정 -->
        <div id="log-container" class="p-6 font-mono text-sm flex-grow overflow-y-auto space-y-2">
            <div class="text-gray-500">System Ready. Waiting for files...</div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('drop-zone');
    const uploadBtn = document.getElementById('upload-btn');
    const logContainer = document.getElementById('log-container');
    const locationHint = document.getElementById('location-hint');
    const existingPostIdInput = document.getElementById('existing-post-id');
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
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('border-blue-500/50', 'bg-white/5'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-blue-500/50', 'bg-white/5'));
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault(); dropZone.classList.remove('border-blue-500/50', 'bg-white/5');
        selectedFiles = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
        updateUI();
    });

    uploadBtn.addEventListener('click', async () => {
        uploadBtn.setAttribute('disabled', 'true');
        uploadBtn.textContent = 'PROCESSING...';

        currentPostId = existingPostIdInput.value.trim() || null;

        if (currentPostId) {
            log(`\n🚀 Starting batch upload (Attaching to Post ID: ${currentPostId})...`, 'info');
        } else {
            log(`\n🚀 Starting batch upload process (Creating New Album)...`, 'info');
        }

        for (let i = 0; i < selectedFiles.length; i++) {
            const file = selectedFiles[i];
            log(`[${i+1}/${selectedFiles.length}] Uploading ${file.name}...`);

            let uploadSuccess = false;
            let retryCount = 0;
            let currentWaitTime = 30000;

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
                        log(`   -> Title: ${data.data.title?.ko || 'Untitiled'}`);
                        uploadSuccess = true;
                    } else {
                        const errorMsg = data.error || 'Unknown error';

                        if (errorMsg.includes('Quota') || errorMsg.includes('retry') || errorMsg.includes('429')) {
                            retryCount++;
                            log(`⚠️ Rate Limit Hit! API 허용량 초과. ${currentWaitTime / 1000}초 대기 후 재시도합니다... (${retryCount}번째 재시도)`, 'warn');

                            await new Promise(r => setTimeout(r, currentWaitTime));
                            currentWaitTime = Math.min(currentWaitTime * 2, 1800000);
                        } else {
                            log(`❌ Error: ${errorMsg}`, 'error');
                            break;
                        }
                    }
                } catch (error) {
                    log(`❌ Request failed: ${error.message}`, 'error');
                    break;
                }
            }

            if (uploadSuccess && i < selectedFiles.length - 1) {
                log('⏳ Waiting 20s to avoid Rate Limit...', 'warn');
                await new Promise(r => setTimeout(r, 20000));
            }
        }

        log('\n✨ Batch processing completed.', 'success');

        // Show Toast (Using common layout function)
        if (window.showToast) window.showToast('모든 작업이 완료되었습니다!', false);

        currentPostId = null;
        existingPostIdInput.value = '';

        uploadBtn.textContent = 'UPLOAD COMPLETED';
        setTimeout(() => { uploadBtn.textContent = 'START BATCH UPLOAD'; updateUI(); }, 3000);
        selectedFiles = [];
        fileInput.value = '';
    });
});
</script>
@endsection
