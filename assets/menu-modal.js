// Menu data embedded statically
const categories = [
    { "index": "1", "name_pl": "Przystawki", "name_en": "Appetizers" },
    { "index": "2", "name_pl": "Zupy", "name_en": "Soups" },
    { "index": "3", "name_pl": "Dania Główne", "name_en": "Main Dishes" },
    { "index": "4", "name_pl": "Napoje Gorące", "name_en": "Hot Drinks" },
    { "index": "5", "name_pl": "Napoje Zimne", "name_en": "Cold Drinks" },
    { "index": "6", "name_pl": "Desery", "name_en": "Desserts" }
];

const products = [
    { "index": "1", "category_index": "1", "price": "25,00", "name_pl": "Śledź w oleju z cebulką po naszemu", "desc_pl": "", "name_en": "Herring in oil with onions, Polish style", "desc_en": "" },
    { "index": "2", "category_index": "1", "price": "30,00", "name_pl": "Śledź w śmietanie", "desc_pl": "Z gorącymi ziemniakami z masłem i szczypiorkiem", "name_en": "Herring in cream", "desc_en": "With hot potatoes, butter, and chives" },
    { "index": "3", "category_index": "1", "price": "48,00", "name_pl": "Tatar wołowy z dodatkami", "desc_pl": "", "name_en": "Beef tartare with accompaniments", "desc_en": "" },
    { "index": "4", "category_index": "1", "price": "25,00", "name_pl": "Bliny gryczane ze śmietaną", "desc_pl": "", "name_en": "Buckwheat pancakes with sour cream", "desc_en": "" },
    { "index": "5", "category_index": "1", "price": "28,00", "name_pl": "Studzina warszawska", "desc_pl": "", "name_en": "Warsaw-style cold cuts", "desc_en": "" },
    { "index": "6", "category_index": "1", "price": "28,00", "name_pl": "Sałatka na galantych liściach z maślaną kromką", "desc_pl": "", "name_en": "Salad on delicate leaves with buttery toast", "desc_en": "" },
    { "index": "7", "category_index": "2", "price": "28,00", "name_pl": "Rosół z kołdunami", "desc_pl": "", "name_en": "Chicken broth with dumplings", "desc_en": "" },
    { "index": "8", "category_index": "2", "price": "38,00", "name_pl": "Flaki po warszawsku z pulpetami", "desc_pl": "", "name_en": "Warsaw-style tripe soup with meatballs", "desc_en": "" },
    { "index": "9", "category_index": "2", "price": "38,00", "name_pl": "Żur z jajkiem i żeberkiem", "desc_pl": "", "name_en": "Sour rye soup with egg and pork rib", "desc_en": "" },
    { "index": "10", "category_index": "3", "price": "35,00", "name_pl": "Gęsie żołądki z szalotką", "desc_pl": "Grzanka z chleba wiejskiego z czosnkiem", "name_en": "Goose stomachs with shallots", "desc_en": "Rustic bread toast with garlic" },
    { "index": "11", "category_index": "3", "price": "58,00", "name_pl": "Sznycel ministerski z kury", "desc_pl": "Ziemniaki opiekane, marchewka glazurowana z miodem lub surówka sezonowa", "name_en": "Minister's chicken schnitzel", "desc_en": "Roasted potatoes, honey-glazed carrots or seasonal salad" },
    { "index": "12", "category_index": "3", "price": "68,00", "name_pl": "Sztufada wołowa szpikowana słoniną", "desc_pl": "Kluska śląska, buraczki zasmażane lub surówka sezonowa", "name_en": "Beef stew with marrow and bacon", "desc_en": "Silesian dumplings, fried beets or seasonal salad" },
    { "index": "13", "category_index": "3", "price": "75,00", "name_pl": "Befsztyk bity wiejski", "desc_pl": "Ziemniaki z koperkiem, buraczki zasmażane lub surówka sezonowa", "name_en": "Country-style beefsteak", "desc_en": "Potatoes with dill, fried beets or seasonal salad" },
    { "index": "14", "category_index": "3", "price": "65,00", "name_pl": "Schabowy z kością", "desc_pl": "Ziemniaczki z koperkiem, kapusta zasmażana lub mizeria ze śmietaną", "name_en": "Pork chop with bone", "desc_en": "Potatoes with dill, braised cabbage or cucumber salad with cream" },
    { "index": "15", "category_index": "3", "price": "75,00", "name_pl": "Szczupak w śmietanie", "desc_pl": "Ziemniaki z koperkiem, fasola szparagowa z masłem i bułeczką", "name_en": "Pike in cream sauce", "desc_en": "Potatoes with dill, green beans with butter and a roll" },
    { "index": "16", "category_index": "3", "price": "36,00", "name_pl": "Pierogi z dudkami", "desc_pl": "Polane skwarkami ze świni", "name_en": "Dumplings with pork cracklings", "desc_en": "Topped with pork cracklings" },
    { "index": 17, "category_index": 3, "price": "38,00", "name_pl": "Pyzy po różycku", "desc_pl": "Z mięsem z boczku lub twarogiem oraz cebulką prażoną", "name_en": "Pyzy po różycku", "desc_en": "With bacon or cottage cheese and fried onions" },
    { "index": "18", "category_index": "4", "price": "10,00", "name_pl": "Herbata czarna", "desc_pl": "", "name_en": "Black tea", "desc_en": "" },
    { "index": "19", "category_index": "4", "price": "10,00", "name_pl": "Herbata smakowa", "desc_pl": "(jaśminowa, zielona, owocowa)", "name_en": "Flavored tea", "desc_en": "(jasmine, green, fruit)" },
    { "index": "20", "category_index": "4", "price": "8,00", "name_pl": "Kawa espresso", "desc_pl": "", "name_en": "Espresso", "desc_en": "" },
    { "index": "21", "category_index": "4", "price": "16,00", "name_pl": "Kawa Late", "desc_pl": "", "name_en": "Caffè latte", "desc_en": "" },
    { "index": "22", "category_index": "4", "price": "10,00", "name_pl": "Americano", "desc_pl": "", "name_en": "Americano", "desc_en": "" },
    { "index": "23", "category_index": "4", "price": "18,00", "name_pl": "Flat white", "desc_pl": "", "name_en": "Flat white", "desc_en": "" },
    { "index": "24", "category_index": "4", "price": "18,00", "name_pl": "Affogato", "desc_pl": "Gałka lodów waniliowych", "name_en": "Affogato", "desc_en": "Scoop of vanilla ice cream" },
    { "index": "25", "category_index": "5", "price": "12,00", "name_pl": "Coca Cola", "desc_pl": "0,2 l/but", "name_en": "Coca Cola", "desc_en": "0.2 l/bottle" },
    { "index": "26", "category_index": "5", "price": "12,00", "name_pl": "Fanta", "desc_pl": "0,2 l/but", "name_en": "Fanta", "desc_en": "0.2 l/bottle" },
    { "index": "27", "category_index": "5", "price": "12,00", "name_pl": "Sprite", "desc_pl": "0,2 l/but", "name_en": "Sprite", "desc_en": "0.2 l/bottle" },
    { "index": "28", "category_index": "5", "price": "10,00", "name_pl": "Woda min gaz", "desc_pl": "Mała butelka", "name_en": "Sparkling water", "desc_en": "Small bottle" },
    { "index": "29", "category_index": "5", "price": "12,00", "name_pl": "Soki naturalne", "desc_pl": "0,2 l", "name_en": "Natural juices", "desc_en": "0.2 l" },
    { "index": "30", "category_index": "5", "price": "12,00", "name_pl": "Soki butelka", "desc_pl": "0,33 l/but", "name_en": "Bottled juices", "desc_en": "0.33 l/bottle" },
    { "index": "31", "category_index": "6", "price": "25,00", "name_pl": "Krem sułtański", "desc_pl": "", "name_en": "Sultan cream", "desc_en": "" },
    { "index": "32", "category_index": "6", "price": "22,00", "name_pl": "W - Z po naszemu", "desc_pl": "", "name_en": "W - Z cake our way", "desc_en": "" },
    { "index": 33, "category_index": 6, "price": "25,00", "name_pl": "Puchar lodowy", "desc_pl": "", "name_en": "Ice cream cup", "desc_en": "" }
];

