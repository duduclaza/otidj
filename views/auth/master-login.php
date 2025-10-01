<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Access - SGQ OTI DJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="glass-effect rounded-2xl shadow-2xl p-8 relative">
            <!-- Back Button -->
            <a href="/login" class="absolute top-4 left-4 text-white hover:text-gray-200">
                ← Voltar
            </a>

            <!-- Logo/Header -->
            <div class="text-center mb-6 mt-8">
                <div class="text-6xl mb-4">🔐</div>
                <h1 class="text-3xl font-bold text-white mb-2">Master Access</h1>
                <p class="text-blue-100 text-sm">Acesso Administrativo Avançado</p>
            </div>

            <!-- Master Login Form -->
            <form id="masterLoginForm" class="space-y-6">
                <div>
                    <label class="block text-white text-sm font-medium mb-2">Email Master</label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                           placeholder="master@email.com">
                </div>

                <div>
                    <label class="block text-white text-sm font-medium mb-2">Senha Master</label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                           placeholder="••••••••">
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 shadow-lg">
                    🔓 Acessar Painel Master
                </button>
            </form>

            <!-- Warning -->
            <div class="mt-6 bg-red-500 bg-opacity-20 border border-red-300 border-opacity-30 rounded-lg p-3">
                <p class="text-red-100 text-xs text-center">
                    ⚠️ Área restrita. Acesso não autorizado será registrado.
                </p>
            </div>

            <!-- Loading Overlay -->
            <div id="masterLoading" class="hidden absolute inset-0 bg-black bg-opacity-50 rounded-2xl flex items-center justify-center">
                <div class="text-white text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mx-auto mb-2"></div>
                    <div class="text-sm">Verificando credenciais...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('masterLoginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const loading = document.getElementById('masterLoading');
        const formData = new FormData(this);
        
        loading.classList.remove('hidden');
        
        fetch('/master/auth', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            loading.classList.add('hidden');
            
            if (result.success) {
                window.location.href = '/master/dashboard';
            } else {
                alert(result.message || 'Credenciais inválidas');
            }
        })
        .catch(error => {
            loading.classList.add('hidden');
            alert('Erro de conexão. Tente novamente.');
        });
    });
    </script>
</body>
</html>
