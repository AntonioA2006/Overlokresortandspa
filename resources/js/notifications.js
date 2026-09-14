const POLL_INTERVAL_MS = 30000;

export function startNotificationPolling(endpoint) {
    if (!endpoint) {
        return;
    }

    const poll = async () => {
        try {
            await fetch(endpoint, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
        } catch (error) {
            console.warn('Notification polling failed', error);
        }
    };

    poll();
    return window.setInterval(poll, POLL_INTERVAL_MS);
}
