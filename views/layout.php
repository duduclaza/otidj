<?php
// Determine the view to load based on the current request
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Map routes to view files
$viewMap = [
    '/melhoria-continua/solicitacoes' => 'melhoria-continua/solicitacoes.php',
    // Add other routes as needed
];

// Find the correct view file
$viewFile = null;
foreach ($viewMap as $route => $view) {
    if ($path === $route) {
        $viewFile = $view;
        break;
    }
}

// If no specific view found, try to determine from path
if (!$viewFile) {
    // Default fallback
    $viewFile = 'dashboard.php';
}

// Function to safely escape output
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGQ OTI DJ - Sistema de Gestão da Qualidade</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .submenu { transition: all 0.3s ease; }
        .submenu.hidden { max-height: 0; opacity: 0; }
        .submenu:not(.hidden) { max-height: 500px; opacity: 1; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-6">
                <?php
                $fullPath = __DIR__ . '/' . $viewFile;
                if (file_exists($fullPath)) {
                    include $fullPath;
                } else {
                    echo '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">';
                    echo 'Erro: View não encontrada - ' . e($viewFile);
                    echo '</div>';
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html>
