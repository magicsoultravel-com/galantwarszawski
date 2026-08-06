// GFX Tuner - cog icon popup with filter dropdown + font settings
(function () {
    'use strict';

    const STORAGE_KEY_FILTER = 'gfx-filter';
    const STORAGE_KEY_FONTS = 'gfx-fonts';
    const STORAGE_KEY_THEME = 'gfx-modal-theme';
    const STORAGE_KEY_PIN = 'gfx-pinned';
    const STORAGE_KEY_LAYOUT = 'gfx-layout';
    const STORAGE_KEY_TRANSITION = 'gfx-transition';

    // Menu background themes: value -> CSS value for --modal-bg
    const THEMES = {
        'dark': '#1a1a1a',
        'light': '#f8f4e9',
        'sepia': 'linear-gradient(135deg, #f4e4bc 0%, #e8d4a8 50%, #dcc89a 100%)',
        'paper': '#fdf6e3',
        'vintage': '#e8dcc0'
    };

    // Layout options: value -> mode
    const LAYOUTS = {
        vertical: 'vertical',
        horizontal: 'horizontal'
    };

    // Transition effect options for page transitions and horizontal mode steps
    const TRANSITIONS = {
        'fade': 'Fade',
        'slide': 'Slide',
        'fade-scale': 'Fade + Scale',
        'slide-fade': 'Slide + Fade',
        'flip': 'Flip'
    };

    // Filter options: value -> body class
    const FILTERS = {
        'original': '',
        'vintage': 'gfx-filter-vintage',
        'bw': 'gfx-filter-bw',
        'sepia': 'gfx-filter-sepia',
        'warm': 'gfx-filter-warm',
        'cool': 'gfx-filter-cool'
    };

    // Font options: 10 fonts available for each group
    const FONT_OPTIONS = [
        { value: '"Tahoma", sans-serif',                         label: 'Tahoma' },
        { value: '"Verdana", sans-serif',                         label: 'Verdana' },
        { value: '"Arial", sans-serif',                          label: 'Arial' },
        { value: '"Helvetica", sans-serif',                      label: 'Helvetica' },
        { value: '"Georgia", "Times New Roman", serif',          label: 'Georgia' },
        { value: '"Times New Roman", serif',                     label: 'Times New Roman' },
        { value: '"Courier New", monospace',                     label: 'Courier New' },
        { value: "'Petit Formal Script', cursive",               label: 'Petit Formal Script' },
        { value: "'Parisienne', cursive",                        label: 'Parisienne' },
        { value: "'Courgette', cursive",                         label: 'Courgette' }
    ];

    // Font group definitions
    const FONT_GROUPS = [
        {
            key: 'body',
            label: 'Body Text',
            familyVar: '--font-body-family',
            sizeVar: '--font-body-scale',
            colorVar: '--font-body-color',
            defaultFamily: '"Tahoma", sans-serif',
            defaultSize: 1.0,
            defaultColor: '#f2f2f2',
            minSize: 0.5,
            maxSize: 2.0,
            step: 0.1,
            isScale: true
        },
        {
            key: 'heading',
            label: 'Headings',
            familyVar: '--font-heading-family',
            sizeVar: '--font-heading-scale',
            colorVar: '--font-heading-color',
            defaultFamily: "'Petit Formal Script', cursive",
            defaultSize: 1.0,
            defaultColor: '#d0bf08',
            minSize: 0.5,
            maxSize: 2.0,
            step: 0.1,
            isScale: true
        },
        {
            key: 'nav',
            label: 'Navigation',
            familyVar: '--font-nav-family',
            sizeVar: '--font-nav-scale',
            colorVar: '--font-nav-color',
            defaultFamily: "'Verdana', sans-serif",
            defaultSize: 1.0,
            defaultColor: '#d0bf08',
            minSize: 0.5,
            maxSize: 2.0,
            step: 0.1,
            isScale: true
        },
        {
            key: 'modal',
            label: 'Modal',
            familyVar: '--font-modal-family',
            sizeVar: '--font-modal-scale',
            colorVar: '--font-modal-color',
            defaultFamily: "'Georgia', 'Times New Roman', serif",
            defaultSize: 1.0,
            defaultColor: '#3e2723',
            minSize: 0.5,
            maxSize: 2.0,
            step: 0.1,
            isScale: true
        }
    ];

    // --- Filter functions ---

    function applyFilter(value) {
        const body = document.body;
        Object.values(FILTERS).forEach(cls => {
            if (cls) body.classList.remove(cls);
        });
        const cls = FILTERS[value];
        if (cls) body.classList.add(cls);
        try {
            localStorage.setItem(STORAGE_KEY_FILTER, value);
        } catch (e) {
            // localStorage unavailable - ignore
        }
    }

    function restoreFilter() {
        let saved = 'original';
        try {
            saved = localStorage.getItem(STORAGE_KEY_FILTER) || 'original';
        } catch (e) {
            // localStorage unavailable - ignore
        }
        if (!FILTERS.hasOwnProperty(saved)) saved = 'original';
        applyFilter(saved);
        return saved;
    }

    // --- Menu background theme functions ---

    function applyTheme(value) {
        const root = document.documentElement;
        if (THEMES.hasOwnProperty(value)) {
            root.style.setProperty('--modal-bg', THEMES[value]);
        }
        try {
            localStorage.setItem(STORAGE_KEY_THEME, value);
        } catch (e) {
            // localStorage unavailable - ignore
        }
    }

    function restoreTheme() {
        let saved = 'sepia';
        try {
            saved = localStorage.getItem(STORAGE_KEY_THEME) || 'sepia';
        } catch (e) {
            // localStorage unavailable - ignore
        }
        if (!THEMES.hasOwnProperty(saved)) saved = 'sepia';
        applyTheme(saved);
        return saved;
    }

    // --- PIN functions ---

    function applyPin(pinned) {
        try {
            localStorage.setItem(STORAGE_KEY_PIN, pinned ? '1' : '0');
        } catch (e) {
            // localStorage unavailable - ignore
        }
    }

    function restorePin() {
        let pinned = false;
        try {
            pinned = localStorage.getItem(STORAGE_KEY_PIN) === '1';
        } catch (e) {
            // localStorage unavailable - ignore
        }
        return pinned;
    }

    // --- Layout functions ---

    let currentStepIndex = 0;
    let prevArrow = null;
    let nextArrow = null;
    let layoutDots = [];

    function goToStep(index, animate) {
        const section = document.getElementById('scrollytelling');
        if (!section) return;
        const steps = section.querySelectorAll('.step');
        const image = document.getElementById('scrolly-image');
        if (!steps.length || !image) return;

        // Loop forever: wrap around at the ends
        currentStepIndex = ((index % steps.length) + steps.length) % steps.length;

        // Show/hide the active step
        steps.forEach((step, i) => {
            step.classList.toggle('active', i === currentStepIndex);
        });

        // Swap background image with the same fade effect as vertical scroll
        const newSrc = steps[currentStepIndex].dataset.img;
        if (newSrc && !image.src.endsWith(newSrc)) {
            if (animate === false) {
                image.src = newSrc;
            } else {
                image.classList.add('fade-out');
                setTimeout(() => {
                    image.src = newSrc;
                    image.classList.remove('fade-out');
                    image.classList.add('fade-in');
                    setTimeout(() => {
                        image.classList.remove('fade-in');
                    }, 500);
                }, 500);
            }
        }

        // Update dots (arrows always enabled for infinite looping)
        layoutDots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentStepIndex);
        });
    }

    function onHorizontalKeydown(e) {
        const tag = (e.target.tagName || '').toLowerCase();
        if (tag === 'input' || tag === 'select' || tag === 'textarea' || e.target.isContentEditable) return;
        const popup = document.getElementById('gfx-popup');
        if (popup && popup.classList.contains('show')) return;
        if (e.key === 'ArrowRight') {
            e.preventDefault();
            goToStep(currentStepIndex + 1);
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            goToStep(currentStepIndex - 1);
        }
    }

    // Map in-page anchor clicks (#contact / #location) to slides in horizontal mode
    function onHorizontalNavClick(e) {
        const anchor = e.target.closest('a[href^="#"]');
        if (!anchor) return;
        const section = document.getElementById('scrollytelling');
        if (!section || !section.classList.contains('layout-horizontal')) return;
        const targetId = anchor.getAttribute('href').slice(1);
        if (!targetId) return;
        const steps = section.querySelectorAll('.step');
        steps.forEach((step, i) => {
            if (step.querySelector('#' + targetId)) {
                e.preventDefault();
                goToStep(i);
            }
        });
    }

    function setupHorizontalLayout() {
        const section = document.getElementById('scrollytelling');
        if (!section) return;

        // Remove any existing nav before rebuilding
        teardownHorizontalLayout();

        // Full-screen nav container (pointer-events none; buttons re-enable)
        const nav = document.createElement('div');
        nav.id = 'gfx-horizontal-nav';
        nav.className = 'gfx-horizontal-nav';

        prevArrow = document.createElement('button');
        prevArrow.className = 'gfx-h-arrow gfx-h-arrow-prev';
        prevArrow.type = 'button';
        prevArrow.setAttribute('aria-label', 'Previous slide');
        prevArrow.title = 'Previous';
        prevArrow.innerHTML = '&#8249;';
        prevArrow.addEventListener('click', () => {
            if (typeof PageTransitions !== 'undefined' && PageTransitions.navigate) {
                const subpages = PageTransitions.getSubpageOrder();
                const currentIndex = PageTransitions.getCurrentPageIndex();
                if (currentIndex > 0) {
                    PageTransitions.navigate(subpages[currentIndex - 1], 'backward');
                }
            } else {
                goToStep(currentStepIndex - 1);
            }
        });

        nextArrow = document.createElement('button');
        nextArrow.className = 'gfx-h-arrow gfx-h-arrow-next';
        nextArrow.type = 'button';
        nextArrow.setAttribute('aria-label', 'Next slide');
        nextArrow.title = 'Next';
        nextArrow.innerHTML = '&#8250;';
        nextArrow.addEventListener('click', () => {
            if (typeof PageTransitions !== 'undefined' && PageTransitions.navigate) {
                const subpages = PageTransitions.getSubpageOrder();
                const currentIndex = PageTransitions.getCurrentPageIndex();
                if (currentIndex < subpages.length - 1) {
                    PageTransitions.navigate(subpages[currentIndex + 1], 'forward');
                }
            } else {
                goToStep(currentStepIndex + 1);
            }
        });

        nav.appendChild(prevArrow);
        nav.appendChild(nextArrow);

        // Set arrow disabled states based on subpage position
        if (typeof PageTransitions !== 'undefined' && PageTransitions.getSubpageOrder) {
            const subpages = PageTransitions.getSubpageOrder();
            const currentIndex = PageTransitions.getCurrentPageIndex();
            prevArrow.disabled = currentIndex <= 0;
            nextArrow.disabled = currentIndex >= subpages.length - 1;
        }

        // Dot indicators
        const dotsWrap = document.createElement('div');
        dotsWrap.className = 'gfx-h-dots';
        const steps = section.querySelectorAll('.step');
        layoutDots = [];
        steps.forEach((step, i) => {
            const dot = document.createElement('button');
            dot.className = 'gfx-h-dot';
            dot.type = 'button';
            dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
            dot.addEventListener('click', () => goToStep(i));
            dotsWrap.appendChild(dot);
            layoutDots.push(dot);
        });
        nav.appendChild(dotsWrap);

        document.body.appendChild(nav);

        // Reset to the first visible slide, no fade on initial load
        goToStep(0, false);

        document.addEventListener('keydown', onHorizontalKeydown);
        document.addEventListener('click', onHorizontalNavClick);

        // Scroll to top so the slideshow fills the screen
        window.scrollTo({ top: 0, behavior: 'auto' });
    }

    function teardownHorizontalLayout() {
        const nav = document.getElementById('gfx-horizontal-nav');
        if (nav) nav.remove();
        prevArrow = null;
        nextArrow = null;
        layoutDots = [];
        document.removeEventListener('keydown', onHorizontalKeydown);
        document.removeEventListener('click', onHorizontalNavClick);
    }

    function applyLayout(value) {
        const section = document.getElementById('scrollytelling');
        if (!section) return;

        if (value === 'horizontal') {
            section.classList.add('layout-horizontal');
            setupHorizontalLayout();
        } else {
            section.classList.remove('layout-horizontal');
            teardownHorizontalLayout();
            const steps = section.querySelectorAll('.step');
            steps.forEach(step => step.classList.remove('active'));
            // Scroll back to the step that was visible in horizontal mode
            if (steps[currentStepIndex]) {
                requestAnimationFrame(() => {
                    steps[currentStepIndex].scrollIntoView({ behavior: 'auto', block: 'start' });
                });
            }
            currentStepIndex = 0;
        }

        try {
            localStorage.setItem(STORAGE_KEY_LAYOUT, value);
        } catch (e) {
            // localStorage unavailable - ignore
        }
    }

    function restoreLayout() {
        let saved = 'vertical';
        try {
            saved = localStorage.getItem(STORAGE_KEY_LAYOUT) || 'vertical';
        } catch (e) {
            // localStorage unavailable - ignore
        }
        if (!LAYOUTS.hasOwnProperty(saved)) saved = 'vertical';
        applyLayout(saved);
        return saved;
    }

    // --- Transition effect functions ---

    function applyTransition(value) {
        const section = document.getElementById('scrollytelling');
        if (section) {
            // Remove all transition classes
            Object.keys(TRANSITIONS).forEach(key => {
                section.classList.remove('transition-' + key);
            });
            // Add the selected transition class (fade-scale is default, no extra class needed)
            if (value && value !== 'fade-scale') {
                section.classList.add('transition-' + value);
            }
        }
        try {
            localStorage.setItem(STORAGE_KEY_TRANSITION, value);
        } catch (e) {
            // localStorage unavailable - ignore
        }
    }

    function restoreTransition() {
        let saved = 'fade-scale';
        try {
            saved = localStorage.getItem(STORAGE_KEY_TRANSITION) || 'fade-scale';
        } catch (e) {
            // localStorage unavailable - ignore
        }
        if (!TRANSITIONS.hasOwnProperty(saved)) saved = 'fade-scale';
        applyTransition(saved);
        return saved;
    }

    // Re-apply all gfx settings (called after AJAX content replacement)
    function applyGfxSettings() {
        restoreFilter();
        restoreTheme();
        restoreLayout();
        restoreTransition();
    }

    // --- Font functions ---

    // Legacy px bases used before the scale conversion (body/modal were px-based)
    const LEGACY_SIZE_BASES = {
        body: 22,
        modal: 14
    };

    // Normalize saved sizes: legacy px values (> group.maxSize) are converted to
    // equivalent scale multipliers, clamped to the valid 0.5-2.0 range
    function normalizeSize(size, group) {
        if (size > group.maxSize) {
            const base = LEGACY_SIZE_BASES[group.key];
            if (base) {
                const scaled = +(size / base).toFixed(1);
                return Math.min(group.maxSize, Math.max(group.minSize, scaled));
            }
            return group.defaultSize;
        }
        return size;
    }

    function applyFontSettings(settings) {
        const root = document.documentElement;
        FONT_GROUPS.forEach(group => {
            const s = settings[group.key] || {};
            if (s.family) {
                root.style.setProperty(group.familyVar, s.family);
            }
            if (s.size !== undefined && s.size !== null) {
                root.style.setProperty(group.sizeVar, s.size);
            }
            if (s.color) {
                root.style.setProperty(group.colorVar, s.color);
            }
        });
    }

    function getDefaultSettings() {
        const settings = {};
        FONT_GROUPS.forEach(group => {
            settings[group.key] = {
                family: group.defaultFamily,
                size: group.defaultSize,
                color: group.defaultColor
            };
        });
        return settings;
    }

    function saveFontSettings(settings) {
        try {
            localStorage.setItem(STORAGE_KEY_FONTS, JSON.stringify(settings));
        } catch (e) {
            // localStorage unavailable - ignore
        }
    }

    // Read settings from localStorage without applying (for event handlers)
    function getFontSettings() {
        let settings = getDefaultSettings();
        try {
            const saved = localStorage.getItem(STORAGE_KEY_FONTS);
            if (saved) {
                const parsed = JSON.parse(saved);
                FONT_GROUPS.forEach(group => {
                    if (parsed[group.key]) {
                        settings[group.key] = {
                            family: parsed[group.key].family || group.defaultFamily,
                            size: parsed[group.key].size !== undefined ? normalizeSize(parsed[group.key].size, group) : group.defaultSize,
                            color: parsed[group.key].color || group.defaultColor
                        };
                    }
                });
            }
        } catch (e) {
            // localStorage unavailable or parse error - use defaults
        }
        return settings;
    }

    // Restore settings from localStorage and apply to CSS variables
    function restoreFontSettings() {
        const settings = getFontSettings();
        applyFontSettings(settings);
        return settings;
    }

    function resetFontSettings() {
        const settings = getDefaultSettings();
        applyFontSettings(settings);
        saveFontSettings(settings);
        return settings;
    }

    // Format size value for display
    function formatSize(size, isScale) {
        return isScale ? size.toFixed(1) + 'x' : size + 'px';
    }

    // --- Popup builder ---

    function buildPopup() {
        const popup = document.createElement('div');
        popup.className = 'gfx-popup';
        popup.id = 'gfx-popup';
        popup.setAttribute('role', 'dialog');
        popup.setAttribute('aria-label', 'Graphics settings');

        // Header
        const header = document.createElement('div');
        header.className = 'gfx-popup-header';

        const title = document.createElement('span');
        title.className = 'gfx-popup-title';
        title.textContent = 'Graphics';

        const headerActions = document.createElement('div');
        headerActions.className = 'gfx-popup-header-actions';

        const pinBtn = document.createElement('button');
        pinBtn.className = 'gfx-pin-btn';
        pinBtn.type = 'button';
        pinBtn.setAttribute('aria-label', 'Pin popup open');
        pinBtn.title = 'Pin popup open';
        pinBtn.innerHTML = '&#128204;';

        const closeBtn = document.createElement('button');
        closeBtn.className = 'gfx-popup-close';
        closeBtn.type = 'button';
        closeBtn.setAttribute('aria-label', 'Close');
        closeBtn.innerHTML = '&times;';

        headerActions.appendChild(pinBtn);
        headerActions.appendChild(closeBtn);

        header.appendChild(title);
        header.appendChild(headerActions);
        popup.appendChild(header);

        // Filter section
        const filterLabel = document.createElement('label');
        filterLabel.className = 'gfx-popup-label';
        filterLabel.setAttribute('for', 'gfx-filter-select');
        filterLabel.textContent = 'Filter';

        const filterSelect = document.createElement('select');
        filterSelect.id = 'gfx-filter-select';

        const filterOptions = [
            { value: 'original', text: 'Original' },
            { value: 'vintage', text: 'Vintage / Old Photo' },
            { value: 'bw', text: 'Black & White' },
            { value: 'sepia', text: 'Sepia' },
            { value: 'warm', text: 'Warm' },
            { value: 'cool', text: 'Cool' }
        ];

        filterOptions.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.text;
            filterSelect.appendChild(option);
        });

        popup.appendChild(filterLabel);
        popup.appendChild(filterSelect);

        // Menu Background theme section
        const themeLabel = document.createElement('label');
        themeLabel.className = 'gfx-popup-label';
        themeLabel.setAttribute('for', 'gfx-theme-select');
        themeLabel.textContent = 'Menu Background';

        const themeSelect = document.createElement('select');
        themeSelect.id = 'gfx-theme-select';

        const themeOptions = [
            { value: 'dark', text: 'Dark' },
            { value: 'light', text: 'Light' },
            { value: 'sepia', text: 'Sepia' },
            { value: 'paper', text: 'Paper' },
            { value: 'vintage', text: 'Vintage Book' }
        ];

        themeOptions.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.text;
            themeSelect.appendChild(option);
        });

        popup.appendChild(themeLabel);
        popup.appendChild(themeSelect);

        // Layout section
        const layoutLabel = document.createElement('label');
        layoutLabel.className = 'gfx-popup-label';
        layoutLabel.setAttribute('for', 'gfx-layout-select');
        layoutLabel.textContent = 'Layout';

        const layoutSelect = document.createElement('select');
        layoutSelect.id = 'gfx-layout-select';

        const layoutOptions = [
            { value: 'vertical', text: 'Vertical (Scroll)' },
            { value: 'horizontal', text: 'Horizontal (Arrows)' }
        ];

        layoutOptions.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.text;
            layoutSelect.appendChild(option);
        });

        popup.appendChild(layoutLabel);
        popup.appendChild(layoutSelect);

        // Transition Effect section
        const transitionLabel = document.createElement('label');
        transitionLabel.className = 'gfx-popup-label';
        transitionLabel.setAttribute('for', 'gfx-transition-select');
        transitionLabel.textContent = 'Transition Effect';

        const transitionSelect = document.createElement('select');
        transitionSelect.id = 'gfx-transition-select';

        Object.entries(TRANSITIONS).forEach(([value, text]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = text;
            transitionSelect.appendChild(option);
        });

        popup.appendChild(transitionLabel);
        popup.appendChild(transitionSelect);

        // Divider
        const divider = document.createElement('div');
        divider.className = 'gfx-section-divider';
        popup.appendChild(divider);

        // Font settings title
        const fontTitle = document.createElement('div');
        fontTitle.className = 'gfx-font-group-title';
        fontTitle.textContent = 'Font Settings';
        popup.appendChild(fontTitle);

        // Build font groups
        const currentSettings = restoreFontSettings();

        FONT_GROUPS.forEach(group => {
            const groupDiv = document.createElement('div');
            groupDiv.className = 'gfx-font-group';
            groupDiv.id = 'gfx-font-group-' + group.key;

            // Group title
            const groupTitle = document.createElement('div');
            groupTitle.className = 'gfx-font-group-title';
            groupTitle.textContent = group.label;
            groupDiv.appendChild(groupTitle);

            // Font dropdown (with live preview in selected font)
            const familySelect = document.createElement('select');
            familySelect.id = 'gfx-font-' + group.key + '-family';
            familySelect.className = 'gfx-font-select';
            familySelect.style.fontFamily = (currentSettings[group.key] || {}).family;

            FONT_OPTIONS.forEach(font => {
                const option = document.createElement('option');
                option.value = font.value;
                option.textContent = font.label;
                option.style.fontFamily = font.value;
                if (font.value === (currentSettings[group.key] || {}).family) {
                    option.selected = true;
                }
                familySelect.appendChild(option);
            });

            groupDiv.appendChild(familySelect);

            // Actions row: size +/- + color picker
            const actionsRow = document.createElement('div');
            actionsRow.className = 'gfx-actions-row';

            // Size control
            const sizeControl = document.createElement('div');
            sizeControl.className = 'gfx-size-control';

            const sizeMinus = document.createElement('button');
            sizeMinus.type = 'button';
            sizeMinus.className = 'gfx-size-btn';
            sizeMinus.setAttribute('data-group', group.key);
            sizeMinus.setAttribute('data-action', 'decrease');
            sizeMinus.innerHTML = '-';
            sizeControl.appendChild(sizeMinus);

            const sizeValue = document.createElement('span');
            sizeValue.className = 'gfx-size-value';
            sizeValue.id = 'gfx-font-' + group.key + '-size-value';
            const currentSize = (currentSettings[group.key] || {}).size;
            sizeValue.textContent = formatSize(currentSize, group.isScale);
            sizeControl.appendChild(sizeValue);

            const sizePlus = document.createElement('button');
            sizePlus.type = 'button';
            sizePlus.className = 'gfx-size-btn';
            sizePlus.setAttribute('data-group', group.key);
            sizePlus.setAttribute('data-action', 'increase');
            sizePlus.innerHTML = '+';
            sizeControl.appendChild(sizePlus);

            actionsRow.appendChild(sizeControl);

            // Color picker
            const colorInput = document.createElement('input');
            colorInput.type = 'color';
            colorInput.id = 'gfx-font-' + group.key + '-color';
            colorInput.className = 'gfx-color-input';
            colorInput.setAttribute('data-group', group.key);
            colorInput.value = (currentSettings[group.key] || {}).color;

            actionsRow.appendChild(colorInput);

            groupDiv.appendChild(actionsRow);
            popup.appendChild(groupDiv);
        });

        // Reset button
        const resetBtn = document.createElement('button');
        resetBtn.type = 'button';
        resetBtn.className = 'gfx-reset-btn';
        resetBtn.id = 'gfx-reset-btn';
        resetBtn.textContent = 'Reset to Defaults';
        popup.appendChild(resetBtn);

        document.body.appendChild(popup);

        // --- Wire up events ---

        // Close button
        closeBtn.addEventListener('click', () => {
            popup.classList.remove('show');
        });

        // Filter change
        filterSelect.addEventListener('change', () => {
            applyFilter(filterSelect.value);
        });

        // Theme change
        themeSelect.addEventListener('change', () => {
            applyTheme(themeSelect.value);
        });

        // Layout change
        layoutSelect.addEventListener('change', () => {
            applyLayout(layoutSelect.value);
        });

        // Transition effect change
        transitionSelect.addEventListener('change', () => {
            applyTransition(transitionSelect.value);
        });

        // PIN toggle
        pinBtn.addEventListener('click', () => {
            const pinned = pinBtn.classList.toggle('active');
            pinBtn.setAttribute('aria-pressed', pinned ? 'true' : 'false');
            pinBtn.title = pinned ? 'Unpin popup' : 'Pin popup open';
            applyPin(pinned);
        });

        // Font family change
        popup.addEventListener('change', (e) => {
            if (e.target.classList.contains('gfx-font-select')) {
                const groupKey = e.target.id.replace('gfx-font-', '').replace('-family', '');
                const group = FONT_GROUPS.find(g => g.key === groupKey);
                if (group) {
                    const settings = getFontSettings();
                    settings[groupKey].family = e.target.value;
                    applyFontSettings(settings);
                    saveFontSettings(settings);
                    // Update the select's font-family for live preview
                    e.target.style.fontFamily = e.target.value;
                }
            }
        });

        // Size +/- buttons
        popup.addEventListener('click', (e) => {
            if (e.target.classList.contains('gfx-size-btn')) {
                const groupKey = e.target.getAttribute('data-group');
                const action = e.target.getAttribute('data-action');
                const group = FONT_GROUPS.find(g => g.key === groupKey);
                if (!group) return;

                const settings = getFontSettings();
                let newSize = settings[groupKey].size;

                if (action === 'increase') {
                    newSize = Math.min(group.maxSize, +(newSize + group.step).toFixed(1));
                } else if (action === 'decrease') {
                    newSize = Math.max(group.minSize, +(newSize - group.step).toFixed(1));
                }

                settings[groupKey].size = newSize;
                applyFontSettings(settings);
                saveFontSettings(settings);

                // Update display
                const sizeValueEl = document.getElementById('gfx-font-' + groupKey + '-size-value');
                if (sizeValueEl) {
                    sizeValueEl.textContent = formatSize(newSize, group.isScale);
                }

                // Update disabled state
                const minusBtn = popup.querySelector('.gfx-size-btn[data-group="' + groupKey + '"][data-action="decrease"]');
                const plusBtn = popup.querySelector('.gfx-size-btn[data-group="' + groupKey + '"][data-action="increase"]');
                if (minusBtn) minusBtn.disabled = (newSize <= group.minSize);
                if (plusBtn) plusBtn.disabled = (newSize >= group.maxSize);
            }
        });

        // Color change
        popup.addEventListener('input', (e) => {
            if (e.target.classList.contains('gfx-color-input')) {
                const groupKey = e.target.getAttribute('data-group');
                const group = FONT_GROUPS.find(g => g.key === groupKey);
                if (!group) return;

                const settings = getFontSettings();
                settings[groupKey].color = e.target.value;
                applyFontSettings(settings);
                saveFontSettings(settings);
            }
        });

        // Reset button
        resetBtn.addEventListener('click', () => {
            // Clear all persisted settings
            try {
                localStorage.removeItem(STORAGE_KEY_FILTER);
                localStorage.removeItem(STORAGE_KEY_FONTS);
                localStorage.removeItem(STORAGE_KEY_THEME);
                localStorage.removeItem(STORAGE_KEY_PIN);
                localStorage.removeItem(STORAGE_KEY_LAYOUT);
                localStorage.removeItem(STORAGE_KEY_TRANSITION);
            } catch (e) {
                // localStorage unavailable - ignore
            }

            // Reset font settings in memory
            const settings = resetFontSettings();

            // Update all UI elements
            FONT_GROUPS.forEach(group => {
                const familySelect = document.getElementById('gfx-font-' + group.key + '-family');
                const sizeValueEl = document.getElementById('gfx-font-' + group.key + '-size-value');
                const colorInput = document.getElementById('gfx-font-' + group.key + '-color');

                if (familySelect) {
                    familySelect.value = settings[group.key].family;
                    familySelect.style.fontFamily = settings[group.key].family;
                }
                if (sizeValueEl) {
                    sizeValueEl.textContent = formatSize(settings[group.key].size, group.isScale);
                }
                if (colorInput) colorInput.value = settings[group.key].color;

                // Update disabled state
                const minusBtn = popup.querySelector('.gfx-size-btn[data-group="' + group.key + '"][data-action="decrease"]');
                const plusBtn = popup.querySelector('.gfx-size-btn[data-group="' + group.key + '"][data-action="increase"]');
                if (minusBtn) minusBtn.disabled = (settings[group.key].size <= group.minSize);
                if (plusBtn) plusBtn.disabled = (settings[group.key].size >= group.maxSize);
            });

            // Reset filter UI
            const filterSelect = document.getElementById('gfx-filter-select');
            if (filterSelect) filterSelect.value = 'original';
            const body = document.body;
            Object.values(FILTERS).forEach(cls => {
                if (cls) body.classList.remove(cls);
            });

            // Reset theme UI
            const themeSelectEl = document.getElementById('gfx-theme-select');
            if (themeSelectEl) themeSelectEl.value = 'sepia';

            // Reset layout UI
            const layoutSelectEl = document.getElementById('gfx-layout-select');
            if (layoutSelectEl) layoutSelectEl.value = 'vertical';
            applyLayout('vertical');

            // Reset transition effect UI
            const transitionSelectEl = document.getElementById('gfx-transition-select');
            if (transitionSelectEl) transitionSelectEl.value = 'fade-scale';
            applyTransition('fade-scale');

            // Reset pin UI
            const pinBtnEl = popup.querySelector('.gfx-pin-btn');
            if (pinBtnEl) {
                pinBtnEl.classList.remove('active');
                pinBtnEl.setAttribute('aria-pressed', 'false');
                pinBtnEl.title = 'Pin popup open';
            }

            // Reload the site to apply all defaults
            window.location.reload();
        });

        // Initialize disabled states
        FONT_GROUPS.forEach(group => {
            const settings = getFontSettings();
            const currentSize = settings[group.key].size;
            const minusBtn = popup.querySelector('.gfx-size-btn[data-group="' + group.key + '"][data-action="decrease"]');
            const plusBtn = popup.querySelector('.gfx-size-btn[data-group="' + group.key + '"][data-action="increase"]');
            if (minusBtn) minusBtn.disabled = (currentSize <= group.minSize);
            if (plusBtn) plusBtn.disabled = (currentSize >= group.maxSize);
        });

        return popup;
    }

    // --- Cog icon builder ---

    function buildCog() {
        const cog = document.createElement('button');
        cog.className = 'gfx-cog';
        cog.type = 'button';
        cog.setAttribute('aria-label', 'Graphics settings');
        cog.title = 'Graphics settings';
        cog.innerHTML =
            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
            '<circle cx="12" cy="12" r="3"></circle>' +
            '<path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>' +
            '</svg>';

        // Find the copyright paragraph in the footer
        const footer = document.querySelector('footer');
        if (!footer) return null;

        const yearSpan = document.getElementById('current-year');
        let anchor = yearSpan ? yearSpan.closest('p') : null;
        if (!anchor) {
            // Fallback: find the paragraph containing the copyright symbol
            const paragraphs = footer.querySelectorAll('p');
            paragraphs.forEach(p => {
                if (p.textContent.indexOf('©') !== -1 && !anchor) {
                    anchor = p;
                }
            });
        }

        if (anchor) {
            anchor.parentNode.insertBefore(cog, anchor.nextSibling);
        } else {
            footer.appendChild(cog);
        }

        return cog;
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        const cog = buildCog();
        const popup = buildPopup();
        const select = document.getElementById('gfx-filter-select');
        const themeSelect = document.getElementById('gfx-theme-select');
        const layoutSelect = document.getElementById('gfx-layout-select');
        const pinBtn = popup.querySelector('.gfx-pin-btn');

        // Restore saved filter
        const saved = restoreFilter();
        if (select) select.value = saved;

        // Restore saved theme
        const savedTheme = restoreTheme();
        if (themeSelect) themeSelect.value = savedTheme;

        // Restore saved layout
        const savedLayout = restoreLayout();
        if (layoutSelect) layoutSelect.value = savedLayout;

        // Restore saved transition effect
        const savedTransition = restoreTransition();
        const transitionSelect = document.getElementById('gfx-transition-select');
        if (transitionSelect) transitionSelect.value = savedTransition;

        // Restore saved PIN state
        const pinned = restorePin();
        if (pinBtn) {
            if (pinned) {
                pinBtn.classList.add('active');
                pinBtn.setAttribute('aria-pressed', 'true');
                pinBtn.title = 'Unpin popup';
            } else {
                pinBtn.setAttribute('aria-pressed', 'false');
            }
        }

        if (cog && popup) {
            cog.addEventListener('click', (e) => {
                e.stopPropagation();
                popup.classList.toggle('show');
            });

            // Close popup when clicking outside (unless pinned)
            document.addEventListener('click', (e) => {
                if (pinBtn && pinBtn.classList.contains('active')) return;
                if (!popup.contains(e.target) && e.target !== cog && !cog.contains(e.target)) {
                    popup.classList.remove('show');
                }
            });

            // Close popup on Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    popup.classList.remove('show');
                }
            });
        }
    });
})();
