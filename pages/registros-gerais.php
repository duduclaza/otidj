<!-- Registros Gerais -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>
        <div class="stat-value">1,247</div>
        <div class="stat-label">Total de Registros</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
        <div class="stat-value">89</div>
        <div class="stat-label">Registros Hoje</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-archive"></i>
            </div>
        </div>
        <div class="stat-value">156</div>
        <div class="stat-label">Arquivados</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="stat-value">23</div>
        <div class="stat-label">Pendentes Revisão</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Registros do Sistema</h3>
        <div class="card-actions">
            <button class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Novo Registro
            </button>
            <button class="btn btn-primary">
                <i class="fas fa-download"></i>
                Exportar
            </button>
        </div>
    </div>
    <div class="card-content">
        <div class="filters-section">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="filter-tipo">Tipo de Registro</label>
                    <select id="filter-tipo">
                        <option value="">Todos</option>
                        <option value="entrada">Entrada</option>
                        <option value="saida">Saída</option>
                        <option value="movimentacao">Movimentação</option>
                        <option value="auditoria">Auditoria</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter-periodo">Período</label>
                    <select id="filter-periodo">
                        <option value="">Todos</option>
                        <option value="hoje">Hoje</option>
                        <option value="semana">Esta Semana</option>
                        <option value="mes">Este Mês</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="search-registro">Pesquisar</label>
                    <input type="text" id="search-registro" placeholder="Digite para pesquisar...">
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Usuário</th>
                        <th>Data/Hora</th>
                        <th>Módulo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>REG-001247</td>
                        <td>Entrada</td>
                        <td>Cadastro de novo toner HP CF217A</td>
                        <td>João Silva</td>
                        <td>15/03/2024 14:30</td>
                        <td>Controle de Toners</td>
                        <td><span class="badge success">Concluído</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Detalhes">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>REG-001246</td>
                        <td>Movimentação</td>
                        <td>Transferência entre estoques</td>
                        <td>Maria Santos</td>
                        <td>15/03/2024 13:15</td>
                        <td>Controle de Toners</td>
                        <td><span class="badge success">Concluído</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Detalhes">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>REG-001245</td>
                        <td>Auditoria</td>
                        <td>Revisão de homologação HOM-001</td>
                        <td>Carlos Lima</td>
                        <td>15/03/2024 11:45</td>
                        <td>Homologações</td>
                        <td><span class="badge warning">Pendente</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Finalizar">
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

<style>
.filters-section {
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    background-color: #f8fafc;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
}

.card-actions {
    display: flex;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .card-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .card-actions .btn {
        justify-content: center;
    }
}
</style>
