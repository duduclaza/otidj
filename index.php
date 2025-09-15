<?php
// Carrega o sistema de bootstrap com detecção automática de ambiente
require_once __DIR__ . '/bootstrap.php';

// Define menu items
$menuItems = [
    'controle-toners' => 'Controle de Toners',
    'homologacoes' => 'Homologações',
    'amostragens' => 'Amostragens',
    'garantias' => 'Garantias',
    'controle-descartes' => 'Controle de Descartes',
    'femea' => 'FEMEA',
    'pops-its' => 'POPs e ITs',
    'fluxogramas' => 'Fluxogramas',
    'melhoria-continua' => 'Melhoria Continua',
    'controle-rc' => 'Controle de RC',
    'registros-gerais' => 'Registros Gerais'
];

// Get current page
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$pageTitle = isset($menuItems[$currentPage]) ? $menuItems[$currentPage] : 'Dashboard';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGQ PRO - <?php echo $pageTitle; ?></title>
    
    <!-- Meta tags para controle de cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4e73df',
                        secondary: '#858796',
                        success: '#1cc88a',
                        info: '#36b9cc',
                        warning: '#f6c23e',
                        danger: '#e74a3b',
                        light: '#f8f9fc',
                        dark: '#5a5c69'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="assets/css/style.css<?= assetVersion('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-cogs"></i>
                    <h2>SGQ PRO</h2>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="sidebar-menu">
                <ul class="menu-list">
                    <li class="menu-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <a href="index.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <?php foreach ($menuItems as $key => $label): ?>
                    <li class="menu-item <?php echo $currentPage === $key ? 'active' : ''; ?>">
                        <a href="?page=<?php echo $key; ?>">
                            <i class="fas fa-<?php echo getMenuIcon($key); ?>"></i>
                            <span><?php echo $label; ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name">Usuário</span>
                        <span class="user-role">Administrador</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1><?php echo $pageTitle; ?></h1>
                    <p class="header-subtitle">Sistema de Gestão da Qualidade</p>
                </div>
                <div class="header-right">
                    <div class="header-actions">
                        <button class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Novo
                        </button>
                        <div class="notifications">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="content">
                <?php
                // Include the appropriate page content
                $pageFile = "pages/{$currentPage}.php";
                if (file_exists($pageFile)) {
                    include $pageFile;
                } else {
                    include "pages/dashboard.php";
                }
                ?>
            </div>
        </main>
    </div>

    <script src="<?php echo assetVersion('assets/js/script.js'); ?>"></script>
</body>
</html>

<?php
function getMenuIcon($key) {
    $icons = [
        'controle-toners' => 'print',
        'homologacoes' => 'check-circle',
        'amostragens' => 'vial',
        'garantias' => 'shield-alt',
        'controle-descartes' => 'trash-alt',
        'femea' => 'exclamation-triangle',
        'pops-its' => 'file-alt',
        'fluxogramas' => 'project-diagram',
        'melhoria-continua' => 'chart-line',
        'controle-rc' => 'clipboard-check',
        'registros-gerais' => 'folder-open'
    ];
    
    return isset($icons[$key]) ? $icons[$key] : 'circle';
}
?>
