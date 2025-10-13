<!-- Documentação API Power BI -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <a href="/api/powerbi" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para APIs
        </a>
        
        <h1 class="text-3xl font-bold text-gray-900 mb-8">📚 Documentação das APIs</h1>

        <!-- Autenticação -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">🔐 Autenticação</h2>
            
            <div class="mb-4">
                <h3 class="font-semibold mb-2 text-green-600">✅ Método 1: Parâmetro na URL (Recomendado para Power BI)</h3>
                <p class="text-sm mb-2">Adicione o token diretamente na URL:</p>
                <pre class="p-4 bg-gray-900 text-green-400 rounded text-sm"><code>?api_token=sgqoti2024@powerbi</code></pre>
            </div>

            <div class="mb-4">
                <h3 class="font-semibold mb-2">Método 2: Header Authorization (Alternativo)</h3>
                <p class="text-sm mb-2">Ou inclua o token no header:</p>
                <pre class="p-4 bg-gray-900 text-green-400 rounded text-sm"><code>Authorization: Bearer sgqoti2024@powerbi</code></pre>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4">
                <p class="text-sm text-yellow-800">
                    <strong>⚠️ Power BI Desktop:</strong> Use o <strong>Método 1</strong> (parâmetro na URL) para evitar erros de header HTTP.
                </p>
            </div>
        </div>

        <!-- API Garantias -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">API de Garantias</h2>
            
            <div class="mb-4">
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-mono">GET</span>
                <code class="ml-2">/api/powerbi/garantias</code>
            </div>

            <h3 class="font-semibold mb-2">Parâmetros:</h3>
            <ul class="list-disc pl-6 mb-4 space-y-1 text-sm">
                <li><code>data_inicio</code> (date) - Filtro data inicial YYYY-MM-DD</li>
                <li><code>data_fim</code> (date) - Filtro data final YYYY-MM-DD</li>
                <li><code>status</code> (string) - Filtro por status</li>
                <li><code>fornecedor_id</code> (int) - Filtro por fornecedor</li>
                <li><code>origem</code> (string) - Amostragem, Homologação ou Em Campo</li>
            </ul>

            <h3 class="font-semibold mb-2">Exemplo de URL Completa:</h3>
            <code class="block p-3 bg-gray-100 rounded text-sm break-all">
                <?php echo $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br'; ?>/api/powerbi/garantias?api_token=sgqoti2024@powerbi&data_inicio=2024-01-01&status=Em%20andamento
            </code>
        </div>

        <!-- Power BI -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">📊 Configuração no Power BI Desktop</h2>
            <ol class="list-decimal pl-6 space-y-3 text-sm">
                <li>Abra o <strong>Power BI Desktop</strong></li>
                <li>Clique em <strong>Obter Dados</strong> → <strong>Web</strong></li>
                <li>Cole a URL da API <strong>com o token incluído</strong>:
                    <div class="mt-2 p-3 bg-green-50 rounded border border-green-200">
                        <code class="text-xs break-all">
                            <?php echo $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br'; ?>/api/powerbi/garantias?api_token=sgqoti2024@powerbi
                        </code>
                    </div>
                </li>
                <li>Clique em <strong>OK</strong></li>
                <li>Os dados serão carregados automaticamente! 🎉</li>
            </ol>
            
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mt-4">
                <p class="text-sm text-blue-800">
                    <strong>💡 Dica:</strong> Adicione filtros opcionais na URL: <code>data_inicio</code>, <code>data_fim</code>, <code>status</code>, <code>fornecedor_id</code>, <code>origem</code>
                </p>
            </div>
        </div>

    </div>
</div>
