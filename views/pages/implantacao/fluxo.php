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
            <h1 class="text-3xl font-bold text-gray-900">🔄 Fluxo de Implantação</h1>
            <p class="text-gray-600 mt-2">Visualize e gerencie todos os fluxos operacionais de implantação</p>
        </div>
    </div>

    <!-- Banner Principal com Gradiente Animado -->
    <div class="relative overflow-hidden bg-gradient-to-br from-cyan-600 via-blue-600 to-indigo-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0">
            <div class="absolute top-0 left-0 w-full h-full opacity-20" style="background-image: radial-gradient(circle at 20% 50%, white 2px, transparent 2px); background-size: 30px 30px;"></div>
        </div>
        
        <div class="relative p-8 md:p-12">
            <div class="flex items-center gap-3 mb-4">
                <span class="px-4 py-2 bg-cyan-400 text-cyan-900 text-sm font-bold rounded-full animate-bounce">
                    ⚡ TRIAL EM BREVE
                </span>
                <span class="px-4 py-2 bg-yellow-400 text-yellow-900 text-sm font-bold rounded-full">
                    🏆 PREMIUM
                </span>
            </div>
            
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">
                Visualização Completa de Fluxos
            </h2>
            
            <p class="text-xl text-cyan-100 mb-8 max-w-3xl">
                Crie, customize e acompanhe fluxogramas interativos de implantação com nossa ferramenta visual intuitiva.
            </p>
            
            <!-- Grid de Features -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 border border-white border-opacity-20 hover:bg-opacity-20 transition-all">
                    <div class="text-3xl mb-2">🎨</div>
                    <h3 class="font-bold text-white mb-1">Editor Visual</h3>
                    <p class="text-cyan-100 text-sm">Arraste e solte elementos para criar fluxos complexos</p>
                </div>
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 border border-white border-opacity-20 hover:bg-opacity-20 transition-all">
                    <div class="text-3xl mb-2">📱</div>
                    <h3 class="font-bold text-white mb-1">Multi-Plataforma</h3>
                    <p class="text-cyan-100 text-sm">Acesse de qualquer dispositivo, anywhere</p>
                </div>
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 border border-white border-opacity-20 hover:bg-opacity-20 transition-all">
                    <div class="text-3xl mb-2">🔗</div>
                    <h3 class="font-bold text-white mb-1">Integração</h3>
                    <p class="text-cyan-100 text-sm">Conecte com DPO e Ordens de Serviço</p>
                </div>
            </div>
            
            <div class="flex items-center gap-6 mb-6">
                <div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-6xl font-bold text-white">R$ 700</span>
                        <span class="text-2xl text-cyan-200">/mês</span>
                    </div>
                    <p class="text-cyan-200 text-sm mt-1">✨ Acesso completo ao módulo de Gestão de Implantação</p>
                </div>
            </div>
            
            <div class="flex gap-4">
                <button disabled class="bg-white text-cyan-700 px-8 py-4 rounded-xl font-bold text-lg shadow-2xl opacity-60 cursor-not-allowed flex items-center gap-2">
                    🔒 <span>Trial Em Breve</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Funcionalidades Principais -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-8 border border-blue-200 shadow-lg">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <span class="text-4xl">⚙️</span>
                Funcionalidades do Editor
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="bg-blue-600 rounded-lg p-2 flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 mb-1">Drag & Drop Intuitivo</h4>
                        <p class="text-gray-600 text-sm">Arraste elementos e conecte facilmente com cliques</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="bg-cyan-600 rounded-lg p-2 flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 mb-1">Templates Predefinidos</h4>
                        <p class="text-gray-600 text-sm">Biblioteca com fluxos prontos para implantação</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="bg-indigo-600 rounded-lg p-2 flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 mb-1">Validação Automática</h4>
                        <p class="text-gray-600 text-sm">Sistema verifica lógica e completude do fluxo</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="bg-purple-600 rounded-lg p-2 flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 mb-1">Exportação Multi-Formato</h4>
                        <p class="text-gray-600 text-sm">PDF, PNG, SVG, Visio e mais...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-8 border border-indigo-200 shadow-lg">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <span class="text-4xl">📊</span>
                Monitoramento em Tempo Real
            </h3>
            
            <div class="space-y-4">
                <div class="bg-white rounded-lg p-4 border border-indigo-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900">Status das Etapas</span>
                        <span class="text-green-600 font-bold">ATIVO</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: 75%"></div>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">75% das etapas concluídas</p>
                </div>

                <div class="bg-white rounded-lg p-4 border border-indigo-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900">Alertas Automáticos</span>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded">3 PENDENTES</span>
                    </div>
                    <p class="text-sm text-gray-600">Notificações quando há atrasos ou gargalos</p>
                </div>

                <div class="bg-white rounded-lg p-4 border border-indigo-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900">Dashboard Executivo</span>
                        <span class="text-blue-600 font-bold">📈</span>
                    </div>
                    <p class="text-sm text-gray-600">Visualização consolidada de todos os fluxos ativos</p>
                </div>

                <div class="bg-white rounded-lg p-4 border border-indigo-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900">Histórico Completo</span>
                        <span class="text-purple-600 font-bold">📚</span>
                    </div>
                    <p class="text-sm text-gray-600">Registro de todas as alterações e execuções</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Final -->
    <div class="bg-gradient-to-r from-cyan-600 to-indigo-600 rounded-2xl p-8 text-center shadow-2xl">
        <h3 class="text-3xl font-bold text-white mb-4">🚀 Revolucione sua Gestão de Implantação</h3>
        <p class="text-xl text-cyan-100 mb-6 max-w-2xl mx-auto">
            Cadastre-se para ser notificado quando o período trial estiver disponível e ganhe acesso prioritário!
        </p>
        <div class="flex justify-center gap-4 flex-wrap">
            <a href="mailto:comercial@sgqoti.com" class="bg-white text-indigo-700 px-8 py-4 rounded-xl font-bold text-lg shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all">
                📧 Quero Ser Notificado
            </a>
            <a href="tel:+5511999999999" class="bg-cyan-500 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all">
                📱 Falar com Consultor
            </a>
        </div>
    </div>
</section>
