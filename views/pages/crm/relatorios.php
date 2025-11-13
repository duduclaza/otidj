<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<section class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"> Relatórios - CRM</h1>
            <p class="text-gray-600 mt-2">Relatórios completos e personalizáveis para análise de performance</p>
        </div>
    </div>

    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-blue-600 to-cyan-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 opacity-10"><div class="absolute inset-0" style="background-image: linear-gradient(45deg, #fff 25%, transparent 25%), linear-gradient(-45deg, #fff 25%, transparent 25%); background-size: 20px 20px;"></div></div>
        <div class="relative p-8 md:p-12">
            <div class="max-w-5xl mx-auto">
                <div class="flex items-center gap-3 mb-4 justify-center">
                    <span class="px-4 py-2 bg-indigo-400 text-indigo-900 text-sm font-bold rounded-full"> TRIAL EM BREVE</span>
                    <span class="px-4 py-2 bg-cyan-400 text-cyan-900 text-sm font-bold rounded-full"> BUSINESS INTELLIGENCE</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-4 text-center">Relatórios & Analytics Avançados</h2>
                <p class="text-xl text-indigo-100 mb-8 text-center max-w-3xl mx-auto">Transforme dados em insights acionáveis com relatórios personalizados, dashboards interativos e análises preditivas.</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-4xl font-bold text-white mb-1">100+</div>
                        <div class="text-indigo-100 text-sm">Relatórios Prontos</div>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-4xl font-bold text-white mb-1"></div>
                        <div class="text-indigo-100 text-sm">Customizações</div>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-4xl font-bold text-white mb-1">15+</div>
                        <div class="text-indigo-100 text-sm">Formatos Export</div>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-4xl font-bold text-white mb-1"></div>
                        <div class="text-indigo-100 text-sm">Tempo Real</div>
                    </div>
                </div>
                <div class="text-center mb-6">
                    <div class="flex items-baseline justify-center gap-2 mb-2">
                        <span class="text-6xl font-bold text-white">R$ 900</span>
                        <span class="text-2xl text-indigo-200">/mês</span>
                    </div>
                    <p class="text-indigo-200 text-lg">CRM Completo com todos os módulos</p>
                </div>
                <div class="flex gap-4 justify-center">
                    <button disabled class="bg-white text-indigo-700 px-8 py-4 rounded-xl font-bold text-lg shadow-2xl opacity-60 cursor-not-allowed"> Trial Em Breve</button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-blue-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4"><div class="bg-blue-100 rounded-lg p-3"><span class="text-3xl"></span></div><h3 class="text-lg font-bold text-gray-900">Vendas & Pipeline</h3></div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li> Performance por vendedor</li>
                <li> Funil de conversão</li>
                <li> Previsão de vendas</li>
                <li> Ticket médio</li>
                <li> Taxa de fechamento</li>
            </ul>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-indigo-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4"><div class="bg-indigo-100 rounded-lg p-3"><span class="text-3xl"></span></div><h3 class="text-lg font-bold text-gray-900">Marketing & Leads</h3></div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li> ROI por campanha</li>
                <li> Custo por lead (CPL)</li>
                <li> Taxa de conversão</li>
                <li> Origem dos leads</li>
                <li> Jornada do cliente</li>
            </ul>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-purple-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4"><div class="bg-purple-100 rounded-lg p-3"><span class="text-3xl"></span></div><h3 class="text-lg font-bold text-gray-900">Relacionamento</h3></div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li> Health Score</li>
                <li> Taxa de churn</li>
                <li> LTV (Lifetime Value)</li>
                <li> NPS e satisfação</li>
                <li> Tickets de suporte</li>
            </ul>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-cyan-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4"><div class="bg-cyan-100 rounded-lg p-3"><span class="text-3xl"></span></div><h3 class="text-lg font-bold text-gray-900">Financeiro</h3></div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li> Receita recorrente (MRR)</li>
                <li> Faturamento por período</li>
                <li> Inadimplência</li>
                <li> Forecast de receita</li>
                <li> Análise de rentabilidade</li>
            </ul>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-green-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4"><div class="bg-green-100 rounded-lg p-3"><span class="text-3xl"></span></div><h3 class="text-lg font-bold text-gray-900">Produtividade</h3></div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li> Atividades por usuário</li>
                <li> Tempo médio de resposta</li>
                <li> Taxa de follow-up</li>
                <li> Metas vs realizado</li>
                <li> Ranking de equipe</li>
            </ul>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-orange-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4"><div class="bg-orange-100 rounded-lg p-3"><span class="text-3xl"></span></div><h3 class="text-lg font-bold text-gray-900">Customizados</h3></div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li> Editor de relatórios</li>
                <li> SQL query builder</li>
                <li> Templates salvos</li>
                <li> Agendamento automático</li>
                <li> Distribuição por email</li>
            </ul>
        </div>
    </div>

    <div class="bg-gradient-to-r from-gray-50 to-indigo-50 rounded-xl p-8 border border-indigo-200">
        <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center"> Formatos de Exportação</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-lg p-4 text-center shadow-md"><div class="text-3xl mb-2"></div><div class="font-bold text-gray-900">Excel</div></div>
            <div class="bg-white rounded-lg p-4 text-center shadow-md"><div class="text-3xl mb-2"></div><div class="font-bold text-gray-900">PDF</div></div>
            <div class="bg-white rounded-lg p-4 text-center shadow-md"><div class="text-3xl mb-2"></div><div class="font-bold text-gray-900">CSV</div></div>
            <div class="bg-white rounded-lg p-4 text-center shadow-md"><div class="text-3xl mb-2"></div><div class="font-bold text-gray-900">HTML</div></div>
            <div class="bg-white rounded-lg p-4 text-center shadow-md"><div class="text-3xl mb-2"></div><div class="font-bold text-gray-900">Email</div></div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-indigo-600 to-cyan-600 rounded-2xl p-10 text-center shadow-2xl">
        <h3 class="text-3xl font-bold text-white mb-4"> Tome Decisões Baseadas em Dados!</h3>
        <p class="text-xl text-indigo-100 mb-8 max-w-3xl mx-auto">Entre em contato e descubra como nossos relatórios podem transformar sua gestão comercial.</p>
        <a href="mailto:comercial@sgqoti.com" class="inline-block bg-white text-indigo-700 px-8 py-4 rounded-xl font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all"> Falar com Especialista</a>
    </div>
</section>

