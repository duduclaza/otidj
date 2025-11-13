<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<section class='space-y-6'>
    <div class='flex justify-between items-center'>
        <div>
            <h1 class='text-3xl font-bold text-gray-900'> Prospecção - CRM</h1>
            <p class='text-gray-600 mt-2'>Capture e qualifique leads com inteligência artificial</p>
        </div>
    </div>

    <!-- Banner Principal -->
    <div class='relative overflow-hidden bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 rounded-2xl shadow-2xl'>
        <div class='absolute inset-0 bg-black opacity-10'></div>
        <div class='absolute top-0 right-0 w-96 h-96 bg-white opacity-5 rounded-full transform translate-x-48 -translate-y-48'></div>
        
        <div class='relative p-8 md:p-12'>
            <div class='max-w-4xl'>
                <div class='flex items-center gap-3 mb-4'>
                    <span class='px-4 py-2 bg-cyan-400 text-cyan-900 text-sm font-bold rounded-full animate-pulse'>
                         TRIAL EM BREVE
                    </span>
                    <span class='px-4 py-2 bg-yellow-400 text-yellow-900 text-sm font-bold rounded-full'>
                         CRM PREMIUM
                    </span>
                </div>
                
                <h2 class='text-3xl md:text-4xl font-bold text-white mb-4'>
                    Prospecção Inteligente de Leads
                </h2>
                
                <p class='text-xl text-blue-100 mb-6'>
                    Capture, qualifique e converta leads com ferramentas de automação e inteligência artificial.
                    Aumente suas vendas com prospecção direcionada.
                </p>
                
                <div class='grid grid-cols-1 md:grid-cols-2 gap-6 mb-8'>
                    <div class='bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20'>
                        <h3 class='text-white font-bold mb-3 flex items-center gap-2'>
                            <span class='text-2xl'></span> Captação Multicanal
                        </h3>
                        <ul class='space-y-2 text-blue-100 text-sm'>
                            <li> Formulários web integrados</li>
                            <li> Landing pages otimizadas</li>
                            <li> Chat ao vivo e chatbot</li>
                            <li> Integração redes sociais</li>
                            <li> Importação de planilhas</li>
                            <li> API para integração externa</li>
                        </ul>
                    </div>
                    
                    <div class='bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20'>
                        <h3 class='text-white font-bold mb-3 flex items-center gap-2'>
                            <span class='text-2xl'></span> Qualificação com IA
                        </h3>
                        <ul class='space-y-2 text-blue-100 text-sm'>
                            <li> Score automático de leads</li>
                            <li> Segmentação inteligente</li>
                            <li> Enriquecimento de dados</li>
                            <li> Previsão de conversão</li>
                            <li> Detecção de duplicatas</li>
                            <li> Análise comportamental</li>
                        </ul>
                    </div>
                </div>
                
                <div class='flex items-center gap-6 mb-6'>
                    <div>
                        <div class='flex items-baseline gap-2'>
                            <span class='text-5xl font-bold text-white'>R$ 800</span>
                            <span class='text-xl text-blue-200'>/mês</span>
                        </div>
                        <p class='text-blue-200 text-sm mt-1'>CRM Completo com todos os módulos</p>
                    </div>
                </div>
                
                <div class='flex gap-4 flex-wrap'>
                    <button disabled class='bg-white text-indigo-700 px-8 py-4 rounded-xl font-bold text-lg shadow-xl opacity-60 cursor-not-allowed'>
                         Trial Em Breve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recursos Detalhados -->
    <div class='grid grid-cols-1 md:grid-cols-3 gap-6'>
        <div class='bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow'>
            <div class='bg-blue-600 w-14 h-14 rounded-xl flex items-center justify-center mb-4 shadow-lg'>
                <span class='text-3xl'></span>
            </div>
            <h3 class='text-xl font-bold text-gray-900 mb-2'>Pipeline Visual</h3>
            <p class='text-gray-600 text-sm mb-4'>
                Visualize todos os leads em um funil interativo. Arraste e solte para mover entre etapas.
            </p>
            <ul class='text-sm text-gray-700 space-y-1'>
                <li> Kanban board customizável</li>
                <li> Etapas personalizadas</li>
                <li> Automação de movimentação</li>
            </ul>
        </div>

        <div class='bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow'>
            <div class='bg-purple-600 w-14 h-14 rounded-xl flex items-center justify-center mb-4 shadow-lg'>
                <span class='text-3xl'></span>
            </div>
            <h3 class='text-xl font-bold text-gray-900 mb-2'>Email Marketing</h3>
            <p class='text-gray-600 text-sm mb-4'>
                Envie campanhas personalizadas e acompanhe métricas de abertura e cliques.
            </p>
            <ul class='text-sm text-gray-700 space-y-1'>
                <li> Templates profissionais</li>
                <li> Disparo em massa</li>
                <li> Rastreamento de resultados</li>
            </ul>
        </div>

        <div class='bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow'>
            <div class='bg-green-600 w-14 h-14 rounded-xl flex items-center justify-center mb-4 shadow-lg'>
                <span class='text-3xl'></span>
            </div>
            <h3 class='text-xl font-bold text-gray-900 mb-2'>Alertas Inteligentes</h3>
            <p class='text-gray-600 text-sm mb-4'>
                Seja notificado sobre leads quentes e oportunidades em tempo real.
            </p>
            <ul class='text-sm text-gray-700 space-y-1'>
                <li> Notificações push</li>
                <li> Email e SMS</li>
                <li> Triggers personalizados</li>
            </ul>
        </div>
    </div>

    <!-- CTA Final -->
    <div class='bg-gradient-to-r from-gray-50 to-blue-50 border-l-4 border-blue-600 rounded-lg p-6'>
        <h3 class='text-lg font-bold text-gray-900 mb-3'> Interessado no CRM completo?</h3>
        <p class='text-gray-700 mb-4'>
            Entre em contato e descubra como nosso CRM pode transformar sua prospecção e aumentar suas vendas.
        </p>
        <div class='flex gap-4 flex-wrap'>
            <a href='mailto:comercial@sgqoti.com' class='inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors'>
                 Email Comercial
            </a>
            <a href='tel:+5511999999999' class='inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors'>
                 WhatsApp
            </a>
        </div>
    </div>
</section>
