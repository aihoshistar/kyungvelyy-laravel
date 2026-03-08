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

    extractPalette: (imgEl) => {
        const defaultColors = [
            { raw: '#4B5563', glow: 'rgba(75, 85, 99, 0.2)' }, { raw: '#374151', glow: 'rgba(55, 65, 81, 0.2)' },
            { raw: '#1F2937', glow: 'rgba(31, 41, 55, 0.2)' }, { raw: '#111827', glow: 'rgba(17, 24, 39, 0.2)' }
        ];
        if (!imgEl.complete || imgEl.naturalWidth === 0) return defaultColors;

        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            canvas.width = 50; canvas.height = 50;
            ctx.drawImage(imgEl, 0, 0, 50, 50);

            const data = ctx.getImageData(0, 0, 50, 50).data;
            const colorCounts = {};

            for (let i = 0; i < data.length; i += 16) {
                const r = Math.round(data[i] / 10) * 10;
                const g = Math.round(data[i+1] / 10) * 10;
                const b = Math.round(data[i+2] / 10) * 10;
                if(data[i+3] < 255) continue;
                const rgb = `${r},${g},${b}`;
                colorCounts[rgb] = (colorCounts[rgb] || 0) + 1;
            }

            const sortedColors = Object.entries(colorCounts)
                .sort((a, b) => b[1] - a[1])
                .map(entry => {
                    const [r, g, b] = entry[0].split(',').map(Number);
                    return { r, g, b };
                });

            const finalColors = [];
            const minDistance = 50;
            const getDistance = (c1, c2) => Math.sqrt(Math.pow(c1.r - c2.r, 2) + Math.pow(c1.g - c2.g, 2) + Math.pow(c1.b - c2.b, 2));

            for (const color of sortedColors) {
                if (finalColors.length >= 4) break;
                let isDistinct = true;
                for (const existingColor of finalColors) {
                    if (getDistance(color, existingColor) < minDistance) { isDistinct = false; break; }
                }
                if (isDistinct) finalColors.push(color);
            }

            while(finalColors.length < 4 && sortedColors.length > finalColors.length) {
                finalColors.push(sortedColors[finalColors.length]);
            }

            return finalColors.map(c => ({
                raw: `rgb(${c.r}, ${c.g}, ${c.b})`,
                glow: `rgba(${c.r}, ${c.g}, ${c.b}, 0.25)`
            }));
        } catch(e) { return defaultColors; }
    },

    applyColorFeatures: (imgEl) => {
        const colors = Utils.extractPalette(imgEl);
        const paletteContainer = document.getElementById('color-palette-container');
        if (paletteContainer && colors && colors.length > 0) {
            paletteContainer.innerHTML = colors.map(c =>
                `<div class="w-8 h-4 rounded-sm shadow-sm border border-white/10 transition-colors duration-500" style="background-color: ${c.raw};"></div>`
            ).join('');

            DOM.modalContainer.style.background = `
                radial-gradient(circle at 10% 10%, ${colors[0]?.glow || 'transparent'} 0%, transparent 50%),
                radial-gradient(circle at 90% 10%, ${colors[1]?.glow || 'transparent'} 0%, transparent 50%),
                radial-gradient(circle at 10% 90%, ${colors[2]?.glow || 'transparent'} 0%, transparent 50%),
                radial-gradient(circle at 90% 90%, ${colors[3]?.glow || 'transparent'} 0%, transparent 50%),
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