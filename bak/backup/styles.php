/* Core Layout Styles */
body { 
    margin: 0; 
    font-family: 'Inter', sans-serif;
    background-color: #f4f4f4;
    line-height: 1.6;
}
#visual-panel { 
    position: fixed; 
    top: 0; 
    left: 0; 
    width: 100vw; 
    height: 100vh; 
    z-index: 1; 
    background: #1f2937; /* Dark background */
}
#scrolly-image { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
    transition: opacity 0.5s ease; /* Simple fade transition is kept */
}
#scrolly-container { 
    position: relative; 
    z-index: 2; 
    background: none; /* Semi-transparent white to slightly obscure background rgba(255,255,255,0.95) */
    box-shadow: 0 0 50px rgba(0,0,0,0.5);
    
    /* MOBILE FIRST: Push content down for the 60px banner + 10px buffer */
    margin-top: 70px; 
}

/* --- TOP BANNER STYLES (Merged) --- */
#top-banner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 60px; /* MOBILE FIRST DEFAULT SIZE */
    background-color: rgba(0, 0, 0, 0.4); 
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: space-between; 
    padding: 0 20px; 
    backdrop-filter: blur(5px);
    box-sizing: border-box; 
}
.logo-container {
    display: flex;
    align-items: center;
}
.logo {
    height: 40px; /* MOBILE FIRST DEFAULT SIZE */
    width: auto;
}
.edge-text {
    color: #ffffff;
    font-weight: 600;
    font-size: 14px;
}

/* --- RESPONSIVE OVERRIDES (Tablet and up: 768px) --- */
@media (min-width: 768px) {
    #top-banner {
        height: 100px; /* DESKTOP: Larger banner height */
    }
    .logo {
        height: 80px; /* DESKTOP: Larger logo size */
    }
    #scrolly-container {
        /* DESKTOP OVERRIDE: Push content down for the 100px banner + 10px buffer */
        margin-top: 110px; 
    }
}

/* Story Section Styles */
.story-section { 
    height: 100vh; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    padding: 20px; 
}
.story-content-box { 
    max-width: 600px; 
    text-align: center; 
    background: white; 
    padding: 40px; 
    border-radius: 12px; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.15); 
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255,255,255,0.8);
    transition: transform 0.3s ease-in-out;
}
.story-section:hover .story-content-box {
    transform: translateY(-5px);
}
h2 {
    font-size: 1.8rem;
    color: #374151;
    margin-bottom: 0.5rem;
}
p {
    font-size: 1.1rem;
    color: #6b7280;
}

/* Footer/Spacer */
.final-spacer { 
    height: 50vh; 
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f4f4f4;
    color: #374151;
    padding: 20px;
}
