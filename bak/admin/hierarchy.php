<?php
// hierarchy.php - A tool to display the file hierarchy of the application

function tree($dir, $basePath, $level = 0) {
    $files = scandir($dir);
    $lastIndex = count($files) - 1;
    $visibleFiles = array_filter($files, function($file) {
        return !in_array($file, ['.', '..']);
    });
    $count = count($visibleFiles);
    $i = 0;

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $i++;
        $filePath = $dir . '/' . $file;
        $realPath = realpath($filePath);
        $fullDisplayPath = str_replace($basePath, '', $realPath);
        $fullDisplayPath = str_replace('\\', '/', $fullDisplayPath);

        $connector = ($i == $count) ? '└── ' : '├── ';
        $indentation = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
        
        $output = $indentation . $connector . htmlspecialchars($fullDisplayPath);

        if (is_dir($filePath)) {
            $output .= "/";
            echo '<div>' . $output . '<a href="javascript:void(0);" class="copy-btn" data-path="' . htmlspecialchars($fullDisplayPath . '/') . '">📋</a></div>';
            tree($realPath, $basePath, $level + 1);
        } else {
            echo '<div>' . $output . '<a href="javascript:void(0);" class="copy-btn" data-path="' . htmlspecialchars($fullDisplayPath) . '">📋</a></div>';
        }
    }
}

$rootDir = realpath(__DIR__ . '/../');
?>
<style>
    .copy-btn {
        margin-left: 5px;
        cursor: pointer;
        user-select: none;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const path = e.currentTarget.getAttribute('data-path');
            navigator.clipboard.writeText(path).then(() => {
                const originalText = e.currentTarget.textContent;
                e.currentTarget.textContent = '✅';
                setTimeout(() => {
                    e.currentTarget.textContent = originalText;
                }, 1000);
            }).catch(err => {
                console.error('Failed to copy text: ', err);
            });
        });
    });
});
</script>
<section>
    <h5>File Hierarchy</h5>
    <div style="text-align: left;">
        <div><?php echo htmlspecialchars(basename($rootDir)) ?>/</div>
        <?php tree($rootDir, $rootDir); ?>
    </div>
</section>