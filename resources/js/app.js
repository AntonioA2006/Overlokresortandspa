import './validation.js';
import './landing.js';
import './booking/search.js';
import { initPremiumHeader } from './ui/header.js';
import { initRevealAnimations } from './ui/animations.js';

document.addEventListener('DOMContentLoaded', () => {
    initPremiumHeader();
    initRevealAnimations();

    if (document.querySelector('[data-reception-token-input]')) {
        import('./reception.js');
    }

    if (document.querySelector('[data-reservation-qr]')) {
        import('./booking/confirmation.js').then(({ initReservationConfirmation }) => {
            initReservationConfirmation();
        });
    }

    const chatWindow = document.querySelector('[data-chat-window]');

    if (chatWindow) {
        import('./chat.js').then(({ startChatPolling, renderChatMessages }) => {
            const messagesUrl = chatWindow.dataset.messagesUrl;
            const container = chatWindow.querySelector('[data-chat-messages]');

            startChatPolling({
                messagesUrl,
                onMessages: (payload) => renderChatMessages(container, payload, Number(chatWindow.dataset.userId || 0)),
            });
        });
    }

    const notificationsList = document.querySelector('[data-notifications-poll]');

    if (notificationsList) {
        import('./notifications.js').then(({ startNotificationPolling }) => {
            startNotificationPolling(notificationsList.dataset.notificationsPoll);
        });
    }
});
