<section class="space-y-6">
  <h1 class="text-2xl font-semibold">Configurações</h1>
  <p class="text-gray-600">Execute o setup do banco de dados para criar/atualizar as tabelas e inserir valores padrões.</p>

  <form method="post" action="/configuracoes/setup-banco" onsubmit="return confirm('Executar setup do banco agora?');">
    <button class="px-4 py-2 rounded bg-primary-600 text-white hover:bg-primary-700">Setup Banco de Dados</button>
  </form>

  <div class="p-4 bg-white border rounded">
    <h2 class="font-medium mb-2">O que o setup faz</h2>
    <ul class="list-disc ml-5 text-sm text-gray-700 space-y-1">
      <li>Cria tabelas: filiais, departamentos, fornecedores, parametros_retornados (se não existirem).</li>
      <li>Insere filiais padrão: Jundiai, Franca, Santos, Caçapava, Uberlândia, Uberaba (se vazio).</li>
      <li>Insere departamentos padrão (se vazio).</li>
      <li>Insere parâmetros de retornados padrão (se vazio).</li>
    </ul>
  </div>
</section>
