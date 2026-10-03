import { createWorker } from 'tesseract.js';

const screenshotInput = document.querySelector('#screenshot');
const ocrStatus = document.querySelector('#ocr-status');
const ocrResult = document.querySelector('#ocr-result');
const rrValuesInput = document.querySelector('#rr-values');
function extractRR(text) {
    const rrValues = [];

    // +22 / -22 などを探す
    const matches = text.match(/[+-]\s*\d{1,2}/g);

    if (!matches) {
        return [];
    }

    for (const match of matches) {
        const rr = match.replace(/\s/g, '');
        const value = Number(rr);

        // VALORANTのRRとして現実的な範囲だけ採用
        if (value >= -50 && value <= 50) {
            rrValues.push(rr);
        }
    }

    return rrValues;
}

function createCrop(image) {
    const canvas = document.createElement('canvas');

    /*
     * VALORANT戦績画面のRR部分だけ切り抜く
     *
     * 今回の画像ではRRが画面左から
     * 約13%～18.5%の位置にある
     */
    const cropX = Math.floor(image.width * 0.13);
    const cropY = 0;
    const cropWidth = Math.floor(image.width * 0.055);
    const cropHeight = image.height;

    // 5倍に拡大
    const scale = 5;

    canvas.width = cropWidth * scale;
    canvas.height = cropHeight * scale;

    const ctx = canvas.getContext('2d');

    ctx.imageSmoothingEnabled = true;

    ctx.drawImage(
        image,
        cropX,
        cropY,
        cropWidth,
        cropHeight,
        0,
        0,
        canvas.width,
        canvas.height
    );

    return canvas;
}

function createColorMask(sourceCanvas) {
    const canvas = document.createElement('canvas');

    canvas.width = sourceCanvas.width;
    canvas.height = sourceCanvas.height;

    const ctx = canvas.getContext('2d');

    ctx.drawImage(sourceCanvas, 0, 0);

    const imageData = ctx.getImageData(
        0,
        0,
        canvas.width,
        canvas.height
    );

    const data = imageData.data;

    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];

        // 赤いRR
        const red =
            r > 100 &&
            r > g * 1.4 &&
            r > b * 1.3;

        // 緑のRR
        const green =
            g > 100 &&
            g > r * 1.15 &&
            g > b * 0.85;

        if (red || green) {
            data[i] = 255;
            data[i + 1] = 255;
            data[i + 2] = 255;
        } else {
            data[i] = 0;
            data[i + 1] = 0;
            data[i + 2] = 0;
        }
    }

    ctx.putImageData(imageData, 0, 0);

    return canvas;
}

if (screenshotInput) {
    screenshotInput.addEventListener('change', async () => {
        const file = screenshotInput.files[0];

if (!file) {
    return;
}

if (rrValuesInput) {
    rrValuesInput.value = '';
}

ocrStatus.textContent = 'RR部分を切り抜いています...';
        ocrResult.textContent = '';

        try {
            const image = new Image();
            const imageUrl = URL.createObjectURL(file);

            image.src = imageUrl;

            await image.decode();

            // RR部分だけ切り抜く
            const croppedCanvas = createCrop(image);

            // 色を利用したOCR用画像
            const colorCanvas = createColorMask(croppedCanvas);

            ocrStatus.textContent = 'RRをOCR解析中...';

            const worker = await createWorker('eng');

            // OCR対象を数字と+ - に限定
            await worker.setParameters({
                tessedit_char_whitelist: '+-0123456789',
                tessedit_pageseg_mode: '6',
            });

            // 通常画像
            const normalResult = await worker.recognize(
                croppedCanvas
            );

            // 色を強調した画像
            const colorResult = await worker.recognize(
                colorCanvas
            );

            const normalRR = extractRR(
                normalResult.data.text
            );

            const colorRR = extractRR(
                colorResult.data.text
            );

            // 件数が多い方を採用
            let rrValues;

            if (colorRR.length > normalRR.length) {
                rrValues = colorRR;
            } else {
                rrValues = normalRR;
            }
            if (rrValuesInput) {
                rrValuesInput.value = JSON.stringify(rrValues);
        }

            if (rrValues.length === 0) {
                ocrResult.textContent =
                    'RRを検出できませんでした。\n\n' +
                    '通常OCR:\n' +
                    normalResult.data.text +
                    '\n\n色強調OCR:\n' +
                    colorResult.data.text;
            } else {
                ocrResult.textContent =
                    rrValues.join('\n');
            }

            await worker.terminate();

            URL.revokeObjectURL(imageUrl);

            ocrStatus.textContent =
                `RRの解析が完了しました！（${rrValues.length}件）`;

        } catch (error) {
            console.error(error);

            ocrStatus.textContent =
                'OCR解析に失敗しました。';
        }
    });
}