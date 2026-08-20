<?php
ob_start();
require_once __DIR__ . '/../inc/auth.php';

$projectRoot = realpath(__DIR__ . '/../');
$styleRel = 'assets/styles.php';
$styleFile = realpath($projectRoot . '/' . $styleRel);

// ---------- Helpers ----------
function safe_out($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function read_styles_css($file){
    if (!$file || !file_exists($file)) return '';
    $txt = file_get_contents($file);
    $txt = preg_replace('/<\?php.*?\?>/s', '', $txt);
    return $txt;
}

function write_styles_css($file, $content){
    if (!$file) return false;
    $header = "<?php header(\"Content-type: text/css\"); ?>\n";
    return file_put_contents($file, $header.$content);
}

function parse_rules($css){
    $rules = [];
    preg_match_all(
        '/\s*(?P<comments>\/\*.*?\*\/)*\s*(?P<selector>[^{}@][^{]+)\{(?P<body>[^}]*)\}/s',
        $css, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE
    );
    foreach ($m as $r){
        $selector = trim(preg_replace('/\s+/', ' ', $r['selector'][0]));
        $propsRaw = array_filter(array_map('trim', explode(';', trim($r['body'][0]))));
        $props = [];
        foreach ($propsRaw as $line){
            if (strpos($line, ':') !== false){
                [$k,$v] = array_map('trim', explode(':', $line, 2));
                if ($k !== '') $props[strtolower($k)] = $v;
            }
        }
        $comments = isset($r['comments']) ? explode('*/', $r['comments'][0]) : [];
        $comments = array_filter(array_map(function($c) {
            return trim(str_replace(['/*', '*'], '', $c));
        }, $comments));

        if ($selector !== '') {
            $rules[] = [
                'selector' => $selector,
                'props'    => $props,
                'comments' => $comments,
                'offset'   => $r[0][1]
            ];
        }
    }
    return $rules;
}

/**
 * Recursively scans directories for HTML/PHP files to find CSS classes + <style> blocks.
 */
function scan_for_classes($dir) {
    $results = [];
    if (!is_dir($dir)) return $results;
    
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($it as $file) {
        if ($file->isDir()) continue;

        $extension = strtolower($file->getExtension());
        if (!in_array($extension, ['php','html','htm'])) continue;

        $filePath = $file->getPathname();
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) continue;

        $fileData = [
            'path' => str_replace($dir, '', $filePath),
            'classes' => [],
            'style_blocks' => []
        ];
        
        // --- CSS blocks in <style> ---
        if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $fileContent, $styleBlocks)) {
            foreach ($styleBlocks[1] as $cssBlock) {
                $fileData['style_blocks'][] = trim($cssBlock);

                // Extract classes from CSS selectors
                $cssRules = parse_rules($cssBlock);
                foreach($cssRules as $rule) {
                    foreach (explode(',', $rule['selector']) as $sel) {
                        if (preg_match_all('/\.([a-zA-Z0-9_-]+)/', $sel, $matches)) {
                            foreach ($matches[1] as $cls) {
                                if (preg_match('/^[a-zA-Z0-9_-]+$/', $cls)) {
                                    $fileData['classes'][] = $cls;
                                }
                            }
                        }
                    }
                }
            }
        }

        // --- class="..." attributes ---
        if (preg_match_all('/class\s*=\s*["\']([\w\-\s]+)["\']/i', $fileContent, $classMatches)) {
            foreach ($classMatches[1] as $match) {
                foreach (explode(' ', $match) as $cls) {
                    if (preg_match('/^[a-zA-Z0-9_-]+$/', $cls)) {
                        $fileData['classes'][] = $cls;
                    }
                }
            }
        }

        $fileData['classes'] = array_unique(array_filter($fileData['classes']));
        
        if (!empty($fileData['classes']) || !empty($fileData['style_blocks'])) {
            $results[] = $fileData;
        }
    }
    return $results;
}

// ---------- Control Flags ----------
$isEditing = isset($_GET['edit']) && is_admin();
$editOffset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$showScanResults = isset($_GET['run_check']);
$scanResults = [];

// ---------- Load Styles ----------
$exists = ($styleFile && file_exists($styleFile));
$css = $exists ? read_styles_css($styleFile) : '';
$saveMessage = isset($_GET['save_success']) ? "Styles saved successfully." : "";

