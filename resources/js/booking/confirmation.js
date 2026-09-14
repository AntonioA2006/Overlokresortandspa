import QRCode from 'qrcode';

export function initReservationConfirmation() {
    const container = document.querySelector('[data-reservation-qr]');

    if (!container) {
        return;
    }

    const canvas = container.querySelector('canvas');
    const checkUrl = container.dataset.checkUrl;

    if (!canvas || !checkUrl) {
        return;
    }

    const size = Math.min(280, container.clientWidth || 280);

    QRCode.toCanvas(canvas, checkUrl, {
        width: size,
        margin: 1,
        color: {
            dark: '#1c1b19',
            light: '#fffdf9',
        },
    }).catch((error) => {
        console.warn('QR render failed', error);
    });
}
