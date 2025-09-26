<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 -m-6">
    <div class="max-w-2xl mx-auto text-center px-6">
        <!-- Ícone principal -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full shadow-2xl">
                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"></path>
                </svg>
            </div>
        </div>

        <!-- Título principal -->
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
            <?= e($title ?? 'Módulo') ?>
        </h1>

        <!-- Subtítulo -->
        <h2 class="text-xl md:text-2xl font-semibold text-blue-600 mb-6">
            Em Breve Disponível
        </h2>

        <!-- Descrição -->
        <p class="text-lg text-gray-600 mb-8 leading-relaxed">
            Estamos trabalhando duro para trazer este módulo para você! <br>
            Nossa equipe está construindo uma experiência incrível que estará disponível em breve.
        </p>

        <!-- Ícones de progresso -->
        <div class="flex justify-center items-center space-x-8 mb-8">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <span class="text-sm text-gray-600">Planejamento</span>
            </div>
            
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-2 animate-pulse">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <span class="text-sm text-gray-600">Desenvolvimento</span>
            </div>
            
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-sm text-gray-600">Lançamento</span>
            </div>
        </div>

        <!-- Card informativo -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex items-center justify-center mb-4">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">O que esperar?</h3>
            </div>
            <p class="text-gray-600">
                Este módulo fará parte do nosso sistema completo de gestão da qualidade, 
                oferecendo funcionalidades avançadas e uma interface intuitiva para 
                otimizar seus processos de trabalho.
            </p>
        </div>

        <!-- Botão de voltar -->
        <div class="space-y-4">
            <button onclick="history.back()" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </button>
            
            <p class="text-sm text-gray-500">
                Enquanto isso, explore os outros módulos disponíveis no menu lateral.
            </p>
        </div>

        <!-- Rodapé com logo -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <div class="flex items-center justify-center space-x-2 text-gray-500">
                <div class="w-6 h-6 bg-blue-600 rounded flex items-center justify-center">
                    <span class="text-white text-xs font-bold">SGQ</span>
                </div>
                <span class="text-sm">Sistema de Gestão da Qualidade - OTI DJ</span>
            </div>
        </div>
    </div>
</section>
