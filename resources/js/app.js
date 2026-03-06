import './bootstrap';
import './portfolio'; // 분리된 비즈니스 로직 임포트

document.addEventListener('DOMContentLoaded', () => {
    // window 객체에 할당된 모듈이 준비되면 초기화
    if (typeof window.Portfolio !== 'undefined') {
        window.Portfolio.init();
    }
});
