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
            <h2 class="text-2xl font-bold mb-4">Autenticação</h2>
            <p class="mb-4">Inclua o token no header:</p>
            <pre class="p-4 bg-gray-900 text-green-400 rounded"><code>Authorization: Bearer sgqoti2024@powerbi</code></pre>
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

            <h3 class="font-semibold mb-2">Exemplo de URL:</h3>
            <code class="block p-3 bg-gray-100 rounded text-sm">
                <?php echo $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br'; ?>/api/powerbi/garantias?data_inicio=2024-01-01&status=Em%20andamento
            </code>
        </div>

        <!-- Power BI -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Configuração no Power BI</h2>
            <ol class="list-decimal pl-6 space-y-3 text-sm">
                <li>Abra o Power BI Desktop</li>
                <li>Clique em <strong>Obter Dados</strong> → <strong>Web</strong></li>
                <li>Cole a URL da API</li>
                <li>Em <strong>Opções Avançadas</strong>, adicione:
                    <div class="mt-2 p-3 bg-gray-50 rounded">
                        <strong>Cabeçalho:</strong> Authorization<br>
                        <strong>Valor:</strong> Bearer sgqoti2024@powerbi
                    </div>
                </li>
                <li>Clique em <strong>OK</strong> e os dados serão carregados</li>
            </ol>
        </div>

    </div>
</div>
