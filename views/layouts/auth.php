<?php
$title = $title ?? 'SGQ OTI - DJ';
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
    .auth-bg {
      background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 50%, #1e293b 100%);
      position: relative;
    }
    .auth-bg::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%23ffffff" stroke-width="0.5" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>') repeat;
    }
    .glass-effect {
      backdrop-filter: blur(15px);
      background: rgba(30, 64, 175, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .btn-primary {
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      border: none;
      box-shadow: 0 4px 15px 0 rgba(37, 99, 235, 0.3);
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
      transform: translateY(-1px);
      box-shadow: 0 8px 25px 0 rgba(37, 99, 235, 0.4);
    }
  </style>
</head>
<body class="auth-bg min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <?php include $viewFile; ?>
  </div>
</body>
</html>
