<!-- FEMEA -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-value">34</div>
        <div class="stat-label">Análises FEMEA</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value">28</div>
        <div class="stat-label">Concluídas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-value">6</div>
        <div class="stat-label">Em Andamento</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-fire"></i>
            </div>
        </div>
        <div class="stat-value">12</div>
        <div class="stat-label">Riscos Críticos</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Análises FEMEA</h3>
        <button class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nova Análise FEMEA
        </button>
    </div>
    <div class="card-content">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Processo</th>
                        <th>Modo de Falha</th>
                        <th>Severidade</th>
                        <th>Ocorrência</th>
                        <th>Detecção</th>
                        <th>RPN</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>FME-001</td>
                        <td>Recebimento Toners</td>
                        <td>Produto Vencido</td>
                        <td>8</td>
                        <td>3</td>
                        <td>2</td>
                        <td class="rpn-high">48</td>
                        <td><span class="badge success">Concluída</span></td>
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
                        <td>FME-002</td>
                        <td>Armazenamento</td>
                        <td>Condições Inadequadas</td>
                        <td>6</td>
                        <td>4</td>
                        <td>3</td>
                        <td class="rpn-medium">72</td>
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

<style>
.rpn-high {
    background-color: #fee2e2;
    color: #991b1b;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}

.rpn-medium {
    background-color: #fef3c7;
    color: #92400e;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}

.rpn-low {
    background-color: #d1fae5;
    color: #065f46;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}
</style>
