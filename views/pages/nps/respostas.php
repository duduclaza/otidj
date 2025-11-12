<section class="space-y-6">
  <div class="flex justify-between items-center">
    <div>
      <a href="/nps" class="text-blue-600 hover:text-blue-700 text-sm mb-2 inline-block flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Voltar para Formulários
      </a>
      <h1 class="text-2xl font-semibold text-gray-900"><?= e($formulario['titulo']) ?></h1>
      <p class="text-gray-600 mt-1">
        <span class="font-medium">Total de respostas:</span> <?= count($respostas) ?>
      </p>
    </div>
    
    <div class="text-right">
      <p class="text-sm text-gray-500">Criado por: <?= e($formulario['criado_por_nome']) ?></p>
      <p class="text-sm text-gray-500">Em: <?= date('d/m/Y H:i', strtotime($formulario['criado_em'])) ?></p>
    </div>
  </div>

  <?php if (empty($respostas)): ?>
    <!-- Sem Respostas -->
    <div class="bg-white rounded-lg shadow p-12 text-center">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <p class="text-gray-600 mb-2">Nenhuma resposta ainda</p>
      <p class="text-sm text-gray-500">Compartilhe o link público para começar a receber respostas</p>
    </div>
  
  <?php else: ?>
    <!-- Lista de Respostas -->
    <div class="space-y-4">
      <?php foreach ($respostas as $resposta): ?>
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6">
          <!-- Cabeçalho da Resposta -->
          <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-200">
            <div>
              <h3 class="font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <?= e($resposta['nome']) ?>
              </h3>
              <?php if ($resposta['email']): ?>
                <p class="text-sm text-gray-600 mt-1 ml-7"><?= e($resposta['email']) ?></p>
              <?php endif; ?>
            </div>
            <div class="text-right">
              <span class="text-sm font-medium text-gray-700">
                <?= date('d/m/Y', strtotime($resposta['respondido_em'])) ?>
              </span>
              <p class="text-xs text-gray-500">
                <?= date('H:i', strtotime($resposta['respondido_em'])) ?>
              </p>
            </div>
          </div>
          
          <!-- Respostas -->
          <div class="space-y-4">
            <?php foreach ($resposta['respostas'] as $r): ?>
              <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <?= e($r['pergunta']) ?>
                </p>
                <p class="text-gray-900 pl-6 <?= is_numeric($r['resposta']) && $r['resposta'] >= 9 ? 'text-green-600 font-bold' : '' ?>">
                  <?= e($r['resposta']) ?>
                  <?php if (is_numeric($r['resposta']) && $r['resposta'] <= 10): ?>
                    <span class="text-xs text-gray-500">/ 10</span>
                  <?php endif; ?>
                </p>
              </div>
            <?php endforeach; ?>
          </div>
          
          <!-- Metadados -->
          <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-xs text-gray-400">
              <span>IP: <?= e($resposta['ip']) ?></span>
            </div>
            <div class="text-xs text-gray-400">
              ID: <?= e($resposta['id']) ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Estatísticas (se houver perguntas numéricas) -->
    <?php
    // Calcular NPS se houver perguntas numéricas
    $perguntasNumericas = array_filter($formulario['perguntas'], fn($p) => $p['tipo'] === 'numero');
    if (!empty($perguntasNumericas)):
    ?>
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Estatísticas</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <?php
          $promotores = 0;
          $neutros = 0;
          $detratores = 0;
          
          foreach ($respostas as $resp) {
            foreach ($resp['respostas'] as $r) {
              if (is_numeric($r['resposta'])) {
                $nota = (int)$r['resposta'];
                if ($nota >= 9) $promotores++;
                elseif ($nota >= 7) $neutros++;
                else $detratores++;
              }
            }
          }
          
          $total = $promotores + $neutros + $detratores;
          $nps = $total > 0 ? (($promotores - $detratores) / $total) * 100 : 0;
          ?>
          
          <div class="bg-white rounded-lg p-4 text-center">
            <p class="text-sm text-gray-600 mb-1">Promotores (9-10)</p>
            <p class="text-3xl font-bold text-green-600"><?= $promotores ?></p>
            <p class="text-xs text-gray-500"><?= $total > 0 ? round(($promotores/$total)*100, 1) : 0 ?>%</p>
          </div>
          
          <div class="bg-white rounded-lg p-4 text-center">
            <p class="text-sm text-gray-600 mb-1">Neutros (7-8)</p>
            <p class="text-3xl font-bold text-yellow-600"><?= $neutros ?></p>
            <p class="text-xs text-gray-500"><?= $total > 0 ? round(($neutros/$total)*100, 1) : 0 ?>%</p>
          </div>
          
          <div class="bg-white rounded-lg p-4 text-center">
            <p class="text-sm text-gray-600 mb-1">Detratores (0-6)</p>
            <p class="text-3xl font-bold text-red-600"><?= $detratores ?></p>
            <p class="text-xs text-gray-500"><?= $total > 0 ? round(($detratores/$total)*100, 1) : 0 ?>%</p>
          </div>
        </div>
        
        <div class="mt-6 bg-white rounded-lg p-6 text-center">
          <p class="text-sm text-gray-600 mb-2">Net Promoter Score (NPS)</p>
          <p class="text-5xl font-bold <?= $nps >= 50 ? 'text-green-600' : ($nps >= 0 ? 'text-yellow-600' : 'text-red-600') ?>">
            <?= round($nps) ?>
          </p>
          <p class="text-sm text-gray-500 mt-2">
            <?php if ($nps >= 75): ?>
              Excelente! Zona de Excelência
            <?php elseif ($nps >= 50): ?>
              Muito Bom! Zona de Qualidade
            <?php elseif ($nps >= 0): ?>
              Zona de Aperfeiçoamento
            <?php else: ?>
              Zona Crítica - Atenção Necessária
            <?php endif; ?>
          </p>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</section>
