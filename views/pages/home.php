<section class="space-y-6">
  <h1 class="text-2xl font-semibold">Bem-vindo ao SGQ OTI - DJ</h1>
  <p class="text-gray-600">Selecione um módulo no menu lateral para começar.</p>
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
    <div class="p-4 bg-white border rounded-lg">
      <h2 class="font-medium mb-1">Status do Sistema</h2>
      <ul class="text-sm text-gray-600 list-disc ml-5">
        <li>Ambiente: <?= e($_ENV['APP_ENV'] ?? 'production') ?></li>
        <li>Debug: <?= e($_ENV['APP_DEBUG'] ?? 'false') ?></li>
      </ul>
    </div>
    <div class="p-4 bg-white border rounded-lg">
      <h2 class="font-medium mb-1">Banco de Dados</h2>
      <p class="text-sm text-gray-600">Host: <?= e($_ENV['DB_HOST'] ?? '') ?> / Base: <?= e($_ENV['DB_NAME'] ?? '') ?></p>
    </div>
  </div>
</section>
