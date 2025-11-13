<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="glass-effect rounded-2xl shadow-2xl p-8">
        <!-- Header -->
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold text-white mb-2">
                Solicitar Acesso
            </h2>
            <p class="mt-2 text-center text-sm text-blue-100">
                Preencha os dados abaixo para solicitar acesso ao sistema
            </p>
        </div>
        
        <form id="requestForm" class="mt-8 space-y-6">
            <div class="space-y-4">
                <!-- Nome -->
                <div>
                    <label for="name" class="block text-white text-sm font-medium mb-2">Nome Completo *</label>
                    <input id="name" name="name" type="text" required 
                           class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
                           placeholder="Seu nome completo">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-white text-sm font-medium mb-2">Email *</label>
                    <input id="email" name="email" type="email" required 
                           class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
                           placeholder="seu.email@exemplo.com">
                </div>

                <!-- Senha -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Senha *</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" required minlength="6"
                               class="appearance-none relative block w-full px-3 py-2 pr-10 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="Mínimo 6 caracteres">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg id="eyeIcon" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Confirmar Senha -->
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirmar Senha *</label>
                    <div class="mt-1 relative">
                        <input id="password_confirm" name="password_confirm" type="password" required minlength="6"
                               class="appearance-none relative block w-full px-3 py-2 pr-10 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="Repita a senha">
                        <button type="button" id="togglePasswordConfirm" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg id="eyeIconConfirm" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="passwordMatch" class="mt-1 text-sm hidden">
                        <span id="passwordMatchText"></span>
                    </div>
                </div>

                <!-- Departamento -->
                <div>
                    <label for="setor" class="block text-sm font-medium text-gray-700">Departamento</label>
                    <select id="setor" name="setor" 
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                        <option value="">Selecione um departamento</option>
                        <?php foreach ($departamentos as $dept): ?>
                            <option value="<?= htmlspecialchars($dept['nome']) ?>"><?= htmlspecialchars($dept['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filial -->
                <div>
                    <label for="filial" class="block text-sm font-medium text-gray-700">Filial</label>
                    <select id="filial" name="filial" 
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                        <option value="">Selecione uma filial</option>
                        <?php foreach ($filiais as $filial): ?>
                            <option value="<?= htmlspecialchars($filial['nome']) ?>"><?= htmlspecialchars($filial['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Justificativa -->
                <div>
                    <label for="justificativa" class="block text-sm font-medium text-gray-700">Justificativa *</label>
                    <textarea id="justificativa" name="justificativa" rows="4" required
                              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                              placeholder="Explique por que você precisa de acesso ao sistema..."></textarea>
                </div>
            </div>

            <div>
                <button type="submit" id="submitBtn"
                        class="btn-primary group relative w-full flex justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="submitText">Enviar Solicitação</span>
                    <span id="submitLoading" class="hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Enviando...
                    </span>
                </button>
            </div>

        </form>

        <!-- Link para login -->
        <div class="mt-6 text-center relative z-50">
            <a href="/login" class="login-link" id="loginLink">
                Já tem acesso? Faça login
            </a>
        </div>

        <!-- Mensagens -->
        <div id="message" class="hidden mt-4 p-4 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg id="messageIcon" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <!-- Ícone será alterado via JavaScript -->
                    </svg>
                </div>
                <div class="ml-3">
                    <p id="messageText" class="text-sm font-medium"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos personalizados para formulário de solicitação */
.request-form label {
    color: white !important;
    font-weight: 500;
    margin-bottom: 0.5rem;
    display: block;
    font-size: 0.875rem;
}

.request-form input,
.request-form select,
.request-form textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 0.5rem;
    color: white;
    font-size: 0.875rem;
}

.request-form input::placeholder,
.request-form textarea::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.request-form input:focus,
.request-form select:focus,
.request-form textarea:focus {
    outline: none;
    ring: 2px;
    ring-color: rgba(255, 255, 255, 0.5);
    border-color: transparent;
}

.request-form select option {
    background: #1e40af;
    color: white;
}

.request-form .btn-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border: none;
    box-shadow: 0 4px 15px 0 rgba(37, 99, 235, 0.3);
    color: white;
    font-weight: 600;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    width: 100%;
    transition: all 0.2s;
}

.request-form .btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    transform: translateY(-1px);
    box-shadow: 0 8px 25px 0 rgba(37, 99, 235, 0.4);
}

.request-form .btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Link para login */
.login-link {
    position: relative !important;
    z-index: 9999 !important;
    display: inline-block !important;
    color: #93c5fd !important;
    text-decoration: underline !important;
    font-weight: 500 !important;
    padding: 0.5rem 1rem !important;
    margin: 1rem 0 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}

.login-link:hover {
    color: #dbeafe !important;
    transform: translateY(-1px) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar classe ao formulário
    document.getElementById('requestForm').classList.add('request-form');
    
    // Garantir que o link de login funcione
    const loginLink = document.getElementById('loginLink');
    if (loginLink) {
        loginLink.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Clicou no link de login');
            window.location.href = '/login';
        });
    }
    const form = document.getElementById('requestForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoading = document.getElementById('submitLoading');
    const messageDiv = document.getElementById('message');
    const messageText = document.getElementById('messageText');
    const messageIcon = document.getElementById('messageIcon');

    // Toggle password visibility
    function setupPasswordToggle(toggleId, inputId, iconId) {
        const toggle = document.getElementById(toggleId);
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        toggle.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            if (type === 'text') {
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                `;
            } else {
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        });
    }

    setupPasswordToggle('togglePassword', 'password', 'eyeIcon');
    setupPasswordToggle('togglePasswordConfirm', 'password_confirm', 'eyeIconConfirm');

    // Password match validation
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');
    const passwordMatch = document.getElementById('passwordMatch');
    const passwordMatchText = document.getElementById('passwordMatchText');

    function checkPasswordMatch() {
        if (passwordConfirm.value.length > 0) {
            passwordMatch.classList.remove('hidden');
            if (password.value === passwordConfirm.value) {
                passwordMatchText.textContent = '✓ Senhas coincidem';
                passwordMatchText.className = 'text-green-600';
            } else {
                passwordMatchText.textContent = '✗ Senhas não coincidem';
                passwordMatchText.className = 'text-red-600';
            }
        } else {
            passwordMatch.classList.add('hidden');
        }
    }

    password.addEventListener('input', checkPasswordMatch);
    passwordConfirm.addEventListener('input', checkPasswordMatch);

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validate passwords match
        if (password.value !== passwordConfirm.value) {
            showMessage('As senhas não coincidem', 'error');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitText.classList.add('hidden');
        submitLoading.classList.remove('hidden');

        try {
            const formData = new FormData(form);
            const response = await fetch('/access-request/process', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showMessage(result.message, 'success');
                form.reset();
                passwordMatch.classList.add('hidden');
            } else {
                showMessage(result.message, 'error');
            }
        } catch (error) {
            showMessage('Erro ao enviar solicitação. Tente novamente.', 'error');
        } finally {
            // Hide loading state
            submitBtn.disabled = false;
            submitText.classList.remove('hidden');
            submitLoading.classList.add('hidden');
        }
    });

    function showMessage(message, type) {
        messageText.textContent = message;
        messageDiv.classList.remove('hidden');

        if (type === 'success') {
            messageDiv.className = 'mt-4 p-4 rounded-md bg-green-50 border border-green-200';
            messageText.className = 'text-sm font-medium text-green-800';
            messageIcon.className = 'h-5 w-5 text-green-400';
            messageIcon.innerHTML = `
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            `;
        } else {
            messageDiv.className = 'mt-4 p-4 rounded-md bg-red-50 border border-red-200';
            messageText.className = 'text-sm font-medium text-red-800';
            messageIcon.className = 'h-5 w-5 text-red-400';
            messageIcon.innerHTML = `
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            `;
        }

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 5000);
        }
    }
});
</script>
