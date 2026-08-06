// Scrollytelling - Intersection Observer for image swapping on scroll
// Exports initScrollytelling() for re-initialization after AJAX content replacement

(function () {
    'use strict';

    let observer = null;

    function initScrollytelling() {
        // Disconnect existing observer to avoid duplicates
        if (observer) {
            observer.disconnect();
            observer = null;
        }

        if (!('IntersectionObserver' in window)) return;

        const scrollytellingSection = document.getElementById('scrollytelling');
        if (!scrollytellingSection) return;

        const steps = scrollytellingSection.querySelectorAll('.step');
        const image = document.getElementById('scrolly-image');
        if (!steps.length || !image) return;

        // Preload images
        steps.forEach(step => {
            const src = step.dataset.img;
            if (src) { const img = new Image(); img.src = src; }
        });

        // Function to swap image source with fade effect
        const swapImage = (newSrc) => {
            if (!newSrc || image.src.endsWith(newSrc)) return;

            image.classList.add('fade-out');

            setTimeout(() => {
                image.src = newSrc;
                image.classList.remove('fade-out');
                image.classList.add('fade-in');

                setTimeout(() => {
                    image.classList.remove('fade-in');
                }, 500);
            }, 500);
        };

        // Intersection Observer setup
        observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                // In horizontal (arrow) layout the image is driven by the cog menu,
                // so don't let the scroll observer fight with it.
                if (entry.isIntersecting && !scrollytellingSection.classList.contains('layout-horizontal')) {
                    swapImage(entry.target.dataset.img);
                }
            });
        }, {
            threshold: 0.5,
            rootMargin: '0px 0px 0px 0px'
        });

        // Start observing all steps
        steps.forEach(step => {
            if (step.dataset.img) {
                observer.observe(step);
            }
        });
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', initScrollytelling);

    // Export for external use (called by page-transitions.js after AJAX content replacement)
    window.initScrollytelling = initScrollytelling;
})();
