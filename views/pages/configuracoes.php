<section class="space-y-6">
  <h1 class="text-2xl font-semibold">Configurações</h1>
  
  <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
    <h2 class="font-medium text-green-800 mb-2">✅ Auto-Migration Ativo</h2>
    <p class="text-sm text-green-700">O sistema agora roda automaticamente as migrações a cada acesso. As tabelas são criadas/atualizadas automaticamente quando você faz push de novas versões.</p>
  </div>

  <div class="p-4 bg-white border rounded">
    <h2 class="font-medium mb-2">Setup Manual (Opcional)</h2>
    <p class="text-gray-600 mb-3">Você ainda pode executar o setup manualmente se necessário:</p>
    <form method="post" action="/configuracoes/setup-banco" onsubmit="return confirm('Executar setup do banco agora?');">
      <button class="px-4 py-2 rounded bg-primary-600 text-white hover:bg-primary-700">Setup Banco de Dados</button>
    </form>
  </div>

  <div class="p-4 bg-white border rounded">
    <h2 class="font-medium mb-2">Como funciona o Auto-Migration</h2>
    <ul class="list-disc ml-5 text-sm text-gray-700 space-y-1">
      <li><strong>Automático:</strong> Roda a cada request, verifica se há atualizações necessárias</li>
      <li><strong>Versionado:</strong> Usa tabela `migrations` para controlar versões executadas</li>
      <li><strong>Seguro:</strong> Só executa se a versão atual for menor que a nova</li>
      <li><strong>Inclui:</strong> Criação de tabelas + inserção de dados padrão (se vazio)</li>
      <li><strong>Push & Deploy:</strong> Quando você faz push, o sistema se atualiza automaticamente</li>
    </ul>
    
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
      <h3 class="font-medium text-blue-800 mb-2">Tabelas Criadas Automaticamente:</h3>
      <ul class="text-sm text-blue-700 space-y-1">
        <li>• <strong>filiais</strong> - Cadastro de filiais da empresa</li>
        <li>• <strong>departamentos</strong> - Departamentos organizacionais</li>
        <li>• <strong>fornecedores</strong> - Cadastro de fornecedores</li>
        <li>• <strong>parametros_retornados</strong> - Parâmetros para análise de retornados</li>
        <li>• <strong>toners</strong> - Cadastro completo de toners com cálculos automáticos</li>
        <li>• <strong>migrations</strong> - Controle de versões do banco</li>
      </ul>
    </div>
  </div>
</section>
