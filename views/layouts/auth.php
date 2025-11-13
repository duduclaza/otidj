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
    .typing-effect {
      border-right: 2px solid #6b7280;
      animation: blink 0.7s step-end infinite;
      display: inline-block;
      min-height: 1.5rem;
    }
    @keyframes blink {
      from, to { border-color: transparent; }
      50% { border-color: #6b7280; }
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
    <!-- Painel Esquerdo - Azul com efeito roxo/lilás -->
    <div class="left-panel w-full md:w-1/2 bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-800 flex items-center justify-center p-4 md:p-8" style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #6366f1 100%);">
      <div class="w-full max-w-md my-8">
        <?php include $viewFile; ?>
      </div>
    </div>
    
    <!-- Painel Direito - Clean Design -->
    <div class="right-panel hidden md:flex md:w-1/2 items-center justify-center p-12" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);">
      <div class="text-center">
        <h1 class="text-8xl font-bold mb-4 text-blue-800">OTI</h1>
        <p class="text-lg text-gray-500 font-light tracking-wide typing-effect" id="typingText"></p>
        
        <!-- Elemento decorativo -->
        <div class="mt-12 flex justify-center gap-2">
          <div class="w-2 h-2 rounded-full bg-blue-400 opacity-60"></div>
          <div class="w-2 h-2 rounded-full bg-blue-500 opacity-70"></div>
          <div class="w-2 h-2 rounded-full bg-blue-600 opacity-80"></div>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    // Efeito de digitação
    const text = 'Organização Tecnológica Integrada';
    const typingElement = document.getElementById('typingText');
    let index = 0;
    let isDeleting = false;
    
    function typeWriter() {
      if (!isDeleting && index <= text.length) {
        typingElement.textContent = text.substring(0, index);
        index++;
        setTimeout(typeWriter, 100);
      } else if (isDeleting && index >= 0) {
        typingElement.textContent = text.substring(0, index);
        index--;
        setTimeout(typeWriter, 50);
      } else if (index > text.length) {
        setTimeout(() => {
          isDeleting = true;
          typeWriter();
        }, 2000);
      } else {
        isDeleting = false;
        index = 0;
        setTimeout(typeWriter, 500);
      }
    }
    
    typeWriter();
  </script>
</body>
</html>
