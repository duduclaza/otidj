<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<section class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"> Relacionamento - CRM</h1>
            <p class="text-gray-600 mt-2">Fortaleça o relacionamento com seus clientes e fidelize sua base</p>
        </div>
    </div>

    <div class="relative overflow-hidden bg-gradient-to-br from-purple-600 via-pink-600 to-rose-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative p-8 md:p-12">
            <div class="max-w-4xl">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-4 py-2 bg-pink-400 text-pink-900 text-sm font-bold rounded-full animate-pulse"> TRIAL EM BREVE</span>
                    <span class="px-4 py-2 bg-yellow-400 text-yellow-900 text-sm font-bold rounded-full"> CRM PREMIUM</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Customer Success & Fidelização</h2>
                <p class="text-xl text-purple-100 mb-8">Acompanhe a jornada do cliente pós-venda, identifique riscos de churn e aumente o LTV (Lifetime Value) com estratégias de relacionamento.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20">
                        <h3 class="text-white font-bold mb-3 flex items-center gap-2"><span class="text-2xl"></span> Comunicação 360°</h3>
                        <ul class="space-y-2 text-purple-100 text-sm">
                            <li> Central de atendimento omnichannel</li>
                            <li> WhatsApp Business integrado</li>
                            <li> Email e SMS automáticos</li>
                            <li> Histórico completo de interações</li>
                            <li> Pesquisas de satisfação NPS</li>
                        </ul>
                    </div>
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20">
                        <h3 class="text-white font-bold mb-3 flex items-center gap-2"><span class="text-2xl"></span> Prevenção de Churn</h3>
                        <ul class="space-y-2 text-purple-100 text-sm">
                            <li> Score de saúde do cliente</li>
                            <li> Alertas de risco de cancelamento</li>
                            <li> Workflows de retenção</li>
                            <li> Análise preditiva com IA</li>
                            <li> Planos de ação automáticos</li>
                        </ul>
                    </div>
                </div>
                <div class="flex items-center gap-6 mb-6">
                    <div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-5xl font-bold text-white">R$ 900</span>
                            <span class="text-xl text-purple-200">/mês</span>
                        </div>
                        <p class="text-purple-200 text-sm mt-1">CRM Completo com todos os módulos</p>
                    </div>
                </div>
                <button disabled class="bg-white text-purple-700 px-8 py-4 rounded-xl font-bold text-lg shadow-xl opacity-60 cursor-not-allowed"> Trial Em Breve</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
            <div class="bg-purple-600 w-14 h-14 rounded-xl flex items-center justify-center mb-4 shadow-lg"><span class="text-3xl"></span></div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Segmentação Avançada</h3>
            <p class="text-gray-600 text-sm mb-4">Crie segmentos personalizados de clientes e direcione ações específicas para cada grupo.</p>
            <ul class="text-sm text-gray-700 space-y-1">
                <li> Segmentos dinâmicos</li>
                <li> Tags e categorias</li>
                <li> Filtros inteligentes</li>
            </ul>
        </div>
        <div class="bg-gradient-to-br from-pink-50 to-rose-50 border border-pink-200 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
            <div class="bg-pink-600 w-14 h-14 rounded-xl flex items-center justify-center mb-4 shadow-lg"><span class="text-3xl"></span></div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Programas de Fidelidade</h3>
            <p class="text-gray-600 text-sm mb-4">Crie programas de pontos, recompensas e benefícios exclusivos para clientes fiéis.</p>
            <ul class="text-sm text-gray-700 space-y-1">
                <li> Sistema de pontos</li>
                <li> Recompensas personalizadas</li>
                <li> Gamificação</li>
            </ul>
        </div>
        <div class="bg-gradient-to-br from-rose-50 to-red-50 border border-rose-200 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
            <div class="bg-rose-600 w-14 h-14 rounded-xl flex items-center justify-center mb-4 shadow-lg"><span class="text-3xl"></span></div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Health Score</h3>
            <p class="text-gray-600 text-sm mb-4">Monitore a saúde do relacionamento com cada cliente em tempo real.</p>
            <ul class="text-sm text-gray-700 space-y-1">
                <li> Score automático</li>
                <li> Indicadores customizáveis</li>
                <li> Dashboard visual</li>
            </ul>
        </div>
    </div>

    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-8 text-center shadow-2xl">
        <h3 class="text-3xl font-bold text-white mb-4"> Fortaleça o relacionamento com seus clientes!</h3>
        <p class="text-xl text-purple-100 mb-6 max-w-2xl mx-auto">Entre em contato e descubra como reduzir o churn e aumentar a fidelização.</p>
        <a href="mailto:comercial@sgqoti.com" class="inline-block bg-white text-purple-700 px-8 py-4 rounded-xl font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all"> Falar com Consultor</a>
    </div>
</section>

