<?php
// Function to check if user has permission
function hasPermission($module, $action = 'view') {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    return \App\Services\PermissionService::hasPermission($userId, $module, $action);
}

// Function to check if user has any permission for a list of modules
function hasAnyPermission($modules) {
    foreach ($modules as $module) {
        if (hasPermission($module)) {
            return true;
        }
    }
    return false;
}

$menu = [
  [
    'label' => 'Operacionais', 
    'href' => '#', 
    'icon' => '🏭', 
    'category' => true,
    'modules' => ['toners_cadastro', 'toners_retornados', 'amostragens', 'garantias', 'controle_descartes'],
    'submenu' => [
      ['label' => 'Cadastro de Toners', 'href' => '/toners/cadastro', 'icon' => '🖨️', 'module' => 'toners_cadastro'],
      ['label' => 'Registro de Retornados', 'href' => '/toners/retornados', 'icon' => '📋', 'module' => 'toners_retornados'],
      ['label' => 'Amostragens', 'href' => '/toners/amostragens', 'icon' => '🧪', 'module' => 'amostragens'],
      ['label' => 'Garantias', 'href' => '/garantias', 'icon' => '🛡️', 'module' => 'garantias'],
      ['label' => 'Controle de Descartes', 'href' => '/controle-de-descartes', 'icon' => '♻️', 'module' => 'controle_descartes'],
    ]
  ],
  [
    'label' => 'Gestão da Qualidade', 
    'href' => '#', 
    'icon' => '📊', 
    'category' => true,
    'modules' => ['homologacoes', 'femea', 'pops_its', 'fluxogramas', 'solicitacao_melhorias', 'controle_rc'],
    'submenu' => [
      ['label' => 'Homologações', 'href' => '/homologacoes', 'icon' => '✅', 'module' => 'homologacoes'],
      ['label' => 'FEMEA', 'href' => '/femea', 'icon' => '📈', 'module' => 'femea'],
      ['label' => 'POPs e ITs', 'href' => '/pops-e-its', 'icon' => '📚', 'module' => 'pops_its'],
      ['label' => 'Fluxogramas', 'href' => '/fluxogramas', 'icon' => '🔀', 'module' => 'fluxogramas'],
      ['label' => 'Solicitação de Melhorias', 'href' => '/melhoria-continua/solicitacoes', 'icon' => '💡', 'module' => 'solicitacao_melhorias'],
      ['label' => 'Controle de RC', 'href' => '/controle-de-rc', 'icon' => '🗂️', 'module' => 'controle_rc'],
    ]
  ],
  [
    'label' => 'Registros', 
    'href' => '#', 
    'icon' => '📄', 
    'category' => true,
    'modules' => ['registros_filiais', 'registros_departamentos', 'registros_fornecedores', 'registros_parametros'],
    'submenu' => [
      ['label' => 'Filiais', 'href' => '/registros/filiais', 'icon' => '🏢', 'module' => 'registros_filiais'],
      ['label' => 'Departamentos', 'href' => '/registros/departamentos', 'icon' => '🏛️', 'module' => 'registros_departamentos'],
      ['label' => 'Fornecedores', 'href' => '/registros/fornecedores', 'icon' => '🏭', 'module' => 'registros_fornecedores'],
      ['label' => 'Parâmetros de Retornados', 'href' => '/registros/parametros', 'icon' => '📊', 'module' => 'registros_parametros'],
    ]
  ],
  [
    'label' => 'Administrativo', 
    'href' => '#', 
    'icon' => '⚙️', 
    'category' => true,
    'modules' => ['configuracoes_gerais', 'admin_usuarios', 'admin_perfis', 'admin_convites', 'admin_painel'],
    'submenu' => [
      ['label' => 'Configurações Gerais', 'href' => '/configuracoes', 'icon' => '⚙️', 'module' => 'configuracoes_gerais'],
      ['label' => 'Gerenciar Usuários', 'href' => '/admin/users', 'icon' => '👥', 'module' => 'admin_usuarios'],
      ['label' => 'Gerenciar Perfis', 'href' => '/admin/profiles', 'icon' => '🎭', 'module' => 'admin_perfis'],
      ['label' => 'Solicitações de Acesso', 'href' => '/admin/invitations', 'icon' => '📧', 'module' => 'admin_convites'],
      ['label' => 'Painel Admin', 'href' => '/admin', 'icon' => '🔧', 'module' => 'admin_painel'],
    ]
  ],
];
$current = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';
?>
<aside class="hidden lg:flex lg:w-72 flex-col bg-slate-800 border-r border-slate-700">
  <div class="h-16 flex items-center px-6 border-b border-slate-700">
    <div>
      <div class="text-lg font-semibold text-white">Sistema SGQ</div>
      <div class="text-xs text-slate-400">Gestão da Qualidade</div>
    </div>
  </div>
  <nav class="flex-1 overflow-y-auto py-4">
    <ul class="space-y-1 px-3">
      <!-- Dashboard só visível se tiver permissão -->
      <?php if (hasPermission('dashboard')): ?>
      <li>
        <a href="/" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-700 <?php echo $current==='/'?'bg-blue-600 text-white shadow-lg':'text-slate-300 hover:text-white'; ?>">
          <span class="text-lg">🏠</span>
          <span>Dashboard</span>
        </a>
      </li>
      <?php endif; ?>
      
      <?php foreach ($menu as $item):
        $active = rtrim($item['href'], '/') === $current;
        $hasSubmenu = isset($item['submenu']);
        $submenuActive = false;
        
        // Verificar se o usuário tem permissão para este item
        $hasPermissionForItem = false;
        if ($hasSubmenu) {
          // Para submenus, verificar se tem permissão para pelo menos um submenu
          $visibleSubmenus = [];
          foreach ($item['submenu'] as $sub) {
            if (hasPermission($sub['module'])) {
              $visibleSubmenus[] = $sub;
              if (rtrim($sub['href'], '/') === $current) {
                $submenuActive = true;
              }
            }
          }
          $hasPermissionForItem = !empty($visibleSubmenus);
        } else {
          // Para itens simples, verificar permissão direta
          $hasPermissionForItem = hasPermission($item['module']);
        }
        
        // Só mostrar o item se o usuário tiver permissão
        if (!$hasPermissionForItem) continue;
      ?>
        <li>
          <?php if ($hasSubmenu): ?>
            <?php $isCategory = isset($item['category']) && $item['category']; ?>
            <div class="submenu-container">
              <button onclick="toggleSubmenu(this)" class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-700 <?php echo $submenuActive?'bg-blue-600 text-white shadow-lg':($isCategory ? 'text-slate-200 hover:text-white bg-slate-700/50' : 'text-slate-300 hover:text-white'); ?>">
                <div class="flex items-center gap-3">
                  <span class="text-lg"><?= e($item['icon']) ?></span>
                  <span class="<?php echo $isCategory ? 'font-semibold' : ''; ?>"><?= e($item['label']) ?></span>
                </div>
                <span class="submenu-arrow transition-transform duration-200 text-slate-400">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </span>
              </button>
              <ul class="submenu ml-6 mt-2 space-y-1 hidden">
                <?php foreach ($item['submenu'] as $sub):
                  // Só mostrar submenu se o usuário tiver permissão
                  if (!hasPermission($sub['module'])) continue;
                  $subActive = rtrim($sub['href'], '/') === $current;
                ?>
                  <li>
                    <a href="<?= e($sub['href']) ?>" class="page-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-700 <?php echo $subActive?'bg-blue-500 text-white shadow-md':'text-slate-400 hover:text-white'; ?>">
                      <span class="text-base"><?= e($sub['icon']) ?></span>
                      <span><?= e($sub['label']) ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php else: ?>
            <a href="<?= e($item['href']) ?>" class="page-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-700 <?php echo $active?'bg-blue-600 text-white shadow-lg':'text-slate-300 hover:text-white'; ?>">
              <span class="text-lg"><?= e($item['icon']) ?></span>
              <span><?= e($item['label']) ?></span>
            </a>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>
  
  <!-- User Menu at bottom -->
  <div class="p-3 border-t border-slate-700">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <a href="/profile" class="flex items-center gap-3 hover:bg-slate-700 rounded-lg p-1 transition-colors">
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
            <span class="text-white text-sm font-medium">
              <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </span>
          </div>
          <div class="text-sm">
            <div class="text-white font-medium"><?= $_SESSION['user_name'] ?? 'Usuário' ?></div>
            <div class="text-slate-400 text-xs"><?= $_SESSION['user_role'] ?? 'user' ?></div>
          </div>
        </a>
      </div>
      <div class="flex items-center gap-1">
        <a href="/profile" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-colors" title="Perfil">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
        </a>
        <a href="/logout" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-colors" title="Logout">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
        </a>
      </div>
    </div>
  </div>
