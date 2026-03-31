@extends('layouts.admin')

@section('title', 'MANAGE ARCHIVE')
@section('header_title', 'MANAGE ARCHIVE')

@section('content')
<!-- Main Content -->
<div class="flex-grow flex w-full overflow-hidden">
    
    <!-- Left Sidebar: Album List -->
    <div class="w-1/3 max-w-sm border-r border-white/10 bg-[#0a0a0a] flex flex-col shrink-0">
        <div class="p-4 border-b border-white/10 bg-[#111]">
            <input type="text" id="search-input" placeholder="앨범 검색..." class="w-full bg-black border border-white/20 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-blue-500 transition">
        </div>
        <div id="album-list" class="flex-grow overflow-y-auto custom-scrollbar p-2 space-y-2">
            <div class="text-center p-8 text-gray-500 text-sm animate-pulse">데이터를 불러오는 중입니다...</div>
        </div>
    </div>

    <!-- Right Content: Editor -->
    <div class="flex-grow bg-black flex flex-col overflow-hidden relative">
        <div id="editor-empty" class="absolute inset-0 flex items-center justify-center text-gray-600 font-medium tracking-widest uppercase">
            왼쪽에서 수정할 앨범을 선택하세요
        </div>
        
        <div id="editor-container" class="hidden flex-grow overflow-y-auto custom-scrollbar p-6 md:p-10 space-y-10 pb-32">
            <!-- Album Form -->
            <div class="bg-[#111] border border-white/10 rounded-2xl p-6 shadow-2xl">
                <div class="flex justify-between items-center mb-6 border-b border-white/10 pb-4">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                        앨범 메타데이터
                    </h2>
                    <div class="flex gap-2">
                        <button id="delete-album-btn" type="button" data-confirm="false" class="bg-red-500/10 text-red-400 border border-red-500/30 px-4 py-1.5 rounded-md text-xs font-bold tracking-widest hover:bg-red-500 hover:text-white transition">앨범 삭제</button>
                        <button id="save-album-btn" type="button" class="bg-white text-black px-4 py-1.5 rounded-md text-xs font-bold tracking-widest hover:bg-gray-200 transition">앨범 정보 저장</button>
                    </div>
                </div>
                <form id="album-form" class="space-y-4">
                    <input type="hidden" id="post-id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div><label class="block text-xs text-gray-500 mb-1">제목 (한국어)</label><input type="text" id="post-title-ko" class="w-full bg-black border border-white/20 rounded md px-3 py-2 text-sm text-white"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">제목 (영어)</label><input type="text" id="post-title-en" class="w-full bg-black border border-white/20 rounded md px-3 py-2 text-sm text-white"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">제목 (일본어)</label><input type="text" id="post-title-ja" class="w-full bg-black border border-white/20 rounded md px-3 py-2 text-sm text-white"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div><label class="block text-xs text-gray-500 mb-1">위치 (한국어)</label><input type="text" id="post-loc-ko" class="w-full bg-black border border-white/20 rounded md px-3 py-2 text-sm text-white"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">위치 (영어)</label><input type="text" id="post-loc-en" class="w-full bg-black border border-white/20 rounded md px-3 py-2 text-sm text-white"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">위치 (일본어)</label><input type="text" id="post-loc-ja" class="w-full bg-black border border-white/20 rounded md px-3 py-2 text-sm text-white"></div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div><label class="block text-xs text-gray-500 mb-1">카테고리</label><input type="text" id="post-category" class="w-full bg-black border border-white/20 rounded md px-3 py-2 text-sm text-white"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">국가</label><input type="text" id="post-country" class="w-full bg-black border border-white/20 rounded md px-3 py-2 text-sm text-white"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">촬영일</label><input type="date" id="post-date" class="w-full bg-black border border-white/20 rounded md px-3 py-2 text-sm text-white"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">위도 (Lat)</label><input type="text" id="post-lat" class="w-full bg-black border border-white/20 rounded md px-3 py-2 text-sm text-white font-mono"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">경도 (Lng)</label><input type="text" id="post-lng" class="w-full bg-black border border-white/20 rounded md px-3 py-2 text-sm text-white font-mono"></div>
                    </div>
                </form>
            </div>

            <!-- Photos Form List -->
            <div class="space-y-6" id="photos-container">
                <!-- Photos will be injected here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let allPosts = [];
    let activePost = null;
    let deleteConfirmTimeout = null;

    const DOM = {
        list: document.getElementById('album-list'), search: document.getElementById('search-input'),
        editorEmpty: document.getElementById('editor-empty'), editorContainer: document.getElementById('editor-container'),
        photosContainer: document.getElementById('photos-container'), 
        btnSaveAlbum: document.getElementById('save-album-btn'), btnDeleteAlbum: document.getElementById('delete-album-btn'),
        album: {
            id: document.getElementById('post-id'), cat: document.getElementById('post-category'), country: document.getElementById('post-country'),
            date: document.getElementById('post-date'), lat: document.getElementById('post-lat'), lng: document.getElementById('post-lng'),
            tKo: document.getElementById('post-title-ko'), tEn: document.getElementById('post-title-en'), tJa: document.getElementById('post-title-ja'),
            lKo: document.getElementById('post-loc-ko'), lEn: document.getElementById('post-loc-en'), lJa: document.getElementById('post-loc-ja')
        }
    };

    const fetchPosts = async () => {
        try {
            const res = await fetch('/admin/api/posts');
            allPosts = await res.json();
            renderList(allPosts);
        } catch (e) {
            window.showToast('데이터를 불러오는데 실패했습니다.', true);
        }
    };

    const renderList = (posts) => {
        DOM.list.innerHTML = '';
        posts.forEach(post => {
            const thumb = post.photos && post.photos.length > 0 ? post.photos[0].url : '';
            const title = post.title?.ko || post.title?.en || `앨범 #${post.id}`;
            
            const el = document.createElement('div');
            el.className = `flex items-center gap-3 p-3 rounded-xl cursor-pointer transition border border-transparent hover:bg-white/5 ${activePost && activePost.id === post.id ? 'bg-white/10 border-white/20' : ''}`;
            el.innerHTML = `
                <div class="w-12 h-12 rounded bg-gray-800 shrink-0 overflow-hidden"><img src="${thumb}" class="w-full h-full object-cover"></div>
                <div class="overflow-hidden">
                    <div class="text-sm font-bold text-white truncate">${title}</div>
                    <div class="text-xs text-gray-500 font-mono mt-0.5">ID: ${post.id} • ${post.photos?.length || 0} 장</div>
                </div>
            `;
            el.addEventListener('click', () => loadEditor(post));
            DOM.list.appendChild(el);
        });
    };

    const resetDeleteButton = () => {
        clearTimeout(deleteConfirmTimeout);
        DOM.btnDeleteAlbum.dataset.confirm = 'false';
        DOM.btnDeleteAlbum.textContent = '앨범 삭제';
        DOM.btnDeleteAlbum.classList.replace('bg-red-600', 'bg-red-500/10');
        DOM.btnDeleteAlbum.classList.replace('text-white', 'text-red-400');
    };

    const loadEditor = (post) => {
        activePost = post;
        renderList(allPosts); 
        resetDeleteButton();

        DOM.editorEmpty.classList.add('hidden');
        DOM.editorContainer.classList.remove('hidden');

        const formattedDate = post.taken_at ? post.taken_at.substring(0, 10) : '';

        // Set Album Data
        DOM.album.id.value = post.id;
        DOM.album.cat.value = post.category || '';
        DOM.album.country.value = post.country || '';
        DOM.album.date.value = formattedDate; 
        DOM.album.lat.value = post.lat || '';
        DOM.album.lng.value = post.lng || '';
        
        DOM.album.tKo.value = post.title?.ko || ''; DOM.album.tEn.value = post.title?.en || ''; DOM.album.tJa.value = post.title?.ja || '';
        DOM.album.lKo.value = post.location?.ko || ''; DOM.album.lEn.value = post.location?.en || ''; DOM.album.lJa.value = post.location?.ja || '';

        // Render Photos
        DOM.photosContainer.innerHTML = `<h2 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">앨범 내 사진 (${post.photos.length})</h2>`;
        
        post.photos.forEach((photo, idx) => {
            const pEl = document.createElement('div');
            pEl.className = "bg-[#111] border border-white/10 rounded-2xl p-6 flex flex-col xl:flex-row gap-6";
            pEl.innerHTML = `
                <div class="w-full xl:w-1/4 shrink-0 flex flex-col gap-2">
                    <div class="rounded-lg overflow-hidden border border-white/10 bg-black"><img src="${photo.url}" class="w-full h-auto object-contain"></div>
                    <div class="text-xs font-mono text-gray-500 break-all">${photo.url.split('/').pop()}</div>
                    <button onclick="savePhoto(${photo.id})" class="mt-auto w-full bg-blue-600 text-white py-2 rounded font-bold text-xs tracking-widest hover:bg-blue-500 transition">사진 정보 저장</button>
                </div>
                <div class="flex-grow space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div><label class="block text-xs text-gray-500 mb-1">제목 (한국어)</label><input type="text" id="pt-${photo.id}-ko" value="${photo.title?.ko || ''}" class="w-full bg-black border border-white/20 rounded px-3 py-1.5 text-sm text-white"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">제목 (영어)</label><input type="text" id="pt-${photo.id}-en" value="${photo.title?.en || ''}" class="w-full bg-black border border-white/20 rounded px-3 py-1.5 text-sm text-white"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">제목 (일본어)</label><input type="text" id="pt-${photo.id}-ja" value="${photo.title?.ja || ''}" class="w-full bg-black border border-white/20 rounded px-3 py-1.5 text-sm text-white"></div>
                    </div>
                    <div class="grid grid-cols-1 gap-2">
                        <div><label class="block text-xs text-gray-500 mb-1">설명 (한국어)</label><textarea id="pd-${photo.id}-ko" class="w-full bg-black border border-white/20 rounded px-3 py-1.5 text-sm text-white custom-scrollbar" rows="2">${photo.description?.ko || ''}</textarea></div>
                        <div><label class="block text-xs text-gray-500 mb-1">설명 (영어)</label><textarea id="pd-${photo.id}-en" class="w-full bg-black border border-white/20 rounded px-3 py-1.5 text-sm text-white custom-scrollbar" rows="2">${photo.description?.en || ''}</textarea></div>
                        <div><label class="block text-xs text-gray-500 mb-1">설명 (일본어)</label><textarea id="pd-${photo.id}-ja" class="w-full bg-black border border-white/20 rounded px-3 py-1.5 text-sm text-white custom-scrollbar" rows="2">${photo.description?.ja || ''}</textarea></div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 pt-2 border-t border-white/10">
                        <div class="md:col-span-2"><label class="block text-xs text-gray-500 mb-1">바디</label><input type="text" id="pe-${photo.id}-body" value="${photo.exif_body || ''}" class="w-full bg-black border border-white/20 rounded px-2 py-1 text-xs text-white"></div>
                        <div class="md:col-span-2"><label class="block text-xs text-gray-500 mb-1">렌즈</label><input type="text" id="pe-${photo.id}-lens" value="${photo.exif_lens || ''}" class="w-full bg-black border border-white/20 rounded px-2 py-1 text-xs text-white"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">초점거리 (mm)</label><input type="text" id="pe-${photo.id}-focal" value="${photo.exif_focal_length || ''}" class="w-full bg-black border border-white/20 rounded px-2 py-1 text-xs text-white font-mono"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">조리개</label><input type="text" id="pe-${photo.id}-aperture" value="${photo.exif_aperture || ''}" class="w-full bg-black border border-white/20 rounded px-2 py-1 text-xs text-white font-mono"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">셔터스피드 (분자)</label><input type="text" id="pe-${photo.id}-ssnum" value="${photo.exif_shutter_speed_num || ''}" class="w-full bg-black border border-white/20 rounded px-2 py-1 text-xs text-white font-mono"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">셔터스피드 (분모)</label><input type="text" id="pe-${photo.id}-ssden" value="${photo.exif_shutter_speed_den || ''}" class="w-full bg-black border border-white/20 rounded px-2 py-1 text-xs text-white font-mono"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">ISO</label><input type="text" id="pe-${photo.id}-iso" value="${photo.exif_iso || ''}" class="w-full bg-black border border-white/20 rounded px-2 py-1 text-xs text-white font-mono"></div>
                    </div>
                </div>
            `;
            DOM.photosContainer.appendChild(pEl);
        });
    };

    DOM.btnDeleteAlbum.addEventListener('click', async () => {
        const id = DOM.album.id.value;
        if(!id) return;

        if (DOM.btnDeleteAlbum.dataset.confirm !== 'true') {
            DOM.btnDeleteAlbum.dataset.confirm = 'true';
            DOM.btnDeleteAlbum.textContent = '정말 삭제하시겠습니까?';
            DOM.btnDeleteAlbum.classList.replace('bg-red-500/10', 'bg-red-600');
            DOM.btnDeleteAlbum.classList.replace('text-red-400', 'text-white');

            deleteConfirmTimeout = setTimeout(resetDeleteButton, 3000);
            return;
        }

        clearTimeout(deleteConfirmTimeout);
        DOM.btnDeleteAlbum.textContent = '삭제 중...';

        try {
            const res = await fetch(`/admin/api/posts/${id}`, {
                method: 'DELETE', 
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
            
            if(data.success) { 
                window.showToast('앨범이 성공적으로 삭제되었습니다.');
                
                DOM.editorContainer.classList.add('hidden');
                DOM.editorEmpty.classList.remove('hidden');
                activePost = null;
                fetchPosts(); 
            } else {
                window.showToast(data.error, true);
            }
        } catch(e) { 
            window.showToast('네트워크 오류가 발생했습니다.', true); 
        }
        
        resetDeleteButton();
    });

    DOM.btnSaveAlbum.addEventListener('click', async () => {
        const id = DOM.album.id.value;
        if(!id) return;
        DOM.btnSaveAlbum.textContent = '저장 중...';
        
        let safeDate = DOM.album.date.value;
        if (!safeDate) {
            safeDate = activePost.taken_at ? activePost.taken_at.substring(0, 10) : new Date().toISOString().split('T')[0];
        }

        const payload = {
            category: DOM.album.cat.value, 
            country: DOM.album.country.value, 
            taken_at: safeDate,
            lat: DOM.album.lat.value, 
            lng: DOM.album.lng.value,
            title: { ko: DOM.album.tKo.value, en: DOM.album.tEn.value, ja: DOM.album.tJa.value },
            location: { ko: DOM.album.lKo.value, en: DOM.album.lEn.value, ja: DOM.album.lJa.value }
        };

        try {
            const res = await fetch(`/admin/api/posts/${id}`, {
                method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if(data.success) { window.showToast('앨범 메타데이터가 저장되었습니다!'); fetchPosts(); }
            else window.showToast(data.error, true);
        } catch(e) { window.showToast('네트워크 오류가 발생했습니다.', true); }
        DOM.btnSaveAlbum.textContent = '앨범 정보 저장';
    });

    window.savePhoto = async (id) => {
        const payload = {
            title: { ko: document.getElementById(`pt-${id}-ko`).value, en: document.getElementById(`pt-${id}-en`).value, ja: document.getElementById(`pt-${id}-ja`).value },
            description: { ko: document.getElementById(`pd-${id}-ko`).value, en: document.getElementById(`pd-${id}-en`).value, ja: document.getElementById(`pd-${id}-ja`).value },
            exif_body: document.getElementById(`pe-${id}-body`).value, exif_lens: document.getElementById(`pe-${id}-lens`).value,
            exif_focal_length: document.getElementById(`pe-${id}-focal`).value, exif_aperture: document.getElementById(`pe-${id}-aperture`).value,
            exif_shutter_speed_num: document.getElementById(`pe-${id}-ssnum`).value, exif_shutter_speed_den: document.getElementById(`pe-${id}-ssden`).value,
            exif_iso: document.getElementById(`pe-${id}-iso`).value
        };

        try {
            const res = await fetch(`/admin/api/photos/${id}`, {
                method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if(data.success) window.showToast('사진 메타데이터가 저장되었습니다!');
            else window.showToast(data.error, true);
        } catch(e) { window.showToast('네트워크 오류가 발생했습니다.', true); }
    };

    DOM.search.addEventListener('input', (e) => {
        const val = e.target.value.toLowerCase();
        const filtered = allPosts.filter(p => {
            const t = p.title?.ko || p.title?.en || '';
            return t.toLowerCase().includes(val) || p.id.toString() === val;
        });
        renderList(filtered);
    });

    fetchPosts();
</script>
@endsection