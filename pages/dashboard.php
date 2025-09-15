<!-- Dashboard Content -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-print"></i>
            </div>
        </div>
        <div class="stat-value">247</div>
        <div class="stat-label">Toners em Estoque</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value">89</div>
        <div class="stat-label">Homologações Ativas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-vial"></i>
            </div>
        </div>
        <div class="stat-value">15</div>
        <div class="stat-label">Amostragens Pendentes</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-value">3</div>
        <div class="stat-label">Itens Vencendo</div>
    </div>
</div>

<div class="grid grid-cols-2">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Atividades Recentes</h3>
            <a href="#" class="btn btn-primary">Ver Todas</a>
        </div>
        <div class="card-content">
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon blue">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="activity-content">
                        <p><strong>Novo toner cadastrado</strong></p>
                        <p class="activity-time">Há 2 horas</p>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon green">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="activity-content">
                        <p><strong>Homologação aprovada</strong></p>
                        <p class="activity-time">Há 4 horas</p>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon yellow">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="activity-content">
                        <p><strong>POP atualizado</strong></p>
                        <p class="activity-time">Ontem</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Alertas do Sistema</h3>
        </div>
        <div class="card-content">
            <div class="alert-list">
                <div class="alert-item warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <p><strong>Toners próximos do vencimento</strong></p>
                        <p>3 itens vencem em 7 dias</p>
                    </div>
                </div>
                
                <div class="alert-item info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <p><strong>Amostragem pendente</strong></p>
                        <p>15 amostras aguardando análise</p>
                    </div>
                </div>
                
                <div class="alert-item success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <p><strong>Sistema atualizado</strong></p>
                        <p>Última atualização: hoje</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Resumo Mensal</h3>
    </div>
    <div class="card-content">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Módulo</th>
                        <th>Itens Processados</th>
                        <th>Pendentes</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Controle de Toners</td>
                        <td>156</td>
                        <td>12</td>
                        <td><span class="badge success">Ativo</span></td>
                    </tr>
                    <tr>
                        <td>Homologações</td>
                        <td>89</td>
                        <td>5</td>
                        <td><span class="badge success">Ativo</span></td>
                    </tr>
                    <tr>
                        <td>Amostragens</td>
                        <td>45</td>
                        <td>15</td>
                        <td><span class="badge warning">Atenção</span></td>
                    </tr>
                    <tr>
                        <td>FEMEA</td>
                        <td>23</td>
                        <td>2</td>
                        <td><span class="badge success">Ativo</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background-color: #f8fafc;
    border-radius: 0.5rem;
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
}

.activity-icon.blue { background-color: #3b82f6; }
.activity-icon.green { background-color: #10b981; }
.activity-icon.yellow { background-color: #f59e0b; }

.activity-content p {
    margin: 0;
}

.activity-time {
    font-size: 0.75rem;
    color: #64748b;
}

.alert-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.alert-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    border-radius: 0.5rem;
    border-left: 4px solid;
}

.alert-item.warning {
    background-color: #fef3c7;
    border-left-color: #f59e0b;
}

.alert-item.info {
    background-color: #dbeafe;
    border-left-color: #3b82f6;
}

.alert-item.success {
    background-color: #d1fae5;
    border-left-color: #10b981;
}

.alert-item i {
    font-size: 1.25rem;
}

.alert-item.warning i { color: #f59e0b; }
.alert-item.info i { color: #3b82f6; }
.alert-item.success i { color: #10b981; }

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge.success {
    background-color: #d1fae5;
    color: #065f46;
}

.badge.warning {
    background-color: #fef3c7;
    color: #92400e;
}
</style>
