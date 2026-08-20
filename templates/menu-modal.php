<?php
//zdev/galant3/templates/menu-modal.php
global $current_lang, $default_lang;

$label1Content = spawn_content('label1', ['show_title' => false, 'wrap' => false]);
$label2Content = spawn_content('label2', ['show_title' => false, 'wrap' => false]);

$label1 = $label1Content ?: translate('label1');
$label2 = $label2Content ?: translate('label2');
?>

<button class="floating-button" id="toggle-modal-btn"><?= $label1 ?></button>

<div class="modal-overlay" id="modal-overlay" style="display: none;">
  <div class="modal-content a4-modal">
    <span id="close-modal" style="position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 24px; font-weight: bold;">x</span>
    <div id="category-container"></div>

    <div class="pagination">
      <button id="prev-page" class="pagination-btn">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M15 18l-6-6 6-6" />
        </svg>
      </button>
      <span id="page-counter"></span>
      <button id="next-page" class="pagination-btn">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 18l6-6-6-6" />
        </svg>
      </button>
    </div>
  </div>
</div>


<script>
let currentPage = 0;
let categories = [];
let products = [];
let currentCategory = null;

function toggleMenuModal() {
  const overlay = document.getElementById('modal-overlay');
  const toggleBtn = document.getElementById('toggle-modal-btn');
  const isVisible = overlay.style.display === 'flex';

  if (isVisible) {
    overlay.style.display = 'none';
    overlay.classList.remove('show');
    toggleBtn.innerHTML = '<?= $label1 ?>';
  } else {
    overlay.style.display = 'flex';
    overlay.classList.add('show');
    toggleBtn.innerHTML = '<?= $label2 ?>';
  }
}

// Fetch categories and inventory
fetch('assets/categories.json')
  .then(res => res.json())
  .then(data => {
    categories = data.categories;
    fetch('assets/inventory.json')
      .then(res => res.json())
      .then(data => {
        products = data.products;
        renderCategory();
        updatePageCounter();
      });
  });

function renderCategory() {
  const container = document.getElementById('category-container');
  container.innerHTML = '';
  currentCategory = categories[currentPage];

  const title = document.createElement('h1');
  title.textContent = currentCategory[`name_<?= $current_lang ?>`] || currentCategory.name_en;
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
      name.textContent = product[`name_<?= $current_lang ?>`] || product.name_en;
      
      name.style.fontSize = '1.05em';

      const price = document.createElement('span');
      price.textContent = `${product.price} zł`;
      price.style.fontStyle = 'italic';
      price.style.color = '#ccc';

      line.appendChild(name);
      line.appendChild(price);
      li.appendChild(line);

      const descText = product[`desc_<?= $current_lang ?>`] || product.desc_en;
      if (descText.trim() !== '') {
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
  document.getElementById('page-counter').textContent = `${currentPage + 1} / ${categories.length}`;
}

// Position floating button
const toggleBtn = document.getElementById('toggle-modal-btn');
const overlay = document.getElementById('modal-overlay');

toggleBtn.addEventListener('click', toggleMenuModal);

// Add event listener for close modal
document.getElementById('close-modal').addEventListener('click', toggleMenuModal);

// Pagination buttons
document.getElementById('prev-page').addEventListener('click', () => {
  if (currentPage > 0) { currentPage--; renderCategory(); updatePageCounter(); }
});
document.getElementById('next-page').addEventListener('click', () => {
  if (currentPage < categories.length - 1) { currentPage++; renderCategory(); updatePageCounter(); }
});
</script>