<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$ref = trim($_GET['ref'] ?? '');
$id  = $ref ? decodeId($ref) : 0;

if (!$id) {
    header('Location: library.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND is_published = 1");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: library.php');
    exit;
}

$pageTitle = 'Read: ' . $book['title'];
$pdfUrl    = UPLOAD_URL . 'books/' . $book['file_path'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?= clean($pageTitle) ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#1a6b3c', dark: '#0f4a28' },
                        gold:    { DEFAULT: '#c9a84c' }
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #1a1a1a; font-family: 'Inter', sans-serif; }

        #topBar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 52px;
            background: #1a6b3c;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        #viewerContainer {
            position: fixed;
            top: 52px;
            bottom: 60px;
            left: 0;
            right: 0;
            overflow-y: auto;
            overflow-x: hidden;
            background: #1a1a1a;
            -webkit-overflow-scrolling: touch;
        }

        #pdfCanvas {
            display: block;
            margin: 0 auto;
            max-width: 100%;
        }

        #bottomBar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: 60px;
            background: #0f4a28;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            z-index: 100;
            padding: 0 16px;
        }

        .nav-btn {
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .nav-btn:hover { background: rgba(255,255,255,0.25); }
        .nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        #pageInfo {
            color: white;
            font-size: 14px;
            min-width: 100px;
            text-align: center;
        }

        #loadingOverlay {
            position: fixed;
            inset: 52px 0 60px 0;
            background: #1a1a1a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255,255,255,0.1);
            border-top-color: #c9a84c;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Disable right click and text selection */
        #viewerContainer {
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        /* Page jump input */
        #pageJump {
            width: 50px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            text-align: center;
            padding: 6px;
            border-radius: 6px;
            font-size: 14px;
        }

        #pageJump:focus { outline: none; background: rgba(255,255,255,0.25); }

        /* Zoom controls */
        #zoomInfo {
            color: rgba(255,255,255,0.7);
            font-size: 12px;
            min-width: 45px;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Top Bar -->
<div id="topBar">
    <div class="flex items-center gap-3">
        <a href="book.php?ref=<?= clean($ref) ?>"
           style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <span style="color: white; font-size: 14px; font-weight: 600;
                     max-width: 200px; white-space: nowrap; overflow: hidden;
                     text-overflow: ellipsis;">
            <?= clean($book['title']) ?>
        </span>
    </div>

    <div class="flex items-center gap-2">
        <!-- Zoom out -->
        <button onclick="zoomOut()" class="nav-btn" title="Zoom Out">
            <i class="fas fa-search-minus" style="font-size:12px;"></i>
        </button>
        <span id="zoomInfo">100%</span>
        <!-- Zoom in -->
        <button onclick="zoomIn()" class="nav-btn" title="Zoom In">
            <i class="fas fa-search-plus" style="font-size:12px;"></i>
        </button>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="spinner"></div>
    <p style="color: rgba(255,255,255,0.6); margin-top: 16px; font-size: 14px;">
        Loading PDF...
    </p>
</div>

<!-- PDF Viewer -->
<div id="viewerContainer">
    <canvas id="pdfCanvas"></canvas>
</div>

<!-- Bottom Navigation Bar -->
<div id="bottomBar">
    <button id="prevBtn" class="nav-btn" onclick="prevPage()" disabled>
        <i class="fas fa-chevron-left"></i>
    </button>

    <div style="display:flex; align-items:center; gap:8px;">
        <input type="number" id="pageJump" value="1" min="1"
               onchange="jumpToPage(this.value)"
               title="Jump to page">
        <span id="pageInfo" style="color:rgba(255,255,255,0.7); font-size:13px;">
            of <span id="totalPages">-</span>
        </span>
    </div>

    <button id="nextBtn" class="nav-btn" onclick="nextPage()">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

<!-- PDF.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    // Configure PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let pdfDoc     = null;
    let currentPage = 1;
    let totalPages  = 0;
    let scale       = 1.0;
    let isRendering = false;
    const canvas    = document.getElementById('pdfCanvas');
    const ctx       = canvas.getContext('2d');

    // Calculate initial scale based on screen width
    function getInitialScale() {
        const containerWidth = window.innerWidth;
        return containerWidth < 768 ? containerWidth / 600 : 1.2;
    }

    scale = getInitialScale();

    // Load the PDF
    const pdfUrl = <?= json_encode($pdfUrl) ?>;

    pdfjsLib.getDocument({
        url: pdfUrl,
        withCredentials: false
    }).promise.then(function(pdf) {
        pdfDoc     = pdf;
        totalPages = pdf.numPages;

        document.getElementById('totalPages').textContent = totalPages;
        document.getElementById('pageJump').max           = totalPages;
        document.getElementById('loadingOverlay').style.display = 'none';

        renderPage(currentPage);

    }).catch(function(err) {
        document.getElementById('loadingOverlay').innerHTML = `
            <i class="fas fa-exclamation-circle" style="color:#e53e3e; font-size:40px;"></i>
            <p style="color:rgba(255,255,255,0.7); margin-top:16px; text-align:center; padding:0 20px;">
                Failed to load PDF. Please try again later.
            </p>
        `;
        console.error('PDF load error:', err);
    });

    // Render a specific page
    function renderPage(pageNum) {
        if (isRendering) return;
        isRendering = true;

        pdfDoc.getPage(pageNum).then(function(page) {
            const viewport = page.getViewport({ scale: scale });

            canvas.height = viewport.height;
            canvas.width  = viewport.width;

            const renderCtx = {
                canvasContext: ctx,
                viewport: viewport
            };

            page.render(renderCtx).promise.then(function() {
                isRendering  = false;
                currentPage  = pageNum;

                // Update UI
                document.getElementById('pageJump').value = currentPage;
                document.getElementById('prevBtn').disabled = currentPage <= 1;
                document.getElementById('nextBtn').disabled = currentPage >= totalPages;

                // Scroll to top of viewer
                document.getElementById('viewerContainer').scrollTop = 0;
            });
        });
    }

    function prevPage() {
        if (currentPage <= 1) return;
        renderPage(currentPage - 1);
    }

    function nextPage() {
        if (currentPage >= totalPages) return;
        renderPage(currentPage + 1);
    }

    function jumpToPage(val) {
        const page = parseInt(val);
        if (page >= 1 && page <= totalPages) {
            renderPage(page);
        }
    }

    function zoomIn() {
        if (scale >= 3.0) return;
        scale += 0.2;
        updateZoom();
        renderPage(currentPage);
    }

    function zoomOut() {
        if (scale <= 0.5) return;
        scale -= 0.2;
        updateZoom();
        renderPage(currentPage);
    }

    function updateZoom() {
        document.getElementById('zoomInfo').textContent = Math.round(scale * 100) + '%';
    }

    // Swipe gestures for mobile
    let touchStartX = 0;
    let touchStartY = 0;

    document.getElementById('viewerContainer').addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    document.getElementById('viewerContainer').addEventListener('touchend', function(e) {
        const deltaX = e.changedTouches[0].screenX - touchStartX;
        const deltaY = e.changedTouches[0].screenY - touchStartY;

        // Only trigger swipe if horizontal movement is greater than vertical
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
            if (deltaX < 0) {
                nextPage(); // Swipe left = next page
            } else {
                prevPage(); // Swipe right = previous page
            }
        }
    }, { passive: true });

    // Disable right click to prevent save image
    document.getElementById('viewerContainer').addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        scale = getInitialScale();
        updateZoom();
        if (pdfDoc) renderPage(currentPage);
    });
</script>

</body>
</html>