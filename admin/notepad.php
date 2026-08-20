<?php
// Base directory for notepad files
$notepadDir = __DIR__ . '/../assets/';

// Whitelist of allowed filenames to prevent directory traversal attacks
$allowedFiles = ['notepad1.txt', 'notepad2.txt', 'notepad3.txt', 'notepad4.txt'];

// Function to validate the requested file
function getNotepadFile($file, $allowedFiles, $notepadDir) {
    if (in_array($file, $allowedFiles)) {
        return $notepadDir . $file;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    $file = $_POST['file'] ?? '';
    $timestamp = (int)($_POST['timestamp'] ?? 0); // Get timestamp from POST request

    $targetFile = getNotepadFile($file, $allowedFiles, $notepadDir);

    if ($targetFile) {
        // Check for file modification by another session
        if (file_exists($targetFile) && filemtime($targetFile) > $timestamp) {
            http_response_code(409); // HTTP 409 Conflict
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Conflict: The file has been modified by another session. Please reload to see the latest changes.']);
            exit;
        }

        // Write the content to the file with a file lock
        file_put_contents($targetFile, $content, LOCK_EX);

        // Send back a success status and the new timestamp in JSON format
        header('Content-Type: application/json');
        echo json_encode(['status' => 'OK', 'timestamp' => filemtime($targetFile)]);
        exit;
    }
    http_response_code(400); // Bad Request
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid file specified.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'load') {
    $file = $_GET['file'] ?? '';

    $targetFile = getNotepadFile($file, $allowedFiles, $notepadDir);

    if ($targetFile && file_exists($targetFile)) {
        $content = file_get_contents($targetFile);
        $timestamp = filemtime($targetFile);
        
        // Send content and timestamp back as JSON
        header('Content-Type: application/json');
        echo json_encode(['content' => $content, 'timestamp' => $timestamp]);
    } else {
        // For non-existent files, send an empty JSON object
        header('Content-Type: application/json');
        echo json_encode(['content' => '', 'timestamp' => 0]);
    }
    exit;
}
?>

<style>
    /* New styles for the tab feature */
    .tab-container {
        display: flex;
        border-bottom: 1px solid #ccc;
        margin-bottom: 10px;
    }

    .tab-button {
        padding: 10px 15px;
        border: 1px solid #ccc;
        border-bottom: none;
        border-radius: 5px 5px 0 0;
        background-color: #f1f1f1;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .tab-button.active {
        background-color: #fff;
        border-bottom: 1px solid #fff;
    }

    .tab-button:not(.active):hover {
        background-color: #e9e9e9;
    }
</style>

<div id="notepad-modal" class="modal-overlay">
    <div id="notepad-content" class="modal-content">
        <div class="tab-container">
            <button class="tab-button active" data-file="notepad1.txt">Tab 1</button>
            <button class="tab-button" data-file="notepad2.txt">Tab 2</button>
            <button class="tab-button" data-file="notepad3.txt">Tab 3</button>
            <button class="tab-button" data-file="notepad4.txt">Tab 4</button>
        </div>
        <textarea id="notepadTextarea" class="modal-textarea"></textarea>
        <div class="modal-buttons">
            <button class="modal-button" onclick="saveNotepad()">Save</button>
            <button class="modal-button" onclick="downloadNotepad()">Download</button>
            <button class="modal-button" onclick="reloadNotepad()">Reload</button>
            <button class="modal-button" onclick="closeNotepad()">Close</button>
            <span id="notepad-status" class="modal-status"></span>
        </div>
        <div class="resizer-handle"></div>
    </div>
</div>

<button onclick="openNotepad()" class="notepad-button">
    📝 Notepad
</button>

<script>
    let saveTimer;
    let currentFile = 'notepad1.txt';
    let notepadCache = {};
    let fileTimestamps = {};

    function triggerAutoSave() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(async () => { await saveNotepad(); }, 3000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const textarea = document.getElementById('notepadTextarea');
        textarea.addEventListener('input', triggerAutoSave);

        const modal = document.getElementById('notepad-content');
        const resizer = modal.querySelector('.resizer-handle');
        const modalOverlay = document.getElementById('notepad-modal');

        resizer.addEventListener('mousedown', function(e) {
            e.preventDefault();
            window.addEventListener('mousemove', resizeModal);
            window.addEventListener('mouseup', stopResize);
        });

        function resizeModal(e) {
            const rect = modal.getBoundingClientRect();
            const newWidth = Math.min(Math.max(e.clientX - rect.left, 300), window.innerWidth * 0.9);
            const newHeight = Math.min(Math.max(e.clientY - rect.top, 200), window.innerHeight * 0.8);
            modal.style.width = newWidth + 'px';
            modal.style.height = newHeight + 'px';
        }

        function stopResize() {
            window.removeEventListener('mousemove', resizeModal);
            window.removeEventListener('mouseup', stopResize);
        }

        modalOverlay.addEventListener('click', function(event) {
            if (event.target === modalOverlay) {
                closeNotepad();
            }
        });

        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (event) => {
                document.querySelector('.tab-button.active').classList.remove('active');
                event.target.classList.add('active');
                notepadCache[currentFile] = textarea.value;
                currentFile = event.target.dataset.file;
                loadNotepadContent();
            });
        });
    });

    async function openNotepad() {
        const modal = document.getElementById('notepad-modal');
        modal.style.display = 'flex';

        if (Object.keys(notepadCache).length === 0) {
            try {
                const fetchPromises = ['notepad1.txt', 'notepad2.txt', 'notepad3.txt', 'notepad4.txt'].map(file =>
                    fetch(`notepad.php?ajax=load&file=${file}`).then(resp => resp.json())
                );

                const responses = await Promise.all(fetchPromises);

                ['notepad1.txt', 'notepad2.txt', 'notepad3.txt', 'notepad4.txt'].forEach((file, index) => {
                    const responseData = responses[index];
                    notepadCache[file] = responseData.content;
                    fileTimestamps[file] = responseData.timestamp;
                });

            } catch(e) {
                alert('Failed to load all notes: ' + e.message);
                return;
            }
        }
        
        loadNotepadContent();
    }
    
    async function reloadNotepad() {
        notepadCache = {};
        fileTimestamps = {};
        const status = document.getElementById('notepad-status');
        status.textContent = 'Reloading...';

        await openNotepad();
        status.textContent = 'Reloaded successfully.';
    }

    function loadNotepadContent() {
        const textarea = document.getElementById('notepadTextarea');
        textarea.value = notepadCache[currentFile] || '';
        textarea.focus();
    }

    function closeNotepad() {
        clearTimeout(saveTimer);
        document.getElementById('notepad-modal').style.display = 'none';
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && document.getElementById('notepad-modal').style.display === 'flex') {
            closeNotepad();
        }
    });

    async function saveNotepad() {
        const content = document.getElementById('notepadTextarea').value;
        notepadCache[currentFile] = content;
        
        try {
            const resp = await fetch('notepad.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `content=${encodeURIComponent(content)}&file=${currentFile}&timestamp=${fileTimestamps[currentFile]}`
            });

            if (resp.status === 409) {
                const errorData = await resp.json();
                alert(errorData.error + '\nYour changes will not be saved.');
                return;
            }

            if (!resp.ok) {
                const errorText = await resp.text();
                throw new Error('Save failed: ' + errorText);
            }
            
            const responseData = await resp.json();

            if (responseData.status === 'OK') {
                fileTimestamps[currentFile] = responseData.timestamp;
                const status = document.getElementById('notepad-status');
                const now = new Date();
                status.textContent = `Last saved ${now.toLocaleTimeString()}`;
            } else {
                throw new Error('Save error');
            }
        } catch(e) {
            alert(e.message);
        }
    }

    function downloadNotepad() {
        const content = document.getElementById('notepadTextarea').value;
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = currentFile;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>