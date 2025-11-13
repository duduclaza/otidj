<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<section class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"> Prospecção - CRM</h1>
            <p class="text-gray-600 mt-2">Inserção, controle e mapeamento de leads</p>
        </div>
    </div>

    <!-- Banner Premium -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full"> PREMIUM</span>
                    <span class="px-3 py-1 bg-green-400 text-green-900 text-xs font-bold rounded-full"> TRIAL EM BREVE</span>
                </div>
                <h2 class="text-2xl font-bold">Módulo CRM - Prospecção</h2>
                <p class="text-blue-100 mt-1">Disponível por R$ 900/mês - Trial em breve!</p>
            </div>
            <div>
                <a href="mailto:comercial@sgqoti.com" class="inline-block bg-white text-blue-600 px-6 py-3 rounded-lg font-bold hover:bg-blue-50 transition-colors">
                     Contratar
                </a>
            </div>
        </div>
    </div>

    <!-- Funcionalidades Principais -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-md border-l-4 border-blue-500">
            <div class="text-4xl mb-4"></div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Inserção de Leads</h3>
            <p class="text-gray-600">Cadastro rápido e fácil de novos leads com campos personalizáveis e importação em lote.</p>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-md border-l-4 border-indigo-500">
            <div class="text-4xl mb-4"></div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Controle de Leads</h3>
            <p class="text-gray-600">Acompanhe o status, histórico e interações de cada lead em um painel centralizado.</p>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-md border-l-4 border-purple-500">
            <div class="text-4xl mb-4"></div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Mapeamento</h3>
            <p class="text-gray-600">Visualize a distribuição geográfica e segmentação dos seus leads.</p>
        </div>
    </div>

    <!-- Recursos Incluídos -->
    <div class="bg-gray-50 rounded-xl p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4"> Recursos Incluídos</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-start gap-3">
                <span class="text-green-600 font-bold"></span>
                <span class="text-gray-700">Cadastro ilimitado de leads</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-green-600 font-bold"></span>
                <span class="text-gray-700">Campos personalizáveis</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-green-600 font-bold"></span>
                <span class="text-gray-700">Importação via Excel/CSV</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-green-600 font-bold"></span>
                <span class="text-gray-700">Filtros avançados</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-green-600 font-bold"></span>
                <span class="text-gray-700">Tags e categorias</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-green-600 font-bold"></span>
                <span class="text-gray-700">Relatórios e exportação</span>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-8 text-center text-white">
        <h3 class="text-2xl font-bold mb-2">Pronto para começar?</h3>
        <p class="text-indigo-100 mb-6">Entre em contato para mais informações sobre o módulo CRM</p>
        <div class="flex gap-4 justify-center">
            <a href="mailto:comercial@sgqoti.com" class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-bold hover:bg-indigo-50 transition-colors">
                 Email Comercial
            </a>
            <a href="tel:+5511999999999" class="bg-green-500 text-white px-6 py-3 rounded-lg font-bold hover:bg-green-600 transition-colors">
                 WhatsApp
            </a>
        </div>
    </div>
</section>
