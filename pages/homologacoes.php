<!-- Homologações -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value">89</div>
        <div class="stat-label">Homologações Ativas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-thumbs-up"></i>
            </div>
        </div>
        <div class="stat-value">156</div>
        <div class="stat-label">Aprovadas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-value">12</div>
        <div class="stat-label">Pendentes</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        <div class="stat-value">3</div>
        <div class="stat-label">Rejeitadas</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Gerenciar Homologações</h3>
        <button class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nova Homologação
        </button>
    </div>
    <div class="card-content">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Fornecedor</th>
                        <th>Data Início</th>
                        <th>Data Fim</th>
                        <th>Status</th>
                        <th>Responsável</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>HOM-001</td>
                        <td>Toner HP CF217A</td>
                        <td>Fornecedor A</td>
                        <td>01/01/2024</td>
                        <td>31/12/2024</td>
                        <td><span class="badge success">Aprovada</span></td>
                        <td>João Silva</td>
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
                        <td>HOM-002</td>
                        <td>Papel A4 75g</td>
                        <td>Fornecedor B</td>
                        <td>15/02/2024</td>
                        <td>-</td>
                        <td><span class="badge warning">Pendente</span></td>
                        <td>Maria Santos</td>
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
                </tbody>
            </table>
        </div>
    </div>
</div>
