/* ============================================================ *
 *  MENU MODAL — Restauracja Galant Warszawski
 *  Adapted from templates/menu-modal.php for static hosting.
 *  The PHP version fetched operations; this static version reads
 *  embedded SITE_CATEGORIES / SITE_PRODUCTS (see js/data.js)
 *  so the modal works on any host, even file://.
 *  Requires: SITE_LANG ('pl' | 'en') + SITE_CATEGORIES/SITE_PRODUCTS
 *  from js/menu-data.js.
 * ============================================================ */

let currentPage = 0;
let currentCategory = null;

/* Open/close labels come from the CMS 'label1'/'label2' content. */
const SITE_LABELS = {
    pl: { open: 'Menu',    close: 'Zamknij' },
    en: { open: 'Menu',    close: 'Close' }
};

function toggleMenuModal() {
    const overlay = document.getElementById('modal-overlay');
    const toggleBtn = document.getElementById('toggle-modal-btn');
    const isVisible = overlay.style.display === 'flex';

    if (isVisible) {
        overlay.style.display = 'none';
        overlay.classList.remove('show');
        toggleBtn.innerHTML = SITE_LABELS[SITE_LANG].open;
    } else {
        overlay.style.display = 'flex';
        overlay.classList.add('show');
        toggleBtn.innerHTML = SITE_LABELS[SITE_LANG].close;
    }
}

function renderCategory() {
    const container = document.getElementById('category-container');
    container.innerHTML = '';
    currentCategory = SITE_CATEGORIES[currentPage];

    const title = document.createElement('h1');
    title.textContent = currentCategory['name_' + SITE_LANG] || currentCategory.name_en;
    container.appendChild(title);

    const ul = document.createElement('ul');

    SITE_PRODUCTS.forEach(product => {
        if (product.category_index == currentCategory.index) {
            const li = document.createElement('li');
            const line = document.createElement('div');
            line.style.display = 'flex';
            line.style.justifyContent = 'space-between';
            line.style.alignItems = 'center';
            line.style.width = '100%';

            const name = document.createElement('span');
            name.textContent = product['name_' + SITE_LANG] || product.name_en;
            name.style.fontSize = '1.05em';

            const price = document.createElement('span');
            price.textContent = product.price + ' zł';
            price.style.fontStyle = 'italic';
            price.style.color = '#ccc';

            line.appendChild(name);
            line.appendChild(price);
            li.appendChild(line);

            const descText = product['desc_' + SITE_LANG] || product.desc_en;
            if (descText && descText.trim() !== '') {
                const desc = document.createElement('p');
                desc.textContent = descText;
                desc.style.margin = '5px 0 0 0';
                desc.style.fontStyle = 'italic';
                desc.style.color = '#aaa';
                desc.style.fontSize = '0.95em';
                desc.style.textAlign = 'left';
                li.appendChild(desc);
            }
            ul.appendChild(li);
        }
    });

    container.appendChild(ul);
}

function updatePageCounter() {
    document.getElementById('page-counter').textContent =
        (currentPage + 1) + ' / ' + SITE_CATEGORIES.length;
}

/* Wire up controls */
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggle-modal-btn');
    const overlay = document.getElementById('modal-overlay');

    toggleBtn.addEventListener('click', toggleMenuModal);
    document.getElementById('close-modal').addEventListener('click', toggleMenuModal);

    document.getElementById('prev-page').addEventListener('click', () => {
        if (currentPage > 0) { currentPage--; renderCategory(); updatePageCounter(); }
    });
    document.getElementById('next-page').addEventListener('click', () => {
        if (currentPage < SITE_CATEGORIES.length - 1) {
            currentPage++; renderCategory(); updatePageCounter();
        }
    });

    renderCategory();
    updatePageCounter();
});