function navigateToToken(tokenInput) {
    const token = tokenInput.value.trim();

    if (token) {
        window.location.href = `/reception/check/${encodeURIComponent(token)}`;
    } else {
        tokenInput.focus();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const tokenInput = document.querySelector('[data-reception-token-input]');
    const lookupButton = document.querySelector('[data-reception-lookup]');

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
});
