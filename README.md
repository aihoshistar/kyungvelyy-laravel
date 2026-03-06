# 📸 KKYOUNG.PHOTO - High-End Portfolio

> "구조를 설계하고, 균형을 기록합니다." 블록체인 인프라 엔지니어이자 사진작가인 Park Kyoung Seo의 공식 포트폴리오 웹 애플리케이션입니다.

## ✨ Core Features (현재 구현된 핵심 기능)

일반적인 정적 갤러리를 넘어, 사용자에게 **앱(App) 수준의 몰입감 있는 경험**을 제공하기 위해 다양한 하이엔드 프론트엔드 기술이 적용되었습니다.

- **Dynamic Color Extraction**: 이미지의 핵심 색상(Palette)을 브라우저 캔버스에서 실시간으로 추출하여 모달 배경(Radial Gradient) 및 테마에 동적으로 반영합니다.
    
- **Progressive Blur-up Loading**: 핀터레스트 스타일의 스켈레톤 로딩 및 크로스 페이드 애니메이션을 적용하여 화면 깜빡임 없는 매끄러운 이미지 로딩 뷰를 제공합니다.
    
- **Hybrid Map Integration**: 사진에 포함된 위치 데이터(Lat/Lng)를 기반으로, 국내 접속 시 카카오맵, 해외 접속 시 구글맵을 자동으로 전환하여 렌더링하는 커스텀 마커 맵을 지원합니다.
    
- **Deep Linking**: 사진 모달 오픈 시 URL 해시(Hash)가 동적으로 변경되며, 링크를 통해 특정 사진으로 직접 접근할 수 있습니다.
    
- **Download with Custom Frame**: 현재 보고 있는 사진을 제목, EXIF 메타데이터, 그리고 해당 사진으로 이동하는 QR 코드가 포함된 '갤러리 프레임' 형태의 이미지로 브라우저 단에서 즉시 렌더링하여 다운로드할 수 있습니다.
    
- **Pro Shortcuts**: 개발자 감성을 담은 단축키(G: 그리드, M: 맵, D: 다크모드, F: 전체화면) 및 커스텀 도움말 오버레이(`?`)를 지원합니다.
    
- **Smooth Scrolling & Touch Swipe**: `Lenis`를 이용한 유체 스크롤링과 모바일 환경에서의 자연스러운 좌우 스와이프 액션을 구현했습니다.
    

## 🛠️ Tech Stack

- **Backend:** Laravel (Hostinger 환경)
    
- **Frontend:** Vanilla JS (ES6+), Tailwind CSS v4, Blade Template
    
- **Storage & CDN:** AWS S3 + CloudFront (CORS 설정 완비)
    
- **Libraries:** Lenis (Smooth Scroll), QRious (QR Code Generation), Kakao Map API, Google Map API
    

## 🚀 TODO & Future Roadmap (사진 데이터 확장 대비)

현재는 사진 데이터가 많지 않아 클라이언트(JS) 단에서 모든 데이터를 한 번에 로드하고 필터링하지만, 향후 수백~수천 장의 사진이 누적될 경우를 대비해 **서버 사이드 렌더링 및 AWS 최적화 구조**로 전환해야 합니다.

### Phase 1: 극강의 이미지 최적화 (True 1KB Loading)

- [ ] **AWS Serverless Image Handler 적용**: 원본 S3 이미지를 그대로 프론트엔드로 보내는 대신, CloudFront + Lambda@Edge 구조를 도입하여 요청 시점에 이미지를 리사이징(WebP/AVIF)하여 전송합니다.
    
- [ ] **진짜 Blur-up (LQIP) 구현**: 현재의 JS/CSS 눈속임 방식이 아닌, 백엔드에서 생성한 `Base64` 초소형(1KB) 썸네일 데이터를 DB에 함께 저장하고, HTML 렌더링 시점에 즉각적으로 보여주는 아키텍처로 변경합니다. (예: `?width=10&blur=10` 요청 활용)
    

### Phase 2: 데이터 페이징 및 백엔드 고도화

- [ ] **Server-side Pagination & Infinite Scroll**: 전체 `PORTFOLIO_DATA`를 한 번에 주입하는 대신, 라라벨의 API 리소스와 Vue/React 또는 Alpine.js를 활용하여 스크롤 시 데이터를 비동기로 추가 로딩(Chunking)하도록 아키텍처를 개편합니다.
    
- [ ] **EXIF Data Pipeline 구축**: 이미지를 S3에 업로드할 때 백엔드 큐(Queue)가 작동하여 EXIF 데이터와 위치 좌표를 자동으로 추출하고 DB에 매핑하는 자동화 파이프라인을 구축합니다.
    

### Phase 3: 분석 및 데이터 시각화

- [ ] **Gear & Stats Dashboard**: 축적된 EXIF 데이터를 기반으로 "가장 많이 사용한 렌즈", "주요 촬영 시간대", "월별 촬영 장수" 등을 차트로 시각화하는 `/stats` 전용 페이지를 구축합니다.
    
- [ ] **Search by Color**: 추출된 색상 데이터를 DB의 Vector 또는 JSON 형태로 인덱싱하여, 특정 색상(예: 파란색, 붉은색 노을)으로 사진을 검색하는 기능을 구현합니다.