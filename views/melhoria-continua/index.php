<?php
// Tabs container view for Melhoria Contínua
// Expects optional $setores, $usuarios for the Solicitações tab
?>
<section class="space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Melhoria Contínua</h1>
  </div>

  <!-- Tabs -->
  <div class="border-b border-gray-200">
    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
      <button id="tab-solicitacoes" class="tab-btn border-blue-500 text-blue-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm" data-target="#pane-solicitacoes">Solicitação de Melhorias</button>
      <button id="tab-pendentes" class="tab-btn text-gray-500 hover:text-gray-700 whitespace-nowrap py-2 px-1 border-b-2 border-transparent font-medium text-sm" data-target="#pane-pendentes">Melhorias Pendentes</button>
      <button id="tab-historico" class="tab-btn text-gray-500 hover:text-gray-700 whitespace-nowrap py-2 px-1 border-b-2 border-transparent font-medium text-sm" data-target="#pane-historico">Histórico de Melhorias</button>
    </nav>
  </div>

  <!-- Panes -->
  <div id="pane-solicitacoes" class="tab-pane">
    <?php include __DIR__ . '/solicitacoes.php'; ?>
  </div>
  <div id="pane-pendentes" class="tab-pane hidden">
    <?php include __DIR__ . '/pendentes.php'; ?>
  </div>
  <div id="pane-historico" class="tab-pane hidden">
    <?php include __DIR__ . '/historico.php'; ?>
  </div>
</section>

<script>
(function(){
  const tabs = document.querySelectorAll('.tab-btn');
  const panes = document.querySelectorAll('.tab-pane');

  function activate(targetSel){
    panes.forEach(p=>p.classList.add('hidden'));
    tabs.forEach(t=>{
      t.classList.remove('border-blue-500','text-blue-600');
      t.classList.add('border-transparent','text-gray-500');
    });
    const pane = document.querySelector(targetSel);
    const tab = Array.from(tabs).find(x=>x.getAttribute('data-target')===targetSel);
    if (pane) pane.classList.remove('hidden');
    if (tab){
      tab.classList.add('border-blue-500','text-blue-600');
      tab.classList.remove('border-transparent','text-gray-500');
    }
  }

  tabs.forEach(t=>t.addEventListener('click', ()=>activate(t.getAttribute('data-target'))));

  // Select tab by hash (?tab=pendentes OR #pendentes)
  const params = new URLSearchParams(window.location.search);
  const queryTab = params.get('tab');
  const hash = window.location.hash.replace('#','');
  const wanted = queryTab || hash;
  if (wanted && ['solicitacoes','pendentes','historico'].includes(wanted)) {
    activate('#pane-' + wanted);
  } else {
    activate('#pane-solicitacoes');
  }
})();
</script>
