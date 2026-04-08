// resources/js/portfolio/utils.js

import { State, DOM } from './core.js';
import { ModalViewer } from './components.js';

export const Utils = {
    getTrans: (obj) => {
        if (!obj) return '';
        if (typeof obj === 'string') return obj;
        return obj[State.lang] || obj['ko'] || obj['en'] || '';
    },

    showToast: (message) => {
        DOM.toast.textContent = message; 
        DOM.toast.classList.remove('opacity-0', 'translate-y-10');
        setTimeout(() => DOM.toast.classList.add('opacity-0', 'translate-y-10'), 3000);
    },

    // 💡 [핵심] K-Means 알고리즘을 이용한 3가지 색상 완벽 추출 (60-30-10 법칙 분석용)
    extractPalette: (imgEl) => {
        const defaultColors = [
            { raw: '#4B5563', glow: 'rgba(75, 85, 99, 0.2)', ratio: 60, role: 'Dominant', displayRatio: 60 }, 
            { raw: '#374151', glow: 'rgba(55, 65, 81, 0.2)', ratio: 30, role: 'Secondary', displayRatio: 30 },
            { raw: '#1F2937', glow: 'rgba(31, 41, 55, 0.2)', ratio: 10, role: 'Accent', displayRatio: 10 }
        ];
        
        if (!imgEl.complete || imgEl.naturalWidth === 0) return defaultColors;

        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            
            // 성능을 위해 50x50 해상도로 다운샘플링하여 연산 (총 2,500픽셀)
            canvas.width = 50; canvas.height = 50;
            ctx.drawImage(imgEl, 0, 0, 50, 50);

            const data = ctx.getImageData(0, 0, 50, 50).data;
            const pixels = [];

            // 투명도가 있는 픽셀은 제외하고 RGB 수집
            for (let i = 0; i < data.length; i += 4) {
                if (data[i+3] >= 128) { 
                    pixels.push({ r: data[i], g: data[i+1], b: data[i+2] });
                }
            }

            if (pixels.length === 0) return defaultColors;

            const k = 3; // 3가지 대표 색상 (60-30-10 법칙 용도)
            let centroids = [];
            
            // 1. 초기 중심점 랜덤 선택
            for (let i = 0; i < k; i++) {
                centroids.push(pixels[Math.floor(Math.random() * pixels.length)]);
            }

            let clusters = Array.from({length: k}, () => []);
            const maxIter = 10; // 브라우저 렉 방지를 위해 최대 10회 반복 제한

            // 2. K-Means 반복 연산
            for (let iter = 0; iter < maxIter; iter++) {
                clusters = Array.from({length: k}, () => []);
                
                // 각 픽셀을 가장 가까운 중심점(Centroid) 군집에 할당
                for (const p of pixels) {
                    let minDist = Infinity;
                    let bestK = 0;
                    for (let i = 0; i < k; i++) {
                        const d = Math.sqrt(Math.pow(p.r - centroids[i].r, 2) + Math.pow(p.g - centroids[i].g, 2) + Math.pow(p.b - centroids[i].b, 2));
                        if (d < minDist) { minDist = d; bestK = i; }
                    }
                    clusters[bestK].push(p);
                }
                
                // 새로운 중심점 계산
                let moved = false;
                for (let i = 0; i < k; i++) {
                    if (clusters[i].length === 0) {
                        centroids[i] = pixels[Math.floor(Math.random() * pixels.length)];
                        moved = true;
                        continue;
                    }
                    let sumR = 0, sumG = 0, sumB = 0;
                    for (const p of clusters[i]) { sumR += p.r; sumG += p.g; sumB += p.b; }
                    const newR = Math.round(sumR / clusters[i].length);
                    const newG = Math.round(sumG / clusters[i].length);
                    const newB = Math.round(sumB / clusters[i].length);
                    
                    if (centroids[i].r !== newR || centroids[i].g !== newG || centroids[i].b !== newB) {
                        moved = true;
                        centroids[i] = { r: newR, g: newG, b: newB };
                    }
                }
                if (!moved) break; // 중심점이 더 이상 변하지 않으면 조기 종료
            }

            // 3. 비율(%) 계산 및 정렬
            const totalPixels = pixels.length;
            const roles = ['Dominant', 'Secondary', 'Accent'];
            const results = clusters.map((cluster, i) => {
                return {
                    color: centroids[i],
                    count: cluster.length,
                    ratio: (cluster.length / totalPixels) * 100
                };
            }).sort((a, b) => b.ratio - a.ratio); // 점유율 높은 순으로 정렬

            // 💡 UX 디테일: 합계가 무조건 100%가 되도록 소수점 오차 보정
            let roundedRatios = results.map(r => Math.round(r.ratio));
            let ratioSum = roundedRatios.reduce((a, b) => a + b, 0);
            if (ratioSum !== 100 && roundedRatios.length > 0) {
                roundedRatios[0] += (100 - ratioSum); // 주조색(Dominant)에 오차값 반영
            }

            return results.map((r, i) => ({
                raw: `rgb(${r.color.r}, ${r.color.g}, ${r.color.b})`,
                glow: `rgba(${r.color.r}, ${r.color.g}, ${r.color.b}, 0.25)`,
                ratio: r.ratio, // CSS width 용 (소수점 유지)
                displayRatio: roundedRatios[i], // Text 표시용 (정수, 합계 100%)
                role: roles[i] || 'Accent'
            }));

        } catch(e) { 
            return defaultColors; 
        }
    },

    applyColorFeatures: (imgEl) => {
        const colors = Utils.extractPalette(imgEl);
        const paletteContainer = document.getElementById('color-palette-container');
        
        if (paletteContainer && colors && colors.length > 0) {
            // 만약 군집이 부족할 경우 빈 데이터 추가 방어 코드
            while (colors.length < 3) colors.push({ raw: 'transparent', glow: 'transparent', ratio: 0, displayRatio: 0, role: '' });

            // 1. 100% 비율 바 (Progress Bar)
            const barHtml = `
                <div class="w-full h-1.5 flex rounded-full overflow-hidden mb-3 opacity-90 shadow-inner bg-gray-200 dark:bg-gray-800">
                    ${colors.filter(c => c.ratio > 0).map(c => `
                        <div style="width: ${c.ratio}%; background-color: ${c.raw};" class="transition-all duration-1000 ease-out"></div>
                    `).join('')}
                </div>
            `;

            // 2. 텍스트 라벨 (Dominant / Secondary / Accent)
            const itemsHtml = `
                <div class="flex flex-wrap gap-x-5 gap-y-2 mt-1">
                    ${colors.filter(c => c.ratio > 0).map((c, i) => `
                        <div class="flex items-center gap-2 group cursor-default">
                            <div class="w-3.5 h-3.5 rounded-full shadow-sm border border-white/20 transition-transform duration-300 group-hover:scale-110" style="background-color: ${c.raw};"></div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-mono font-medium theme-text-muted group-hover:theme-text transition-colors">${c.displayRatio}%</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;

            paletteContainer.className = 'w-full flex flex-col';
            paletteContainer.innerHTML = barHtml + itemsHtml;

            // 💡 3-Color Glow (주조색이 상단을 넓게 감싸고, 보조색/강조색이 하단 양쪽에서 받쳐주는 디자인)
            DOM.modalContainer.style.background = `
                radial-gradient(ellipse at 50% 0%, ${colors[0]?.glow || 'transparent'} 0%, transparent 60%),
                radial-gradient(circle at 0% 100%, ${colors[1]?.glow || 'transparent'} 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, ${colors[2]?.glow || 'transparent'} 0%, transparent 50%),
                var(--color-surface)
            `;
        }
    },

    checkUrlHash: () => {
        const hash = window.location.hash;
        if (hash.startsWith('#post-')) {
            const parts = hash.replace('#post-', '').split('/photo-');
            const postId = parseInt(parts[0], 10);
            const photoIndex = parts.length > 1 ? parseInt(parts[1], 10) : 0;
            const targetPost = State.posts.find(p => p.id == postId);
            if (targetPost) setTimeout(() => ModalViewer.open(targetPost, photoIndex), 500);
        }
    }
};