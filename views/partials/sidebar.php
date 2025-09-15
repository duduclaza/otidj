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
  ['label' => 'Registros Gerais', 'href' => '/registros-gerais', 'icon' => '📄'],
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
      ?>
        <li>
          <a href="<?= e($item['href']) ?>" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium hover:bg-primary-50 <?php echo $active?'bg-primary-100 text-primary-700':'text-gray-700'; ?>">
            <span><?= e($item['icon']) ?></span>
            <span><?= e($item['label']) ?></span>
          </a>
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
  </script>
</div>
