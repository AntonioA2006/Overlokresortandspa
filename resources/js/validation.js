import { showToast } from './ui/toast.js';

export function isRequired(value) {
    return value !== null && String(value).trim() !== '';
}

export function isValidDateRange(checkIn, checkOut) {
    if (!checkIn || !checkOut) {
        return false;
    }

    return new Date(checkOut) > new Date(checkIn);
}

export function clearFieldError(field) {
    if (!field) {
        return;
    }

    field.classList.remove('is-error');
    const error = field.querySelector('[data-field-error]');

    if (error) {
        error.textContent = '';
        error.hidden = true;
    }
}

export function setFieldError(field, message) {
    if (!field) {
        showToast(message, 'error');

        return;
    }

    field.classList.add('is-error');
    const error = field.querySelector('[data-field-error]');

    if (error) {
        error.textContent = message;
        error.hidden = false;
    }
}

export function bindDateValidation(form) {
    if (!form) {
        return;
    }

    const checkInField = form.querySelector('[data-field-check-in]');
    const checkOutField = form.querySelector('[data-field-check-out]');
    const checkInInput = form.querySelector('[name="check_in_date"]');
    const checkOutInput = form.querySelector('[name="check_out_date"]');

    const validate = () => {
        if (checkInField) {
            clearFieldError(checkInField);
        }

        if (checkOutField) {
            clearFieldError(checkOutField);
        }

        if (!isRequired(checkInInput?.value)) {
            setFieldError(checkInField, 'Selecciona tu fecha de llegada.');

            return false;
        }

        if (!isRequired(checkOutInput?.value)) {
            setFieldError(checkOutField, 'Selecciona tu fecha de salida.');

            return false;
        }

        if (!isValidDateRange(checkInInput.value, checkOutInput.value)) {
            setFieldError(checkOutField, 'La salida debe ser posterior a la llegada.');

            return false;
        }

        return true;
    };

    [checkInInput, checkOutInput].forEach((input) => {
        input?.addEventListener('input', () => {
            if (checkInField) {
                clearFieldError(checkInField);
            }

            if (checkOutField) {
                clearFieldError(checkOutField);
            }
        });
    });

    form.addEventListener('submit', (event) => {
        if (!validate()) {
            event.preventDefault();
        }
    });
}
