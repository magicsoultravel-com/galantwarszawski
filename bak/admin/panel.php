<?php
// panel.php - The main administration panel interface with robust tool handling

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));

require_once __DIR__ . '/../inc/auth.php';
if (!is_admin()) {
    header('Location: ' . $basePath . '/');
    exit;
}

$toolsDir = __DIR__;
$tools = [];

// Scan current directory for PHP files (tools)
foreach (scandir($toolsDir) as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && $file !== 'panel.php') {
        $toolName = pathinfo($file, PATHINFO_FILENAME);
        if (!in_array($toolName, ['auth', 'config', 'api_handler'])) {
            $tools[$toolName] = $file;
        }
    }
}
ksort($tools);

$currentTool = $_GET['tool'] ?? null;
if ($currentTool && !isset($tools[$currentTool])) {
    $currentTool = null;
}

// Initialize content variables
$headContent = '';
$bodyContent = '';

// Check for valid tool and capture its output
if ($currentTool && isset($tools[$currentTool])) {
    ob_start();
    include __DIR__ . '/' . $tools[$currentTool];
    $capturedOutput = ob_get_clean();

    // Regular expressions to find and extract head-related tags and their content
    // <title>
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $capturedOutput, $matches)) {
        $headContent .= $matches[0];
        $capturedOutput = str_replace($matches[0], '', $capturedOutput);
    }

    // <style>
    if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $capturedOutput, $matches)) {
        foreach ($matches[0] as $match) {
            $headContent .= $match;
        }
        $capturedOutput = str_replace($matches[0], '', $capturedOutput);
    }
    
    // <link>
    if (preg_match_all('/<link[^>]+>/is', $capturedOutput, $matches)) {
        foreach ($matches[0] as $match) {
            $headContent .= $match;
        }
        $capturedOutput = str_replace($matches[0], '', $capturedOutput);
    }

    // <script>
    if (preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $capturedOutput, $matches)) {
        foreach ($matches[0] as $match) {
            $headContent .= $match;
        }
        $capturedOutput = str_replace($matches[0], '', $capturedOutput);
    }

    // Assign the remaining output to the body
    $bodyContent = $capturedOutput;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars(basename(dirname(__DIR__))) ?> Panel</title>
<link rel="stylesheet" href="<?= $baseUrl ?>../assets/styles-scrolly.php">
<link rel="stylesheet" href="<?= $baseUrl ?>../assets/styles-modal.php"> 
<link rel="stylesheet" href="<?= $baseUrl ?>../assets/styles.php">
    <link rel="icon" href="../assets/favicon.png" type="image/png">
    
    <?= $headContent ?>
</head>
<body>
<?php include 'notepad.php'; ?>
<main>
    <div class="section">
        <h5><?= htmlspecialchars(basename(dirname(__DIR__))) ?> magic admin</h5>
        <h5>tools</h5>
        <div>
            <?php foreach ($tools as $toolName => $file): ?>
                <button onclick="location.href='?tool=<?= htmlspecialchars($toolName) ?>'" 
                        class="<?= ($currentTool === $toolName) ? 'active-tool' : '' ?>">
                    <?= htmlspecialchars(ucfirst(str_replace('-', ' ', $toolName))) ?>
                </button>
            <?php endforeach; ?>
            <button onclick="location.href='<?= htmlspecialchars('../') ?>'">&larr; Return to Site</button>
        </div>
    </div>
    
    <?= $bodyContent ?>
</main>
</body>
</html>