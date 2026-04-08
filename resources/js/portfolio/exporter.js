// resources/js/portfolio/exporter.js

import { State } from './core.js';
import { Utils } from './utils.js';

export const Exporter = {
    
    AUTHOR_INFO: '@kkyoung.photo',

    loadImage: (url) => {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = "Anonymous";
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = url;
        });
    },

    generateCanvas: async (post, photo, style) => {
        const img = await Exporter.loadImage(photo.url);
        
        // 💡 [수정] 모바일/Safari 캔버스 메모리 한계(약 16.77MP) 초과 방지
        const MAX_CANVAS_AREA = 16000000; 
        let scale = 1.0;
        let estimatedArea = img.width * img.height;

        if (style === 'polaroid' || style === 'instagram') estimatedArea *= 1.3;
        else if (style === 'nikon_lcd') estimatedArea *= 1.5;

        let sourceImg = img;
        if (estimatedArea > MAX_CANVAS_AREA) {
            scale = Math.sqrt(MAX_CANVAS_AREA / estimatedArea);
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = img.width * scale;
            tempCanvas.height = img.height * scale;
            tempCanvas.getContext('2d').drawImage(img, 0, 0, tempCanvas.width, tempCanvas.height);
            sourceImg = tempCanvas; 
        }

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        if (style === 'original') Exporter.drawOriginal(ctx, canvas, sourceImg);
        else if (style === 'instagram') await Exporter.drawInstagramStyle(ctx, canvas, sourceImg, post, photo);
        else if (style === 'polaroid') Exporter.drawPolaroidStyle(ctx, canvas, sourceImg, post, photo);
        else if (style === 'zf_viewfinder') Exporter.drawZfViewfinderStyle(ctx, canvas, sourceImg, post, photo);
        else if (style === 'nikon_lcd') Exporter.drawNikonLcdStyle(ctx, canvas, sourceImg, post, photo);
        else if (style === 'overlay') Exporter.drawCinematicOverlay(ctx, canvas, sourceImg, post, photo);

        return canvas;
    },

    getPreviewUrl: async (post, photo, style) => {
        try {
            const canvas = await Exporter.generateCanvas(post, photo, style);
            return canvas.toDataURL("image/jpeg", 0.85); // 뷰어용은 최적화를 위해 0.85 반환
        } catch (error) {
            console.error('Preview generation failed:', error);
            return photo.url;
        }
    },

    processDownload: async () => {
        const selectEl = document.getElementById('download-template-select');
        const style = selectEl ? selectEl.value : 'polaroid';
        
        const post = State.activePost;
        const photo = post.photos[State.currentPhotoIndex];
        
        Utils.showToast(window.I18N[State.lang].toastWait || 'Generating frame...');
        
        try {
            const canvas = await Exporter.generateCanvas(post, photo, style);
            const cleanSlug = post.slug ? post.slug.replace(/[^a-zA-Z0-9-]/g, '') : 'post';
            const filename = `kkyung_${style}_${cleanSlug}_${photo.id}.jpg`;
            
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL("image/jpeg", 1.0); // 다운로드는 화질 1.0 보장
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } catch (error) {
            console.error('Export failed:', error);
            alert('이미지 다운로드 중 오류가 발생했습니다.');
        }
    },

    // 💡 [수정] 캔버스에 직접 그리드를 그릴 때, 이미지의 offset 좌표를 기준으로 그림
    drawGridIfActive: (ctx, img, offsetX, offsetY) => {
        if (!State.isGridActive) return;
        ctx.strokeStyle = "rgba(255, 255, 255, 0.6)";
        ctx.lineWidth = Math.max(1.5, img.width * 0.002);
        ctx.shadowColor = "transparent";
        ctx.beginPath();
        ctx.moveTo(offsetX + img.width / 3, offsetY); ctx.lineTo(offsetX + img.width / 3, offsetY + img.height);
        ctx.moveTo(offsetX + (img.width * 2) / 3, offsetY); ctx.lineTo(offsetX + (img.width * 2) / 3, offsetY + img.height);
        ctx.moveTo(offsetX, offsetY + img.height / 3); ctx.lineTo(offsetX + img.width, offsetY + img.height / 3);
        ctx.moveTo(offsetX, offsetY + (img.height * 2) / 3); ctx.lineTo(offsetX + img.width, offsetY + (img.height * 2) / 3);
        ctx.stroke();
    },

    /* ==============================================================
     * 개별 프레임 렌더링 스타일 모음
     * ============================================================== */

    drawOriginal: (ctx, canvas, img) => {
        canvas.width = img.width; canvas.height = img.height;
        ctx.drawImage(img, 0, 0);
        Exporter.drawGridIfActive(ctx, img, 0, 0);
    },

    drawInstagramStyle: async (ctx, canvas, img, post, photo) => {
        return new Promise((resolve) => {
            const marginX = img.width * 0.05; const marginTop = marginX; const marginBottom = img.width * 0.15; 
            canvas.width = img.width + (marginX * 2); canvas.height = img.height + marginTop + marginBottom;
            
            ctx.fillStyle = "#FFFFFF"; ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.shadowColor = "rgba(0, 0, 0, 0.15)"; ctx.shadowBlur = Math.max(20, img.width * 0.015); ctx.shadowOffsetY = Math.max(10, img.width * 0.008);
            ctx.drawImage(img, marginX, marginTop, img.width, img.height); ctx.shadowColor = "transparent";

            const leftTextArr = []; if (photo.exif_body) leftTextArr.push(photo.exif_body); if (photo.exif_lens) leftTextArr.push(photo.exif_lens);
            const leftText = leftTextArr.join(' | ');

            const rightTextArr = [];
            if (photo.exif_focal_length) rightTextArr.push(`${photo.exif_focal_length}mm`);
            if (photo.exif_aperture) rightTextArr.push(`f/${parseFloat(photo.exif_aperture)}`);
            if (photo.exif_shutter_speed_num && photo.exif_shutter_speed_den) rightTextArr.push(`${photo.exif_shutter_speed_num}/${photo.exif_shutter_speed_den}s`);
            if (photo.exif_iso) rightTextArr.push(`ISO ${photo.exif_iso}`);
            const rightText = rightTextArr.join(' | ');

            const textY = marginTop + img.height + (marginBottom * 0.4); const fontSize = Math.max(18, img.width * 0.016);
            ctx.textBaseline = "middle";

            ctx.fillStyle = "#333333"; ctx.textAlign = "left"; ctx.font = `600 ${fontSize}px Pretendard, sans-serif`; ctx.fillText(leftText, marginX, textY);
            ctx.fillStyle = "#888888"; ctx.font = `400 ${fontSize * 0.85}px Pretendard, sans-serif`; ctx.fillText(Exporter.AUTHOR_INFO, marginX, textY + fontSize * 1.5);

            const qrSize = marginBottom * 0.5; const qrX = canvas.width - marginX - qrSize; const qrY = marginTop + img.height + (marginBottom - qrSize) / 2;
            ctx.fillStyle = "#333333"; ctx.textAlign = "right"; ctx.font = `400 ${fontSize}px Pretendard, sans-serif`; ctx.fillText(rightText, qrX - (marginX * 0.5), qrY + (qrSize / 2));

            const locale = window.location.pathname.split('/')[1] || 'ko'; const postUrl = `${window.location.origin}/${locale}#post-${post.id}/photo-${State.currentPhotoIndex}`;
            if (typeof QRious !== 'undefined') {
                const qr = new QRious({ value: postUrl, size: qrSize, level: 'M' }); const qrImg = new Image(); qrImg.src = qr.toDataURL();
                qrImg.onload = () => { ctx.globalAlpha = 0.8; ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize); ctx.globalAlpha = 1.0; Exporter.drawGridIfActive(ctx, img, marginX, marginTop); resolve(); };
            } else {
                Exporter.drawGridIfActive(ctx, img, marginX, marginTop); resolve();
            }
        });
    },

    drawPolaroidStyle: (ctx, canvas, img, post, photo) => {
        const marginX = img.width * 0.04; const marginTop = marginX * 1.2; const marginBottom = marginX * 5.0; 
        canvas.width = img.width + (marginX * 2); canvas.height = img.height + marginTop + marginBottom;
        
        ctx.fillStyle = "#F9F8F6"; ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, marginX, marginTop, img.width, img.height);
        ctx.strokeStyle = "rgba(0,0,0,0.08)"; ctx.lineWidth = 1; ctx.strokeRect(marginX, marginTop, img.width, img.height);

        const textY = marginTop + img.height + (marginBottom * 0.35);
        const titleFontSize = Math.max(30, canvas.width * 0.025); const metaFontSize = Math.max(18, canvas.width * 0.014);
        
        ctx.textAlign = "center"; ctx.fillStyle = "#222222"; ctx.font = `bold ${titleFontSize}px Pretendard, serif`;
        ctx.fillText(Utils.getTrans(photo.title) || Utils.getTrans(post.title) || 'Untitled', canvas.width / 2, textY);
        
        const exifArr = [];
        if (photo.exif_body) exifArr.push(photo.exif_body);
        if (photo.exif_lens) exifArr.push(photo.exif_lens);
        if (photo.exif_focal_length) exifArr.push(`${photo.exif_focal_length}mm`);
        if (photo.exif_aperture) exifArr.push(`f/${parseFloat(photo.exif_aperture)}`);
        if (photo.exif_shutter_speed_num && photo.exif_shutter_speed_den) exifArr.push(`${photo.exif_shutter_speed_num}/${photo.exif_shutter_speed_den}s`);
        if (photo.exif_iso) exifArr.push(`ISO ${photo.exif_iso}`);

        ctx.fillStyle = "#777777"; ctx.font = `400 ${metaFontSize}px Pretendard, monospace`; ctx.fillText(exifArr.join(' • '), canvas.width / 2, textY + titleFontSize * 1.4);
        ctx.fillStyle = "#999999"; ctx.font = `italic 300 ${metaFontSize * 0.8}px Pretendard, serif`; ctx.fillText(Exporter.AUTHOR_INFO, canvas.width / 2, textY + titleFontSize * 1.4 + metaFontSize * 1.5);

        Exporter.drawGridIfActive(ctx, img, marginX, marginTop);
    },

    // 💡 [수정] 카메라 세팅값 텍스트 간격 넉넉히 벌리고 포커스 사각형/점들 삭제, 배터리 100% 충전
    drawZfViewfinderStyle: (ctx, canvas, img, post, photo) => {
        canvas.width = img.width;
        canvas.height = img.height;

        ctx.drawImage(img, 0, 0, img.width, img.height);
        
        const padX = img.width * 0.03;
        const padY = img.height * 0.04;
        const fontSize = Math.max(20, img.width * 0.022);
        const fontSmall = fontSize * 0.6;
        const fontLarge = fontSize * 1.2;
        
        ctx.shadowColor = "rgba(0, 0, 0, 0.9)";
        ctx.shadowBlur = Math.max(3, img.width * 0.003);
        ctx.shadowOffsetX = 1;
        ctx.shadowOffsetY = 1;
        ctx.fillStyle = "#FFFFFF";
        ctx.strokeStyle = "#FFFFFF";
        ctx.lineWidth = Math.max(1.5, img.width * 0.0015);
        
        // 상단 UI 정렬
        ctx.textAlign = "left";
        ctx.textBaseline = "middle";
        const topY = padY + fontLarge * 0.5;

        ctx.font = `bold ${fontLarge}px Arial, sans-serif`;
        ctx.fillText("P", padX, topY);
        
        ctx.font = `bold ${fontSize}px Arial, sans-serif`;
        const sBoxX = padX + fontLarge * 2.0; 
        ctx.strokeRect(sBoxX, topY - fontSize * 0.8, fontSize * 1.3, fontSize * 1.6);
        ctx.fillText("S", sBoxX + fontSize * 0.3, topY);
        
        const afcX = sBoxX + fontSize * 3.0; // 간격 넓힘
        ctx.fillText("AF-C", afcX, topY);

        const meterX = afcX + fontSize * 4.5; 
        ctx.strokeRect(meterX, topY - fontSize * 0.6, fontSize * 1.8, fontSize * 1.2);
        ctx.beginPath(); 
        ctx.arc(meterX + fontSize * 0.9, topY, fontSize * 0.3, 0, Math.PI * 2); 
        ctx.stroke();

        ctx.textAlign = "right";
        const rightX = canvas.width - padX;
        
        ctx.font = `bold ${fontSize}px Arial, sans-serif`;
        ctx.fillText("FX", rightX, topY);
        
        ctx.font = `bold ${fontSmall}px Arial, sans-serif`;
        ctx.fillText("NORM", rightX - fontSize * 3.5, topY + fontSize * 0.15); // 간격 넓힘
        
        ctx.font = `bold ${fontSize}px Arial, sans-serif`;
        ctx.fillText("AUTO", rightX - fontSize * 6.5, topY); // 간격 넓힘
        
        ctx.font = `bold ${fontSmall}px Arial, sans-serif`;
        ctx.fillText("WB", rightX - fontSize * 9.5, topY + fontSize * 0.15); // 간격 넓힘

        // 하단 UI
        ctx.textBaseline = "alphabetic"; 
        const bottomY = canvas.height - padY;
        const cx = canvas.width / 2;

        ctx.textAlign = "left";
        const iconH = fontSize * 1.5;
        const iconY = bottomY - iconH + (fontSize * 0.2); 
        ctx.strokeRect(padX, iconY, fontSize * 2.0, iconH);
        ctx.beginPath(); 
        ctx.arc(padX + fontSize * 1.0, iconY + iconH/2, fontSize * 0.4, 0, Math.PI * 2); 
        ctx.fill();
        
        ctx.textAlign = "center";
        ctx.font = `bold ${fontSize * 1.2}px Arial, sans-serif`; 
        
        const fStop = photo.exif_aperture ? `F${parseFloat(photo.exif_aperture)}` : 'F--';
        const sSpeed = (photo.exif_shutter_speed_num && photo.exif_shutter_speed_den) ? `${photo.exif_shutter_speed_num}/${photo.exif_shutter_speed_den}` : '---';
        const isoVal = photo.exif_iso ? `${photo.exif_iso}` : '---';

        ctx.fillText(sSpeed, cx - fontSize * 6.0, bottomY); // 셔터스피드 간격
        ctx.fillText(fStop, cx - fontSize * 1.0, bottomY); // 조리개 간격
        
        ctx.shadowColor = "transparent";
        const isoBoxX = cx + fontSize * 3.5; 
        const isoBoxH = fontSize * 1.8;
        const isoBoxY = bottomY - isoBoxH + (fontSize * 0.2); 
        const isoBoxW = fontSize * 6.0; 
        
        ctx.fillStyle = "rgba(20, 20, 20, 0.85)";
        if(ctx.roundRect) {
            ctx.beginPath(); ctx.roundRect(isoBoxX, isoBoxY, isoBoxW, isoBoxH, 4); ctx.fill();
        } else {
            ctx.fillRect(isoBoxX, isoBoxY, isoBoxW, isoBoxH);
        }
        
        ctx.fillStyle = "#FFFFFF";
        ctx.textAlign = "left";
        ctx.font = `bold ${fontSize * 0.6}px Arial, sans-serif`;
        ctx.textBaseline = "middle";
        ctx.fillText("ISO", isoBoxX + fontSize * 0.5, isoBoxY + isoBoxH * 0.35);
        ctx.fillText("AUTO", isoBoxX + fontSize * 0.5, isoBoxY + isoBoxH * 0.7);
        
        ctx.font = `bold ${fontSize * 1.2}px Arial, sans-serif`;
        ctx.fillText(isoVal, isoBoxX + fontSize * 2.2, isoBoxY + isoBoxH * 0.55);

        ctx.textBaseline = "alphabetic"; 
        ctx.shadowColor = "rgba(0, 0, 0, 0.9)";
        ctx.textAlign = "right";
        ctx.font = `bold ${fontSize}px Arial, sans-serif`;
        ctx.fillText("[ 8.0k ]", rightX, bottomY);

        const iBoxY = bottomY - fontSize * 5.0;
        ctx.font = `italic bold ${fontSize * 1.2}px "Times New Roman", serif`;
        ctx.strokeRect(rightX - fontSize * 1.5, iBoxY, fontSize * 1.5, fontSize * 1.5);
        ctx.textBaseline = "middle";
        ctx.fillText("i", rightX - fontSize * 0.5, iBoxY + fontSize * 0.75);
        ctx.textBaseline = "alphabetic";
        
        // 🔋 배터리 100% 꽉 채움 그래픽
        const batY = bottomY - fontSize * 3.0;
        ctx.lineWidth = Math.max(1.5, img.width * 0.0015);
        ctx.strokeRect(rightX - fontSize * 2.0, batY, fontSize * 1.8, fontSize * 0.8);
        // 배터리 잔량 3칸 모두 칠함
        ctx.fillRect(rightX - fontSize * 1.8, batY + fontSize * 0.15, fontSize * 1.4, fontSize * 0.5); 
        ctx.fillRect(rightX - fontSize * 0.2, batY + fontSize * 0.2, fontSize * 0.2, fontSize * 0.4);

        Exporter.drawGridIfActive(ctx, img, 0, 0);
    },

    // 💡 [수정] 텍스트 간격 완전 분리, 포커스 사각형 완전 삭제, 100% 배터리
    drawNikonLcdStyle: (ctx, canvas, img, post, photo) => {
        const maxDim = Math.max(img.width, img.height);
        const pad = maxDim * 0.12; 
        
        canvas.width = img.width + (pad * 2);
        canvas.height = img.height + (pad * 2);

        ctx.fillStyle = "#0c0c0c";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, pad, pad, img.width, img.height);

        const cy = canvas.height / 2;

        // 초점 포커스 박스 삭제됨

        const fSize = Math.max(16, maxDim * 0.02);
        const fSmall = fSize * 0.7;
        const fLarge = fSize * 1.4;
        
        ctx.fillStyle = "#FFFFFF";
        ctx.strokeStyle = "#FFFFFF";
        ctx.lineWidth = Math.max(1.5, maxDim * 0.0015);
        ctx.textBaseline = "middle";

        const fStop = photo.exif_aperture ? `F ${parseFloat(photo.exif_aperture)}` : 'F 1.4';
        const sSpeed = (photo.exif_shutter_speed_num && photo.exif_shutter_speed_den) ? `${photo.exif_shutter_speed_num}/${photo.exif_shutter_speed_den}` : '1/250';
        const isoVal = photo.exif_iso ? `ISO ${photo.exif_iso}` : 'ISO 100';

        const drawBoxedText = (text, x, y) => {
            ctx.font = `bold ${fSize}px Arial, sans-serif`;
            const m = ctx.measureText(text);
            const p = fSize * 0.3;
            if(ctx.roundRect) {
                ctx.beginPath(); ctx.roundRect(x - m.width/2 - p, y - fSize/2 - p/1.5, m.width + p*2, fSize + p, 4); ctx.stroke();
            } else {
                ctx.strokeRect(x - m.width/2 - p, y - fSize/2 - p/1.5, m.width + p*2, fSize + p);
            }
            ctx.fillText(text, x, y);
        };

        const drawRotatedText = (text, x, y, align="center", font=`bold ${fSize}px Arial, sans-serif`, color="#FFFFFF") => {
            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(-Math.PI / 2); 
            ctx.textAlign = align;
            ctx.fillStyle = color;
            ctx.font = font;
            ctx.fillText(text, 0, 0);
            ctx.restore();
        };

        // --- 상단 UI (간격 넉넉하게 확장) ---
        const topY = pad / 2;
        ctx.textAlign = "center";
        ctx.font = `bold ${fSize}px Arial, sans-serif`;
        
        let tx = pad + fSize * 2.0;
        drawBoxedText("S", tx, topY);
        
        tx += fSize * 4.0; ctx.fillText("AF-C", tx, topY);
        tx += fSize * 5.0; ctx.fillText("[ WIDE-L ]", tx, topY);
        
        // 우측 정렬된 그룹
        const rightAnchor = canvas.width - pad;
        drawBoxedText("DX", rightAnchor - fSize * 2.0, topY);
        ctx.fillText("WB A1", rightAnchor - fSize * 6.5, topY);
        drawBoxedText("NL", rightAnchor - fSize * 10.5, topY);
        drawBoxedText("OFF", rightAnchor - fSize * 14.5, topY);

        ctx.beginPath();
        ctx.arc(canvas.width - pad - fSize, topY, fSize*0.6, 0, Math.PI*2);
        ctx.moveTo(canvas.width - pad - fSize, topY - fSize*0.4);
        ctx.lineTo(canvas.width - pad - fSize + fSize*0.3, topY + fSize*0.4);
        ctx.stroke();

        // --- 하단 UI ---
        const botY = canvas.height - (pad / 2);
        ctx.textAlign = "left";
        ctx.font = `bold ${fLarge}px Arial, sans-serif`;
        ctx.fillText("M", pad + fSize, botY);

        ctx.strokeStyle = "#ff4444";
        ctx.beginPath(); ctx.arc(pad + fSize*3.5, botY, fSize*0.6, 0, Math.PI*2); ctx.stroke();
        ctx.strokeStyle = "#FFFFFF";

        ctx.textAlign = "right";
        ctx.font = `bold ${fSmall}px Arial, sans-serif`;
        ctx.fillText("A FLICKER", canvas.width - pad - fSize*4.0, botY);
        
        // 🔋 배터리 100% 꽉 채움
        const batX = canvas.width - pad - fSize*2.5;
        ctx.strokeRect(batX, botY - fSize*0.4, fSize*1.6, fSize*0.8);
        ctx.fillRect(batX + fSize*0.1, botY - fSize*0.3, fSize*1.4, fSize*0.6); 
        ctx.fillRect(batX + fSize*1.6, botY - fSize*0.2, fSize*0.15, fSize*0.4);

        // --- 우측 세로 UI (간격 분리) ---
        const rightX = canvas.width - (pad / 2);
        
        drawRotatedText("RAW+B", rightX, pad + fSize*4.5, "center");
        ctx.strokeRect(rightX - fSize*0.4, pad + fSize*7.5, fSize*0.8, fSize*1.1);
        ctx.fillRect(rightX - fSize*0.2, pad + fSize*7.7, fSize*0.4, fSize*0.3);

        drawRotatedText("+....0....-", rightX, cy - fSize*3, "center");

        drawRotatedText(isoVal, rightX, cy + fSize*4, "center", `bold ${fSize}px Arial, sans-serif`);
        drawRotatedText(fStop, rightX, cy + fSize*8.5, "center", `bold ${fLarge}px Arial, sans-serif`);
        drawRotatedText(sSpeed, rightX, cy + fSize*13.5, "center", `bold ${fLarge}px Arial, sans-serif`);

        drawRotatedText("[ 2.6 ] k", rightX, canvas.height - pad - fSize*3, "center", `bold ${fSize}px Arial, sans-serif`);

        Exporter.drawGridIfActive(ctx, img, pad, pad);
    },

    drawCinematicOverlay: (ctx, canvas, img, post, photo) => {
        canvas.width = img.width;
        canvas.height = img.height;

        ctx.drawImage(img, 0, 0, img.width, img.height);

        const gradHeight = canvas.height * 0.25;
        const grad = ctx.createLinearGradient(0, canvas.height - gradHeight, 0, canvas.height);
        grad.addColorStop(0, 'rgba(0,0,0,0)');
        grad.addColorStop(1, 'rgba(0,0,0,0.6)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, canvas.height - gradHeight, canvas.width, gradHeight);

        const bodyText = [photo.exif_body, photo.exif_lens].filter(Boolean).join(' | ') || 'Camera';
        const titleStr = Utils.getTrans(photo.title) || Utils.getTrans(post.title);
        const locStr = `${post.country || ''} ${Utils.getTrans(post.location) || ''}`.trim() || titleStr;

        const minDim = Math.min(img.width, img.height);
        const mainFontSize = Math.max(30, minDim * 0.035); 
        const subFontSize = Math.max(16, minDim * 0.015);
        const bottomPadding = img.height * 0.04; 

        ctx.textAlign = "center";
        ctx.textBaseline = "bottom";
        ctx.fillStyle = "#FFFFFF";
        ctx.shadowColor = "rgba(0, 0, 0, 0.5)";
        ctx.shadowBlur = 10;

        ctx.font = `500 ${subFontSize}px Pretendard, sans-serif`;
        ctx.fillText(locStr, canvas.width / 2, canvas.height - bottomPadding);

        ctx.font = `bold ${mainFontSize}px Pretendard, serif`; 
        ctx.fillText(bodyText, canvas.width / 2, canvas.height - bottomPadding - subFontSize * 1.5);

        ctx.shadowColor = "transparent";

        ctx.globalAlpha = 0.7;
        ctx.textAlign = "right";
        ctx.font = `italic 300 ${subFontSize * 0.9}px Pretendard, serif`;
        ctx.fillText(Exporter.AUTHOR_INFO, canvas.width - img.width * 0.03, canvas.height - bottomPadding);
        ctx.globalAlpha = 1.0;

        Exporter.drawGridIfActive(ctx, img, 0, 0);
    }
};