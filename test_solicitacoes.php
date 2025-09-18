<?php
// Teste simples para verificar se a página carrega
session_start();

// Simular dados de sessão para teste
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Usuário de Teste';
$_SESSION['user_email'] = 'teste@empresa.com';

// Dados de teste
$setores = ['TI', 'Qualidade', 'Produção', 'Administrativo'];
$usuarios = [
    ['id' => 1, 'name' => 'Administrador', 'email' => 'admin@empresa.com'],
    ['id' => 2, 'name' => 'João Silva', 'email' => 'joao@empresa.com'],
    ['id' => 3, 'name' => 'Maria Santos', 'email' => 'maria@empresa.com']
];

// Function to safely escape output
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste - Solicitação de Melhorias</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-6xl mx-auto">
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
            ✅ Página de teste carregada com sucesso!
        </div>
        
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Teste - Solicitação de Melhorias</h1>
        
        <!-- Formulário de Teste -->
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Formulário de Teste</h3>
            
            <form class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Usuário (automático) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Usuário</label>
                        <input type="text" value="<?= e($_SESSION['user_name'] ?? 'Usuário') ?>" readonly 
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm bg-gray-100 cursor-not-allowed">
                    </div>

                    <!-- Data (automática) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data da Solicitação</label>
                        <input type="text" value="<?= date('d/m/Y H:i') ?>" readonly 
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm bg-gray-100 cursor-not-allowed">
                    </div>

                    <!-- Setor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Setor *</label>
                        <select name="setor" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">Selecione o setor</option>
                            <?php foreach ($setores as $setor): ?>
                                <option value="<?= e($setor) ?>"><?= e($setor) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status (automático) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <input type="text" value="Pendente" readonly 
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm bg-gray-100 cursor-not-allowed">
                    </div>
                </div>

                <!-- Processo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Processo *</label>
                    <input type="text" name="processo" required 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="Descreva o processo relacionado à melhoria">
                </div>

                <!-- Responsáveis -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Responsáveis *</label>
                    <div class="border border-gray-300 rounded-lg p-4 max-h-48 overflow-y-auto">
                        <?php foreach ($usuarios as $usuario): ?>
                            <label class="flex items-center space-x-2 py-1">
                                <input type="checkbox" name="responsaveis[]" value="<?= $usuario['id'] ?>" 
                                       class="form-checkbox h-4 w-4 text-blue-600 rounded">
                                <span class="text-sm"><?= e($usuario['name']) ?> (<?= e($usuario['email']) ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="alert('Teste OK!')" class="px-6 py-3 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
                        Teste de Envio
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Informações de Debug -->
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
            <h4 class="font-semibold mb-2">Informações de Debug:</h4>
            <ul class="text-sm space-y-1">
                <li>• Sessão iniciada: <?= session_status() === PHP_SESSION_ACTIVE ? 'Sim' : 'Não' ?></li>
                <li>• User ID: <?= $_SESSION['user_id'] ?? 'Não definido' ?></li>
                <li>• User Name: <?= $_SESSION['user_name'] ?? 'Não definido' ?></li>
                <li>• Setores carregados: <?= count($setores) ?></li>
                <li>• Usuários carregados: <?= count($usuarios) ?></li>
            </ul>
        </div>
    </div>
</body>
</html>
