<?php
$menu = [
  ['label' => 'Controle de Toners', 'href' => '/controle-de-toners', 'icon' => '🖨️'],
  ['label' => 'Homologações', 'href' => '/homologacoes', 'icon' => '✅'],
  ['label' => 'Amostragens', 'href' => '/amostragens', 'icon' => '🧪'],
  ['label' => 'Garantias', 'href' => '/garantias', 'icon' => '🛡️'],
  ['label' => 'Controle de Descartes', 'href' => '/controle-de-descartes', 'icon' => '♻️'],
  ['label' => 'FEMEA', 'href' => '/femea', 'icon' => '📈'],
  ['label' => 'POPs e ITs', 'href' => '/pops-e-its', 'icon' => '📚'],
  ['label' => 'Fluxogramas', 'href' => '/fluxogramas', 'icon' => '🔀'],
  ['label' => 'Melhoria Continua', 'href' => '/melhoria-continua', 'icon' => '⚙️'],
  ['label' => 'Controle de RC', 'href' => '/controle-de-rc', 'icon' => '🗂️'],
  ['label' => 'Registros Gerais', 'href' => '#', 'icon' => '📄', 'submenu' => [
    ['label' => 'Filiais', 'href' => '/registros/filiais', 'icon' => '🏢'],
    ['label' => 'Departamentos', 'href' => '/registros/departamentos', 'icon' => '🏛️'],
    ['label' => 'Fornecedores', 'href' => '/registros/fornecedores', 'icon' => '🏭'],
    ['label' => 'Parâmetros de Retornados', 'href' => '/registros/parametros', 'icon' => '📊'],
  ]],
  ['label' => 'Configurações', 'href' => '/configuracoes', 'icon' => '⚙️'],
];
$current = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';
?>
<aside class="hidden lg:flex lg:w-72 flex-col bg-white border-r border-gray-200">
  <div class="h-16 flex items-center px-6 border-b border-gray-200">
    <span class="text-lg font-semibold text-primary-700">SGQ OTI - DJ</span>
  </div>
  <nav class="flex-1 overflow-y-auto py-4">
    <ul class="space-y-1 px-3">
      <li>
        <a href="/" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium hover:bg-primary-50 <?php echo $current==='/'?'bg-primary-100 text-primary-700':'text-gray-700'; ?>">
          <span>🏠</span>
          <span>Início</span>
        </a>
      </li>
      <?php foreach ($menu as $item):
        $active = rtrim($item['href'], '/') === $current;
        $hasSubmenu = isset($item['submenu']);
        $submenuActive = false;
        if ($hasSubmenu) {
          foreach ($item['submenu'] as $sub) {
            if (rtrim($sub['href'], '/') === $current) {
              $submenuActive = true;
              break;
            }
          }
        }
      ?>
        <li>
          <?php if ($hasSubmenu): ?>
            <div class="submenu-container">
              <button onclick="toggleSubmenu(this)" class="flex items-center justify-between w-full px-3 py-2 rounded-md text-sm font-medium hover:bg-primary-50 <?php echo $submenuActive?'bg-primary-100 text-primary-700':'text-gray-700'; ?>">
                <div class="flex items-center gap-3">
                  <span><?= e($item['icon']) ?></span>
                  <span><?= e($item['label']) ?></span>
                </div>
                <span class="submenu-arrow transition-transform">▼</span>
              </button>
              <ul class="submenu ml-6 mt-1 space-y-1 hidden">
                <?php foreach ($item['submenu'] as $sub):
                  $subActive = rtrim($sub['href'], '/') === $current;
                ?>
                  <li>
                    <a href="<?= e($sub['href']) ?>" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium hover:bg-primary-50 <?php echo $subActive?'bg-primary-100 text-primary-700':'text-gray-600'; ?>">
                      <span><?= e($sub['icon']) ?></span>
                      <span><?= e($sub['label']) ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php else: ?>
            <a href="<?= e($item['href']) ?>" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium hover:bg-primary-50 <?php echo $active?'bg-primary-100 text-primary-700':'text-gray-700'; ?>">
              <span><?= e($item['icon']) ?></span>
              <span><?= e($item['label']) ?></span>
            </a>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>
</aside>

<!-- Mobile sidebar -->
<div class="lg:hidden">
  <div class="h-14 flex items-center justify-between px-4 bg-white border-b border-gray-200">
    <button id="menuBtn" class="p-2 rounded-md border text-gray-600">☰</button>
    <span class="text-base font-semibold text-primary-700">SGQ OTI - DJ</span>
    <span></span>
  </div>
  <div id="mobileMenu" class="hidden fixed inset-0 bg-black/30 z-40"></div>
  <div id="mobileDrawer" class="hidden fixed inset-y-0 left-0 w-72 bg-white z-50 shadow-lg">
    <div class="h-14 flex items-center px-4 border-b">Menu</div>
    <nav class="p-3 space-y-1">
      <a href="/" class="block px-3 py-2 rounded hover:bg-primary-50">Início</a>
      <?php foreach ($menu as $item): ?>
        <a href="<?= e($item['href']) ?>" class="block px-3 py-2 rounded hover:bg-primary-50"><?= e($item['label']) ?></a>
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
