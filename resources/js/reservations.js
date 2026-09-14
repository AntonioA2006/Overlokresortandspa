import { bindDateValidation } from './validation.js';

document.addEventListener('DOMContentLoaded', () => {
    bindDateValidation(document.querySelector('[data-reservation-search-form]'));
});
