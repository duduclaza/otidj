<?php
// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<section class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                📊 DPO <span class="text-lg font-normal text-gray-500">(Documento de Planejamento de Operações)</span>
            </h1>
            <p class="text-gray-600 mt-2">Gestão completa de planejamento operacional de implantação</p>
        </div>
    </div>

    <!-- Banner Principal - Trial Disponível -->
    <div class="relative overflow-hidden bg-gradient-to-br from-purple-600 via-blue-600 to-indigo-700 rounded-2xl shadow-2xl">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full transform translate-x-32 -translate-y-32"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white opacity-5 rounded-full transform -translate-x-48 translate-y-48"></div>
        
        <div class="relative p-8 md:p-12">
            <div class="flex items-start justify-between flex-wrap gap-6">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="px-4 py-2 bg-yellow-400 text-yellow-900 text-sm font-bold rounded-full animate-pulse">
                            🎯 TRIAL DISPONÍVEL EM BREVE
                        </span>
                        <span class="px-4 py-2 bg-green-400 text-green-900 text-sm font-bold rounded-full">
                            ✨ MÓDULO PREMIUM
                        </span>
                    </div>
                    
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                        Sistema DPO Completo
                    </h2>
                    
                    <p class="text-xl text-blue-100 mb-6 max-w-3xl">
                        Planeje, gerencie e acompanhe todas as operações de implantação com nosso sistema inteligente de DPO.
                    </p>
                    
                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex items-baseline gap-2">
                            <span class="text-5xl font-bold text-white">R$ 700</span>
                            <span class="text-xl text-blue-200">/mês</span>
                        </div>
                    </div>
                    
                    <div class="flex gap-4 flex-wrap">
                        <button disabled class="bg-white text-purple-700 px-8 py-4 rounded-xl font-bold text-lg shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all opacity-60 cursor-not-allowed">
                            🔒 Período Trial Em Breve
                        </button>
                        <button disabled class="bg-purple-500 bg-opacity-30 text-white border-2 border-white px-8 py-4 rounded-xl font-semibold text-lg opacity-60 cursor-not-allowed">
                            Contratar Agora
                        </button>
                    </div>
                </div>
                
                <div class="hidden lg:block">
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-6 border border-white border-opacity-20">
                        <p class="text-white font-semibold mb-3">📦 O que inclui:</p>
                        <ul class="space-y-2 text-blue-100 text-sm">
                            <li>✓ Criação ilimitada de DPOs</li>
                            <li>✓ Templates personalizáveis</li>
                            <li>✓ Aprovações multinível</li>
                            <li>✓ Dashboard executivo</li>
                            <li>✓ Relatórios automáticos</li>
                            <li>✓ Integração com equipe</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recursos Premium -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
            <div class="bg-blue-600 w-14 h-14 rounded-xl flex items-center justify-center mb-4 shadow-lg">
                <span class="text-3xl">📋</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Gestão Completa</h3>
            <p class="text-gray-600 text-sm mb-4">
                Crie, edite e gerencie todos os DPOs em um único lugar. Interface intuitiva e organizada.
            </p>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>• Campos customizáveis</li>
                <li>• Histórico de alterações</li>
                <li>• Versionamento automático</li>
            </ul>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
            <div class="bg-purple-600 w-14 h-14 rounded-xl flex items-center justify-center mb-4 shadow-lg">
                <span class="text-3xl">🔄</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Workflow Inteligente</h3>
            <p class="text-gray-600 text-sm mb-4">
                Fluxos de aprovação configuráveis com notificações automáticas em cada etapa.
            </p>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>• Aprovações por hierarquia</li>
                <li>• Notificações por email/SMS</li>
                <li>• Rastreamento em tempo real</li>
            </ul>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
            <div class="bg-green-600 w-14 h-14 rounded-xl flex items-center justify-center mb-4 shadow-lg">
                <span class="text-3xl">📊</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Analytics Avançado</h3>
            <p class="text-gray-600 text-sm mb-4">
                Dashboards e relatórios detalhados para tomada de decisão estratégica.
            </p>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>• KPIs personalizados</li>
                <li>• Gráficos interativos</li>
                <li>• Exportação em múltiplos formatos</li>
            </ul>
        </div>
    </div>

    <!-- Informações de Contato -->
    <div class="bg-gradient-to-r from-gray-50 to-blue-50 border-l-4 border-blue-600 rounded-lg p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-3">💼 Interessado em contratar?</h3>
        <p class="text-gray-700 mb-4">
            Entre em contato com nossa equipe comercial para conhecer todos os benefícios do módulo de Gestão de Implantação
            e garantir condições especiais de contratação.
        </p>
        <div class="flex gap-4 flex-wrap">
            <a href="mailto:comercial@sgqoti.com" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                📧 Email Comercial
            </a>
            <a href="tel:+5511999999999" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                📱 WhatsApp
            </a>
        </div>
    </div>
</section>
