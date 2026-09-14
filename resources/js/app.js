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
});
