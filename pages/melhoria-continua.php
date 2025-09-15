<!-- Melhoria Continua -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
        <div class="stat-value">42</div>
        <div class="stat-label">Projetos Ativos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-trophy"></i>
            </div>
        </div>
        <div class="stat-value">28</div>
        <div class="stat-label">Implementados</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-lightbulb"></i>
            </div>
        </div>
        <div class="stat-value">67</div>
        <div class="stat-label">Sugestões</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-percentage"></i>
            </div>
        </div>
        <div class="stat-value">85%</div>
        <div class="stat-label">Taxa de Sucesso</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Projetos de Melhoria</h3>
        <button class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Novo Projeto
        </button>
    </div>
    <div class="card-content">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Projeto</th>
                        <th>Área</th>
                        <th>Responsável</th>
                        <th>Data Início</th>
                        <th>Prazo</th>
                        <th>Progresso</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>MEL-001</td>
                        <td>Otimização Estoque</td>
                        <td>Logística</td>
                        <td>João Silva</td>
                        <td>01/03/2024</td>
                        <td>30/06/2024</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 75%"></div>
                                <span class="progress-text">75%</span>
                            </div>
                        </td>
                        <td><span class="badge success">Em Andamento</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>MEL-002</td>
                        <td>Redução Desperdício</td>
                        <td>Produção</td>
                        <td>Maria Santos</td>
                        <td>15/02/2024</td>
                        <td>15/05/2024</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 100%"></div>
                                <span class="progress-text">100%</span>
                            </div>
                        </td>
                        <td><span class="badge success">Concluído</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Relatório">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button class="btn-icon" title="Resultados">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.progress-bar {
    position: relative;
    width: 100px;
    height: 20px;
    background-color: #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #059669);
    transition: width 0.3s ease;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.75rem;
    font-weight: 600;
    color: #374151;
}
</style>
