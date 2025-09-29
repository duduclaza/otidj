<?php
// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📚 POPs e ITs</h1>
            <p class="text-gray-600 mt-2">Procedimentos Operacionais Padrão e Instruções de Trabalho</p>
        </div>
    </div>

    <!-- Sistema de Abas -->
    <div class="bg-white rounded-lg shadow-sm border">
        <!-- Navegação das Abas -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <!-- Aba 1: Cadastro de Títulos -->
                <?php if ($canViewCadastroTitulos): ?>
                <button id="tab-cadastro" class="tab-button active border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Cadastro de Títulos
                </button>
                <?php endif; ?>

                <!-- Aba 2: Meus Registros -->
                <?php if ($canViewMeusRegistros): ?>
                <button id="tab-registros" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Meus Registros
                </button>
                <?php endif; ?>

                <!-- Aba 3: Pendente Aprovação (Apenas Admin) -->
                <?php if ($canViewPendenteAprovacao): ?>
                <button id="tab-pendentes" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Pendente Aprovação
                </button>
                <?php endif; ?>

                <!-- Aba 4: Visualização -->
                <?php if ($canViewVisualizacao): ?>
                <button id="tab-visualizacao" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Visualização
                </button>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Conteúdo das Abas -->
        <div class="p-6">
            
            <!-- ABA 1: CADASTRO DE TÍTULOS -->
            <?php if ($canViewCadastroTitulos): ?>
            <div id="content-cadastro" class="tab-content">
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Cadastro de Títulos</h3>
                    <p class="text-gray-600 mb-4">Em construção</p>
                    <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Funcionalidade em desenvolvimento
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ABA 2: MEUS REGISTROS -->
            <?php if ($canViewMeusRegistros): ?>
            <div id="content-registros" class="tab-content hidden">
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Meus Registros</h3>
                    <p class="text-gray-600 mb-4">Em construção</p>
                    <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Funcionalidade em desenvolvimento
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ABA 3: PENDENTE APROVAÇÃO (Apenas Admin) -->
            <?php if ($canViewPendenteAprovacao): ?>
            <div id="content-pendentes" class="tab-content hidden">
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Pendente Aprovação</h3>
                    <p class="text-gray-600 mb-4">Em construção</p>
                    <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Funcionalidade em desenvolvimento
                    </div>
                    <div class="mt-4 text-sm text-gray-500">
                        <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 rounded-full">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Acesso restrito a administradores
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ABA 4: VISUALIZAÇÃO -->
            <?php if ($canViewVisualizacao): ?>
            <div id="content-visualizacao" class="tab-content hidden">
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Visualização</h3>
                    <p class="text-gray-600 mb-4">Em construção</p>
                    <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Funcionalidade em desenvolvimento
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Mensagem caso não tenha permissão para nenhuma aba -->
            <?php if (!$canViewCadastroTitulos && !$canViewMeusRegistros && !$canViewPendenteAprovacao && !$canViewVisualizacao): ?>
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Acesso Negado</h3>
                <p class="text-gray-600 mb-4">Você não possui permissão para acessar nenhuma funcionalidade deste módulo.</p>
                <p class="text-sm text-gray-500">Entre em contato com o administrador para solicitar acesso.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Sistema de Abas
document.addEventListener('DOMContentLoaded', function() {
    // Configurar abas
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.id.replace('tab-', '');
            
            // Remover classe ativa de todas as abas
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Adicionar classe ativa na aba clicada
            button.classList.add('active', 'border-blue-500', 'text-blue-600');
            button.classList.remove('border-transparent', 'text-gray-500');
            
            // Esconder todos os conteúdos
            tabContents.forEach(content => content.classList.add('hidden'));
            
            // Mostrar conteúdo da aba ativa
            const activeContent = document.getElementById(`content-${tabId}`);
            if (activeContent) {
                activeContent.classList.remove('hidden');
            }
        });
    });
    
    // Ativar primeira aba disponível se nenhuma estiver ativa
    const firstTab = document.querySelector('.tab-button');
    if (firstTab && !document.querySelector('.tab-button.active')) {
        firstTab.click();
    }
});
</script>




