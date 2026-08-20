<?php
// Start session
if (session_status() === PHP_SESSION_NONE) session_start();

include 'inc/functions.php';
include 'inc/auth.php';

$isLoggedIn = is_logged_in();
$email = $_SESSION['email'] ?? '';

include 'templates/header.php';
?>

<section id="scrollytelling">

<div class="scroll-image" aria-hidden="true">
<img id="scrolly-image" src="1.jpg" alt="Scene image">
</div>

<div class="scroll-text">
<div class="step" data-img="1.jpg">

<p><?= spawn_content('about') ?></p>
<p><?= spawn_content('open-hours')?></p>

</div>

<div class="step" data-img="2.jpg">
<a id="contact"></a>
<p><?= spawn_content('contact')?></p>
</div>

<div class="step" data-img="3.jpg">

<a id="location"></a>
<p>

<?= spawn_content('location') ?> 


<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4887.1372428749155!2d20.998504114466524!3d52.2330547087358!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x471ecde1eb35fa97%3A0x5bd58fac56caab87!2sGalant%20Warszawski!5e0!3m2!1sen!2spl!4v1761685045459!5m2!1sen!2spl" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if ('IntersectionObserver' in window) {
        const scrollytellingSection = document.getElementById('scrollytelling');
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
    }
});
</script>

<?php include 'templates/footer.php'; ?>