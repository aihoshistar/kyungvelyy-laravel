import './bootstrap';
import './portfolio';

document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.Portfolio !== 'undefined') {
        window.Portfolio.init();
    }
});
