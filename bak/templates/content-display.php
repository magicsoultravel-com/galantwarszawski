<?php
// Define constants for asset directories
define('CLIPBOARD_DIR', __DIR__ . '/../assets/clipboard/');
define('USERS_CLIPBOARD_DIR', CLIPBOARD_DIR . 'users/');

// Create asset directories if they don't exist
if (!is_dir(CLIPBOARD_DIR)) {
    mkdir(CLIPBOARD_DIR, 0755, true);
}

if (!is_dir(USERS_CLIPBOARD_DIR)) {
    mkdir(USERS_CLIPBOARD_DIR, 0755, true);
}

// Check if user is logged in
if (is_logged_in()) {
    $email = $_SESSION['email'];
    $userDir = USERS_CLIPBOARD_DIR . $email . '/';
    if (!is_dir($userDir)) {
        mkdir($userDir, 0755, true);
    }
} else {
    echo 'You must be logged in to use the Clipboard Catcher.';
    exit;
}

// ------------------
// AJAX upload handler
// ------------------
if (isset($_GET['upload']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['clipboardContent'])) {
        $file = $_FILES['clipboardContent'];
        $fileName = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filePath = $userDir . $fileName;
        move_uploaded_file($file['tmp_name'], $filePath);
        echo json_encode(['success' => true, 'fileName' => $fileName]);
        exit;
    } elseif (isset($_POST['clipboardText'])) {
        $text = $_POST['clipboardText'];
        $fileName = uniqid() . '.txt';
        $filePath = $userDir . $fileName;
        file_put_contents($filePath, $text);
        echo json_encode(['success' => true, 'fileName' => $fileName]);
        exit;
    }
}

// ------------------
// AJAX list handler
// ------------------
if (isset($_GET['list'])) {
    $files = scandir($userDir);
    $files = array_diff($files, ['.', '..']);
    foreach ($files as $file) {
        echo "<li>" . htmlspecialchars($file) . "</li>";
    }
    exit;
}

// ------------------
// Default page load
// ------------------
$files = scandir($userDir);
$files = array_diff($files, ['.', '..']);
?>

<section>
    <h2>Clipboard Catcher</h2>
    <form id="clipboard-form">
        <button id="paste-button">Paste Clipboard Content</button>
    </form>
    <h3>Uploaded Files:</h3>
    <ul id="uploaded-files">
        <?php foreach ($files as $file) : ?>
            <li><?= htmlspecialchars($file) ?></li>
        <?php endforeach; ?>
    </ul>
    <div id="preview-container"></div>
</section>

<script>
const pasteButton = document.getElementById('paste-button');
const uploadedFilesList = document.getElementById('uploaded-files');
const previewContainer = document.getElementById('preview-container');

// Fetch uploaded files
function fetchUploadedFiles() {
    fetch('?list=1')
    .then(r => r.text())
    .then(html => {
        uploadedFilesList.innerHTML = html;
    });
}

pasteButton.addEventListener('click', async (e) => {
    e.preventDefault();
    try {
        const clipboardContent = await navigator.clipboard.read();
        const file = clipboardContent[0];
        const fileReader = new FileReader();
        fileReader.onload = () => {
            const previewContent = document.createElement('div');
            previewContent.innerHTML = `
                <img src="${fileReader.result}" style="max-width: 100%; max-height: 200px;">
                <button id="upload-button">Upload</button>
                <button id="discard-button">Discard</button>
            `;
            previewContainer.innerHTML = '';
            previewContainer.appendChild(previewContent);

            document.getElementById('upload-button').addEventListener('click', () => {
                const formData = new FormData();
                formData.append('clipboardContent', file);
                fetch('?upload=1', {
                    method: 'POST',
                    body: formData,
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        fetchUploadedFiles();
                        previewContainer.innerHTML = '';
                    }
                });
            });

            document.getElementById('discard-button').addEventListener('click', () => {
                previewContainer.innerHTML = '';
            });
        };
        fileReader.readAsDataURL(file);
    } catch (error) {
        // Handle text clipboard content
        navigator.clipboard.readText().then(text => {
            const previewContent = document.createElement('div');
            previewContent.innerHTML = `
                <textarea style="width: 100%; height: 200px;">${text}</textarea>
                <button id="upload-button">Upload</button>
                <button id="discard-button">Discard</button>
            `;
            previewContainer.innerHTML = '';
            previewContainer.appendChild(previewContent);

            document.getElementById('upload-button').addEventListener('click', () => {
                const formData = new FormData();
                formData.append('clipboardText', text);
                fetch('?upload=1', {
                    method: 'POST',
                    body: formData,
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        fetchUploadedFiles();
                        previewContainer.innerHTML = '';
                    }
                });
            });

            document.getElementById('discard-button').addEventListener('click', () => {
                previewContainer.innerHTML = '';
            });
        });
    }
});
</script>
