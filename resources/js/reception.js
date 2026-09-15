import { extractReservationLookup } from './qr.js';

function setStatus(statusEl, message) {
    if (!statusEl) {
        return;
    }

    statusEl.textContent = message || '';
}

function stopStream(video) {
    const stream = video?.srcObject;

    if (!stream) {
        return;
    }

    stream.getTracks().forEach((track) => track.stop());
    video.srcObject = null;
}

function submitLookup(form, tokenInput, rawValue) {
    const lookup = extractReservationLookup(rawValue);

    if (!lookup) {
        tokenInput?.focus();
        return false;
    }

    tokenInput.value = lookup;
    form.requestSubmit();
    return true;
}

function canUseCamera() {
    return Boolean(navigator.mediaDevices?.getUserMedia);
}

function canUseBarcodeDetector() {
    return 'BarcodeDetector' in window;
}

function captureFrame(video, canvas, context) {
    const width = video.videoWidth;
    const height = video.videoHeight;

    if (!width || !height) {
        return null;
    }

    canvas.width = width;
    canvas.height = height;
    context.drawImage(video, 0, 0, width, height);

    return context.getImageData(0, 0, width, height);
}

async function detectWithBarcodeDetector(video) {
    const detector = new window.BarcodeDetector({ formats: ['qr_code'] });
    const barcodes = await detector.detect(video);

    return barcodes[0]?.rawValue || null;
}

async function createJsQrScanner() {
    const module = await import('jsqr');
    const jsQR = module.default;
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d', { willReadFrequently: true });

    return (video) => {
        const imageData = captureFrame(video, canvas, context);

        if (!imageData) {
            return null;
        }

        const result = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: 'attemptBoth',
        });

        return result?.data || null;
    };
}

async function startCamera({ video, form, tokenInput, statusEl, startButton, stopButton, strings }) {
    if (!canUseCamera()) {
        return false;
    }

    if (!window.isSecureContext) {
        throw Object.assign(new Error('insecure'), { name: 'SecurityError' });
    }

    let stream;

    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
        });
    } catch (error) {
        if (error?.name !== 'OverconstrainedError' && error?.name !== 'NotFoundError') {
            throw error;
        }

        stream = await navigator.mediaDevices.getUserMedia({ video: true });
    }

    video.srcObject = stream;
    await video.play();
    startButton.hidden = true;

    if (stopButton) {
        stopButton.hidden = false;
    }
    setStatus(statusEl, strings.scanning);

    const detectFrame = canUseBarcodeDetector()
        ? detectWithBarcodeDetector
        : await createJsQrScanner();

    let stopped = false;

    const stop = () => {
        stopped = true;
        stopStream(video);
        startButton.hidden = false;

        if (stopButton) {
            stopButton.hidden = true;
        }
    };

    const scan = async () => {
        if (stopped) {
            return;
        }

        if (video.readyState < 2) {
            window.requestAnimationFrame(scan);
            return;
        }

        try {
            const raw = await detectFrame(video);

            if (raw) {
                setStatus(statusEl, strings.detected);
                stop();
                submitLookup(form, tokenInput, raw);
                return;
            }
        } catch (error) {
            console.warn('QR scan failed', error);
        }

        window.requestAnimationFrame(scan);
    };

    if (stopButton) {
        stopButton.onclick = () => {
            stop();
            setStatus(statusEl, '');
        };
    }

    scan();
    return true;
}

function cameraErrorMessage(error, strings) {
    if (error?.name === 'NotAllowedError' || error?.name === 'PermissionDeniedError') {
        return strings.denied;
    }

    if (error?.name === 'SecurityError' || error?.name === 'NotSupportedError') {
        return strings.insecure;
    }

    if (error?.name === 'NotFoundError' || error?.name === 'OverconstrainedError') {
        return strings.missing;
    }

    return strings.unavailable;
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-reception-lookup-form]');
    const tokenInput = document.querySelector('[data-reception-token-input]');
    const cameraWrap = document.querySelector('[data-reception-camera-wrap]');
    const video = document.querySelector('[data-reception-camera]');
    const startButton = document.querySelector('[data-reception-camera-start]');
    const stopButton = document.querySelector('[data-reception-camera-stop]');
    const fallback = document.querySelector('[data-reception-camera-fallback]');
    const statusEl = document.querySelector('[data-reception-camera-status]');
    const scanPage = document.querySelector('[data-reception-scan-page]');
    const strings = {
        scanning: scanPage?.dataset.i18nScanning || '',
        detected: scanPage?.dataset.i18nDetected || '',
        denied: scanPage?.dataset.i18nDenied || '',
        insecure: scanPage?.dataset.i18nInsecure || '',
        missing: scanPage?.dataset.i18nMissing || '',
        unavailable: scanPage?.dataset.i18nUnavailable || '',
    };

    if (!form || !tokenInput) {
        return;
    }

    form.addEventListener('submit', () => {
        tokenInput.value = extractReservationLookup(tokenInput.value);
    });

    const cameraSupported = Boolean(cameraWrap && video && startButton && canUseCamera());

    if (!cameraSupported) {
        return;
    }

    cameraWrap.hidden = false;
    fallback?.setAttribute('hidden', '');

    const attemptStart = async () => {
        try {
            await startCamera({
                video,
                form,
                tokenInput,
                statusEl,
                startButton,
                stopButton,
                strings,
            });
        } catch (error) {
            console.warn('Camera unavailable', error);
            stopStream(video);
            startButton.hidden = false;
            if (stopButton) {
                stopButton.hidden = true;
            }
            fallback?.removeAttribute('hidden');
            if (fallback) {
                fallback.textContent = cameraErrorMessage(error, strings);
            }
            setStatus(statusEl, cameraErrorMessage(error, strings));
        }
    };

    startButton.addEventListener('click', attemptStart);

    if (scanPage) {
        attemptStart();
    }
});
