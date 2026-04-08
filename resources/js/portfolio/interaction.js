import { State, DOM } from './core.js';
import { Utils } from './utils.js';

export const Interaction = {
    updateReactionUI: () => {
        if (!State.activePost) return;
        const photo = State.activePost.photos[State.currentPhotoIndex];
        if (!photo) return;
        
        const container = document.getElementById('reactions-container');
        if (!container) return;

        const reactions = [{ key: 'love', icon: '❤️' }, { key: 'fire', icon: '🔥' }, { key: 'inspired', icon: '✨' }, { key: 'calm', icon: '🧘' }];
        
        container.innerHTML = reactions.map(r => {
            const count = (photo.reaction_counts && photo.reaction_counts[r.key]) || 0;
            const isActive = localStorage.getItem(`reacted_${photo.id}_${r.key}`);
            return `<button onclick="window.Portfolio.handleReaction('${r.key}', ${photo.id})" class="reaction-btn flex items-center space-x-2 px-4 py-2 rounded-full border theme-border theme-surface ${isActive ? 'active' : ''}"><span class="text-lg">${r.icon}</span><span class="text-sm font-medium theme-text-muted transition-colors">${count}</span></button>`;
        }).join('');
    },

    fetchReactions: async (photoId) => {
        try {
            const response = await fetch(`/api/photos/${photoId}/reactions`, { headers: { 'Accept': 'application/json' } });
            if (response.ok) {
                const data = await response.json();
                const photo = State.activePost.photos[State.currentPhotoIndex];
                if (photo && photo.id === photoId) { 
                    photo.reaction_counts = data.counts; 
                    Interaction.updateReactionUI();
                }
            }
        } catch (e) { console.log('Reactions fetch skipped'); }
    },

    handleReaction: async (type, photoId) => {
        const storageKey = `reacted_${photoId}_${type}`;
        const hasReacted = localStorage.getItem(storageKey);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const photo = State.activePost.photos[State.currentPhotoIndex];
        if (!photo.reaction_counts) photo.reaction_counts = {};

        if (hasReacted) { photo.reaction_counts[type] = Math.max(0, (photo.reaction_counts[type] || 1) - 1); localStorage.removeItem(storageKey); }
        else { photo.reaction_counts[type] = (photo.reaction_counts[type] || 0) + 1; localStorage.setItem(storageKey, 'true'); }
        
        Interaction.updateReactionUI();

        try {
            const response = await fetch(`/api/photos/${photoId}/react`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ type: type, fingerprint: State.fingerprint })
            });
            if (response.ok) {
                const data = await response.json(); 
                photo.reaction_counts = data.counts; 
                Interaction.updateReactionUI();
            }
        } catch (e) { console.error('Reaction sync failed'); }
    },

    sharePhoto: () => {
        const photo = State.activePost.photos[State.currentPhotoIndex];
        const title = Utils.getTrans(photo.title) || Utils.getTrans(State.activePost.title);
        
        // 💡 [수정] 공유 버튼을 눌렀을 때 생성되는 URL도 Slug 기반(Clean URL)으로 생성
        const slugOrId = State.activePost.slug || State.activePost.id;
        const postUrl = `${window.location.origin}/${State.lang}/archive/${slugOrId}/${State.currentPhotoIndex + 1}`;

        if (navigator.share) { navigator.share({ title: `PHOTO - ${title}`, url: postUrl }); }
        else { navigator.clipboard.writeText(postUrl).then(() => Utils.showToast(window.I18N[State.lang].toastCopy || 'Link Copied!')); }
    }
};