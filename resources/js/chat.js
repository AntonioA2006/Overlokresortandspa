function csrfHeaders() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    return {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token || '',
    };
}

const POLL_INTERVAL_MS = 4000;

export function startChatPolling({ messagesUrl, onMessages }) {
    if (!messagesUrl || typeof onMessages !== 'function') {
        return null;
    }

    const poll = async () => {
        try {
            const response = await fetch(messagesUrl, {
                headers: csrfHeaders(),
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

export function renderChatMessages(container, payload, currentUserId) {
    if (!container || !payload?.messages) {
        return;
    }

    container.innerHTML = '';

    payload.messages.forEach((message) => {
        const article = document.createElement('article');
        article.className = `chat-message${message.sender_id === currentUserId ? ' is-own' : ''}`;

        const meta = document.createElement('p');
        meta.className = 'chat-message__meta';
        meta.textContent = message.sender_name || '';

        const body = document.createElement('p');
        body.className = 'chat-message__body';
        body.textContent = message.body;

        article.append(meta, body);
        container.append(article);
    });

    container.scrollTop = container.scrollHeight;
}
