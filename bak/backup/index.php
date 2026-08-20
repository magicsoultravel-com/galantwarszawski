<?php
// index.php - Scrollytelling implementation with full-screen background images.

// 1. PHP Logic: Dynamically find image files in the current folder, supporting common formats.
$image_pattern = '{*.png,*.jpg,*.jpeg,*.gif}';
$image_files = glob($image_pattern, GLOB_BRACE);
sort($image_files);

// Default content for the story steps (flexible)
$default_content = [
    ['title' => 'Strona w trakcie budowy', 'text' => 'Galant Warszawski to miejsce z duszą, poświęcone wyjątkowej i klimatycznej kuchni starowarszawskiej..'],
    ['title' => 'O Nas', 'text' => 'Nasza restauracja zlokalizowana jest w prestiżowej części stolicy przy ul. Świętokrzyskiej 32. Serdecznie zapraszamy do odwiedzin.
Galant Warszawski to miejsce stworzone z pasji do tradycji i szacunku dla polskiej kultury kulinarnej. Nasza specjalność to kuchnia staropolska – autentyczna, wykwintna i inspirowana historycznymi recepturami, które przywracamy w nowoczesnej odsłonie.
W eleganckim wnętrzu restauracji łączymy wyjątkowy smak z wysokim standardem obsługi, tworząc atmosferę idealną zarówno do codziennego lunchu, jak i uroczystych kolacji.
Na górnym piętrze restauracji znajduje się profesjonalnie wyposażona sala konferencyjna, doskonale przystosowana do organizacji:
 • spotkań biznesowych,
 • szkoleń i prezentacji,
 • wydarzeń okolicznościowych i przyjęć zamkniętych.
Zapewniamy kompleksową obsługę, elastyczność organizacyjną oraz indywidualne podejście do każdego klienta.
Zachęcamy do odwiedzenia naszej restauracji i skorzystania z wyjątkowej oferty kulinarnej oraz przestrzeni sprzyjającej zarówno pracy, jak i celebracji wyjątkowych chwil.
Galant Warszawski – smak tradycji w sercu Warszawy.'],
    ['title' => 'Strona w trakcie budowy', 'text' => 'Strona w trakcie budowy.'],
    ['title' => 'Strona w trakcie budowy', 'text' => 'Strona w trakcie budowy.'],
];

// Determine the content to use based on images found
$story_points = [];
$story_images = [];

foreach ($image_files as $i => $filename) {
    if (!isset($default_content[$i])) {
        // Stop if we run out of defined content
        break; 
    }
    $story_images[] = $filename;
    // Map filename to story content
    $story_points[$filename] = $default_content[$i];
}

// Fallback for 0 images (just use the first 3 placeholders)
if (empty($story_images)) {
    for ($i = 0; $i < 3; $i++) {
        $placeholder_name = "placeholder-step-" . ($i + 1) . ".jpg";
        $story_images[] = $placeholder_name;
        $story_points[$placeholder_name] = $default_content[$i];
    }
}

$first_image = $story_images[0] ?? 'Placeholder';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galant Warszawski - restauracja, kuchnia starowarszawska, polska, tradycyjna</title>
    <!-- Styles are now included via PHP -->
    <style>
        <?php include 'styles.php'; ?>
    </style>
</head>
<body>
    
    <!-- INCLUDE THE NEW TOP BANNER MODULE -->
    <?php include 'top-banner.php'; ?>

    <!-- VISUAL PANEL: Fixed Full-Screen Background Image -->
    <div id="visual-panel">
        <img id="scrolly-image" 
             src="<?php echo htmlspecialchars($first_image); ?>" 
             alt="Scrollytelling Step 1" 
             onerror="
                 this.src='https://picsum.photos/seed/<?php echo htmlspecialchars(hash('crc32', $first_image)); ?>/1920/1080'; 
                 this.style.opacity = 1;
             ">
    </div>

    <!-- SCROLL CONTAINER -->
    <div id="scrolly-container">
        <?php foreach ($story_points as $filename => $content) : ?>
            <div class="story-section" data-image-src="<?php echo htmlspecialchars($filename); ?>">
                <div class="story-content-box">
                    <h2><?php echo htmlspecialchars($content['title']); ?></h2>
                    <p><?php echo htmlspecialchars($content['text']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="final-spacer">
             <p>Strona w trakcie budowy</p>
        </div>
    </div>

    <script>
        const storySections = document.querySelectorAll('.story-section');
        const scrollyImage = document.getElementById('scrolly-image');
        
        // Ensure initial opacity is set for the transition
        scrollyImage.style.opacity = 1; 

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const newSrc = entry.target.getAttribute('data-image-src');

                    // Only update if the source is different
                    if (scrollyImage.src.indexOf(newSrc) === -1) { 
                        // 1. Fade out (Opacity 0)
                        scrollyImage.style.opacity = 0; 

                        // 2. Wait for the CSS transition (0.5s) to start before changing src
                        // We use a small delay here to ensure the browser registers opacity: 0
                        setTimeout(() => {
                             scrollyImage.src = newSrc;
                             // 3. Fade in (Opacity 1)
                             scrollyImage.style.opacity = 1;

                             // Fallback: If the local file fails, use a public image URL
                             scrollyImage.onerror = function() {
                                 const seed = newSrc.replace(/\D/g, '') || 'default';
                                 this.src = `https://picsum.photos/seed/${seed}/1920/1080`;
                                 this.style.opacity = 1; 
                             };
                        }, 50); 
                    }
                }
            });
        }, { threshold: 0.5 }); // Image switches when the section center crosses the viewport center
        
        storySections.forEach(section => observer.observe(section));
    </script>
</body>
</html>
