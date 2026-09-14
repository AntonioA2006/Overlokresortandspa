import { clearFieldError, isRequired, isValidDateRange, setFieldError } from '../validation.js';
import { initDatePickers } from './date-picker.js';
import { initGuestsPicker } from './guests.js';

function getFormMessages(form) {
    return {
        searching: form.dataset.searchingText || '',
        checkInRequired: form.dataset.errorCheckInRequired || '',
        checkOutRequired: form.dataset.errorCheckOutRequired || '',
        checkOutAfterCheckIn: form.dataset.errorCheckOutAfterCheckIn || '',
    };
}

function initLoadingState(form) {
    const submitButton = form.querySelector('[data-search-submit]');
    const messages = getFormMessages(form);

    if (!submitButton) {
        return;
    }

    form.addEventListener('submit', () => {
        submitButton.classList.add('is-loading');
        submitButton.setAttribute('aria-busy', 'true');
        submitButton.disabled = true;

        const text = submitButton.querySelector('.booking-bar__submit-text');

        if (text) {
            submitButton.dataset.originalLabel = text.textContent.trim();
            text.textContent = messages.searching;
        }
    });
}

function submitButtonReset(form) {
    const submitButton = form.querySelector('[data-search-submit]');

    if (!submitButton) {
        return;
    }

    submitButton.classList.remove('is-loading');
    submitButton.removeAttribute('aria-busy');
    submitButton.disabled = false;

    const text = submitButton.querySelector('.booking-bar__submit-text');

    if (text && submitButton.dataset.originalLabel) {
        text.textContent = submitButton.dataset.originalLabel;
    }
}

function bindSearchValidation(form, guestsApi) {
    const messages = getFormMessages(form);
    const checkInField = form.querySelector('[data-field-check-in]');
    const checkOutField = form.querySelector('[data-field-check-out]');
    const checkInInput = form.querySelector('[name="check_in_date"]');
    const checkOutInput = form.querySelector('[name="check_out_date"]');

    form.addEventListener('booking:dates-changed', () => {
        clearFieldError(checkInField);
        clearFieldError(checkOutField);
    });

    form.addEventListener('submit', (event) => {
        let valid = true;

        clearFieldError(checkInField);
        clearFieldError(checkOutField);

        if (!isRequired(checkInInput?.value)) {
            setFieldError(checkInField, messages.checkInRequired);
            valid = false;
        }

        if (!isRequired(checkOutInput?.value)) {
            setFieldError(checkOutField, messages.checkOutRequired);
            valid = false;
        }

        if (valid && !isValidDateRange(checkInInput.value, checkOutInput.value)) {
            setFieldError(checkOutField, messages.checkOutAfterCheckIn);
            valid = false;
        }

        if (!valid) {
            event.preventDefault();
            submitButtonReset(form);

            return;
        }

        guestsApi?.closePanel?.();
    });
}

export function initBookingSearchForms() {
    document.querySelectorAll('[data-booking-search-form]').forEach((form) => {
        initDatePickers(form);
        const guestsApi = initGuestsPicker(form);
        initLoadingState(form);
        bindSearchValidation(form, guestsApi);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initBookingSearchForms();
});
