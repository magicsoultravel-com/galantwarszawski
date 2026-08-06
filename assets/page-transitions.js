// ==========================================================
// Page Transitions Manager
// Handles AJAX page loading with smooth transitions
// Integrates with gfx-tuner for transition effect selection
// ==========================================================

(function () {
    'use strict';

    const STORAGE_KEY = 'gfx-transition';
    const TRANSITION_DURATION = 500; // ms — must match CSS transition duration

    // Transition effect definitions
    // Each effect has an "out" class (applied to leaving content) and an "in" class (applied to entering content)
    // "out" can be a string (same for both directions) or an object with "forward" and "backward" keys
    const EFFECTS = {
        'fade': {
            out: 'pt-out-fade',
            in: 'pt-in-fade'
        },
        'slide': {
            out: { forward: 'pt-out-slide-left', backward: 'pt-out-slide-right' },
            in: 'pt-in-slide'
        },
        'fade-scale': {
            out: 'pt-out-fade-scale',
            in: 'pt-in-fade-scale'
        },
        'slide-fade': {
            out: { forward: 'pt-out-slide-fade-left', backward: 'pt-out-slide-fade-right' },
            in: 'pt-in-slide-fade'
        },
        'flip': {
            out: 'pt-out-flip',
            in: 'pt-in-flip'
        }
    };

    // --- Storage helpers ---

    function getEffect() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved && EFFECTS.hasOwnProperty(saved)) return saved;
        } catch (e) {
            // localStorage unavailable
        }
        return 'fade'; // default
    }

    function setEffect(effect) {
        try {
            localStorage.setItem(STORAGE_KEY, effect);
        } catch (e) {
            // localStorage unavailable
        }
    }

    // --- Class helpers ---

    function getOutClass(effect, direction) {
        const config = EFFECTS[effect] || EFFECTS['fade'];
        if (typeof config.out === 'string') {
            return config.out;
        }
        return direction === 'forward' ? config.out.forward : config.out.backward;
    }

    function getInClass(effect) {
        const config = EFFECTS[effect] || EFFECTS['fade'];
        return config.in;
    }

    // --- Loading overlay ---

    function showLoading() {
        let loading = document.getElementById('pt-loading');
        if (!loading) {
            loading = document.createElement('div');
            loading.id = 'pt-loading';
            loading.className = 'pt-loading';
            loading.innerHTML = '<div class="pt-loading-spinner"></div>';
            document.body.appendChild(loading);
        }
        loading.classList.add('show');
    }

    function hideLoading() {
        const loading = document.getElementById('pt-loading');
        if (loading) {
            loading.classList.remove('show');
        }
    }

    // --- Subpage order helper ---

    // Returns the list of subpage URLs for the current language
    function getSubpageOrder() {
        const section = document.getElementById('scrollytelling');
        if (section && section.dataset.subpages) {
            return section.dataset.subpages.split(',');
        }
        // Fallback: derive from current language
        const lang = document.documentElement.lang || 'pl';
        const langSuffix = lang === 'pl' ? '' : '-' + lang;
        return [
            'index' + langSuffix + '.html',
            'rezerwacje' + langSuffix + '.html',
            'lokalizacja' + langSuffix + '.html',
            'menu' + langSuffix + '.html'
        ];
    }

    // Returns the index of the current page in the subpage order
    function getCurrentPageIndex() {
        const subpages = getSubpageOrder();
        const currentPath = window.location.pathname.split('/').pop();
        return subpages.indexOf(currentPath);
    }

    // --- Core navigation ---

    // Navigate to a URL with a smooth transition
    function navigate(url, direction) {
        const effect = getEffect();
        const wrapper = document.getElementById('page-transition-wrapper');

        if (!wrapper) {
            // No wrapper — fall back to normal navigation
            window.location.href = url;
            return;
        }

        // If direction not specified, try to determine from subpage order
        if (!direction) {
            const subpages = getSubpageOrder();
            const currentPath = window.location.pathname.split('/').pop();
            const currentIndex = subpages.indexOf(currentPath);
            const targetIndex = subpages.indexOf(url.split('/').pop());
            if (targetIndex > currentIndex) direction = 'forward';
            else if (targetIndex < currentIndex) direction = 'backward';
            else direction = 'forward';
        }

        const outClass = getOutClass(effect, direction);
        const inClass = getInClass(effect);

        // Apply out class to trigger transition
        wrapper.classList.add(outClass);

        // Wait for transition to complete, then load new content
        setTimeout(() => {
            showLoading();

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Extract the page-transition-wrapper content
                    const newWrapper = doc.getElementById('page-transition-wrapper');

                    if (newWrapper) {
                        // Replace content
                        wrapper.innerHTML = newWrapper.innerHTML;

                        // Remove out class, add in class
                        wrapper.classList.remove(outClass);
                        wrapper.classList.add(inClass);

                        hideLoading();

                        // Update URL via History API
                        history.pushState(
                            { url: url, direction: direction },
                            '',
                            url
                        );

                        // Re-initialize scripts for the new content
                        reinitializeScripts();

                        // Clean up in class after transition
                        setTimeout(() => {
                            wrapper.classList.remove(inClass);
                        }, TRANSITION_DURATION);
                    } else {
                        // No wrapper found — fall back to normal navigation
                        window.location.href = url;
                    }
                })
                .catch(() => {
                    // Error — fall back to normal navigation
                    window.location.href = url;
                });
        }, TRANSITION_DURATION);
    }

    // --- Script re-initialization ---

    function reinitializeScripts() {
        // Re-initialize scrollytelling (Intersection Observer)
        if (typeof initScrollytelling === 'function') {
            initScrollytelling();
        }

        // Re-apply gfx-tuner settings (layout, transition effect)
        if (typeof applyGfxSettings === 'function') {
            applyGfxSettings();
        }

        // Re-initialize menu language
        if (typeof setMenuLanguage === 'function') {
            const htmlLang = document.documentElement.lang;
            const menuLang = htmlLang === 'en' ? 'en' : (htmlLang === 'fr' ? 'fr' : (htmlLang === 'es' ? 'es' : 'pl'));
            setMenuLanguage(menuLang);
        }

        // Re-set copyright year
        const yearSpan = document.getElementById('current-year');
        if (yearSpan) {
            yearSpan.textContent = new Date().getFullYear();
        }
    }

    // --- Link interception ---

    function initLinkInterception() {
        document.addEventListener('click', function (e) {
            const anchor = e.target.closest('a[href]');
            if (!anchor) return;

            const href = anchor.getAttribute('href');

            // Skip: external links, anchors, javascript:, empty
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('http') || href.startsWith('mailto:') || href.startsWith('tel:')) return;

            // Skip: ctrl/cmd click or middle click (open in new tab)
            if (e.ctrlKey || e.metaKey || e.button === 1) return;

            // Skip: if the link is to the same page
            const currentPath = window.location.pathname.split('/').pop();
            const targetPath = href.split('/').pop();
            if (currentPath === targetPath) return;

            e.preventDefault();
            navigate(href);
        });
    }

    // --- Back/forward button support ---

    function initPopState() {
        window.addEventListener('popstate', function (e) {
            if (e.state && e.state.url) {
                // AJAX navigation back/forward
                const url = e.state.url;
                const direction = e.state.direction || 'backward';

                const wrapper = document.getElementById('page-transition-wrapper');
                if (!wrapper) {
                    window.location.href = url;
                    return;
                }

                const effect = getEffect();
                const outClass = getOutClass(effect, direction);
                const inClass = getInClass(effect);

                wrapper.classList.add(outClass);

                setTimeout(() => {
                    showLoading();

                    fetch(url)
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.text();
                        })
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newWrapper = doc.getElementById('page-transition-wrapper');

                            if (newWrapper) {
                                wrapper.innerHTML = newWrapper.innerHTML;
                                wrapper.classList.remove(outClass);
                                wrapper.classList.add(inClass);
                                hideLoading();
                                reinitializeScripts();
                                setTimeout(() => {
                                    wrapper.classList.remove(inClass);
                                }, TRANSITION_DURATION);
                            } else {
                                window.location.href = url;
                            }
                        })
                        .catch(() => {
                            window.location.href = url;
                        });
                }, TRANSITION_DURATION);
            } else {
                // No AJAX state — normal back/forward, reload
                window.location.reload();
            }
        });
    }

    // --- Public API ---

    window.PageTransitions = {
        navigate: navigate,
        getEffect: getEffect,
        setEffect: setEffect,
        EFFECTS: EFFECTS,
        getSubpageOrder: getSubpageOrder,
        getCurrentPageIndex: getCurrentPageIndex,
        init: function () {
            initLinkInterception();
            initPopState();
        }
    };

    // --- Initialize on DOM ready ---

    document.addEventListener('DOMContentLoaded', function () {
        PageTransitions.init();
    });

})();
