<?php
// style-manager.php - Admin panel module to manage dynamic style sheets.

// This check prevents direct access to the file and ensures it's only
// run from within the main admin panel.
if (!isset($basePath)) {
    exit('This file cannot be accessed directly.');
}

// Set up dynamic paths. The styles are in the 'assets' directory,
// which is one level up from the current admin directory.
$assetsDir = dirname(__DIR__) . '/assets/';
$stylesFile = $assetsDir . 'styles.php';

// Handle the POST request to load a new style file.
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['load_style'])) {
    $styleToLoad = basename($_POST['load_style']); // Use basename for security
    $sourceFile = $assetsDir . $styleToLoad;

    if (file_exists($sourceFile)) {
        // Read the content of the source file.
        $content = file_get_contents($sourceFile);
        
        // Overwrite or create the styles.php file with the new content.
        if (file_put_contents($stylesFile, $content) !== false) {
            $message = 'success';
        } else {
            $message = 'error';
        }
    } else {
        $message = 'not_found';
    }
}
?>

<div class="section">
    <h3>Style Manager</h3>
    <p>Select a style template to load. This will replace the content of your site's main <code>styles.php</code> file.</p>

    <?php if ($message === 'success'): ?>
        <p class="message success">Style loaded successfully! The site's style has been updated. </p>
    <?php elseif ($message === 'error'): ?>
        <p class="message error">Error: Could not write to <code>styles.php</code>. Please check file permissions. </p>
    <?php elseif ($message === 'not_found'): ?>
        <p class="message error">Error: The selected style file was not found. 

[Image of a warning sign]
</p>
    <?php endif; ?>

    <style>
        .style-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .style-table th, .style-table td {
            border: 1px solid #ddd;
            padding: 0.75rem;
            text-align: left;
            vertical-align: top;
        }
        .style-table th {
            background-color: #f5f5f5;
            font-weight: 600;
        }
        .style-table button {
            padding: 0.5rem 1rem;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .style-table button:hover {
            background-color: #45a049;
        }
        .message {
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 5px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>

    <table class="style-table">
        <thead>
            <tr>
                <th>Style File</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Scan the assets directory for style files.
            $files = scandir($assetsDir);
            $foundStyles = false;
            foreach ($files as $file) {
                // Check if the file matches the "styles<number>.php" pattern.
                if (preg_match('/^styles\d+\.php$/', $file)) {
                    $foundStyles = true;
                    $filePath = $assetsDir . $file;
                    $fileContent = file_get_contents($filePath);

                    // Extract the first 30 characters of comments.
                    preg_match('/\/\*(.*?)\*\//s', $fileContent, $matches);
                    $description = 'No comment found.';
                    if (isset($matches[1])) {
                        $description = trim($matches[1]);
                        $description = substr($description, 0, 30);
                        if (strlen(trim($matches[1])) > 30) {
                            $description .= '...';
                        }
                    }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($file) ?></td>
                        <td><?= htmlspecialchars($description) ?></td>
                        <td>
                            <form method="post" action="?tool=style-manager">
                                <input type="hidden" name="load_style" value="<?= htmlspecialchars($file) ?>">
                                <button type="submit">Load</button>
                            </form>
                        </td>
                    </tr>
                    <?php
                }
            }
            if (!$foundStyles) {
                echo '<tr><td colspan="3">No style files found in the assets directory matching the pattern <code>styles&lt;number&gt;.php</code>.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
