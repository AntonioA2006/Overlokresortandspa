import { clearFieldError, setFieldError } from '../validation.js';

function parseGuestLabels(form) {
    try {
        return JSON.parse(form.dataset.guestLabels || '{}');
    } catch {
        return {};
    }
}

function formatCount(template, count) {
    return String(template || '').replace(':count', String(count));
}

export function buildGuestSummary(adults, children, labels) {
    const parts = [];

    if (adults > 0) {
        parts.push(formatCount(adults === 1 ? labels.adultOne : labels.adultOther, adults));
    }

    if (children === 0) {
        parts.push(formatCount(labels.childZero, children));
    } else if (children === 1) {
        parts.push(formatCount(labels.childOne, children));
    } else {
        parts.push(formatCount(labels.childOther, children));
    }

    return parts.join(labels.join || ' · ');
}

export function initGuestsPicker(form) {
    const trigger = form.querySelector('[data-guests-trigger]');
    const panel = form.querySelector('[data-guests-panel]');
    const summary = form.querySelector('[data-guests-summary]');
    const adultsInput = form.querySelector('[data-guest-input="adults"]');
    const childrenInput = form.querySelector('[data-guest-input="children"]');
    const guestsField = form.querySelector('[data-guests-field]');

    if (!trigger || !panel || !summary || !adultsInput || !childrenInput) {
        return;
    }

    const maxGuests = Number(form.dataset.maxGuests || 8);
    const labels = parseGuestLabels(form);
    const messages = {
        guestsMax: form.dataset.errorGuestsMax || '',
    };

    const getCounts = () => ({
        adults: Number(adultsInput.value || 0),
        children: Number(childrenInput.value || 0),
    });

    const updateSummary = () => {
        const { adults, children } = getCounts();
        summary.textContent = buildGuestSummary(adults, children, labels);

        form.querySelectorAll('[data-guest-stepper]').forEach((row) => {
            const type = row.dataset.guestStepper;
            const valueEl = row.querySelector('[data-stepper-value]');

            if (valueEl) {
                valueEl.textContent = type === 'adults' ? adultsInput.value : childrenInput.value;
            }
        });
    };

    const closePanel = () => {
        panel.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
        guestsField?.classList.remove('is-open');
    };

    const openPanel = () => {
        panel.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        guestsField?.classList.add('is-open');
    };

    trigger.addEventListener('click', () => {
        if (panel.hidden) {
            openPanel();
        } else {
            closePanel();
        }
    });

    document.addEventListener('click', (event) => {
        if (!guestsField?.contains(event.target)) {
            closePanel();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closePanel();
        }
    });

    form.querySelectorAll('[data-guest-stepper]').forEach((row) => {
        const type = row.dataset.guestStepper;
        const decrease = row.querySelector('[data-stepper-decrease]');
        const increase = row.querySelector('[data-stepper-increase]');
        const input = form.querySelector(`[data-guest-input="${type}"]`);

        if (!decrease || !increase || !input) {
            return;
        }

        const min = Number(input.min || 0);
        const max = Number(input.max || maxGuests);

        const applyValue = (nextValue) => {
            const bounded = Math.min(Math.max(nextValue, min), max);
            const otherType = type === 'adults' ? 'children' : 'adults';
            const otherInput = form.querySelector(`[data-guest-input="${otherType}"]`);
            const total = bounded + Number(otherInput?.value || 0);

            if (total > maxGuests) {
                setFieldError(guestsField, messages.guestsMax);

                return;
            }

            clearFieldError(guestsField);
            input.value = String(bounded);
            updateSummary();
        };

        decrease.addEventListener('click', () => applyValue(Number(input.value) - 1));
        increase.addEventListener('click', () => applyValue(Number(input.value) + 1));
    });

    updateSummary();

    return { closePanel };
}
