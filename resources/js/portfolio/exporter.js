import { State } from './core.js';
import { Utils } from './utils.js';

export const Exporter = {
    processDownload: () => {
        const selectEl = document.getElementById('download-template-select');
        const style = selectEl ? selectEl.value : 'polaroid';
        
        const post = State.activePost;
        const photo = post.photos[State.currentPhotoIndex];
        const postUrl = `${window.location.origin}${window.location.pathname}#post-${post.id}/photo-${State.currentPhotoIndex}`;
        
        Utils.showToast(window.I18N[State.lang].toastWait || 'Generating frame...');
        const img = new Image();
        img.crossOrigin = "Anonymous";
        img.src = photo.url;

        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            const title = Utils.getTrans(photo.title) || Utils.getTrans(post.title);
            const exifArr = [];
            if (photo.exif_body) exifArr.push(photo.exif_body);
            if (photo.exif_lens) exifArr.push(photo.exif_lens);
            if (photo.exif_focal_length) exifArr.push(`${photo.exif_focal_length}mm`);
            if (photo.exif_aperture) exifArr.push(`f/${parseFloat(photo.exif_aperture)}`);
            if (photo.exif_shutter_speed_num && photo.exif_shutter_speed_den) exifArr.push(`${photo.exif_shutter_speed_num}/${photo.exif_shutter_speed_den}s`);
            if (photo.exif_iso) exifArr.push(`ISO ${photo.exif_iso}`);

            // 작가 정보를 인스타그램 ID로 변경
            const authorInfo = "@kkyoung.photo";
            
            // -------------------------------------------------------------
            // 1. Film Polaroid: 클래식 폴라로이드 감성 (QR 코드 O)
            // -------------------------------------------------------------
            if (style === 'polaroid') {
                const marginX = img.width * 0.04;
                const marginTop = marginX * 1.2;
                // 작가 정보를 추가할 공간을 위해 하단 여백을 조금 더 확보합니다.
                const marginBottom = marginX * 5.0; 
                
                canvas.width = img.width + (marginX * 2);
                canvas.height = img.height + marginTop + marginBottom;

                ctx.fillStyle = "#F9F8F6"; 
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, marginX, marginTop, img.width, img.height);
                
                ctx.strokeStyle = "rgba(0,0,0,0.08)";
                ctx.lineWidth = 1;
                ctx.strokeRect(marginX, marginTop, img.width, img.height);

                const textY = marginTop + img.height + (marginBottom * 0.35);
                const titleFontSize = Math.max(30, img.width * 0.025);
                const metaFontSize = Math.max(18, img.width * 0.014);
                
                ctx.textAlign = "center";
                ctx.fillStyle = "#222222";
                ctx.font = `bold ${titleFontSize}px Pretendard, serif`; 
                ctx.fillText(title, canvas.width / 2, textY);

                // EXIF 정보
                ctx.fillStyle = "#777777";
                ctx.font = `400 ${metaFontSize}px Pretendard, monospace`;
                ctx.fillText(exifArr.join(' • '), canvas.width / 2, textY + titleFontSize * 1.4);

                // EXIF 아래에 작가 정보(인스타그램 ID) 추가
                ctx.fillStyle = "#999999";
                ctx.font = `italic 300 ${metaFontSize * 0.8}px Pretendard, serif`;
                ctx.fillText(authorInfo, canvas.width / 2, textY + titleFontSize * 1.4 + metaFontSize * 1.5);

                const qrSize = marginX * 1.5;
                const qr = new QRious({ value: postUrl, size: qrSize, level: 'M' });
                const qrImg = new Image();
                qrImg.src = qr.toDataURL();
                qrImg.onload = () => {
                    ctx.globalAlpha = 0.8;
                    ctx.drawImage(qrImg, canvas.width - marginX - qrSize, canvas.height - marginX - qrSize, qrSize, qrSize);
                    ctx.globalAlpha = 1.0;
                    
                    if (State.isGridActive) {
                        ctx.strokeStyle = "rgba(255, 255, 255, 0.7)";
                        ctx.lineWidth = Math.max(1.5, img.width * 0.002);
                        ctx.beginPath();
                        ctx.moveTo(marginX + img.width / 3, marginTop); ctx.lineTo(marginX + img.width / 3, marginTop + img.height);
                        ctx.moveTo(marginX + (img.width * 2) / 3, marginTop); ctx.lineTo(marginX + (img.width * 2) / 3, marginTop + img.height);
                        ctx.moveTo(marginX, marginTop + img.height / 3); ctx.lineTo(marginX + img.width, marginTop + img.height / 3);
                        ctx.moveTo(marginX, marginTop + (img.height * 2) / 3); ctx.lineTo(marginX + img.width, marginTop + (img.height * 2) / 3);
                        ctx.stroke();
                    }

                    Exporter.downloadCanvas(canvas, style, post, photo);
                };
                return;
            } 
            
            // -------------------------------------------------------------
            // 2. Clean White Gallery: 흰색 갤러리 테마 (QR 코드 O)
            // -------------------------------------------------------------
            else if (style === 'instagram') {
                const marginX = img.width * 0.05; 
                const marginTop = marginX;
                const marginBottom = img.width * 0.15; 
                
                canvas.width = img.width + (marginX * 2);
                canvas.height = img.height + marginTop + marginBottom;
                
                ctx.fillStyle = "#FFFFFF"; 
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.shadowColor = "rgba(0, 0, 0, 0.15)";
                ctx.shadowBlur = Math.max(20, img.width * 0.015);
                ctx.shadowOffsetY = Math.max(10, img.width * 0.008);
                ctx.drawImage(img, marginX, marginTop, img.width, img.height);
                ctx.shadowColor = "transparent";

                const leftTextArr = [];
                if (photo.exif_body) leftTextArr.push(photo.exif_body);
                if (photo.exif_lens) leftTextArr.push(photo.exif_lens);
                const leftText = leftTextArr.join(' | ');

                const rightTextArr = [];
                if (photo.exif_focal_length) rightTextArr.push(`${photo.exif_focal_length}mm`);
                if (photo.exif_aperture) rightTextArr.push(`F${parseFloat(photo.exif_aperture)}`);
                if (photo.exif_shutter_speed_num && photo.exif_shutter_speed_den) rightTextArr.push(`${photo.exif_shutter_speed_num}/${photo.exif_shutter_speed_den}s`);
                if (photo.exif_iso) rightTextArr.push(`ISO ${photo.exif_iso}`);
                const rightText = rightTextArr.join(' | ');

                const textY = marginTop + img.height + (marginBottom * 0.4);
                const fontSize = Math.max(18, img.width * 0.016);

                ctx.textBaseline = "middle";

                // 좌측: 기종 및 렌즈 정보
                ctx.fillStyle = "#333333";
                ctx.textAlign = "left";
                ctx.font = `600 ${fontSize}px Pretendard, sans-serif`;
                ctx.fillText(leftText, marginX, textY);

                // 좌측: 작가 정보 (기종 정보 바로 아래 배치)
                ctx.fillStyle = "#888888";
                ctx.font = `400 ${fontSize * 0.85}px Pretendard, sans-serif`;
                ctx.fillText(authorInfo, marginX, textY + fontSize * 1.5);

                // 우측 QR 코드 렌더링
                const qrSize = marginBottom * 0.5;
                const qrX = canvas.width - marginX - qrSize;
                const qrY = marginTop + img.height + (marginBottom - qrSize) / 2;

                // 우측: 노출 정보 (QR 코드의 바로 왼쪽에 정렬)
                ctx.fillStyle = "#333333";
                ctx.textAlign = "right";
                ctx.font = `400 ${fontSize}px Pretendard, sans-serif`;
                // QR코드 높이의 중간쯤에 텍스트가 오도록 Y값 미세 조정
                ctx.fillText(rightText, qrX - (marginX * 0.5), qrY + (qrSize / 2));

                const qr = new QRious({ value: postUrl, size: qrSize, level: 'M' });
                const qrImg = new Image();
                qrImg.src = qr.toDataURL();
                qrImg.onload = () => {
                    ctx.globalAlpha = 0.8;
                    ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);
                    ctx.globalAlpha = 1.0;
                    
                    if (State.isGridActive) Exporter.drawGridIfActive(ctx, style, img, canvas, marginX, marginTop);
                    Exporter.downloadCanvas(canvas, style, post, photo);
                };
                return;
            }

            // -------------------------------------------------------------
            // 3. Nikon Zf Viewfinder: 카메라 뷰파인더 HUD 오버레이
            // -------------------------------------------------------------
            else if (style === 'zf_viewfinder') {
                canvas.width = img.width;
                canvas.height = img.height;

                ctx.drawImage(img, 0, 0, img.width, img.height);
                
                const padX = img.width * 0.03;
                const padY = img.height * 0.04;
                const fontSize = Math.max(16, img.width * 0.018); 
                const fontSmall = fontSize * 0.7;
                const fontLarge = fontSize * 1.4;
                
                ctx.shadowColor = "rgba(0, 0, 0, 0.9)";
                ctx.shadowBlur = Math.max(3, img.width * 0.003);
                ctx.shadowOffsetX = 1;
                ctx.shadowOffsetY = 1;
                ctx.fillStyle = "#FFFFFF";
                ctx.strokeStyle = "#FFFFFF";
                ctx.lineWidth = Math.max(1.5, img.width * 0.0015);
                
                // --- Top Row ---
                ctx.textAlign = "left";
                ctx.textBaseline = "middle"; 
                const topY = padY + fontLarge * 0.5;

                ctx.font = `bold ${fontLarge}px Arial, sans-serif`;
                ctx.fillText("P", padX, topY);
                
                ctx.font = `bold ${fontSize}px Arial, sans-serif`;
                const sBoxX = padX + fontLarge * 2.0; 
                ctx.strokeRect(sBoxX, topY - fontSize * 0.75, fontSize * 1.5, fontSize * 1.5);
                ctx.fillText("S", sBoxX + fontSize * 0.35, topY);
                
                const afcX = sBoxX + fontSize * 2.8; 
                ctx.fillText("AF-C", afcX, topY);

                const meterX = afcX + fontSize * 4.5; 
                ctx.strokeRect(meterX, topY - fontSize * 0.6, fontSize * 1.8, fontSize * 1.2);
                ctx.beginPath(); 
                ctx.arc(meterX + fontSize * 0.9, topY, fontSize * 0.3, 0, Math.PI * 2); 
                ctx.stroke();

                // Right Side
                ctx.textAlign = "right";
                const rightX = canvas.width - padX;
                
                ctx.font = `bold ${fontSize}px Arial, sans-serif`;
                ctx.fillText("FX", rightX, topY);
                
                ctx.font = `bold ${fontSmall}px Arial, sans-serif`;
                const normX = rightX - fontSize * 2.8;
                ctx.fillText("NORM", normX, topY + fontSize * 0.15);
                
                ctx.font = `bold ${fontSize}px Arial, sans-serif`;
                const autoX = normX - fontSize * 3.0;
                ctx.fillText("AUTO", autoX, topY);
                
                ctx.font = `bold ${fontSmall}px Arial, sans-serif`;
                const wbX = autoX - fontSize * 3.8;
                ctx.fillText("WB", wbX, topY + fontSize * 0.15);

                // --- Bottom Row ---
                ctx.shadowColor = "rgba(0, 0, 0, 0.9)";
                ctx.fillStyle = "#FFFFFF";
                ctx.strokeStyle = "#FFFFFF";
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

                const fStopX = cx - fontSize * 1.0;
                const sSpeedX = cx - fontSize * 6.5; 
                
                ctx.fillText(sSpeed, sSpeedX, bottomY);
                ctx.fillText(fStop, fStopX, bottomY);
                
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

                // [수정] 배터리와 i 아이콘의 상하 위치를 스왑
                // 'i' 아이콘을 위로 (기존 3.5 -> 5.0)
                const iBoxY = bottomY - fontSize * 5.0;
                ctx.font = `italic bold ${fontSize * 1.2}px "Times New Roman", serif`;
                ctx.strokeRect(rightX - fontSize * 1.5, iBoxY, fontSize * 1.5, fontSize * 1.5);
                ctx.textBaseline = "middle";
                ctx.fillText("i", rightX - fontSize * 0.5, iBoxY + fontSize * 0.75);
                ctx.textBaseline = "alphabetic";
                
                // 배터리 아이콘을 아래로 (기존 5.0 -> 3.0)
                const batY = bottomY - fontSize * 3.0;
                ctx.lineWidth = Math.max(1.5, img.width * 0.0015);
                ctx.strokeRect(rightX - fontSize * 2.0, batY, fontSize * 1.8, fontSize * 0.8);
                ctx.fillRect(rightX - fontSize * 1.8, batY + fontSize * 0.15, fontSize * 0.8, fontSize * 0.5); 
                ctx.fillRect(rightX - fontSize * 0.2, batY + fontSize * 0.2, fontSize * 0.2, fontSize * 0.4);

                // 작가 서명 우측 상단 배치
                ctx.globalAlpha = 0.8;
                ctx.textAlign = "right";
                ctx.textBaseline = "top";
                ctx.font = `italic 300 ${fontSmall * 1.2}px "Times New Roman", serif`;
                ctx.fillText(authorInfo, rightX, padY + fontLarge * 1.8);
                ctx.globalAlpha = 1.0;

                if (State.isGridActive) Exporter.drawGridIfActive(ctx, style, img, canvas, 0, 0);

                Exporter.downloadCanvas(canvas, style, post, photo);
                return;
            }

            // -------------------------------------------------------------
            // 4. Cinematic Overlay (QR X, 서명 O)
            // -------------------------------------------------------------
            else if (style === 'overlay') {
                canvas.width = img.width;
                canvas.height = img.height;

                ctx.drawImage(img, 0, 0, img.width, img.height);

                const gradHeight = canvas.height * 0.25;
                const grad = ctx.createLinearGradient(0, canvas.height - gradHeight, 0, canvas.height);
                grad.addColorStop(0, 'rgba(0,0,0,0)');
                grad.addColorStop(1, 'rgba(0,0,0,0.6)');
                ctx.fillStyle = grad;
                ctx.fillRect(0, canvas.height - gradHeight, canvas.width, gradHeight);

                // [수정] 카메라 이름 옆에 렌즈 정보 추가
                const bodyText = [photo.exif_body, photo.exif_lens].filter(Boolean).join(' | ') || 'Camera';
                const locStr = `${post.country || ''} ${Utils.getTrans(post.location) || ''}`.trim() || title;

                // [수정] 가로/세로 중 더 짧은 축을 기준으로 폰트 크기를 계산하여 
                // 가로 사진일 때 폰트가 지나치게 커지는 현상 방지
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

                // 작가 정보 우측 하단에 추가
                ctx.globalAlpha = 0.7;
                ctx.textAlign = "right";
                ctx.font = `italic 300 ${subFontSize * 0.9}px Pretendard, serif`;
                ctx.fillText(authorInfo, canvas.width - img.width * 0.03, canvas.height - bottomPadding);
                ctx.globalAlpha = 1.0;

                if (State.isGridActive) Exporter.drawGridIfActive(ctx, style, img, canvas, 0, 0);
                
                Exporter.downloadCanvas(canvas, style, post, photo);
                return;
            }
        };
    },

    drawGridIfActive: (ctx, style, img, canvas, offsetX, offsetY) => {
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

    downloadCanvas: (canvas, style, post, photo) => {
        const link = document.createElement('a');
        const cleanSlug = post.slug ? post.slug.replace(/[^a-zA-Z0-9-]/g, '') : 'post';
        link.download = `kkyung_${style}_${cleanSlug}_${photo.id}.jpg`;
        link.href = canvas.toDataURL("image/jpeg", 0.95);
        link.click();
    }
};