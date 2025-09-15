<!-- Garantias -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
        <div class="stat-value">127</div>
        <div class="stat-label">Garantias Ativas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value">89</div>
        <div class="stat-label">Válidas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-value">23</div>
        <div class="stat-label">Vencendo</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        <div class="stat-value">15</div>
        <div class="stat-label">Vencidas</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Controle de Garantias</h3>
        <button class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nova Garantia
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
                        <th>Período</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>GAR-001</td>
                        <td>Impressora HP LaserJet</td>
                        <td>HP Brasil</td>
                        <td>01/01/2024</td>
                        <td>01/01/2026</td>
                        <td>24 meses</td>
                        <td><span class="badge success">Válida</span></td>
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
                        <td>GAR-002</td>
                        <td>Scanner Canon</td>
                        <td>Canon Brasil</td>
                        <td>15/06/2023</td>
                        <td>15/06/2024</td>
                        <td>12 meses</td>
                        <td><span class="badge danger">Vencida</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Renovar">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
