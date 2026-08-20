<?php
// /admin/inventory.php
require_once __DIR__ . '/../inc/functions.php'; // for get_supported_languages()

$module_title   = "Inventory Manager";
$json_file      = __DIR__ . '/../assets/inventory.json';
$categories_file= __DIR__ . '/../assets/categories.json';

$languages = get_supported_languages(); // dynamic, e.g. ['en','pl','ar',...]
$primary_lang = 'en'; // admin UI uses English; also our default fallback for labels

// ---------- Helpers ----------
function ensure_product_lang_fields(array $product, array $langs): array {
    foreach ($langs as $lng) {
        if (!array_key_exists("name_$lng", $product)) $product["name_$lng"] = '';
        if (!array_key_exists("desc_$lng", $product)) $product["desc_$lng"] = '';
    }
    return $product;
}
function ensure_category_lang_fields(array $category, array $langs): array {
    foreach ($langs as $lng) {
        if (!array_key_exists("name_$lng", $category)) $category["name_$lng"] = '';
    }
    return $category;
}
function normalize_products_categories(array $products, array $categories, array $langs): array {
    // Back-compat: If products stored 'category' (string, usually name_en), upgrade to 'category_index'
    // Build a map name_en -> index for migration
    $nameEnToIndex = [];
    foreach ($categories as $c) {
        $nameEnToIndex[$c['name_en'] ?? ''] = $c['index'] ?? null;
    }
    foreach ($products as &$p) {
        $p = ensure_product_lang_fields($p, $langs);
        if (!isset($p['category_index'])) {
            if (isset($p['category']) && isset($nameEnToIndex[$p['category']])) {
                $p['category_index'] = $nameEnToIndex[$p['category']];
                unset($p['category']); // migrate away from string category
            }
        }
    }
    unset($p);
    // Ensure categories have full language keys
    foreach ($categories as &$c) {
        $c = ensure_category_lang_fields($c, $langs);
    }
    unset($c);
    return [$products, $categories];
}
function category_label(array $cat, string $preferred, string $fallback='en'): string {
    return $cat["name_$preferred"] ?? ($cat["name_$fallback"] ?? ('Category '.$cat['index']));
}

