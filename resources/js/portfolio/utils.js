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

    // 💡 K-Means 알고리즘을 이용한 3가지 색상 완벽 추출 (60-30-10 법칙)
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
            
            canvas.width = 50; canvas.height = 50;
            ctx.drawImage(imgEl, 0, 0, 50, 50);

            const data = ctx.getImageData(0, 0, 50, 50).data;
            const pixels = [];

            for (let i = 0; i < data.length; i += 4) {
                if (data[i+3] >= 128) { 
                    pixels.push({ r: data[i], g: data[i+1], b: data[i+2] });
                }
            }

            if (pixels.length === 0) return defaultColors;

            const k = 3; 
            let centroids = [];
            
            for (let i = 0; i < k; i++) {
                centroids.push(pixels[Math.floor(Math.random() * pixels.length)]);
            }

            let clusters = Array.from({length: k}, () => []);
            const maxIter = 10;

            for (let iter = 0; iter < maxIter; iter++) {
                clusters = Array.from({length: k}, () => []);
                
                for (const p of pixels) {
                    let minDist = Infinity;
                    let bestK = 0;
                    for (let i = 0; i < k; i++) {
                        const d = Math.sqrt(Math.pow(p.r - centroids[i].r, 2) + Math.pow(p.g - centroids[i].g, 2) + Math.pow(p.b - centroids[i].b, 2));
                        if (d < minDist) { minDist = d; bestK = i; }
                    }
                    clusters[bestK].push(p);
                }
                
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
                if (!moved) break;
            }

            const totalPixels = pixels.length;
            const roles = ['Dominant', 'Secondary', 'Accent'];
            const results = clusters.map((cluster, i) => {
                return {
                    color: centroids[i],
                    count: cluster.length,
                    ratio: (cluster.length / totalPixels) * 100
                };
            }).sort((a, b) => b.ratio - a.ratio);

            let roundedRatios = results.map(r => Math.round(r.ratio));
            let ratioSum = roundedRatios.reduce((a, b) => a + b, 0);
            if (ratioSum !== 100 && roundedRatios.length > 0) {
                roundedRatios[0] += (100 - ratioSum);
            }

            return results.map((r, i) => ({
                raw: `rgb(${r.color.r}, ${r.color.g}, ${r.color.b})`,
                glow: `rgba(${r.color.r}, ${r.color.g}, ${r.color.b}, 0.25)`,
                ratio: r.ratio, 
                displayRatio: roundedRatios[i],
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
            while (colors.length < 3) colors.push({ raw: 'transparent', glow: 'transparent', ratio: 0, displayRatio: 0, role: '' });

            const barHtml = `
                <div class="w-full h-1.5 flex rounded-full overflow-hidden mb-3 opacity-90 shadow-inner bg-gray-200 dark:bg-gray-800">
                    ${colors.filter(c => c.ratio > 0).map(c => `
                        <div style="width: ${c.ratio}%; background-color: ${c.raw};" class="transition-all duration-1000 ease-out"></div>
                    `).join('')}
                </div>
            `;

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

            DOM.modalContainer.style.background = `
                radial-gradient(ellipse at 50% 0%, ${colors[0]?.glow || 'transparent'} 0%, transparent 60%),
                radial-gradient(circle at 0% 100%, ${colors[1]?.glow || 'transparent'} 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, ${colors[2]?.glow || 'transparent'} 0%, transparent 50%),
                var(--color-surface)
            `;
        }
    },

    // 💡 [추가] 새로운 Slug URL 파서: /ko/archive/slug/3 형태를 파싱하여 모달을 띄웁니다.
    checkDeepLink: () => {
        const path = window.location.pathname;
        const match = path.match(/\/archive\/([^/]+)(?:\/(\d+))?/);
        if (match) {
            const slug = match[1];
            // URL에서는 유저가 읽기 편하게 1번부터 시작하므로 인덱스는 -1 해줌
            const photoIndex = match[2] ? parseInt(match[2], 10) - 1 : 0; 
            
            // Slug로 포스트를 검색 (혹시 몰라 ID로도 Fallback 검색)
            const targetPost = State.posts.find(p => p.slug === slug || String(p.id) === slug);
            if (targetPost) {
                setTimeout(() => ModalViewer.open(targetPost, photoIndex), 500);
            }
        }
    }
};