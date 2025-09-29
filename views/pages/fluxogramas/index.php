<?php
// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">📊 Fluxogramas</h1>
        <p class="text-gray-600 mt-2">Sistema de gestão de fluxogramas e processos</p>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="text-center">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">🚧 Módulo em Desenvolvimento</h2>
            <p class="text-gray-600 mb-6">O módulo de Fluxogramas está sendo implementado.</p>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-lg font-medium text-blue-900 mb-2">📋 Funcionalidades Planejadas:</h3>
                <ul class="text-left text-blue-800 space-y-1">
                    <li>✅ Cadastro de títulos de fluxogramas</li>
                    <li>✅ Upload de arquivos (PDF, imagens)</li>
                    <li>✅ Sistema de versionamento</li>
                    <li>✅ Aprovação/reprovação por administradores</li>
                    <li>✅ Controle de visibilidade por departamentos</li>
                    <li>✅ Logs de auditoria</li>
                    <li>✅ Sistema de notificações</li>
                </ul>
            </div>
            
            <div class="mt-6">
                <p class="text-sm text-gray-500">
                    <strong>Status:</strong> Estrutura backend completa, interface em desenvolvimento
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Placeholder para funcionalidades futuras
console.log('Módulo Fluxogramas carregado - versão de desenvolvimento');
</script>