$allRules = parse_rules($css);
$definedClasses = [];
foreach ($allRules as $rule) {
    foreach (explode(',', $rule['selector']) as $selector) {
        if (preg_match_all('/\.([a-zA-Z0-9_-]+)/', $selector, $matches)) {
            foreach ($matches[1] as $cls) {
                if (preg_match('/^[a-zA-Z0-9_-]+$/', $cls)) {
                    $definedClasses[] = $cls;
                }
            }
        }
    }
}
$definedClasses = array_unique($definedClasses);

if ($showScanResults) {
    $scanResults = scan_for_classes($projectRoot);
}

// ---------- Save ----------
if(isset($_POST['save_styles'])){
    if(!is_admin()){ http_response_code(403); echo "Access denied"; exit; }
    $newCSS = $_POST['new_css'] ?? '';
    if(write_styles_css($styleFile, $newCSS)) {
        $saveMessage="Styles saved successfully.";
    } else {
        $saveMessage="Failed to save styles.";
    }
    header("Location: ?tool=style-inspector&save_success=1");
    exit;
}
?>
<style>
.editor-container {
    padding: 10px;
    background: #f4f4f4;
    border-radius: 8px;
}
.editor-textarea {
    width: 100%;
    min-height: 500px;
    font-family: monospace;
    font-size: 14px;
    padding: 10px;
    box-sizing: border-box;
}
.props-container {
    max-height: 500px;
    overflow: hidden;
    transition: max-height 0.3s ease-in-out;
}
.props-container.collapsed {
    max-height: 0;
}
.selector-cell { font-weight: bold; }
.selector-toggle {
    cursor: pointer;
    user-select: none;
    border: none;
    background: none;
    color: inherit;
    font: inherit;
    padding: 0;
    margin: 0;
    text-align: left;
    width: 100%;
}
.props-list { margin: 0; padding: 0 0 0 20px; list-style-type: disc; }
.sortable-header { cursor: pointer; position: relative; user-select: none; padding-right: 20px; }
.sort-arrow { position: absolute; right: 5px; top: 50%; transform: translateY(-50%); font-size: 12px; }
.highlight-green { color: #4CAF50; font-weight: bold; }
</style>

<div class="style-inspector">
    <section>
        <h2>🎛️ Style Inspector</h2>
        <?php if(!$exists): ?>
        <p><b>Styles file not found:</b> <code><?= safe_out($projectRoot.'/'.$styleRel) ?></code></p>
        <?php return; endif; ?>

        <?php if (!empty($saveMessage)): ?>
        <p style="color:#8bc34a; font-weight: bold;"><?= safe_out($saveMessage) ?></p>
        <?php endif; ?>

        <?php if ($isEditing): ?>
            <div class="editor-container">
                <h4>Editing: <code><?= safe_out($styleRel) ?></code></h4>
                <form method="post" action="">
                    <textarea id="css-editor" name="new_css" class="editor-textarea"><?= safe_out($css) ?></textarea>
                    <br>
                    <button type="submit" name="save_styles">Save Styles</button>
                    <a href="?tool=style-inspector">Cancel</a>
                </form>
            </div>
        <?php else: ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4>📚 Selectors Overview</h4>
                <div>
                    <button id="toggleAllBtn" type="button" class="btn">Collapse/Expand All</button>
                    <form style="display:inline;" method="get" action="">
                        <input type="hidden" name="tool" value="style-inspector">
                        <button type="submit" name="run_check" value="1" style="margin-left:10px;">Run Check</button>
                    </form>
                    <form style="display:inline;" method="get" action="">
                        <input type="hidden" name="tool" value="style-inspector">
                        <button type="submit" name="edit" value="1" style="margin-left:10px;">Edit Entire File</button>
                    </form>
                </div>
            </div>

            <table id="selectorsTable" class="file-browser-table">
                <thead>
                    <tr>
                        <th id="selectorHeader" class="sortable-header">Selector <span id="sortArrow" class="sort-arrow"></span></th>
                        <th>Properties</th>
                        <th>Comments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="selectorsTableBody">
                <?php foreach($allRules as $rr): ?>
                    <tr class="rule-row" data-original-index="<?= safe_out($rr['offset']) ?>">
                        <td class="selector-cell" style="text-align:left;">
                            <button type="button" class="selector-toggle">
                                <code><?= safe_out($rr['selector']) ?></code>
                            </button>
                        </td>
                        <td class="props-cell">
                            <div class="props-container collapsed">
                                <?php if (count($rr['props']) > 0): ?>
                                    <ul class="props-list">
                                    <?php foreach($rr['props'] as $prop => $val): ?>
                                        <li><strong><?= safe_out($prop) ?></strong>: <?= safe_out($val) ?>;</li>
                                    <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    –
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($rr['comments'])): ?>
                                <?php foreach ($rr['comments'] as $comment): ?>
                                    <p><?= safe_out($comment) ?></p>
                                <?php endforeach; ?>
                            <?php else: ?>
                                –
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                            <form method="get" action="">
                                <input type="hidden" name="tool" value="style-inspector">
                                <input type="hidden" name="edit" value="1">
                                <button type="submit" name="offset" value="<?= safe_out($rr['offset']) ?>">Edit</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($showScanResults): ?>
                <h4 style="margin-top: 20px;">🔍 Classes & Style Blocks Found</h4>
                <table id="scanResultsTable" class="file-browser-table">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Classes Found</th>
                            <th>&lt;style&gt; Blocks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scanResults as $file): ?>
                        <tr>
                            <td><code><?= safe_out($file['path']) ?></code></td>
                            <td>
                                <?php if (!empty($file['classes'])): ?>
                                    <?php 
                                        $classes_with_highlight = [];
                                        foreach ($file['classes'] as $class) {
                                            $safe_class = safe_out($class);
                                            if (in_array($class, $definedClasses)) {
                                                $classes_with_highlight[] = '<span class="highlight-green">' . $safe_class . '</span>';
                                            } else {
                                                $classes_with_highlight[] = $safe_class;
                                            }
                                        }
                                        echo implode(', ', $classes_with_highlight);
                                    ?>
                                <?php else: ?>
                                    –
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($file['style_blocks'])): ?>
                                    <details>
                                        <summary><?= count($file['style_blocks']) ?> block(s)</summary>
                                        <?php foreach ($file['style_blocks'] as $block): ?>
                                            <pre><?= safe_out($block) ?></pre>
                                        <?php endforeach; ?>
                                    </details>
                                <?php else: ?>
                                    –
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleAllBtn = document.getElementById('toggleAllBtn');
    const selectorToggles = document.querySelectorAll('.selector-toggle');
    const propsContainers = document.querySelectorAll('.props-container');
    let allCollapsed = true;

    function setAllVisibility(collapsed) {
        propsContainers.forEach(container => {
            container.classList.toggle('collapsed', collapsed);
        });
        allCollapsed = collapsed;
    }
    if (toggleAllBtn) {
        toggleAllBtn.addEventListener('click', () => {
            setAllVisibility(!allCollapsed);
        });
    }
    selectorToggles.forEach(toggle => {
        toggle.addEventListener('click', (event) => {
            const container = event.target.closest('tr').querySelector('.props-container');
            container.classList.toggle('collapsed');
        });
    });

    const selectorHeader = document.getElementById('selectorHeader');
    const tableBody = document.getElementById('selectorsTableBody');
    const originalRows = Array.from(tableBody.querySelectorAll('tr'));
    const sortStates = ['default', 'asc', 'desc'];
    let currentSortState = 'default';

    function sortRows(state) {
        let sortedRows = [];
        if (state === 'default') {
            sortedRows = originalRows;
        } else {
            sortedRows = [...originalRows].sort((a, b) => {
                const selectorA = a.querySelector('.selector-toggle code').textContent.trim();
                const selectorB = b.querySelector('.selector-toggle code').textContent.trim();
                return state === 'asc' ? selectorA.localeCompare(selectorB) : selectorB.localeCompare(selectorA);
            });
        }
        tableBody.innerHTML = '';
        sortedRows.forEach(row => tableBody.appendChild(row));
    }

    if (selectorHeader) {
        selectorHeader.addEventListener('click', () => {
            const currentIndex = sortStates.indexOf(currentSortState);
            const nextIndex = (currentIndex + 1) % sortStates.length;
            currentSortState = sortStates[nextIndex];
            
            if (currentSortState === 'asc') {
                sortArrow.textContent = ' ▲';
            } else if (currentSortState === 'desc') {
                sortArrow.textContent = ' ▼';
            } else {
                sortArrow.textContent = '';
            }
            sortRows(currentSortState);
        });
    }
});
</script>