let currentPage = 0;
let currentCategory = null;
let currentLang = 'pl'; // Default language

// Function to set language from external calls
function setMenuLanguage(lang) {
    currentLang = lang;
    if (currentCategory) {
        renderCategory();
    }
}

function toggleMenuModal() {
    const overlay = document.getElementById('modal-overlay');
    const toggleBtn = document.getElementById('toggle-modal-btn');
    const isVisible = overlay.style.display === 'flex';

    if (isVisible) {
        overlay.style.display = 'none';
        overlay.classList.remove('show');
        toggleBtn.innerHTML = getLabel('label1');
    } else {
        overlay.style.display = 'flex';
        overlay.classList.add('show');
        toggleBtn.innerHTML = getLabel('label2');
    }
}

function getLabel(labelKey) {
    // Static labels for menu modal
    const labels = {
        'label1': currentLang === 'pl' ? 'Menu' : 'Menu',
        'label2': currentLang === 'pl' ? 'Zamknij' : 'Close'
    };
    return labels[labelKey] || labelKey;
}

function renderCategory() {
    const container = document.getElementById('category-container');
    container.innerHTML = '';
    currentCategory = categories[currentPage];

    const title = document.createElement('h1');
    title.textContent = currentCategory[`name_${currentLang}`] || currentCategory.name_en;
    container.appendChild(title);

    const ul = document.createElement('ul');

    products.forEach(product => {
        if (product.category_index == currentCategory.index) {
            const li = document.createElement('li');
            const line = document.createElement('div');
            line.style.display = 'flex';
            line.style.justifyContent = 'space-between';
            line.style.alignItems = 'center';
            line.style.width = '100%';

            const name = document.createElement('span');
            name.textContent = product[`name_${currentLang}`] || product.name_en;
            name.style.fontSize = '1.05em';

            const price = document.createElement('span');
            price.textContent = `${product.price} zł`;
            price.style.fontStyle = 'italic';

            line.appendChild(name);
            line.appendChild(price);
            li.appendChild(line);

            const descText = product[`desc_${currentLang}`] || product.desc_en;
            if (descText.trim() !== '') {
                const desc = document.createElement('p');
                desc.textContent = descText;
                desc.style.margin = '5px 0 0 0';
                desc.style.fontStyle = 'italic';
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
    document.getElementById('page-counter').textContent = `${currentPage + 1} / ${categories.length}`;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggle-modal-btn');
    const overlay = document.getElementById('modal-overlay');

    if (toggleBtn && overlay) {
        toggleBtn.addEventListener('click', toggleMenuModal);
        
        // Add event listener for close modal
        const closeBtn = document.getElementById('close-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', toggleMenuModal);
        }

        // Pagination buttons
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentPage > 0) { currentPage--; renderCategory(); updatePageCounter(); }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (currentPage < categories.length - 1) { currentPage++; renderCategory(); updatePageCounter(); }
            });
        }

        // Initial render
        renderCategory();
        updatePageCounter();
    }
});