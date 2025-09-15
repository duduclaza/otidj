<?php
// Expect: $filiais, $departamentos, $fornecedores, $parametros
$tablesExist = !empty($filiais) || !empty($departamentos) || !empty($fornecedores) || !empty($parametros);
?>
<section class="space-y-8">
  <h1 class="text-2xl font-semibold">Registros Gerais</h1>
  
  <?php if (!$tablesExist): ?>
    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
      <p class="text-yellow-800 mb-2"><strong>⚠️ Tabelas não encontradas</strong></p>
      <p class="text-sm text-yellow-700">Execute o setup do banco de dados primeiro:</p>
      <a href="/configuracoes" class="inline-block mt-2 px-3 py-1 bg-primary-600 text-white text-sm rounded hover:bg-primary-700">Ir para Configurações</a>
    </div>
  <?php endif; ?>

  <div class="bg-white border rounded-lg">
    <div class="border-b px-4 py-2">
      <nav class="flex flex-wrap gap-3 text-sm">
        <a href="#filiais" class="px-3 py-2 rounded hover:bg-primary-50">Cadastro de Filiais</a>
        <a href="#departamentos" class="px-3 py-2 rounded hover:bg-primary-50">Cadastro de Departamentos</a>
        <a href="#fornecedores" class="px-3 py-2 rounded hover:bg-primary-50">Cadastro de Fornecedores</a>
        <a href="#parametros" class="px-3 py-2 rounded hover:bg-primary-50">Parâmetros de Retornados</a>
      </nav>
    </div>

    <div class="p-4 space-y-10">
      <div id="filiais" class="scroll-mt-24">
        <h2 class="text-lg font-medium mb-3">Cadastro de Filiais</h2>
        <form method="post" action="/registros-gerais/filiais" class="flex flex-col sm:flex-row gap-3 items-start">
          <input type="text" name="nome" placeholder="Nome da filial" class="border rounded px-3 py-2 w-full sm:w-80" required>
          <button class="px-4 py-2 rounded bg-primary-600 text-white hover:bg-primary-700">Salvar</button>
        </form>
        <ul class="mt-4 list-disc ml-5 text-sm text-gray-700">
          <?php foreach ($filiais as $f): ?>
            <li><?= e($f['nome']) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div id="departamentos" class="scroll-mt-24">
        <h2 class="text-lg font-medium mb-3">Cadastro de Departamentos</h2>
        <form method="post" action="/registros-gerais/departamentos" class="flex flex-col sm:flex-row gap-3 items-start">
          <input type="text" name="nome" placeholder="Nome do departamento" class="border rounded px-3 py-2 w-full sm:w-80" required>
          <button class="px-4 py-2 rounded bg-primary-600 text-white hover:bg-primary-700">Salvar</button>
        </form>
        <ul class="mt-4 list-disc ml-5 text-sm text-gray-700">
          <?php foreach ($departamentos as $d): ?>
            <li><?= e($d['nome']) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div id="fornecedores" class="scroll-mt-24">
        <h2 class="text-lg font-medium mb-3">Cadastro de Fornecedores</h2>
        <form method="post" action="/registros-gerais/fornecedores" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
          <input type="text" name="nome" placeholder="Nome do fornecedor (obrigatório)" class="border rounded px-3 py-2" required>
          <input type="text" name="contato" placeholder="Contato (link/email/telefone)" class="border rounded px-3 py-2">
          <input type="text" name="rma" placeholder="RMA (link/email/telefone)" class="border rounded px-3 py-2">
          <button class="px-4 py-2 rounded bg-primary-600 text-white hover:bg-primary-700">Salvar</button>
        </form>
        <div class="mt-4 overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left border-b">
                <th class="py-2 pr-4">Nome</th>
                <th class="py-2 pr-4">Contato</th>
                <th class="py-2 pr-4">RMA</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <?php foreach ($fornecedores as $f): ?>
              <tr>
                <td class="py-2 pr-4"><?= e($f['nome']) ?></td>
                <td class="py-2 pr-4"><a href="<?= e($f['contato']) ?>" class="text-primary-700 hover:underline" target="_blank"><?= e($f['contato']) ?></a></td>
                <td class="py-2 pr-4"><a href="<?= e($f['rma']) ?>" class="text-primary-700 hover:underline" target="_blank"><?= e($f['rma']) ?></a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="parametros" class="scroll-mt-24">
        <h2 class="text-lg font-medium mb-3">Cadastro de Parâmetros de Retornados</h2>
        <form method="post" action="/registros-gerais/parametros" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
          <input type="text" name="nome" placeholder="Nome do parâmetro" class="border rounded px-3 py-2" required>
          <input type="number" name="faixa_min" placeholder="Faixa mínima (%)" class="border rounded px-3 py-2" required>
          <input type="number" name="faixa_max" placeholder="Faixa máxima (%) (opcional)" class="border rounded px-3 py-2">
          <textarea name="orientacao" placeholder="Orientação" class="border rounded px-3 py-2 sm:col-span-2 lg:col-span-3" required></textarea>
          <button class="px-4 py-2 rounded bg-primary-600 text-white hover:bg-primary-700">Salvar</button>
        </form>
        <div class="mt-4 overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left border-b">
                <th class="py-2 pr-4">Nome</th>
                <th class="py-2 pr-4">Faixa</th>
                <th class="py-2 pr-4">Orientação</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <?php foreach ($parametros as $p): ?>
                <tr>
                  <td class="py-2 pr-4"><?= e($p['nome']) ?></td>
                  <td class="py-2 pr-4">
                    <?= e((string)$p['faixa_min']) ?>% - <?= $p['faixa_max'] !== null ? e((string)$p['faixa_max']).'%' : '∞' ?>
                  </td>
                  <td class="py-2 pr-4 max-w-2xl"><?= e($p['orientacao']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</section>
