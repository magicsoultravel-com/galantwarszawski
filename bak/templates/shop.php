<?php
// /templates/shop.php
require_once __DIR__ . '/../inc/functions.php'; // for $current_lang and get_supported_languages()

$inventory_file  = __DIR__ . '/../assets/inventory.json';
$categories_file = __DIR__ . '/../assets/categories.json';

if (file_exists($inventory_file) && file_exists($categories_file)) {
    $inventory   = json_decode(file_get_contents($inventory_file), true) ?: [];
    $categories  = json_decode(file_get_contents($categories_file), true)['categories'] ?? [];
    $products    = $inventory['products'] ?? [];
    $languages   = get_supported_languages();
    $fallback    = 'en';

    $ensureLang = function(array $item, array $langs, array $fields) {
        foreach ($fields as $f) {
            foreach ($langs as $lng) {
                $k = "{$f}_$lng";
                if (!array_key_exists($k, $item)) $item[$k] = '';
            }
        }
        return $item;
    };

    $catByIndex = [];
    foreach ($categories as $c) {
        $c = $ensureLang($c, $languages, ['name']);
        if (isset($c['index'])) $catByIndex[(int)$c['index']] = $c;
    }

    foreach ($products as &$p) {
        $p = $ensureLang($p, $languages, ['name','desc']);
        if (!isset($p['category_index']) && isset($p['category'])) {
            foreach ($catByIndex as $idx => $c) {
                if (($c['name_en'] ?? null) === $p['category']) {
                    $p['category_index'] = $idx;
                    break;
                }
            }
        }
    }
    unset($p);
    ?>
    <section class="menu-section">
        <h1 style="text-align: center; margin-bottom: 20px;"><?= htmlspecialchars(translate('label1')) ?></h1>

        <?php
        ksort($catByIndex);
        foreach ($catByIndex as $idx => $category):
            $cat_name = $category["name_$current_lang"] ?? ($category["name_$fallback"] ?? ('Category '.$idx));
            $cat_products = array_values(array_filter($products, function($p) use ($idx) {
                return isset($p['category_index']) && (int)$p['category_index'] === (int)$idx;
            }));
            if (!empty($cat_products)):
        ?>
            <h2 style="border-bottom: 1px solid #777; padding-bottom: 5px; margin-bottom: 15px;"><?= htmlspecialchars($cat_name) ?></h2>

            <?php foreach ($cat_products as $product):
                $pname = $product["name_$current_lang"] ?? ($product["name_$fallback"] ?? '');
                $pdesc = $product["desc_$current_lang"] ?? ($product["desc_$fallback"] ?? '');
                $price = $product['price'] ?? '';
            ?>
                <div class="menu-item" style="margin-bottom: 12px;">
                    <div class="item-line" style="display: flex; justify-content: space-between; border-bottom: 1px dotted #555; padding-bottom: 3px; margin-bottom: 3px;">
                        <span class="item-name" style="font-weight: bold; font-size: 1.05em;"><?= htmlspecialchars($pname) ?></span>
                        <span class="item-price" style="font-style: italic; color: #ccc;"><?= htmlspecialchars($price) ?> zł</span>
                    </div>
                    <?php if (trim($pdesc) !== ''): ?>
                        <div class="item-line" style="display: flex; justify-content: space-between;">
                            <span class="item-desc" style="font-style: italic; color: #aaa; font-size: 0.95em;"><?= htmlspecialchars($pdesc) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        <?php
            endif;
        endforeach; ?>
    </section>
<?php } ?>
