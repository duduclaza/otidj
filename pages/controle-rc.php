<!-- Controle de RC -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </div>
        <div class="stat-value">156</div>
        <div class="stat-label">RCs Registrados</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value">134</div>
        <div class="stat-label">Resolvidos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-value">18</div>
        <div class="stat-label">Em Andamento</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-value">4</div>
        <div class="stat-label">Críticos</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Registro de Não Conformidades</h3>
        <button class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nova RC
        </button>
    </div>
    <div class="card-content">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descrição</th>
                        <th>Área</th>
                        <th>Criticidade</th>
                        <th>Data Abertura</th>
                        <th>Responsável</th>
                        <th>Prazo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>RC-001</td>
                        <td>Toner fora da validade</td>
                        <td>Estoque</td>
                        <td><span class="badge danger">Alta</span></td>
                        <td>15/03/2024</td>
                        <td>João Silva</td>
                        <td>20/03/2024</td>
                        <td><span class="badge success">Resolvido</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Relatório">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>RC-002</td>
                        <td>Procedimento não seguido</td>
                        <td>Qualidade</td>
                        <td><span class="badge warning">Média</span></td>
                        <td>22/03/2024</td>
                        <td>Maria Santos</td>
                        <td>30/03/2024</td>
                        <td><span class="badge warning">Em Andamento</span></td>
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
