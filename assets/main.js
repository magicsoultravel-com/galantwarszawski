// Set dynamic copyright year
document.addEventListener('DOMContentLoaded', () => {
    const yearSpan = document.getElementById('current-year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
});

// Set menu language based on page language
document.addEventListener('DOMContentLoaded', () => {
    // Check the HTML lang attribute to determine the page language
    const htmlLang = document.documentElement.lang;
    const menuLang = htmlLang === 'en' ? 'en' : (htmlLang === 'fr' ? 'fr' : (htmlLang === 'es' ? 'es' : 'pl'));
    
    // Call setMenuLanguage if it exists (from menu-modal.js)
    if (typeof setMenuLanguage === 'function') {
        setMenuLanguage(menuLang);
    }
});
