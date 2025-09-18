<section class="space-y-6">
  <h1 class="text-2xl font-semibold">Configurações</h1>
  
  <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
    <h2 class="font-medium text-green-800 mb-2">✅ Auto-Migration Ativo</h2>
    <p class="text-sm text-green-700">O sistema agora roda automaticamente as migrações a cada acesso. As tabelas são criadas/atualizadas automaticamente quando você faz push de novas versões.</p>
  </div>

  <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg">
    <h2 class="font-semibold text-lg text-blue-900 mb-3">🔧 Setup Manual do Banco de Dados</h2>
    <p class="text-blue-700 mb-4">Execute este setup para:</p>
    <ul class="list-disc list-inside text-blue-700 text-sm mb-4 space-y-1">
      <li>Criar todas as tabelas necessárias</li>
      <li>Atualizar estrutura do banco de dados</li>
      <li>Executar migrações pendentes</li>
      <li>Inserir dados padrão se necessário</li>
    </ul>
    <form method="post" action="/configuracoes/setup-banco" onsubmit="return confirm('⚠️ ATENÇÃO: Isso irá atualizar o banco de dados.\n\n✅ Executar migrações\n✅ Criar tabelas necessárias\n✅ Inserir dados padrão\n\nDeseja continuar?');">
      <button class="px-6 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors shadow-lg">
        🚀 Executar Setup Completo
      </button>
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
        <li>• <strong>users</strong> - Usuários do sistema</li>
        <li>• <strong>profiles</strong> - Perfis de usuário</li>
        <li>• <strong>profile_permissions</strong> - Permissões por perfil</li>
        <li>• <strong>invitations</strong> - Convites de acesso</li>
        <li>• <strong>filiais</strong> - Cadastro de filiais da empresa</li>
        <li>• <strong>departamentos</strong> - Departamentos organizacionais</li>
        <li>• <strong>fornecedores</strong> - Cadastro de fornecedores</li>
        <li>• <strong>parametros_retornados</strong> - Parâmetros para análise de retornados</li>
        <li>• <strong>toners</strong> - Cadastro completo de toners</li>
        <li>• <strong>retornados</strong> - Registro de toners retornados</li>
        <li>• <strong>migrations</strong> - Controle de versões do banco</li>
      </ul>
    </div>
  </div>
</section>
