<!-- Registros Gerais -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Registros Gerais</h3>
    </div>
    <div class="card-content">
        <!-- Sistema de Abas -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active" data-tab="filiais">
                    <i class="fas fa-building"></i>
                    Cadastro de Filiais
                </button>
                <button class="tab-button" data-tab="departamentos">
                    <i class="fas fa-sitemap"></i>
                    Cadastro de Departamentos
                </button>
                <button class="tab-button" data-tab="fornecedores">
                    <i class="fas fa-truck"></i>
                    Cadastro de Fornecedores
                </button>
                <button class="tab-button" data-tab="debug">
                    <i class="fas fa-bug"></i>
                    Debug & Relatório
                </button>
                <button class="tab-button" data-tab="parametros">
                    <i class="fas fa-cogs"></i>
                    Parâmetros de Retornados
                </button>
            </div>

            <!-- Aba Filiais -->
            <div class="tab-content active" id="filiais">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-building text-primary mr-2"></i>
                        Cadastro de Filiais
                    </h4>
                    <form id="form-filiais" class="space-y-4">
                        <div class="form-group">
                            <label for="nome-filial" class="block text-sm font-medium text-gray-700 mb-2">Nome da Filial</label>
                            <input type="text" id="nome-filial" name="nome_filial" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <button type="submit" class="bg-primary hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Adicionar Filial
                        </button>
                    </form>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h5 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-list text-info mr-2"></i>
                        Filiais Cadastradas
                    </h5>
                    <div class="grid gap-2" id="lista-filiais">
                        <?php
                        try {
                            $db = getDB();
                            $filiais = $db->fetchAll("SELECT * FROM filiais ORDER BY nome");
                            foreach ($filiais as $filial) {
                                echo '<div class="bg-gray-50 border border-gray-200 rounded-md p-3 hover:bg-gray-100 transition duration-200">' . htmlspecialchars($filial['nome']) . '</div>';
                            }
                        } catch (Exception $e) {
                            echo '<div class="bg-red-50 border border-red-200 text-red-700 rounded-md p-3">Erro ao carregar filiais</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Aba Departamentos -->
            <div class="tab-content" id="departamentos">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-sitemap text-primary mr-2"></i>
                        Cadastro de Departamentos
                    </h4>
                    <form id="form-departamentos" class="space-y-4">
                        <div class="form-group">
                            <label for="nome-departamento" class="block text-sm font-medium text-gray-700 mb-2">Nome do Departamento</label>
                            <input type="text" id="nome-departamento" name="nome_departamento" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <button type="submit" class="bg-primary hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Adicionar Departamento
                        </button>
                    </form>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h5 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-list text-info mr-2"></i>
                        Departamentos Cadastrados
                    </h5>
                    <div class="grid gap-2" id="lista-departamentos">
                        <?php
                        try {
                            $db = getDB();
                            $departamentos = $db->fetchAll("SELECT * FROM departamentos ORDER BY nome");
                            foreach ($departamentos as $departamento) {
                                echo '<div class="bg-gray-50 border border-gray-200 rounded-md p-3 hover:bg-gray-100 transition duration-200">' . htmlspecialchars($departamento['nome']) . '</div>';
                            }
                        } catch (Exception $e) {
                            echo '<div class="bg-red-50 border border-red-200 text-red-700 rounded-md p-3">Erro ao carregar departamentos</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Aba Fornecedores -->
            <div class="tab-content" id="fornecedores">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-truck text-primary mr-2"></i>
                        Cadastro de Fornecedores
                    </h4>
                    <form id="form-fornecedores" class="space-y-4">
                        <div class="form-group">
                            <label for="nome-fornecedor" class="block text-sm font-medium text-gray-700 mb-2">Nome do Fornecedor</label>
                            <input type="text" id="nome-fornecedor" name="nome_fornecedor" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label for="contato-fornecedor" class="block text-sm font-medium text-gray-700 mb-2">Contato</label>
                                <input type="text" id="contato-fornecedor" name="contato" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div class="form-group">
                                <label for="email-fornecedor" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" id="email-fornecedor" name="email" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                        <button type="submit" class="bg-primary hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Adicionar Fornecedor
                        </button>
                    </form>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h5 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-list text-info mr-2"></i>
                        Fornecedores Cadastrados
                    </h5>
                    <div class="grid gap-3" id="lista-fornecedores">
                        <?php
                        try {
                            $db = getDB();
                            $fornecedores = $db->fetchAll("SELECT * FROM fornecedores ORDER BY nome");
                            foreach ($fornecedores as $fornecedor) {
                                echo '<div class="bg-gray-50 border border-gray-200 rounded-md p-4 hover:bg-gray-100 transition duration-200">';
                                echo '<h6 class="font-semibold text-gray-800 mb-2">' . htmlspecialchars($fornecedor['nome']) . '</h6>';
                                if (!empty($fornecedor['contato'])) {
                                    echo '<p class="text-sm text-gray-600 mb-1"><i class="fas fa-phone mr-1"></i>Contato: ' . htmlspecialchars($fornecedor['contato']) . '</p>';
                                }
                                if (!empty($fornecedor['email'])) {
                                    echo '<p class="text-sm text-gray-600"><i class="fas fa-envelope mr-1"></i>Email: ' . htmlspecialchars($fornecedor['email']) . '</p>';
                                }
                                echo '</div>';
                            }
                        } catch (Exception $e) {
                            echo '<div class="bg-red-50 border border-red-200 text-red-700 rounded-md p-3">Erro ao carregar fornecedores</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Aba Debug -->
            <div class="tab-content" id="debug">
                <?php
                // Inclui o sistema de debug
                require_once __DIR__ . '/../debug_report.php';
                $debugSystem = new DebugReport();
                $debugReport = $debugSystem->generateReport();
                echo $debugSystem->renderHTML($debugReport);
                ?>
            </div>

            <!-- Aba Parâmetros -->
            <div class="tab-content" id="parametros">
                <div class="form-section">
                    <h4>Parâmetros de Retornados</h4>
                    <p class="info-text">Configure os parâmetros que definem o destino dos toners retornados baseado na porcentagem restante.</p>
                    
                    <div class="parametros-grid">
                        <div class="parametro-card">
                            <h5>Destino Descarte</h5>
                            <div class="form-group">
                                <label>Porcentagem</label>
                                <input type="text" value="≤ 5%" readonly>
                            </div>
                            <div class="form-group">
                                <label>Orientação</label>
                                <textarea readonly>Descarte o Toner.</textarea>
                            </div>
                        </div>

                        <div class="parametro-card">
                            <h5>Uso Interno</h5>
                            <div class="form-group">
                                <label>Porcentagem</label>
                                <input type="text" value="≥ 6% e ≤ 39%" readonly>
                            </div>
                            <div class="form-group">
                                <label>Orientação</label>
                                <textarea readonly>Teste o Toner se a qualidade estiver boa utilize internamente para testes se estiver ruim descarte.</textarea>
                            </div>
                        </div>

                        <div class="parametro-card">
                            <h5>Estoque Semi Novo</h5>
                            <div class="form-group">
                                <label>Porcentagem</label>
                                <input type="text" value="≥ 40% e ≤ 89%" readonly>
                            </div>
                            <div class="form-group">
                                <label>Orientação</label>
                                <textarea readonly>Teste o Toner se a qualidade estiver boa envie para o estoque como seminovo e marque a % na caixa para a logística ver e se estiver ruim solicite garantia.</textarea>
                            </div>
                        </div>

                        <div class="parametro-card">
                            <h5>Estoque Novo</h5>
                            <div class="form-group">
                                <label>Porcentagem</label>
                                <input type="text" value="≥ 90%" readonly>
                            </div>
                            <div class="form-group">
                                <label>Orientação</label>
                                <textarea readonly>Teste o Toner se a qualidade estiver boa envie para o estoque como novo e marque na caixa que é novo para a logística ver e se estiver ruim solicite garantia.</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Sistema de Abas */
.tabs-container {
    width: 100%;
}

.tabs-nav {
    display: flex;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 2rem;
    overflow-x: auto;
    gap: 0.5rem;
}

.tab-button {
    background: none;
    border: none;
    padding: 1rem 1.5rem;
    cursor: pointer;
    border-radius: 0.5rem 0.5rem 0 0;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #64748b;
    white-space: nowrap;
}

.tab-button:hover {
    background-color: #f1f5f9;
    color: #334155;
}

.tab-button.active {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-bottom: 3px solid #1d4ed8;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Formulários */
.form-section {
    background: #f8fafc;
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    border: 1px solid #e2e8f0;
}

.form-section h4 {
    margin-bottom: 1.5rem;
    color: #1e293b;
    font-size: 1.25rem;
    font-weight: 600;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
    min-height: 80px;
    resize: vertical;
}

/* Listas */
.list-section {
    background: white;
    padding: 1.5rem;
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
}

.list-section h5 {
    margin-bottom: 1rem;
    color: #1e293b;
    font-size: 1.1rem;
    font-weight: 600;
}

.items-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.75rem;
}

.items-list .item {
    background: #f1f5f9;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s;
}

.items-list .item:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}

/* Fornecedores */
.fornecedores-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.fornecedor-item {
    background: #f8fafc;
    padding: 1.5rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
}

.fornecedor-header {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.fornecedor-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    font-size: 0.875rem;
    color: #64748b;
}

/* Parâmetros */
.parametros-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.parametro-card {
    background: white;
    padding: 1.5rem;
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.parametro-card h5 {
    margin-bottom: 1rem;
    color: #1e293b;
    font-size: 1.1rem;
    font-weight: 600;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e2e8f0;
}

.info-text {
    background: #eff6ff;
    padding: 1rem;
    border-radius: 0.5rem;
    color: #1e40af;
    margin-bottom: 2rem;
    border-left: 4px solid #3b82f6;
}

/* Responsivo */
@media (max-width: 768px) {
    .tabs-nav {
        flex-direction: column;
    }
    
    .tab-button {
        border-radius: 0.5rem;
        margin-bottom: 0.25rem;
    }
    
    .form-section {
        padding: 1.5rem;
    }
    
    .items-list {
        grid-template-columns: 1fr;
    }
    
    .parametros-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Sistema de Abas
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });

    // Formulário de Filiais
    document.getElementById('form-filiais').addEventListener('submit', function(e) {
        e.preventDefault();
        const nomeFilial = document.getElementById('nome-filial').value;
        if (nomeFilial.trim()) {
            adicionarItem('lista-filiais', nomeFilial);
            this.reset();
        }
    });

    // Formulário de Departamentos
    document.getElementById('form-departamentos').addEventListener('submit', function(e) {
        e.preventDefault();
        const nomeDepartamento = document.getElementById('nome-departamento').value;
        if (nomeDepartamento.trim()) {
            adicionarItem('lista-departamentos', nomeDepartamento);
            this.reset();
        }
    });

    // Formulário de Fornecedores
    document.getElementById('form-fornecedores').addEventListener('submit', function(e) {
        e.preventDefault();
        const nome = document.getElementById('nome-fornecedor').value;
        const contato = document.getElementById('contato-fornecedor').value;
        const rma = document.getElementById('rma-fornecedor').value;
        
        if (nome.trim()) {
            adicionarFornecedor(nome, contato, rma);
            this.reset();
        }
    });

    function adicionarItem(listaId, texto) {
        const lista = document.getElementById(listaId);
        const item = document.createElement('div');
        item.className = 'item';
        item.innerHTML = `
            ${texto}
            <button onclick="this.parentElement.remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;">
                <i class="fas fa-trash"></i>
            </button>
        `;
        lista.appendChild(item);
    }

    function adicionarFornecedor(nome, contato, rma) {
        const lista = document.getElementById('lista-fornecedores');
        const item = document.createElement('div');
        item.className = 'fornecedor-item';
        item.innerHTML = `
            <div class="fornecedor-header">${nome}</div>
            <div class="fornecedor-info">
                <div><strong>Contato:</strong> ${contato || 'Não informado'}</div>
                <div><strong>RMA:</strong> ${rma || 'Não informado'}</div>
            </div>
            <button onclick="this.parentElement.remove()" style="position:absolute;top:10px;right:10px;background:none;border:none;color:#ef4444;cursor:pointer;">
                <i class="fas fa-trash"></i>
            </button>
        `;
        item.style.position = 'relative';
        lista.appendChild(item);
    }
});
</script>
