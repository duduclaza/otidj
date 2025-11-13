<?php
$title = $title ?? 'OTI - Login';
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
  <style>
    body {
      margin: 0;
      padding: 0;
      overflow-y: auto;
    }
    .btn-primary {
      background: #2563eb;
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      background: #1d4ed8;
      transform: scale(1.02);
    }
    @media (max-width: 768px) {
      .right-panel {
        display: none;
      }
      .left-panel {
        min-height: 100vh;
      }
    }
  </style>
</head>
<body class="min-h-screen">
  <div class="flex min-h-screen">
    <!-- Painel Esquerdo - Azul com Formulário -->
    <div class="left-panel w-full md:w-1/2 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 flex items-center justify-center p-4 md:p-8">
      <div class="w-full max-w-md my-8">
        <?php include $viewFile; ?>
      </div>
    </div>
    
    <!-- Painel Direito - Branco com Logo -->
    <div class="right-panel hidden md:flex md:w-1/2 bg-white items-center justify-center p-12">
      <div class="text-center">
        <img src="/assets/otilogo.png" alt="Logo OTI" class="mx-auto w-96 h-auto object-contain mb-8">
        <h2 class="text-4xl font-bold text-gray-800 mb-4">Organização Tecnológica Integrada</h2>
      </div>
    </div>
  </div>
</body>
</html>
