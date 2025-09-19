<?php
$title = $title ?? 'SGQ OTI - DJ';
$viewFile = $viewFile ?? __DIR__ . '/../pages/home.php';
$sidebar = __DIR__ . '/../partials/sidebar.php';
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title><?= e($title) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <link rel="stylesheet" href="/src/Support/modal-styles.css">
  <script src="/src/Support/modal-utils.js"></script>
  <script>
    // User permissions for frontend
    window.userPermissions = <?= json_encode($_SESSION['user_permissions'] ?? []) ?>;
  </script>
  <script>
    // Tailwind config with dark theme
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81'
            },
          }
        }
      }
    }
  </script>
  <style>
    /* Page transition styles */
    .page-transition {
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.3s ease-in-out;
    }
    .page-transition.loaded {
      opacity: 1;
      transform: translateY(0);
    }
    
    /* Smooth scrolling */
    html {
      scroll-behavior: smooth;
    }
    
    /* Loading overlay */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(30, 41, 59, 0.8);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      opacity: 0;
      visibility: hidden;
      transition: all 0.2s ease-in-out;
    }
    .loading-overlay.active {
      opacity: 1;
      visibility: visible;
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
        <?php if ($msg = flash('success')): ?>
          <div class="mb-4 rounded-md border border-green-200 bg-green-50 text-green-800 px-4 py-2 text-sm"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
          <div class="mb-4 rounded-md border border-red-200 bg-red-50 text-red-800 px-4 py-2 text-sm"><?= e($msg) ?></div>
        <?php endif; ?>
        <div class="page-transition">
          <?php include $viewFile; ?>
        </div>
      </main>
    </div>
  </div>

  <!-- Loading overlay -->
  <div class="loading-overlay" id="loadingOverlay">
    <div class="text-white text-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mx-auto mb-2"></div>
      <div class="text-sm">Carregando...</div>
    </div>
  </div>

  <script>
    // Page transition and smooth navigation
    document.addEventListener('DOMContentLoaded', function() {
      // Add loaded class for initial page load
      const pageContent = document.querySelector('.page-transition');
      if (pageContent) {
        setTimeout(() => pageContent.classList.add('loaded'), 100);
      }

      // Handle page navigation with smooth transitions
      document.addEventListener('click', function(e) {
        const link = e.target.closest('.page-link');
        if (link && link.href && !link.href.includes('#')) {
          e.preventDefault();
          
          // Show loading overlay
          const overlay = document.getElementById('loadingOverlay');
          overlay.classList.add('active');
          
          // Add fade out effect
          const currentContent = document.querySelector('.page-transition');
          if (currentContent) {
            currentContent.classList.remove('loaded');
          }
          
          // Navigate after short delay
          setTimeout(() => {
            window.location.href = link.href;
          }, 200);
        }
      });

      // Handle form submissions with loading
      document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.tagName === 'FORM') {
          const overlay = document.getElementById('loadingOverlay');
          overlay.classList.add('active');
        }
      });
    });
  </script>
</body>
</html>
