<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKYOUNG.PHOTO | Park Kyoung Seo</title>
    <!-- Tailwind CSS CDN -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Google Fonts (Pretendard or Inter equivalent) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        /* 이미지 호버 효과를 위한 커스텀 클래스 */
        .photo-card:hover .photo-overlay {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-[#0a0a0a] text-gray-300 antialiased selection:bg-gray-800 selection:text-white">

    <!-- Navigation -->
    <nav class="sticky top-0 z-50 backdrop-blur-md bg-[#0a0a0a]/80 border-b border-gray-800/50">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="#" class="text-white font-bold tracking-wider text-lg">KKYOUNG.PHOTO</a>
            <div class="flex items-center space-x-6 text-sm">
                <a href="#" class="hover:text-white transition">Profile</a>
                <a href="#gallery" class="hover:text-white transition">Gallery</a>
                <div class="flex space-x-2 text-xs text-gray-500">
                    <button class="hover:text-white transition">KO</button>
                    <span>|</span>
                    <button class="hover:text-white transition">EN</button>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 pt-20 pb-24">


        <!-- Profile Header Section -->
        <header class="mb-24 max-w-3xl">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-3">
                Park Kyoung Seo
            </h1>
            <p class="text-lg md:text-xl text-gray-400 mb-8 font-light tracking-wide">
                Blockchain Infra Engineer & Photographer
            </p>

            <div class="space-y-6">
                <h2 class="text-2xl md:text-3xl font-medium text-white leading-snug">
                    구조를 설계하고,<br>균형을 기록합니다.
                </h2>
                <p class="text-gray-400 leading-relaxed max-w-2xl font-light">
                    개발자로 일하며 구조와 흐름을 설계하고, 여행지와 도시에서는 빛과 공간의 균형을 기록합니다. 사진은 2024년부터 취미로 시작했지만, 지금은 더 진지하게 다루고 있습니다.
                </p>
                <div class="flex items-center space-x-4 pt-4">
                    <span class="inline-flex items-center space-x-2 text-sm text-gray-400 bg-gray-900 px-3 py-1.5 rounded-full border border-gray-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Based in Seoul (강남 & 건대)</span>
                    </span>
                </div>
            </div>
        </header>

        <!-- My Gear Section -->
        <section class="mb-24">
            <h3 class="text-sm uppercase tracking-widest text-gray-500 mb-6 font-semibold">My Gear</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Camera 1 -->
                <div class="p-6 rounded-2xl bg-[#111] border border-gray-800/60 hover:border-gray-700 transition duration-300">
                    <h4 class="text-xl font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Nikon Zf
                    </h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center before:content-['·'] before:mr-2 before:text-gray-600">Z 40mm f/2</li>
                        <li class="flex items-center before:content-['·'] before:mr-2 before:text-gray-600">Z 24-70mm f/4 S</li>
                        <li class="flex items-center before:content-['·'] before:mr-2 before:text-gray-600">Z 180-600mm f/5.6-6.3 VR</li>
                        <li class="flex items-center before:content-['·'] before:mr-2 before:text-gray-600">Viltrox AF 75mm f/1.2 PRO</li>
                    </ul>
                </div>
                <!-- Camera 2 -->
                <div class="p-6 rounded-2xl bg-[#111] border border-gray-800/60 hover:border-gray-700 transition duration-300">
                    <h4 class="text-xl font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Sony α7 II
                    </h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center before:content-['·'] before:mr-2 before:text-gray-600">Brightin Star 35mm F1.7</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section id="gallery">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 space-y-4 md:space-y-0">
                <h3 class="text-sm uppercase tracking-widest text-gray-500 font-semibold">Photography</h3>

                <!-- Category Filter -->
                <div class="flex space-x-2 overflow-x-auto pb-2 md:pb-0">
                    <button class="px-4 py-1.5 rounded-full bg-white text-black text-sm font-medium">전체</button>
                    <button class="px-4 py-1.5 rounded-full bg-[#111] border border-gray-800 text-gray-400 hover:text-white text-sm transition">풍경 (Landscape)</button>
                    <button class="px-4 py-1.5 rounded-full bg-[#111] border border-gray-800 text-gray-400 hover:text-white text-sm transition">도시 (Cityscape)</button>
                </div>
            </div>

            <!-- Photo Grid -->
<!-- Photo Grid 내부를 이렇게 변경하세요 -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    @foreach($photos as $photo)
    <!-- json 타입인 images 컬럼의 첫 번째 이미지를 대표 이미지로 사용합니다. -->
    @php $firstImage = json_decode($photo->images)[0] ?? null; @endphp

    <div class="photo-card group relative rounded-xl overflow-hidden bg-[#111] border border-gray-800">
        <div class="aspect-[4/3] w-full overflow-hidden">
            @if($firstImage)
            <img src="{{ $firstImage->url }}"
                 alt="{{ json_decode($photo->title)->ko ?? 'Photo' }}"
                 class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700 ease-in-out">
            @endif
        </div>

        <div class="p-5 bg-[#0a0a0a]/95 border-t border-gray-800">
            <div class="flex justify-between items-start mb-3">
                <h4 class="text-white font-medium text-lg">{{ json_decode($photo->title)->ko ?? '' }}</h4>
                <span class="text-xs px-2 py-1 bg-gray-900 text-gray-400 rounded">{{ $photo->category }}</span>
            </div>

            @if($firstImage && isset($firstImage->exif))
            <div class="grid grid-cols-2 gap-y-2 gap-x-4 text-xs text-gray-500 mt-4">
                <div class="flex flex-col">
                    <span class="text-gray-600 mb-0.5">CAMERA</span>
                    <span class="text-gray-300 font-medium">{{ $firstImage->exif->body }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-gray-600 mb-0.5">LENS</span>
                    <span class="text-gray-300 font-medium">{{ $firstImage->exif->lens }}</span>
                </div>
                <div class="flex flex-col col-span-2 pt-2 border-t border-gray-800/50 mt-1 flex-row space-x-4">
                    <span><strong class="text-gray-300">{{ $firstImage->exif->focalLength }}</strong></span>
                    <span><strong class="text-gray-300">{{ $firstImage->exif->aperture }}</strong></span>
                    <span>ISO <strong class="text-gray-300">{{ $firstImage->exif->iso }}</strong></span>
                    <span><strong class="text-gray-300">{{ $firstImage->exif->shutterSpeed }}</strong></span>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-800/50 bg-[#0a0a0a] py-12">
        <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
            <div class="mb-4 md:mb-0">
                <p class="text-gray-400 font-medium mb-1">사람이 지나간 흔적이 남아 있는 공간을 좋아합니다.</p>
                <p>© 2026 Park Kyoung Seo. All rights reserved.</p>
            </div>
            <div class="flex items-center space-x-6">
                <a href="#" class="hover:text-white transition">GitHub</a>
                <a href="#" class="hover:text-white transition">Instagram</a>
                <span class="px-3 py-1 bg-gray-900 rounded-full text-xs">Built with Laravel & Tailwind</span>
            </div>
        </div>
    </footer>

</body>
</html>
