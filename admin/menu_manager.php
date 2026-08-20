<?php
// /admin/menu_manager.php
require_once __DIR__ . '/../inc/functions.php'; // Assuming get_supported_languages() and spawn_content() are here.

$module_title = "Menu Manager";
$json_file    = __DIR__ . '/../assets/menu_data.json';
$primary_lang = 'en'; // Default language for labels in the admin UI.

// Helper function to ensure menu items have required keys
function normalize_menu_items(array $items): array {
    foreach ($items as &$item) {
        if (!isset($item['slug'])) $item['slug'] = '';
        if (!isset($item['type'])) $item['type'] = 'link';
        if (!isset($item['target'])) $item['target'] = '#';
        if (!isset($item['order'])) $item['order'] = 0;
    }
    unset($item);
    return $items;
}

// 1. Initialize JSON file if it doesn't exist
if (!file_exists($json_file)) {
    file_put_contents($json_file, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// 2. Load existing data
$menu_items = json_decode(file_get_contents($json_file), true) ?: [];
$menu_items = normalize_menu_items($menu_items);

// Sort items by 'order'
usort($menu_items, function($a, $b) {
    return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
});

// 3. Handle form submissions (CRUD operations)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = null;

    // --- Add new item ---
    if (isset($_POST['add_item'])) {
        $new_item = [
            'slug'   => trim($_POST['slug'] ?? ''),
            'type'   => trim($_POST['type'] ?? 'link'),
            'target' => trim($_POST['target'] ?? '#'),
            'order'  => count($menu_items) + 1 // Add to the end
        ];

        if (empty($new_item['slug']) || empty($new_item['target'])) {
            $error = "Slug and Target are required.";
        } else {
            $menu_items[] = $new_item;
        }
    }

    // --- Update item ---
    elseif (isset($_POST['update_item'])) {
        $index = (int)($_POST['edit_index'] ?? -1);
        if (isset($menu_items[$index])) {
            $menu_items[$index]['slug'] = trim($_POST['edit_slug'] ?? $menu_items[$index]['slug']);
            $menu_items[$index]['type'] = trim($_POST['edit_type'] ?? $menu_items[$index]['type']);
            $menu_items[$index]['target'] = trim($_POST['edit_target'] ?? $menu_items[$index]['target']);
        } else {
            $error = "Menu item not found.";
        }
    }
    
    // --- Update order (via AJAX from drag-and-drop) ---
    elseif (isset($_POST['update_order'])) {
        $new_order = json_decode($_POST['update_order'], true);
        $ordered_items = [];
        foreach ($new_order as $order_index => $slug) {
            foreach ($menu_items as $item) {
                if ($item['slug'] === $slug) {
                    $item['order'] = $order_index + 1;
                    $ordered_items[] = $item;
                    break;
                }
            }
        }
        $menu_items = $ordered_items;
    }

    // Save changes to the JSON file
    if (!$error) {
        file_put_contents($json_file, json_encode($menu_items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // Redirect to avoid form resubmission, unless it was an AJAX request
        if (!isset($_POST['update_order'])) {
            header("Location: ?tool=menu_manager");
            exit;
        } else {
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
}

// 4. Handle item deletion
if (isset($_GET['delete'])) {
    $index = (int)$_GET['delete'];
    if (isset($menu_items[$index])) {
        array_splice($menu_items, $index, 1);
        // Re-index the 'order'
        foreach ($menu_items as $idx => &$item) {
            $item['order'] = $idx + 1;
        }
        file_put_contents($json_file, json_encode($menu_items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    header("Location: ?tool=menu_manager");
    exit;
}
?>

<div class="admin-module">
    <h3>Menu Management</h3>

    <?php if (!empty($error)): ?>
        <div style="color: #a00; padding: 10px; background: rgba(255,0,0,0.08); margin-bottom: 15px; border: 1px solid rgba(255,0,0,0.25);">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="margin: 20px 0; padding: 15px; background: rgba(0,0,0,0.03); border-radius: 5px;">
        <h4>Add New Menu Item</h4>
        <form method="POST">
            <input type="text" name="slug" placeholder="Slug (e.g., 'about')" required>
            <select name="type" onchange="toggleTargetField(this)">
                <option value="link">Link</option>
                <option value="modal">Modal</option>
            </select>
            <input type="text" name="target" placeholder="Target (e.g., '/about.php' or 'openModal()')" style="width: 250px;" required>
            <button type="submit" name="add_item">Add Item</button>
        </form>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr>
                <th>#</th>
                <th>Slug</th>
                <th>Type</th>
                <th>Target</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="sortable-list">
            <?php foreach ($menu_items as $i => $item): ?>
                <tr class="menu-item-row" data-slug="<?= htmlspecialchars($item['slug']) ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($item['slug']) ?></td>
                    <td><?= htmlspecialchars($item['type']) ?></td>
                    <td><?= htmlspecialchars($item['target']) ?></td>
                    <td>
                        <a href="?tool=menu_manager&edit=<?= $i ?>">Edit</a> |
                        <a href="?tool=menu_manager&delete=<?= $i ?>" onclick="return confirm('Are you sure you want to delete this menu item?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (isset($_GET['edit'])): ?>
        <?php $edit_index = (int)$_GET['edit']; ?>
        <?php if (isset($menu_items[$edit_index])): ?>
            <?php $edit_item = $menu_items[$edit_index]; ?>
            <div id="edit-modal" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 20px; background: white; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0,0,0,0.1); z-index: 1000;">
                <h4>Edit Menu Item</h4>
                <form method="POST">
                    <input type="hidden" name="edit_index" value="<?= $edit_index ?>">
                    <p>
                        <label>Slug:</label><br>
                        <input type="text" name="edit_slug" value="<?= htmlspecialchars($edit_item['slug']) ?>" required>
                    </p>
                    <p>
                        <label>Type:</label><br>
                        <select name="edit_type" onchange="toggleTargetField(this)">
                            <option value="link" <?= $edit_item['type'] === 'link' ? 'selected' : '' ?>>Link</option>
                            <option value="modal" <?= $edit_item['type'] === 'modal' ? 'selected' : '' ?>>Modal</option>
                        </select>
                    </p>
                    <p>
                        <label>Target:</label><br>
                        <input type="text" name="edit_target" value="<?= htmlspecialchars($edit_item['target']) ?>" style="width: 250px;" required>
                    </p>
                    <button type="submit" name="update_item">Save Changes</button>
                    <a href="?tool=menu_manager">Cancel</a>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script>
    // Drag-and-drop functionality
    const sortableList = document.getElementById('sortable-list');
    new Sortable(sortableList, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd: function(evt) {
            const newOrder = Array.from(sortableList.children).map(row => row.dataset.slug);
            
            // Send the new order to the server using AJAX
            const formData = new FormData();
            formData.append('update_order', JSON.stringify(newOrder));

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    console.log('Menu order updated successfully.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });

    // Function to toggle the placeholder text based on selected type
    function toggleTargetField(select) {
        const input = select.nextElementSibling;
        if (select.value === 'link') {
            input.placeholder = "Target (e.g., '/about.php')";
        } else if (select.value === 'modal') {
            input.placeholder = "Target (e.g., 'openMenuModal()')";
        }
    }

    // Call the function on page load for the add form
    document.addEventListener('DOMContentLoaded', () => {
        const addSelect = document.querySelector('form select[name="type"]');
        if (addSelect) {
            toggleTargetField(addSelect);
        }
        
        // Show the edit modal if the 'edit' parameter is present
        if (window.location.search.includes('edit=')) {
            const editModal = document.getElementById('edit-modal');
            if (editModal) {
                editModal.style.display = 'block';
            }
        }
    });
</script>
<style>
/* Basic styling for the admin table and forms */
.admin-module input, .admin-module select, .admin-module button {
    padding: 5px;
    margin-right: 5px;
    border: 1px solid #ccc;
    border-radius: 3px;
}
.admin-module table {
    border: 1px solid #ccc;
    border-collapse: collapse;
}
.admin-module th, .admin-module td {
    padding: 8px;
    border: 1px solid #eee;
    text-align: left;
}
.menu-item-row {
    cursor: grab;
    background-color: #f9f9f9;
}
.menu-item-row:hover {
    background-color: #f1f1f1;
}
.sortable-ghost {
    opacity: 0.5;
    background-color: #e0e0e0;
}
</style>