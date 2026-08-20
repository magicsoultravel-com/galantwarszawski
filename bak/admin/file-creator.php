<?php
ob_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_file') {
    if (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');

    // Make sure the auth.php file is included if it's not already
    require_once __DIR__ . '/../inc/auth.php';
    if (!is_admin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied. Administrator privileges required.']);
        exit;
    }

    $rootDir = realpath(__DIR__ . '/../');
    $folder = $_POST['folder'];
    $filename = $_POST['filename'];

    // Security check to ensure the folder path is within the root directory
    $targetPath = realpath($rootDir . '/' . $folder);
    if ($targetPath === false || strpos($targetPath, $rootDir) !== 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid folder path']);
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
        echo json_encode(['success' => false, 'message' => 'Invalid filename']);
        exit;
    }

    $filePath = $targetPath . '/' . $filename;
    try {
        touch($filePath);
        echo json_encode(['success' => true, 'message' => 'File created successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating file: ' . $e->getMessage()]);
    }
    exit;
}

/**
 * Recursively gets a list of all directories and subdirectories within a root path.
 *
 * @param string $dir The directory to scan.
 * @param array $results The array to store the results.
 * @param string $prefix The path prefix for nested directories.
 */
function get_all_subfolders($dir, &$results = [], $prefix = '') {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            $folderName = $prefix . $file;
            $results[] = $folderName;
            get_all_subfolders($path, $results, $folderName . '/');
        }
    }
}

$rootDir = realpath(__DIR__ . '/../');
$allFolders = [];
get_all_subfolders($rootDir, $allFolders, '');

// Add the root folder itself to the list.
$allFolders[] = '.';
sort($allFolders);
?>

<div class="sections-container">
    <div id="createFileModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); z-index: 1000;">
        <h5>Create File</h5>
        <form id="createFileForm">
            <label for="folder">Folder:</label>
            <select id="folder" name="folder">
                <?php
                foreach ($allFolders as $folder) {
                    echo '<option value="' . htmlspecialchars($folder) . '">' . htmlspecialchars($folder) . '</option>';
                }
                ?>
            </select>
            <br>
            <label for="filename">Filename:</label>
            <input type="text" id="filename" name="filename" required>
            <br>
            <button type="submit" class="action-button">Create File</button>
            <button type="button" id="closeModalButton" class="action-button">Close</button>
        </form>
        <div id="createFileFeedback"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('open_modal') === '1' || window.parent === window) {
            document.getElementById('createFileModal').style.display = 'block';
        }

        document.getElementById('closeModalButton').addEventListener('click', function() {
            document.getElementById('createFileModal').style.display = 'none';
        });

        document.getElementById('createFileForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'create_file');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const feedbackElement = document.getElementById('createFileFeedback');
                if (data.success) {
                    feedbackElement.innerHTML = '<p style="color: green;">' + data.message + '</p>';
                } else {
                    feedbackElement.innerHTML = '<p style="color: red;">' + data.message + '</p>';
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
</script>