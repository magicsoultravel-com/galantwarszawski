<?php
// /admin/content.php
require_once __DIR__ . '/../inc/functions.php';

$module_title = "Content Manager";
$content_file = __DIR__ . '/../assets/content.json';
$languages    = get_supported_languages();
$primary_lang = 'en';

// 1) Initialize file if missing
if (!file_exists($content_file)) {
    file_put_contents($content_file, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// 2) Load
$articles = json_decode(file_get_contents($content_file), true) ?: [];

// 2a) Ensure each article has all language keys (when new language is added)
$normalize_article = function(array $a) use ($languages) {
    foreach ($languages as $lng) {
        $a["title_$lng"]   = $a["title_$lng"]   ?? '';
        $a["content_$lng"] = $a["content_$lng"] ?? '';
    }
    // Ensure required meta fields
    if (!isset($a['date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $a['date'])) {
        $a['date'] = date('Y-m-d');
    }
    if (!isset($a['index'])) {
        $a['index'] = count($GLOBALS['articles']) + 1;
    }
    if (!isset($a['slug']) || $a['slug'] === '') {
        $fallbackTitle = $a["title_en"] ?? '';
        $a['slug'] = slugify($fallbackTitle ?: ('article-'.$a['index']));
    }
    return $a;
};
foreach ($articles as &$art) $art = $normalize_article($art);
unset($art);

// 3) Handle POST (Add / Update)
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Add new article ---
    if (isset($_POST['add_article'])) {
        $index = (int)($_POST['index'] ?? 0);
        $date  = trim($_POST['date'] ?? date('Y-m-d'));
        $slug  = trim($_POST['slug'] ?? '');

        if ($index < 1) $error = "Index must be a positive integer.";
        if (!$error && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $error = "Date must be YYYY-MM-DD.";

        // Titles/contents by language
        $new = [
            'index' => $index,
            'date'  => $date,
        ];
        foreach ($languages as $lng) {
            $new["title_$lng"]   = trim($_POST["title_$lng"] ?? '');
            $new["content_$lng"] = trim($_POST["content_$lng"] ?? '');
        }

        // Slug: provided or from EN title
        $new['slug'] = $slug !== '' ? slugify($slug) : slugify($new['title_en'] ?: ('article-'.$index));

        // Uniqueness checks
        if (!$error) {
            foreach ($articles as $a) {
                if ((int)$a['index'] === $index) { $error = "Index already exists."; break; }
                if (($a['slug'] ?? '') === $new['slug']) { $error = "Slug already exists."; break; }
            }
        }

        if (!$error) {
            $articles[] = $normalize_article($new);
            file_put_contents($content_file, json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            header("Location: ?tool=content");
            exit;
        }
    }

    // --- Update article ---
    if (isset($_POST['update_article'])) {
        $i = (int)($_POST['edit_i'] ?? -1);
        if (!isset($articles[$i])) {
            $error = "Article not found.";
        } else {
            $date = trim($_POST['edit_date'] ?? date('Y-m-d'));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');

            $newSlug = trim($_POST['edit_slug'] ?? '');
            $newSlug = $newSlug !== '' ? slugify($newSlug) : slugify($_POST['edit_title_en'] ?? $articles[$i]['title_en'] ?? ('article-'.$articles[$i]['index']));

            // Prevent slug duplicates with other records
            foreach ($articles as $k => $a) {
                if ($k === $i) continue;
                if (($a['slug'] ?? '') === $newSlug) {
                    $error = "Slug already exists.";
                    break;
                }
            }
            if (!$error) {
                $articles[$i]['index'] = (int)($_POST['edit_index_val'] ?? $articles[$i]['index']);
                $articles[$i]['date']  = $date;
                $articles[$i]['slug']  = $newSlug;

                foreach ($languages as $lng) {
                    $articles[$i]["title_$lng"]   = trim($_POST["edit_title_$lng"]   ?? $articles[$i]["title_$lng"]);
                    $articles[$i]["content_$lng"] = trim($_POST["edit_content_$lng"] ?? $articles[$i]["content_$lng"]);
                }

                $articles[$i] = $normalize_article($articles[$i]);
                file_put_contents($content_file, json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                header("Location: ?tool=content");
                exit;
            }
        }
    }
}

// 4) Handle DELETE
if (isset($_GET['delete'])) {
    $i = (int)$_GET['delete'];
    if (isset($articles[$i])) {
        array_splice($articles, $i, 1);
        file_put_contents($content_file, json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: ?tool=content");
        exit;
    }
}

// 5) Persist normalization (in case new language was added)
file_put_contents($content_file, json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
?>
<div class="admin-module">
    <h3>Content Management</h3>

    <?php if (!empty($error)): ?>
        <div style="color:#a00;padding:10px;background:rgba(255,0,0,0.08);margin-bottom:15px;border:1px solid rgba(255,0,0,0.25);">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

   <!-- Add New Article -->
<div class="section">
    <h4>Add New Article</h4>
    <form method="POST">
        <input type="hidden" name="add_article" value="1">
        <div class="form-field">
            <label>Index</label>
            <input type="number" name="index" min="1" required>
        </div>
        <div class="form-field">
            <label>Date</label>
            <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-field">
            <label>Slug</label>
            <input type="text" name="slug" placeholder="auto from EN title if empty">
        </div>
        <div class="form-field">
            <?php foreach ($languages as $lng): ?>
                <div style="display:inline-block; vertical-align:top; margin-right:20px;">
                    <h5><?= $flags[$lng] ?? $lng ?></h5>
                    <label>Title</label>
                    <input type="text" name="title_<?= htmlspecialchars($lng) ?>" placeholder="Title <?= strtoupper(htmlspecialchars($lng)) ?>" <?= $lng === $primary_lang ? 'required' : '' ?>>
                    <label>Content</label>
                    <textarea name="content_<?= htmlspecialchars($lng) ?>" placeholder="Content <?= strtoupper(htmlspecialchars($lng)) ?>" rows="3"></textarea>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="form-field">
            <button type="submit">Add</button>
        </div>
    </form>
</div>

    <!-- Existing Articles -->
    <table style="width:100%;border-collapse:collapse;margin-top:20px;">
        <thead>
        <tr>
            <th style="width:50px;">#</th>
            <th style="width:80px;">Index</th>
            <th style="width:120px;">Date</th>
            <th>Slug</th>
            <th>Title (EN)</th>
            <th style="width:140px;">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($articles as $i => $a): ?>
            <?php if (isset($_GET['edit']) && (int)$_GET['edit'] === $i): ?>
                <form method="POST">
                    <input type="hidden" name="edit_i" value="<?= (int)$i ?>">
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <input type="number" name="edit_index_val" value="<?= (int)($a['index'] ?? ($i+1)) ?>" min="1" style="width:80px;">
                        </td>
                        <td>
                            <input type="date" name="edit_date" value="<?= htmlspecialchars($a['date'] ?? date('Y-m-d')) ?>" style="width:140px;">
                        </td>
                        <td>
                            <input type="text" name="edit_slug" value="<?= htmlspecialchars($a['slug'] ?? '') ?>" placeholder="unique slug">
                        </td>
                        <td>
                            <em>(List shows EN only; full language fields below.)</em>
                        </td>
                        <td>
                            <button type="submit" name="update_article" value="1">Save</button>
                            <a href="?tool=content" style="margin-left:8px;">Cancel</a>
                        </td>
                    </tr>

                    <!-- Language fields row -->
                    <tr>
                        <td colspan="6">
                            <div style="display:grid;grid-template-columns:repeat(<?= count($languages) ?>,1fr);gap:12px;">
                                <?php foreach ($languages as $lng): ?>
                                    <div style="border:1px solid #ddd;padding:10px;border-radius:6px;background:#fafafa;">
                                        <strong><?= strtoupper(htmlspecialchars($lng)) ?></strong>
                                        <div style="margin-top:8px;">
                                            <label>Title</label>
                                            <input type="text" name="edit_title_<?= htmlspecialchars($lng) ?>" value="<?= htmlspecialchars($a["title_$lng"] ?? '') ?>" style="width:100%;">
                                        </div>
                                        <div style="margin-top:8px;">
                                            <label>Content</label>
                                            <textarea name="edit_content_<?= htmlspecialchars($lng) ?>" rows="6" style="width:100%;"><?= htmlspecialchars($a["content_$lng"] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                </form>
            <?php else: ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= (int)($a['index'] ?? ($i+1)) ?></td>
                    <td><?= htmlspecialchars($a['date'] ?? '') ?></td>
                    <td><?= htmlspecialchars($a['slug'] ?? '') ?></td>
                    <td><?= htmlspecialchars($a['title_en'] ?? '') ?></td>
                    <td>
                        <a href="?tool=content&edit=<?= (int)$i ?>">Edit</a> |
                        <a href="?tool=content&delete=<?= (int)$i ?>" onclick="return confirm('Delete this article?')">Delete</a>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