// ---------- 1. Initialize JSON files if missing ----------
if (!file_exists($json_file)) {
    file_put_contents($json_file, json_encode(['products' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
if (!file_exists($categories_file)) {
    file_put_contents($categories_file, json_encode(['categories' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ---------- 2. Load existing data ----------
$inventory  = json_decode(file_get_contents($json_file), true) ?: [];
$products   = $inventory['products'] ?? [];
$categories = json_decode(file_get_contents($categories_file), true)['categories'] ?? [];

// Normalize to ensure all language keys exist & migrate category to category_index if needed
[$products, $categories] = normalize_products_categories($products, $categories, $languages);

// ---------- 3. Handle form submissions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = null;

    // --- Category addition ---
    if (isset($_POST['add_category'])) {
        $cat_index = (int)($_POST['cat_index'] ?? 0);
        if ($cat_index < 1) {
            $error = "Category index must be a positive integer.";
        }
        // Build category with all languages
        $newCat = ['index' => $cat_index];
        foreach ($languages as $lng) {
            $field = "cat_name_$lng";
            $newCat["name_$lng"] = trim($_POST[$field] ?? '');
        }
        if (!$error) {
            // Prevent duplicate index
            foreach ($categories as $c) {
                if ((int)$c['index'] === $cat_index) {
                    $error = "Category with this index already exists.";
                    break;
                }
            }
        }
        if (!$error) {
            $categories[] = $newCat;
            file_put_contents($categories_file, json_encode(['categories' => $categories], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            header("Location: ?tool=inventory");
            exit;
        }
    }

    // --- Product update ---
    if (isset($_POST['update_product'])) {
        $i = (int)($_POST['edit_index'] ?? -1);
        if (!isset($products[$i])) {
            $error = "Product not found.";
        } else {
            $p = $products[$i];
            $p['index'] = (int)($_POST['edit_index_val'] ?? $p['index'] ?? 0); // keep original index (display-only numbering is i+1)
            $p['category_index'] = (int)($_POST['edit_category_index'] ?? 0);
            $p['price'] = trim($_POST['edit_price'] ?? '');

            // Per-language fields
            foreach ($languages as $lng) {
                $p["name_$lng"] = trim($_POST["edit_name_$lng"] ?? ($p["name_$lng"] ?? ''));
                $p["desc_$lng"] = trim($_POST["edit_desc_$lng"] ?? ($p["desc_$lng"] ?? ''));
            }

            $products[$i] = $p;
            file_put_contents($json_file, json_encode(['products' => $products], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            header("Location: ?tool=inventory");
            exit;
        }
    }
    // --- Product addition ---
    elseif (isset($_POST['index'])) {
        $index          = (int)($_POST['index'] ?? 0);
        $category_index = (int)($_POST['category_index'] ?? 0);
        $price          = trim($_POST['price'] ?? '');

        if ($index < 1 || $category_index < 1 || $price === '') {
            $error = "Index, Category and Price are required.";
        }

        $newProduct = [
            'index' => $index,
            'category_index' => $category_index,
            'price' => $price,
        ];
        foreach ($languages as $lng) {
            $newProduct["name_$lng"] = trim($_POST["name_$lng"] ?? '');
            $newProduct["desc_$lng"] = trim($_POST["desc_$lng"] ?? '');
        }

        if (!$error) {
            $products[] = $newProduct;
            file_put_contents($json_file, json_encode(['products' => $products], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            header("Location: ?tool=inventory");
            exit;
        }
    }
}

// ---------- 4. Handle product deletion ----------
if (isset($_GET['delete'])) {
    $idx = (int)$_GET['delete'];
    if (isset($products[$idx])) {
        array_splice($products, $idx, 1);
        file_put_contents($json_file, json_encode(['products' => $products], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: ?tool=inventory");
        exit;
    }
}

// ---------- Save any normalization back to disk (in case new lang was added) ----------
file_put_contents($json_file, json_encode(['products' => $products], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
file_put_contents($categories_file, json_encode(['categories' => $categories], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
?>

<div class="admin-module">
    <h3>Inventory Management</h3>

    <?php if (!empty($error)): ?>
        <div style="color: #a00; padding: 10px; background: rgba(255,0,0,0.08); margin-bottom: 15px; border: 1px solid rgba(255,0,0,0.25);">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Category Management -->
    <div style="margin-bottom: 30px; padding: 15px; background: rgba(0,0,0,0.03); border-radius: 5px;">
        <form method="POST">
            <button type="button" onclick="this.nextElementSibling.style.display='block'"
                    style="padding: 5px 10px; background: #555; color: white; border: none; border-radius: 3px;">
                + Add Category
            </button>
            <div style="display:none; margin-top:10px;">
                <input type="number" name="cat_index" placeholder="Index" required style="width: 80px;" min="1">
                <?php foreach ($languages as $lng): ?>
                    <input type="text" name="cat_name_<?= htmlspecialchars($lng) ?>"
                           placeholder="Name <?= strtoupper(htmlspecialchars($lng)) ?>"
                           required="<?= $lng === $primary_lang ? 'required' : '' ?>">
                <?php endforeach; ?>
                <button type="submit" name="add_category" style="padding: 3px 10px;">Save</button>
            </div>
        </form>

        <?php if (!empty($categories)): ?>
            <div style="margin-top: 15px;">
                <strong>Current Categories:</strong>
                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
                    <?php foreach ($categories as $cat): ?>
                        <span style="padding: 3px 8px; background: rgba(0,0,0,0.1); border-radius: 3px;">
                            <?= htmlspecialchars(category_label($cat, $primary_lang)) ?> (<?= (int)$cat['index'] ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add New Product Form -->
    <form method="POST" id="add-form" style="margin: 20px 0;">
        <table style="width: 100%; border-collapse: collapse;" id="products-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <?php foreach ($languages as $lng): ?>
                        <th>Name <?= strtoupper(htmlspecialchars($lng)) ?></th>
                        <th>Desc <?= strtoupper(htmlspecialchars($lng)) ?></th>
                    <?php endforeach; ?>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="number" name="index" style="width: 70px;" required min="1"></td>
                    <td>
                        <select name="category_index" required style="width: 100%; padding: 5px;">
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['index'] ?>">
                                        <?= htmlspecialchars(category_label($cat, $primary_lang)) ?> (<?= (int)$cat['index'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No categories available</option>
                            <?php endif; ?>
                        </select>
                    </td>

                    <?php foreach ($languages as $lng): ?>
                        <td><input type="text" name="name_<?= htmlspecialchars($lng) ?>" placeholder="Name <?= strtoupper(htmlspecialchars($lng)) ?>" <?= $lng === $primary_lang ? 'required' : '' ?>></td>
                        <td><input type="text" name="desc_<?= htmlspecialchars($lng) ?>" placeholder="Desc <?= strtoupper(htmlspecialchars($lng)) ?>"></td>
                    <?php endforeach; ?>

                    <td><input type="text" name="price" required></td>
                    <td><button type="submit">Add</button></td>
                </tr>
            </tbody>
        </table>
    </form>

    <!-- Existing Products Table -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;" id="products-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Category</th>
                <?php foreach ($languages as $lng): ?>
                    <th>Name <?= strtoupper(htmlspecialchars($lng)) ?></th>
                    <th>Desc <?= strtoupper(htmlspecialchars($lng)) ?></th>
                <?php endforeach; ?>
                <th>Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Build category index -> label map for display
        $catIndexToLabel = [];
        foreach ($categories as $c) {
            $catIndexToLabel[$c['index']] = category_label($c, $primary_lang);
        }
        ?>
        <?php foreach ($products as $i => $product): ?>
            <tr id="product-row-<?= $i ?>" class="product-row">
                <td><?= $i + 1 ?></td>
                <td>
                    <?php
                    $ci = (int)($product['category_index'] ?? 0);
                    echo htmlspecialchars($catIndexToLabel[$ci] ?? '—');
                    ?>
                </td>

                <?php foreach ($languages as $lng): ?>
                    <td><?= htmlspecialchars($product["name_$lng"] ?? '') ?></td>
                    <td><?= htmlspecialchars($product["desc_$lng"] ?? '') ?></td>
                <?php endforeach; ?>

                <td><?= htmlspecialchars($product['price'] ?? '') ?></td>
                <td>
                    <button type="button" class="edit-btn" data-index="<?= $i ?>">Edit</button>
                    <a href="?tool=inventory&delete=<?= (int)$i ?>" onclick="return confirm('Delete this item?')">Delete</a>
                </td>
            </tr>
            <tr id="edit-form-<?= $i ?>" class="edit-form-row" style="display: none;">
                <form method="POST" class="edit-form">
                <td>
                    <?= $i + 1 ?>
                    <input type="hidden" name="edit_index" value="<?= (int)$i ?>">
                    <input type="hidden" name="edit_index_val" value="<?= (int)($product['index'] ?? ($i+1)) ?>">
                </td>
                <td>
                    <select name="edit_category_index" required style="width: 100%; padding: 5px;">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['index'] ?>"
                                <?= ((int)($product['category_index'] ?? 0) === (int)$cat['index']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(category_label($cat, $primary_lang)) ?> (<?= (int)$cat['index'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>

                <?php foreach ($languages as $lng): ?>
                    <td>
                        <input type="text" 
                               name="edit_name_<?= htmlspecialchars($lng) ?>" 
                               value="<?= htmlspecialchars($product["name_$lng"] ?? '') ?>" 
                               <?= $lng === $primary_lang ? 'required' : '' ?>
                               style="min-width: <?= max(100, strlen($product["name_$lng"] ?? '') * 8) ?>px;">
                    </td>
                    <td>
                        <input type="text" 
                               name="edit_desc_<?= htmlspecialchars($lng) ?>" 
                               value="<?= htmlspecialchars($product["desc_$lng"] ?? '') ?>"
                               style="min-width: <?= max(150, strlen($product["desc_$lng"] ?? '') * 8) ?>px;">
                    </td>
                <?php endforeach; ?>

                <td>
                    <input type="text" 
                           name="edit_price" 
                           value="<?= htmlspecialchars($product['price'] ?? '') ?>" 
                           required
                           style="min-width: <?= max(80, strlen($product['price'] ?? '') * 8) ?>px;">
                </td>
                <td>
                    <button type="submit" name="update_product">Save</button>
                    <button type="button" class="cancel-edit-btn" data-index="<?= $i ?>">Cancel</button>
                </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
/* Table styling for better readability */
#products-table {
    border: 1px solid #ddd;
}

#products-table th, #products-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

#products-table th {
    background-color: #f2f2f2;
    position: sticky;
    top: 0;
}

/* Ensure inputs in edit form have adequate width */
.edit-form input[type="text"] {
    width: 100%;
    box-sizing: border-box;
    min-width: 100px;
}

.edit-form select {
    width: 100%;
    box-sizing: border-box;
}

/* Highlight the row being edited */
.edit-form-row {
    background-color: #f9f9f9;
}

/* Button styling */
.edit-btn, .cancel-edit-btn {
    padding: 5px 10px;
    margin: 2px;
    cursor: pointer;
    border: 1px solid #ccc;
    background-color: #f5f5f5;
    border-radius: 3px;
}

.edit-btn:hover, .cancel-edit-btn:hover {
    background-color: #e9e9e9;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit button clicks
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            
            // Hide the display row
            document.getElementById('product-row-' + index).style.display = 'none';
            
            // Show the edit form row
            const editForm = document.getElementById('edit-form-' + index);
            editForm.style.display = 'table-row';
            
            // Calculate and set minimum widths for inputs based on content
            const inputs = editForm.querySelectorAll('input[type="text"]');
            inputs.forEach(input => {
                // Set minimum width based on content length
                const minWidth = Math.max(input.value.length * 8, 100);
                input.style.minWidth = minWidth + 'px';
            });
        });
    });
    
    // Handle cancel edit button clicks
    document.querySelectorAll('.cancel-edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            
            // Show the display row
            document.getElementById('product-row-' + index).style.display = 'table-row';
            
            // Hide the edit form row
            document.getElementById('edit-form-' + index).style.display = 'none';
        });
    });
    
    // Set column widths based on content when page loads
    function setColumnWidths() {
        const table = document.getElementById('products-table');
        const headers = table.querySelectorAll('thead th');
        const rows = table.querySelectorAll('tbody tr');
        
        // Initialize column widths
        const colWidths = Array(headers.length).fill(0);
        
        // Check header widths
        headers.forEach((header, index) => {
            colWidths[index] = Math.max(colWidths[index], header.scrollWidth);
        });
        
        // Check cell widths in all rows
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (index < colWidths.length) {
                    colWidths[index] = Math.max(colWidths[index], cell.scrollWidth);
                }
            });
        });
        
        // Apply minimum widths
        headers.forEach((header, index) => {
            header.style.minWidth = colWidths[index] + 'px';
        });
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (index < colWidths.length) {
                    cell.style.minWidth = colWidths[index] + 'px';
                }
            });
        });
    }
    
    // Run column width calculation after page loads
    setTimeout(setColumnWidths, 100);
});
</script>