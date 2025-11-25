<?php
// views/pages/dashboard-manutencao.php
// Página bonita informando que o Dashboard está em manutenção
?>

<div class="min-h-[60vh] flex flex-col items-center justify-center text-center">
  <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 shadow-xl mb-6">
    <span class="text-4xl">🛠️</span>
  </div>

  <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">
    Dashboard em manutenção
  </h1>

  <p class="max-w-xl text-slate-500 mb-6 text-sm md:text-base">
    Estamos preparando melhorias no painel de indicadores para deixar tudo ainda mais claro,
    rápido e confiável. Durante esse período, alguns gráficos e cards do dashboard principal
    ficarão temporariamente indisponíveis.
  </p>

  <div class="flex flex-col sm:flex-row items-center gap-3 mb-8">
    <a href="/inicio" class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-blue-600 text-white text-sm font-semibold shadow-lg shadow-blue-500/40 hover:bg-blue-700 transition-all duration-200">
      <span>Voltar para a página inicial</span>
    </a>
    <span class="text-xs text-slate-400">Você ainda pode navegar normalmente pelos outros módulos do sistema.</span>
  </div>

  <div class="relative max-w-xl w-full">
    <div class="absolute -inset-4 bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-purple-500/10 rounded-3xl blur-2xl -z-10"></div>
    <div class="rounded-2xl border border-slate-200 bg-white/80 backdrop-blur-sm px-5 py-4 text-left shadow-md">
      <p class="text-xs font-semibold text-slate-500 mb-2 flex items-center gap-2">
        <span class="inline-flex h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
        Status da manutenção
      </p>
      <ul class="space-y-1 text-xs text-slate-500">
        <li><span class="font-semibold">⏳ Em andamento:</span> ajustes nos gráficos e nos dados de fornecedores.</li>
        <li><span class="font-semibold">✅ Sistema online:</span> todos os demais módulos continuam funcionando normalmente.</li>
        <li><span class="font-semibold">💡 Dica:</span> se algo parecer estranho, tente atualizar a página (Ctrl + F5).</li>
      </ul>
    </div>
  </div>
</div>
