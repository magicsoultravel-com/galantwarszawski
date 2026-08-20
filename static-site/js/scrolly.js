/* ============================================================ *
 *  SCROLLYTELLING — Restauracja Galant Warszawski
 *  Adapted from the inline script in index.php.
 *  Fixed full-screen background image swaps with a fade effect
 *  as the user scrolls between content steps.
 * ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
    const scrollytellingSection = document.getElementById('scrollytelling');
    if (!scrollytellingSection || !('IntersectionObserver' in window)) return;

    const steps = scrollytellingSection.querySelectorAll('.step');
    const image = document.getElementById('scrolly-image');

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
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
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
});