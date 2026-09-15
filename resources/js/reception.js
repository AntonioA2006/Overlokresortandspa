function navigateToToken(tokenInput) {
    const token = tokenInput.value.trim();

    if (token) {
        window.location.href = `/reception/check/${encodeURIComponent(token)}`;
    } else {
        tokenInput.focus();
    }
}

async function startCamera(video, tokenInput) {
    if (!('BarcodeDetector' in window) || !navigator.mediaDevices?.getUserMedia) {
        return false;
    }

    const stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'environment' },
    });

    video.srcObject = stream;
    await video.play();

    const detector = new window.BarcodeDetector({ formats: ['qr_code'] });

    const scan = async () => {
        if (video.readyState < 2) {
            window.requestAnimationFrame(scan);
            return;
        }

        try {
            const barcodes = await detector.detect(video);
            const raw = barcodes[0]?.rawValue;

            if (raw) {
                const token = raw.split('/').filter(Boolean).pop();
                tokenInput.value = token;
                stream.getTracks().forEach((track) => track.stop());
                navigateToToken(tokenInput);
                return;
            }
        } catch (error) {
            console.warn('QR scan failed', error);
        }

        window.requestAnimationFrame(scan);
    };

    scan();
    return true;
}

document.addEventListener('DOMContentLoaded', () => {
    const tokenInput = document.querySelector('[data-reception-token-input]');
    const lookupButton = document.querySelector('[data-reception-lookup]');
    const cameraWrap = document.querySelector('[data-reception-camera-wrap]');
    const video = document.querySelector('[data-reception-camera]');
    const startButton = document.querySelector('[data-reception-camera-start]');
    const fallback = document.querySelector('[data-reception-camera-fallback]');

    if (!tokenInput) {
        return;
    }

    tokenInput.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        navigateToToken(tokenInput);
    });

    lookupButton?.addEventListener('click', () => {
        navigateToToken(tokenInput);
    });

    if (cameraWrap && video && 'BarcodeDetector' in window) {
        cameraWrap.hidden = false;
        fallback?.setAttribute('hidden', '');

        startButton?.addEventListener('click', async () => {
            try {
                await startCamera(video, tokenInput);
            } catch (error) {
                console.warn('Camera unavailable', error);
                fallback?.removeAttribute('hidden');
            }
        });
    }
});
