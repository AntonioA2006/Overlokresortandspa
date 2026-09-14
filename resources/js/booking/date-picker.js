import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';

const localeMap = {
    es: Spanish,
    en: flatpickr.l10ns.default,
};

function formatDisplayDate(date, locale) {
    if (!date) {
        return '';
    }

    const day = date.getDate();
    const month = date.toLocaleString(locale === 'es' ? 'es-ES' : 'en-US', { month: 'short' }).replace('.', '');
    const year = date.getFullYear();

    return `${day} ${month.toUpperCase()} ${year}`;
}

function syncDisplayInput(displayInput, date) {
    if (!displayInput) {
        return;
    }

    const locale = document.documentElement.lang?.startsWith('es') ? 'es' : 'en';
    displayInput.value = formatDisplayDate(date, locale);
}

export function initDatePickers(form) {
    const checkInHidden = form.querySelector('[data-date-value="check-in"]');
    const checkOutHidden = form.querySelector('[data-date-value="check-out"]');
    const checkInDisplay = form.querySelector('[data-date-display="check-in"]');
    const checkOutDisplay = form.querySelector('[data-date-display="check-out"]');

    if (!checkInHidden || !checkOutHidden || !checkInDisplay || !checkOutDisplay) {
        return null;
    }

    const localeCode = document.documentElement.lang?.startsWith('es') ? 'es' : 'en';
    const locale = localeMap[localeCode] ?? flatpickr.l10ns.default;
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let checkOutPicker;

    const checkInPicker = flatpickr(checkInDisplay, {
        locale,
        dateFormat: 'Y-m-d',
        minDate: today,
        disableMobile: true,
        clickOpens: true,
        allowInput: false,
        defaultDate: checkInHidden.value || null,
        onReady: (_selectedDates, _dateStr, instance) => {
            if (checkInHidden.value) {
                syncDisplayInput(checkInDisplay, instance.selectedDates[0]);
            }
        },
        onChange: (selectedDates, dateStr) => {
            checkInHidden.value = dateStr;
            syncDisplayInput(checkInDisplay, selectedDates[0]);

            if (checkOutPicker) {
                const nextDay = new Date(selectedDates[0]);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOutPicker.set('minDate', nextDay);

                if (checkOutPicker.selectedDates[0] && checkOutPicker.selectedDates[0] <= selectedDates[0]) {
                    checkOutPicker.clear();
                    checkOutHidden.value = '';
                    syncDisplayInput(checkOutDisplay, null);
                }
            }

            form.dispatchEvent(new CustomEvent('booking:dates-changed'));
        },
        onClose: () => {
            checkInDisplay.closest('[data-field]')?.classList.remove('is-focused');
        },
        onOpen: () => {
            checkInDisplay.closest('[data-field]')?.classList.add('is-focused');
            checkOutPicker?.close();
        },
    });

    const checkOutMin = checkInHidden.value
        ? new Date(new Date(`${checkInHidden.value}T00:00:00`).getTime() + 86400000)
        : new Date(today.getTime() + 86400000);

    checkOutPicker = flatpickr(checkOutDisplay, {
        locale,
        dateFormat: 'Y-m-d',
        minDate: checkOutMin,
        disableMobile: true,
        clickOpens: true,
        allowInput: false,
        defaultDate: checkOutHidden.value || null,
        onReady: (_selectedDates, _dateStr, instance) => {
            if (checkOutHidden.value) {
                syncDisplayInput(checkOutDisplay, instance.selectedDates[0]);
            }
        },
        onChange: (selectedDates, dateStr) => {
            checkOutHidden.value = dateStr;
            syncDisplayInput(checkOutDisplay, selectedDates[0]);
            form.dispatchEvent(new CustomEvent('booking:dates-changed'));
        },
        onClose: () => {
            checkOutDisplay.closest('[data-field]')?.classList.remove('is-focused');
        },
        onOpen: () => {
            checkOutDisplay.closest('[data-field]')?.classList.add('is-focused');
            checkInPicker.close();
        },
    });

    return { checkInPicker, checkOutPicker };
}
