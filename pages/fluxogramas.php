<!-- Fluxogramas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-project-diagram"></i>
            </div>
        </div>
        <div class="stat-value">25</div>
        <div class="stat-label">Fluxogramas Ativos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value">18</div>
        <div class="stat-label">Aprovados</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-edit"></i>
            </div>
        </div>
        <div class="stat-value">4</div>
        <div class="stat-label">Em Revisão</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-value">3</div>
        <div class="stat-label">Pendentes</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Biblioteca de Fluxogramas</h3>
        <button class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Novo Fluxograma
        </button>
    </div>
    <div class="card-content">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Processo</th>
                        <th>Versão</th>
                        <th>Data Criação</th>
                        <th>Responsável</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>FLX-001</td>
                        <td>Processo de Recebimento</td>
                        <td>Logística</td>
                        <td>1.2</td>
                        <td>10/01/2024</td>
                        <td>Ana Silva</td>
                        <td><span class="badge success">Aprovado</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn-icon" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>FLX-002</td>
                        <td>Controle de Qualidade</td>
                        <td>Qualidade</td>
                        <td>2.0</td>
                        <td>25/02/2024</td>
                        <td>Carlos Santos</td>
                        <td><span class="badge warning">Em Revisão</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon" title="Aprovar">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
