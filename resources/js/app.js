import './bootstrap';

// 기존의 단일 portfolio.js 파일 대신 분할된 core 모듈을 불러옵니다.
// (core.js 내부에서 다른 하위 모듈들을 모두 import 하고 있습니다)
import { init } from './portfolio/core.js';

// DOM이 완전히 로드된 후 포트폴리오 애플리케이션을 초기화합니다.
document.addEventListener('DOMContentLoaded', () => {
    // 특정 페이지(포트폴리오 메인)에서만 실행되도록 
    // 전역 변수 PORTFOLIO_DATA가 존재하는지 확인하는 방어 코드
    if (typeof window.PORTFOLIO_DATA !== 'undefined') {
        init();
    }
});