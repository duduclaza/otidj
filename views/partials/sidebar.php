<?php
// Function to check if user has permission
function hasPermission($module, $action = 'view') {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Admin e Super Admin sempre tem acesso
    $userRole = $_SESSION['user_role'] ?? '';
    if (in_array($userRole, ['admin', 'super_admin'])) {
        return true;
    }
    
    // Para outros usuários, verificar permissão no banco via PermissionService
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
    'label' => 'Cadastros', 
    'href' => '#', 
    'icon' => '📝', 
    'category' => true,
    'modules' => ['toners_cadastro', 'cadastro_maquinas', 'cadastro_pecas', 'registros_fornecedores', 'cadastro_contratos', 'cadastro_clientes'],
    'submenu' => [
      ['label' => 'Cadastro de Toners', 'href' => '/toners/cadastro', 'icon' => '💧', 'module' => 'toners_cadastro'],
      ['label' => 'Cadastro de Máquinas', 'href' => '/cadastro-maquinas', 'icon' => '🖨️', 'module' => 'cadastro_maquinas'],
      ['label' => 'Cadastro de Peças', 'href' => '/cadastro-pecas', 'icon' => '🔧', 'module' => 'cadastro_pecas'],
      ['label' => 'Cadastro de Fornecedores', 'href' => '/registros/fornecedores', 'icon' => '🏭', 'module' => 'registros_fornecedores'],
      ['label' => 'Cadastro de Contratos', 'href' => '/cadastros/contratos', 'icon' => '📄', 'module' => 'cadastro_contratos'],
      ['label' => 'Cadastro de Clientes', 'href' => '/cadastros/clientes', 'icon' => '👥', 'module' => 'cadastro_clientes', 'admin_only' => true],
    ]
  ],
  [
    'label' => 'Gestão da Qualidade', 
    'href' => '#', 
    'icon' => '📋', 
    'category' => true,
    'modules' => ['toners_retornados', 'amostragens_2', 'garantias', 'controle_descartes', 'homologacoes', 'certificados', 'fmea', 'pops_its_visualizacao', 'pops_its_cadastro_titulos', 'pops_its_meus_registros', 'pops_its_pendente_aprovacao', 'fluxogramas', 'auditorias', 'nao_conformidades', 'melhoria_continua', 'melhoria_continua_2', 'controle_rc', 'nps'],
    'submenu' => [
      ['label' => 'Registro de Retornados', 'href' => '/toners/retornados', 'icon' => '📋', 'module' => 'toners_retornados'],
      ['label' => 'Amostragens 2.0', 'href' => '/amostragens-2', 'icon' => '🔬', 'module' => 'amostragens_2'],
      ['label' => 'Controle de Descartes', 'href' => '/controle-descartes', 'icon' => '♻️', 'module' => 'controle_descartes'],
      // Itens originais de Gestão da Qualidade
      ['label' => 'Homologações', 'href' => '/homologacoes', 'icon' => '✅', 'module' => 'homologacoes'],
      ['label' => 'Certificados', 'href' => '/certificados', 'icon' => '📜', 'module' => 'certificados'],
      ['label' => 'FMEA', 'href' => '/fmea', 'icon' => '📈', 'module' => 'fmea'],
      ['label' => 'POPs e ITs', 'href' => '/pops-e-its', 'icon' => '📚', 'module' => 'pops_its_visualizacao'],
      ['label' => 'Fluxogramas', 'href' => '/fluxogramas', 'icon' => '🔀', 'module' => 'fluxogramas'],
      ['label' => 'Auditorias', 'href' => '/auditorias', 'icon' => '🔍', 'module' => 'auditorias', 'admin_only' => true],
      ['label' => 'Não Conformidades', 'href' => '/nao-conformidades', 'icon' => '⚠️', 'module' => 'nao_conformidades'],
      ['label' => 'Melhoria Contínua', 'href' => '/melhoria-continua-2', 'icon' => '🚀', 'module' => 'melhoria_continua_2'],
      ['label' => 'Controle de RC', 'href' => '/controle-de-rc', 'icon' => '🗂️', 'module' => 'controle_rc'],
      // Garantia com submenu
      [
        'label' => 'Garantia',
        'href' => '#',
        'icon' => '🛡️',
        'module' => 'garantias',
        'has_submenu' => true,
        'submenu' => [
          ['label' => 'Registro de Garantias', 'href' => '/garantias', 'icon' => '📝', 'module' => 'garantias'],
          ['label' => 'Requisição de Garantias', 'href' => '/garantias/requisicao', 'icon' => '📋', 'module' => 'garantias'],
          ['label' => 'Requisições Pendentes', 'href' => '/garantias/pendentes', 'icon' => '⏳', 'module' => 'garantias'],
        ]
      ],
      ['label' => 'Formulários Online', 'href' => '/nps', 'icon' => '📊', 'module' => 'nps'],
    ]
  ],
  [
    'label' => 'Administrativo', 
    'href' => '#', 
    'icon' => '⚙️', 
    'category' => true,
    'modules' => ['admin_usuarios', 'admin_perfis', 'admin_convites', 'admin_painel', 'api_powerbi', 'registros_filiais', 'registros_departamentos', 'registros_fornecedores', 'registros_parametros'],
    'submenu' => [
      ['label' => 'Gerenciar Usuários', 'href' => '/admin/users', 'icon' => '👥', 'module' => 'admin_usuarios'],
      ['label' => 'Gerenciar Perfis', 'href' => '/admin/profiles', 'icon' => '🎭', 'module' => 'admin_perfis'],
      ['label' => 'Solicitações de Acesso', 'href' => '/admin/access-requests', 'icon' => '📧', 'module' => 'admin_convites'],
      ['label' => 'APIs para Power BI', 'href' => '/api/powerbi', 'icon' => '📊', 'module' => 'api_powerbi', 'badge' => 'BETA'],
      ['label' => 'Filiais', 'href' => '/registros/filiais', 'icon' => '🏢', 'module' => 'registros_filiais'],
      ['label' => 'Departamentos', 'href' => '/registros/departamentos', 'icon' => '🏛️', 'module' => 'registros_departamentos'],
      ['label' => 'Fornecedores', 'href' => '/registros/fornecedores', 'icon' => '🏭', 'module' => 'registros_fornecedores'],
      ['label' => 'Parâmetros de Retornados', 'href' => '/registros/parametros', 'icon' => '📊', 'module' => 'registros_parametros'],
    ]
  ],
  [
    'label' => 'Módulos Especiais', 
    'href' => '#', 
    'icon' => '🚀', 
    'category' => true,
    'admin_only' => true,
    'submenu' => [
      // Gestão de Implantação
      [
        'label' => 'Gestão de Implantação',
        'href' => '#',
        'icon' => '🚀',
        'admin_only' => true,
        'has_submenu' => true,
        'submenu' => [
          ['label' => 'DPO', 'href' => '/implantacao/dpo', 'icon' => '📊', 'admin_only' => true],
          ['label' => 'Ordem de Serviços de Implantação', 'href' => '/implantacao/ordem-servicos', 'icon' => '📋', 'admin_only' => true],
          ['label' => 'Fluxo de Implantação', 'href' => '/implantacao/fluxo', 'icon' => '🔄', 'admin_only' => true],
          ['label' => 'Relatórios', 'href' => '/implantacao/relatorios', 'icon' => '📈', 'admin_only' => true],
        ]
      ],
      // CRM
      [
        'label' => 'CRM',
        'href' => '#',
        'icon' => '💼',
        'admin_only' => true,
        'has_submenu' => true,
        'submenu' => [
          ['label' => 'Prospecção', 'href' => '/crm/prospeccao', 'icon' => '🎯', 'admin_only' => true],
          ['label' => 'Vendas', 'href' => '/crm/vendas', 'icon' => '💰', 'admin_only' => true],
          ['label' => 'Relacionamento', 'href' => '/crm/relacionamento', 'icon' => '🤝', 'admin_only' => true],
          ['label' => 'Marketing', 'href' => '/crm/marketing', 'icon' => '📢', 'admin_only' => true],
          ['label' => 'Relatórios', 'href' => '/crm/relatorios', 'icon' => '📈', 'admin_only' => true],
          ['label' => 'Dashboards', 'href' => '/crm/dashboards', 'icon' => '📊', 'admin_only' => true],
        ]
      ],
      // Logística
      [
        'label' => 'Logística',
        'href' => '#',
        'icon' => '📦',
        'admin_only' => true,
        'has_submenu' => true,
        'badge' => 'R$ 600/mês',
        'submenu' => [
          ['label' => 'Entrada de Estoque', 'href' => '/logistica/entrada-estoque', 'icon' => '📥', 'admin_only' => true],
          ['label' => 'Entrada de Almoxarifados', 'href' => '/logistica/entrada-almoxarifados', 'icon' => '🏭', 'admin_only' => true],
          ['label' => 'Inventários', 'href' => '/logistica/inventarios', 'icon' => '📋', 'admin_only' => true],
          ['label' => 'Consulta de Estoque', 'href' => '/logistica/consulta-estoque', 'icon' => '🔍', 'admin_only' => true],
          ['label' => 'Consulta de Almoxarifado', 'href' => '/logistica/consulta-almoxarifado', 'icon' => '🏢', 'admin_only' => true],
          ['label' => 'Transferências Internas', 'href' => '/logistica/transferencias-internas', 'icon' => '🔄', 'admin_only' => true],
          ['label' => 'Transferências Externas', 'href' => '/logistica/transferencias-externas', 'icon' => '🚚', 'admin_only' => true],
          ['label' => 'Estoque Técnico', 'href' => '/logistica/estoque-tecnico', 'icon' => '🔧', 'admin_only' => true],
        ]
      ],
    ]
  ],
];
$current = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';
?>
<aside id="sidebar" class="hidden lg:flex lg:w-72 flex-col bg-slate-800 border-r border-slate-700">
  <div class="h-16 flex items-center justify-center px-6 border-b border-slate-700">
    <div class="text-center">
      <div class="text-lg font-semibold text-white">OTI</div>
      <div class="text-xs text-slate-400">Organização Tecnológica Integrada</div>
    </div>
  </div>
  <nav class="flex-1 overflow-y-auto py-4 sidebar-scroll">
    <ul class="space-y-1 px-3">
      <!-- Início - acessível a todos os usuários autenticados -->
      <li>
        <a href="/inicio" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-700 <?php echo $current==='/inicio'?'bg-blue-600 text-white shadow-lg':'text-slate-300 hover:text-white'; ?>">
          <span class="text-lg">🏠</span>
          <span>Início</span>
        </a>
      </li>
      
      <!-- Dashboard só visível se tiver permissão -->
      <?php if (hasPermission('dashboard')): ?>
      <li>
        <a href="/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-700 <?php echo $current==='/dashboard'?'bg-blue-600 text-white shadow-lg':'text-slate-300 hover:text-white'; ?>">
          <span class="text-lg">📊</span>
          <span>Dashboard</span>
        </a>
      </li>
      <?php endif; ?>
      
      <!-- Suporte movido para o header (ícone ao lado do sininho) -->
      <!-- Removido do sidebar para ficar mais acessível no topo da página -->
      <?php /* if (isAdmin()): ?>
      <li>
        <a href="/suporte" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-700 <?php echo $current==='/suporte'?'bg-blue-600 text-white shadow-lg':'text-slate-300 hover:text-white'; ?>">
          <span class="text-lg">🆘</span>
          <span>Suporte</span>
        </a>
      </li>
      <?php endif; */ ?>
      
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
            // Verificar se é admin_only e se o usuário é admin
            if (isset($sub['admin_only']) && $sub['admin_only']) {
              $isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin', 'superadmin']);
              if (!$isAdmin) continue;
              // Para admin_only, adicionar direto sem verificar banco
              $visibleSubmenus[] = $sub;
              if (rtrim($sub['href'], '/') === $current) {
                $submenuActive = true;
              }
            } else {
              // Para outros itens, verificar permissão no banco (se tiver module)
              if (isset($sub['module']) && hasPermission($sub['module'])) {
                $visibleSubmenus[] = $sub;
                if (rtrim($sub['href'], '/') === $current) {
                  $submenuActive = true;
                }
              }
              // Se não tem module mas tem has_submenu, pode ser um submenu aninhado (como Garantia ou Loja)
              elseif (isset($sub['has_submenu']) && $sub['has_submenu']) {
                // Verificar se tem pelo menos um submenu interno visível
                if (isset($sub['submenu']) && is_array($sub['submenu'])) {
                  foreach ($sub['submenu'] as $nestedSub) {
                    // Verificar se é admin_only
                    if (isset($nestedSub['admin_only']) && $nestedSub['admin_only']) {
                      $isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin', 'superadmin']);
                      if ($isAdmin) {
                        $visibleSubmenus[] = $sub;
                        break; // Encontrou pelo menos um, adicionar o pai
                      }
                    }
                    // Ou verificar por module
                    elseif (isset($nestedSub['module']) && hasPermission($nestedSub['module'])) {
                      $visibleSubmenus[] = $sub;
                      break; // Encontrou pelo menos um, adicionar o pai
                    }
                  }
                }
              }
            }
          }
          $hasPermissionForItem = !empty($visibleSubmenus);
        } else {
          // Para itens simples, verificar permissão direta
          $hasPermissionForItem = isset($item['module']) && hasPermission($item['module']);
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
                  // Verificar se é admin_only
                  if (isset($sub['admin_only']) && $sub['admin_only']) {
                    $isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin', 'superadmin']);
                    if (!$isAdmin) continue;
                    // Para admin_only, não precisa verificar permissão no banco
                    $subActive = rtrim($sub['href'], '/') === $current;
                  } else {
                    // Só mostrar submenu se o usuário tiver permissão
                    if (!isset($sub['module']) || !hasPermission($sub['module'])) continue;
                    $subActive = rtrim($sub['href'], '/') === $current;
                  }
                  
                  // Verificar se este submenu tem seus próprios submenus
                  $hasNestedSubmenu = isset($sub['has_submenu']) && $sub['has_submenu'] && isset($sub['submenu']);
                ?>
                  <li>
                    <?php if ($hasNestedSubmenu): ?>
                      <!-- Submenu aninhado -->
                      <div class="submenu-container">
                        <button onclick="toggleSubmenu(this)" class="flex items-center justify-between w-full px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-700 text-slate-400 hover:text-white">
                          <div class="flex items-center gap-3">
                            <span class="text-base"><?= e($sub['icon']) ?></span>
                            <span><?= e($sub['label']) ?></span>
                          </div>
                          <span class="submenu-arrow transition-transform duration-200 text-slate-400">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                          </span>
                        </button>
                        <ul class="submenu ml-6 mt-1 space-y-1 hidden">
                          <?php foreach ($sub['submenu'] as $nestedSub):
                            // Verificar se é admin_only
                            if (isset($nestedSub['admin_only']) && $nestedSub['admin_only']) {
                              $isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin', 'superadmin']);
                              if (!$isAdmin) continue;
                            } elseif (isset($nestedSub['module']) && !hasPermission($nestedSub['module'])) {
                              continue;
                            }
                            $nestedActive = rtrim($nestedSub['href'], '/') === $current;
                          ?>
                            <li>
                              <a href="<?= e($nestedSub['href']) ?>" class="page-link flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition-all duration-200 hover:bg-slate-700 <?php echo $nestedActive?'bg-blue-500 text-white shadow-md':'text-slate-500 hover:text-white'; ?>">
                                <span><?= e($nestedSub['icon']) ?></span>
                                <span><?= e($nestedSub['label']) ?></span>
                              </a>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      </div>
                    <?php else: ?>
                      <!-- Link normal do submenu -->
                      <a href="<?= e($sub['href']) ?>" class="page-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-slate-700 <?php echo $subActive?'bg-blue-500 text-white shadow-md':'text-slate-400 hover:text-white'; ?> <?php echo isset($sub['beta']) && $sub['beta'] ? 'beta-menu' : ''; ?>">
                        <span class="text-base"><?= e($sub['icon']) ?></span>
                        <span class="flex items-center gap-2">
                          <?= e($sub['label']) ?>
                          <?php if (isset($sub['beta']) && $sub['beta']): ?>
                            <span class="beta-badge">BETA</span>
                          <?php elseif (isset($sub['badge'])): ?>
                            <span class="px-1.5 py-0.5 bg-yellow-500 text-yellow-900 text-xs font-bold rounded"><?= e($sub['badge']) ?></span>
                          <?php endif; ?>
                        </span>
                      </a>
                    <?php endif; ?>
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
  <div class="p-4 border-t border-slate-700">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <a href="/profile" class="flex items-center gap-3 hover:bg-slate-700 rounded-lg p-1 transition-colors">
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center overflow-hidden">
            <img id="sidebarUserPhoto" src="" alt="Foto de Perfil" class="w-full h-full object-cover hidden">
            <span id="sidebarUserInitial" class="text-white text-sm font-medium">
              <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </span>
          </div>
          <div class="text-sm">
            <div class="text-white font-medium"><?= $_SESSION['user_name'] ?? 'Usuário' ?></div>
            <div class="text-slate-400 text-xs"><?= $_SESSION['user_role'] ?? 'user' ?></div>
          </div>
        </a>
      </div>
      <div class="flex items-center gap-2">
        <!-- DEBUG: Valor da sessão -->
        <?php 
        $notifStatus = isset($_SESSION['notificacoes_ativadas']) ? $_SESSION['notificacoes_ativadas'] : 'não definido';
        // error_log("DEBUG SIDEBAR - notificacoes_ativadas: " . var_export($notifStatus, true));
        ?>
        
        <!-- Sininho de Notificações - Visível apenas se ativado -->
        <?php if (isset($_SESSION['notificacoes_ativadas']) && $_SESSION['notificacoes_ativadas']): ?>
        <div class="relative">
          <button id="notificationBtn" class="text-slate-400 hover:text-white p-2 rounded-lg hover:bg-slate-700 transition-colors relative" title="Notificações">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <!-- Contador de notificações -->
            <span id="notificationCount" class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">0</span>
          </button>
          
          <!-- Dropdown de Notificações -->
          <div id="notificationDropdown" class="hidden absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
            <!-- Setinha apontando para baixo (para o botão) -->
            <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-4 h-4 bg-white border-r border-b border-gray-200 transform rotate-45 -mt-2"></div>
            <div class="p-4 border-b border-gray-200">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-sm font-semibold text-gray-900">Notificações</h3>
                <div class="flex space-x-2">
                  <button id="markAllReadBtn" class="text-xs text-blue-600 hover:text-blue-800">Marcar como lidas</button>
                  <button id="clearHistoryBtn" class="text-xs text-red-600 hover:text-red-800">Limpar histórico</button>
                </div>
              </div>
              <p class="text-xs text-gray-500">Últimas 30 dias • Clique para navegar</p>
            </div>
            <div id="notificationsList" class="max-h-64 overflow-y-auto">
              <!-- Notificações serão carregadas aqui -->
            </div>
            <div class="p-3 border-t border-gray-200 text-center">
              <span class="text-xs text-gray-500">Atualizando automaticamente...</span>
            </div>
          </div>
        </div>
        <?php endif; ?>
        
        <a href="/logout" class="text-slate-400 hover:text-white p-2 rounded-lg hover:bg-slate-700 transition-colors" title="Sair">
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
      <div class="text-sm font-semibold text-white">OTI</div>
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
      <a href="/dashboard" class="page-link block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition-all duration-200">Dashboard</a>
      <?php endif; ?>
      
      <?php foreach ($menu as $item): 
        // Verificar permissões para mobile também
        $hasSubmenu = isset($item['submenu']);
        $hasPermissionForItem = false;
        
        if ($hasSubmenu) {
          // Para submenus, verificar se tem permissão para pelo menos um submenu
          foreach ($item['submenu'] as $sub) {
            // Verificar se é admin_only
            if (isset($sub['admin_only']) && $sub['admin_only']) {
              $isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin', 'superadmin']);
              if ($isAdmin) {
                $hasPermissionForItem = true;
                break;
              }
            }
            // Verificar se tem a chave 'module' antes de usar
            elseif (isset($sub['module']) && hasPermission($sub['module'])) {
              $hasPermissionForItem = true;
              break;
            }
            // Se não tem 'module', pode ser um submenu aninhado - verificar recursivamente
            elseif (isset($sub['submenu']) && is_array($sub['submenu'])) {
              foreach ($sub['submenu'] as $nestedSub) {
                // Verificar se é admin_only
                if (isset($nestedSub['admin_only']) && $nestedSub['admin_only']) {
                  $isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin', 'superadmin']);
                  if ($isAdmin) {
                    $hasPermissionForItem = true;
                    break 2;
                  }
                }
                // Ou verificar por module
                elseif (isset($nestedSub['module']) && hasPermission($nestedSub['module'])) {
                  $hasPermissionForItem = true;
                  break 2; // Sair dos dois loops
                }
              }
            }
          }
        } else {
          // Para itens simples, verificar permissão direta
          $hasPermissionForItem = isset($item['module']) && hasPermission($item['module']);
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
            <?php 
              // Verificar se é admin_only
              if (isset($sub['admin_only']) && $sub['admin_only']) {
                $isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin', 'superadmin']);
                if (!$isAdmin) continue;
                // Admin pode ver
              } elseif (isset($sub['module'])) {
                // Tem module, verificar permissão
                if (!hasPermission($sub['module'])) continue;
              } else {
                // Sem module e sem admin_only, pode ser submenu aninhado - mostrar se tem has_submenu
                if (!isset($sub['has_submenu']) || !$sub['has_submenu']) continue;
              }
            ?>
            <a href="<?= e($sub['href']) ?>" class="page-link block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition-all duration-200 ml-2">
              <span class="text-sm"><?= e($sub['icon']) ?></span> <?= e($sub['label']) ?>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <a href="<?= e($item['href']) ?>" class="page-link block px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition-all duration-200"><?= e($item['label']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
  </div>
  
  <style>
    /* Efeito brilhante para menu beta */
    .beta-menu {
      position: relative;
      overflow: hidden;
    }
    
    .beta-menu::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      animation: shine 2s infinite;
      z-index: 1;
    }
    
    .beta-badge {
      background: linear-gradient(45deg, #ff6b6b, #feca57);
      color: white;
      font-size: 0.6rem;
      font-weight: bold;
      padding: 1px 4px;
      border-radius: 3px;
      text-shadow: 0 1px 2px rgba(0,0,0,0.3);
      animation: pulse 2s infinite;
      position: relative;
      z-index: 2;
    }
    
    @keyframes shine {
      0% { left: -100%; }
      50% { left: 100%; }
      100% { left: 100%; }
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    
    /* Efeito adicional no hover */
    .beta-menu:hover::before {
      animation: shine 0.5s;
    }
    
    .beta-menu:hover .beta-badge {
      animation: pulse 0.5s;
    }
    
    /* Ocultar scrollbar da sidebar */
    .sidebar-scroll {
      scrollbar-width: none; /* Firefox */
      -ms-overflow-style: none; /* Internet Explorer 10+ */
    }
    
    .sidebar-scroll::-webkit-scrollbar {
      display: none; /* WebKit */
    }
    
    /* Ocultar scrollbar dos submenus */
    .submenu {
      scrollbar-width: none; /* Firefox */
      -ms-overflow-style: none; /* Internet Explorer 10+ */
    }
    
    .submenu::-webkit-scrollbar {
      display: none; /* WebKit */
    }
  </style>
  
  <script>
    // ===== DEFINIR FUNÇÃO GLOBAL PRIMEIRO - ANTES DE TUDO =====
    window.toggleSubmenu = function(button) {
      console.log('toggleSubmenu chamada!', button);
      const submenu = button.parentElement.querySelector('.submenu');
      const arrow = button.querySelector('.submenu-arrow');
      if (submenu && arrow) {
        submenu.classList.toggle('hidden');
        arrow.style.transform = submenu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
      } else {
        console.error('Submenu ou arrow não encontrado', {submenu, arrow});
      }
    }
    console.log('toggleSubmenu definida:', typeof window.toggleSubmenu);
    
    // Mobile menu
    const btn = document.getElementById('menuBtn');
    const overlay = document.getElementById('mobileMenu');
    const drawer = document.getElementById('mobileDrawer');
    function toggle(){ overlay.classList.toggle('hidden'); drawer.classList.toggle('hidden'); }
    btn?.addEventListener('click', toggle);
    overlay?.addEventListener('click', toggle);
    
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

    // ===== Sistema de Notificações =====
    let notificationInterval;
    let notificationSound;
    let soundInterval;
    let hasUnreadNotifications = false;

    // Criar som de notificação usando Web Audio API
    function createNotificationSound() {
      const audioContext = new (window.AudioContext || window.webkitAudioContext)();
      
      return function playNotificationSound() {
        // Criar oscilador para um som suave
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        // Configurar som (frequência e tipo)
        oscillator.frequency.setValueAtTime(800, audioContext.currentTime); // Nota aguda
        oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1); // Nota mais grave
        oscillator.type = 'sine'; // Som suave
        
        // Configurar volume (fade in/out)
        gainNode.gain.setValueAtTime(0, audioContext.currentTime);
        gainNode.gain.linearRampToValueAtTime(0.1, audioContext.currentTime + 0.05);
        gainNode.gain.linearRampToValueAtTime(0, audioContext.currentTime + 0.3);
        
        // Tocar som
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
      };
    }

    // Inicializar sistema de notificações
    document.addEventListener('DOMContentLoaded', function() {
      // Verificar se o sino de notificações está presente (usuário tem notificações ativadas)
      const notificationBtn = document.getElementById('notificationBtn');
      if (!notificationBtn) {
        console.log('Sistema de notificações desativado para este usuário');
        return; // Não inicializar se notificações estiverem desativadas
      }
      
      // Criar som de notificação (DESABILITADO - Som removido)
      // try {
      //   notificationSound = createNotificationSound();
      // } catch (e) {
      //   console.log('Web Audio API não suportada');
      // }
      
      loadNotifications();
      // Atualizar a cada 30 segundos
      notificationInterval = setInterval(loadNotifications, 30000);
      
      // Event listeners
      notificationBtn.addEventListener('click', toggleNotifications);
      document.getElementById('markAllReadBtn')?.addEventListener('click', markAllAsRead);
      document.getElementById('clearHistoryBtn')?.addEventListener('click', clearNotificationHistory);
      
      // Fechar dropdown ao clicar fora
      document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notificationDropdown');
        const btn = document.getElementById('notificationBtn');
        if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
          dropdown.classList.add('hidden');
        }
      });
    });

    // Carregar notificações
    async function loadNotifications() {
      try {
        const response = await fetch('/api/notifications');
        const data = await response.json();
        
        if (data.success) {
          updateNotificationCount(data.unread_count);
          updateNotificationsList(data.notifications);
        }
      } catch (error) {
        console.error('Erro ao carregar notificações:', error);
      }
    }

    // Atualizar contador
    function updateNotificationCount(count) {
      const counter = document.getElementById('notificationCount');
      if (!counter) return; // Sair se elemento não existir (notificações desativadas)
      
      const previousCount = hasUnreadNotifications;
      
      if (count > 0) {
        counter.textContent = count > 99 ? '99+' : count;
        counter.classList.remove('hidden');
        hasUnreadNotifications = true;
        
        // Se há novas notificações, iniciar som (DESABILITADO - Som removido)
        // if (!previousCount && notificationSound) {
        //   startNotificationSound();
        // }
      } else {
        counter.classList.add('hidden');
        hasUnreadNotifications = false;
        stopNotificationSound();
      }
    }

    // Iniciar som de notificação contínuo (DESABILITADO - Som removido)
    function startNotificationSound() {
      // SOM DESABILITADO - apenas notificação visual ativa
      // if (soundInterval) return; // Já está tocando
      // 
      // // Tocar som imediatamente
      // if (notificationSound) {
      //   notificationSound();
      // }
      // 
      // // Repetir a cada 5 segundos
      // soundInterval = setInterval(() => {
      //   if (hasUnreadNotifications && notificationSound) {
      //     notificationSound();
      //   } else {
      //     stopNotificationSound();
      //   }
      // }, 5000);
    }

    // Parar som de notificação
    function stopNotificationSound() {
      if (soundInterval) {
        clearInterval(soundInterval);
        soundInterval = null;
      }
    }

    // Atualizar lista de notificações
    function updateNotificationsList(notifications) {
      const list = document.getElementById('notificationsList');
      if (!list) return; // Sair se elemento não existir (notificações desativadas)
      
      if (notifications.length === 0) {
        list.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">Nenhuma notificação nos últimos 30 dias</div>';
        return;
      }
      
      // Separar notificações não lidas das lidas
      const unreadNotifications = notifications.filter(n => n.is_unread == 1);
      const readNotifications = notifications.filter(n => n.is_unread == 0);
      
      let html = '';
      
      // Seção de não lidas
      if (unreadNotifications.length > 0) {
        html += '<div class="bg-blue-50 px-3 py-2 border-b border-blue-200"><span class="text-xs font-medium text-blue-800">📢 NOVAS NOTIFICAÇÕES</span></div>';
        html += unreadNotifications.map(notification => createNotificationHTML(notification, false)).join('');
      }
      
      // Seção de lidas (histórico)
      if (readNotifications.length > 0) {
        html += '<div class="bg-gray-50 px-3 py-2 border-b border-gray-200"><span class="text-xs font-medium text-gray-600">📋 HISTÓRICO</span></div>';
        html += readNotifications.map(notification => createNotificationHTML(notification, true)).join('');
      }
      
      list.innerHTML = html;
    }
    
    // Criar HTML de uma notificação
    function createNotificationHTML(notification, isRead) {
      const clickAction = getNotificationClickAction(notification);
      const iconColor = getNotificationIconColor(notification.type);
      const bgClass = isRead ? 'bg-gray-50 opacity-75' : 'bg-white';
      const textClass = isRead ? 'text-gray-500' : 'text-gray-900';
      const actionText = getNotificationActionText(notification.type);
      
      return `
        <div class="p-3 border-b border-gray-100 hover:bg-gray-100 cursor-pointer ${bgClass}" onclick="${clickAction}">
          <div class="flex items-start gap-3">
            <div class="w-2 h-2 ${iconColor} rounded-full mt-2 flex-shrink-0 ${isRead ? 'opacity-50' : ''}"></div>
            <div class="flex-1 min-w-0">
              <h4 class="text-sm font-medium ${textClass} truncate">${notification.title}</h4>
              <p class="text-xs text-gray-600 mt-1">${notification.message}</p>
              <div class="flex justify-between items-center mt-2">
                <span class="text-xs text-gray-400">${formatDate(notification.created_at)}</span>
                ${isRead ? 
                  `<span class="text-xs text-gray-400">✓ Lida em ${formatDate(notification.read_at)}</span>` :
                  `<span class="text-xs text-blue-600 font-medium">${actionText}</span>`
                }
              </div>
            </div>
          </div>
        </div>
      `;
    }
    
    // Obter texto de ação baseado no tipo
    function getNotificationActionText(type) {
      const actions = {
        'access_request': 'Clique para revisar →',
        'success': 'Clique para ver →',
        'warning': 'Clique para verificar →',
        'error': 'Clique para resolver →',
        'info': 'Clique para ver →'
      };
      return actions[type] || 'Clique para ver →';
    }
    
    // Determinar ação do clique baseada no tipo de notificação
    function getNotificationClickAction(notification) {
      return `handleNotificationClick(${notification.id}, '${notification.type}', '${notification.related_type}', ${notification.related_id || 'null'})`;
    }
    
    // Determinar cor do ícone baseada no tipo
    function getNotificationIconColor(type) {
      const colors = {
        'access_request': 'bg-orange-500',
        'success': 'bg-green-500',
        'warning': 'bg-yellow-500',
        'error': 'bg-red-500',
        'info': 'bg-blue-500'
      };
      return colors[type] || 'bg-blue-500';
    }
    
    // Lidar com clique em notificação (navegação inteligente)
    async function handleNotificationClick(notificationId, type, relatedType, relatedId) {
      try {
        // Marcar como lida se não estiver lida
        await fetch(`/api/notifications/${notificationId}/read`, { method: 'POST' });
        
        // Fechar dropdown
        document.getElementById('notificationDropdown').classList.add('hidden');
        
        // Navegar para o módulo correto baseado no tipo
        const targetUrl = getNotificationTargetUrl(type, relatedType, relatedId);
        if (targetUrl) {
          window.location.href = targetUrl;
        }
        
        // Recarregar notificações
        loadNotifications();
      } catch (error) {
        console.error('Erro ao processar notificação:', error);
      }
    }
    
    // Determinar URL de destino baseada no tipo de notificação
    function getNotificationTargetUrl(type, relatedType, relatedId) {
      const navigationMap = {
        'access_request': '/admin/access-requests',
        'garantia': '/garantias',
        // 'amostragem': '/toners/amostragens', // DESATIVADO - usar amostragens_2
        'homologacao': '/homologacoes',
        'melhoria': '/melhoria-continua/solicitacoes',
        'melhoria_continua_2': '/melhoria-continua-2',
        'amostragens_2': '/amostragens-2',
        'financeiro': '/financeiro',
        'toner': '/toners/cadastro',
        'retornado': '/toners/retornados',
        'fmea': '/fmea',
        'pop': '/pops-e-its',
        'fluxograma': '/fluxogramas',
        'user': '/admin/users',
        'profile': '/admin/profiles',
        'controle_descartes': '/controle-descartes',
        'controle_rc': '/controle-rc',
        // Notificações de POPs e ITs
        'pops_its_pendente': '/pops-e-its?tab=pendentes',
        'pops_its_aprovado': '/pops-e-its?tab=registros',
        'pops_its_reprovado': '/pops-e-its?tab=registros',
        'pops_its_exclusao_pendente': '/pops-e-its?tab=pendentes',
        'pops_its_exclusao_aprovada': '/pops-e-its?tab=registros',
        'pops_its_exclusao_reprovada': '/pops-e-its?tab=registros'
      };
      
      // Primeiro, tentar mapear por related_type
      if (relatedType && navigationMap[relatedType]) {
        return navigationMap[relatedType];
      }
      
      // Depois, tentar mapear por type
      if (navigationMap[type]) {
        return navigationMap[type];
      }
      
      // Fallback para dashboard se for admin, senão página inicial
      return '/';
    }
    
    // Limpar histórico de notificações
    async function clearNotificationHistory() {
      if (!confirm('Tem certeza que deseja limpar o histórico de notificações? Esta ação não pode ser desfeita.')) {
        return;
      }
      
      try {
        const response = await fetch('/api/notifications/clear-history', { method: 'POST' });
        const data = await response.json();
        
        if (data.success) {
          // Mostrar mensagem de sucesso
          showNotificationToast(data.message, 'success');
          
          // Recarregar notificações
          loadNotifications();
        } else {
          showNotificationToast(data.message || 'Erro ao limpar histórico', 'error');
        }
      } catch (error) {
        console.error('Erro ao limpar histórico:', error);
        showNotificationToast('Erro de conexão ao limpar histórico', 'error');
      }
    }
    
    // Mostrar toast de notificação
    function showNotificationToast(message, type = 'info') {
      // Criar elemento de toast
      const toast = document.createElement('div');
      toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white text-sm max-w-sm transform transition-all duration-300 translate-x-full`;
      
      // Definir cor baseada no tipo
      const colors = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'warning': 'bg-yellow-500',
        'info': 'bg-blue-500'
      };
      toast.classList.add(colors[type] || colors.info);
      
      // Definir ícone baseado no tipo
      const icons = {
        'success': '✅',
        'error': '❌',
        'warning': '⚠️',
        'info': 'ℹ️'
      };
      
      toast.innerHTML = `
        <div class="flex items-center gap-2">
          <span>${icons[type] || icons.info}</span>
          <span>${message}</span>
        </div>
      `;
      
      // Adicionar ao DOM
      document.body.appendChild(toast);
      
      // Animar entrada
      setTimeout(() => {
        toast.classList.remove('translate-x-full');
      }, 100);
      
      // Remover após 4 segundos
      setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
          if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
          }
        }, 300);
      }, 4000);
    }

    // Toggle dropdown
    function toggleNotifications() {
      const dropdown = document.getElementById('notificationDropdown');
      const isHidden = dropdown.classList.contains('hidden');
      
      dropdown.classList.toggle('hidden');
      
      // Se abriu o dropdown, parar o som (usuário viu as notificações)
      if (isHidden) {
        stopNotificationSound();
      }
    }

    // Marcar como lida
    async function markAsRead(notificationId) {
      try {
        await fetch(`/api/notifications/${notificationId}/read`, { method: 'POST' });
        loadNotifications(); // Recarregar
      } catch (error) {
        console.error('Erro ao marcar como lida:', error);
      }
    }

    // Marcar todas como lidas
    async function markAllAsRead() {
      try {
        await fetch('/api/notifications/read-all', { method: 'POST' });
        stopNotificationSound(); // Parar som imediatamente
        loadNotifications(); // Recarregar
      } catch (error) {
        console.error('Erro ao marcar todas como lidas:', error);
      }
    }

    // Formatar data
    function formatDate(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diff = now - date;
      
      if (diff < 60000) return 'Agora';
      if (diff < 3600000) return Math.floor(diff / 60000) + 'm';
      if (diff < 86400000) return Math.floor(diff / 3600000) + 'h';
      return Math.floor(diff / 86400000) + 'd';
    }

    // Carregar foto do usuário na sidebar
    async function loadSidebarUserPhoto() {
      try {
        const response = await fetch('/api/profile');
        const user = await response.json();
        
        if (user && user.profile_photo) {
          const img = document.getElementById('sidebarUserPhoto');
          const initial = document.getElementById('sidebarUserInitial');
          
          if (img && initial) {
            img.src = `data:${user.profile_photo_type};base64,${user.profile_photo}`;
            img.classList.remove('hidden');
            initial.classList.add('hidden');
          }
        }
      } catch (error) {
        console.log('Foto de perfil não disponível na sidebar');
      }
    }

    // Carregar foto quando a página carregar
    document.addEventListener('DOMContentLoaded', loadSidebarUserPhoto);
  </script>
</div>