</aside>

<!-- Mobile sidebar -->
<div class="lg:hidden">
  <div class="h-14 flex items-center justify-between px-4 bg-slate-800 border-b border-slate-700">
    <button id="menuBtn" class="p-2 rounded-md text-white hover:bg-slate-700">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
    <div class="text-center">
      <div class="text-sm font-semibold text-white">Sistema SGQ</div>
    </div>
    <span></span>
  </div>
  <div id="mobileMenu" class="hidden fixed inset-0 bg-black/50 z-40"></div>
  <div id="mobileDrawer" class="hidden fixed inset-y-0 left-0 w-72 bg-slate-800 z-50 shadow-lg">
    <div class="h-14 flex items-center px-4 border-b border-slate-700">
      <div class="text-white font-semibold">Menu</div>
    </div>
    <nav class="p-3 space-y-1">
      <?php if (hasPermission('dashboard')): ?>
      <a href="/" class="page-link block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition-all duration-200">Dashboard</a>
      <?php endif; ?>
      
      <?php foreach ($menu as $item): 
        // Verificar permissões para mobile também
        $hasSubmenu = isset($item['submenu']);
        $hasPermissionForItem = false;
        
        if ($hasSubmenu) {
          // Para submenus, verificar se tem permissão para pelo menos um submenu
          foreach ($item['submenu'] as $sub) {
            if (hasPermission($sub['module'])) {
              $hasPermissionForItem = true;
              break;
            }
          }
        } else {
          // Para itens simples, verificar permissão direta
          $hasPermissionForItem = hasPermission($item['module']);
        }
        
        // Só mostrar se tiver permissão
        if (!$hasPermissionForItem) continue;
      ?>
        <?php if ($hasSubmenu): ?>
          <?php $isCategory = isset($item['category']) && $item['category']; ?>
          <?php if ($isCategory): ?>
            <!-- Mostrar categoria como separador no mobile -->
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider px-3 py-2 mt-4 first:mt-0">
              <?= e($item['label']) ?>
            </div>
          <?php endif; ?>
          <!-- Para mobile, mostrar todos os subitens que o usuário tem permissão -->
          <?php foreach ($item['submenu'] as $sub): ?>
            <?php if (hasPermission($sub['module'])): ?>
              <a href="<?= e($sub['href']) ?>" class="page-link block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition-all duration-200 ml-2">
                <span class="text-sm"><?= e($sub['icon']) ?></span> <?= e($sub['label']) ?>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <a href="<?= e($item['href']) ?>" class="page-link block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition-all duration-200"><?= e($item['label']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
  </div>
  <script>
    const btn = document.getElementById('menuBtn');
    const overlay = document.getElementById('mobileMenu');
    const drawer = document.getElementById('mobileDrawer');
    function toggle(){ overlay.classList.toggle('hidden'); drawer.classList.toggle('hidden'); }
    btn?.addEventListener('click', toggle);
    overlay?.addEventListener('click', toggle);
    
    // Submenu toggle function
    function toggleSubmenu(button) {
      const submenu = button.parentElement.querySelector('.submenu');
      const arrow = button.querySelector('.submenu-arrow');
      submenu.classList.toggle('hidden');
      arrow.style.transform = submenu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    }
    
    // Auto-expand active submenu
    document.addEventListener('DOMContentLoaded', function() {
      const activeSubmenuItem = document.querySelector('.submenu a.bg-primary-100');
      if (activeSubmenuItem) {
        const submenu = activeSubmenuItem.closest('.submenu');
        const button = submenu.parentElement.querySelector('button');
        const arrow = button.querySelector('.submenu-arrow');
        submenu.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
      }
    });
  </script>
</div>
