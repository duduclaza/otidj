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
  <script>
    // Tailwind config (optional branding)
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
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="min-h-screen flex">
    <?php include $sidebar; ?>
    <main class="flex-1 p-6 lg:p-8">
      <div class="max-w-6xl mx-auto">
        <?php if ($msg = flash('success')): ?>
          <div class="mb-4 rounded-md border border-green-200 bg-green-50 text-green-800 px-4 py-2 text-sm"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
          <div class="mb-4 rounded-md border border-red-200 bg-red-50 text-red-800 px-4 py-2 text-sm"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php include $viewFile; ?>
      </div>
    </main>
  </div>
</body>
</html>
