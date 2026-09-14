const POLL_INTERVAL_MS = 4000;

export function startChatPolling({ messagesUrl, onMessages }) {
    if (!messagesUrl || typeof onMessages !== 'function') {
        return null;
    }

    const poll = async () => {
        try {
            const response = await fetch(messagesUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (response.ok) {
                onMessages(await response.json());
            }
        } catch (error) {
            console.warn('Chat polling failed', error);
        }
    };

    poll();
    return window.setInterval(poll, POLL_INTERVAL_MS);
}
