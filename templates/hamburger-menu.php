<?php
// Define the path to the JSON file using a dynamic path.
$json_file = __DIR__ . '/../assets/menu_data.json';
$menu_items = [];
if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $menu_items = json_decode($json_data, true);
}
?>



<?php
if ($menu_items !== null && is_array($menu_items)) {
    foreach ($menu_items as $index => $item) {
        $slug = htmlspecialchars($item['slug']);
        $type = $item['type'] ?? 'link';
        $target = $item['target'] ?? '#';

        echo '<a class="navigation__link"';
        if ($type === 'modal') {
    // This tells the browser to execute no navigation for the href.
    echo ' href="javascript:void(0)" onclick="' . htmlspecialchars($target) . '"';
} else {
            echo ' href="' . htmlspecialchars($target) . '"';
        }
        echo '>' . spawn_content($slug, ['show_content' => false]) . '</a>';
    }
}
?>
