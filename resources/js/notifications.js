const POLL_INTERVAL_MS = 30000;

function csrfHeaders() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token || '',
    };
}

export function startNotificationPolling(endpoint) {
    if (!endpoint) {
        return;
    }

    const poll = async () => {
        try {
            await fetch(endpoint, {
                headers: csrfHeaders(),
                credentials: 'same-origin',
            });
        } catch (error) {
            console.warn('Notification polling failed', error);
        }
    };

    poll();
    return window.setInterval(poll, POLL_INTERVAL_MS);
}
