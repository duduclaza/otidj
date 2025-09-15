<!-- Amostragens -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-vial"></i>
            </div>
        </div>
        <div class="stat-value">45</div>
        <div class="stat-label">Amostras Coletadas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value">32</div>
        <div class="stat-label">Analisadas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-value">13</div>
        <div class="stat-label">Em Análise</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-value">2</div>
        <div class="stat-label">Não Conformes</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Controle de Amostragens</h3>
        <button class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nova Amostragem
        </button>
    </div>
    <div class="card-content">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Lote</th>
                        <th>Data Coleta</th>
                        <th>Responsável</th>
                        <th>Status</th>
                        <th>Resultado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>AMO-001</td>
                        <td>Toner HP CF217A</td>
                        <td>LT240315</td>
                        <td>15/03/2024</td>
                        <td>Ana Costa</td>
                        <td><span class="badge success">Conforme</span></td>
                        <td>Aprovado</td>
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
                        <td>AMO-002</td>
                        <td>Papel A4 75g</td>
                        <td>LT240320</td>
                        <td>20/03/2024</td>
                        <td>Carlos Lima</td>
                        <td><span class="badge warning">Em Análise</span></td>
                        <td>Pendente</td>
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
