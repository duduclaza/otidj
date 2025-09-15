<!-- Controle de Toners -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
        <div class="stat-value">247</div>
        <div class="stat-label">Total em Estoque</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-value">189</div>
        <div class="stat-label">Disponíveis</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-value">45</div>
        <div class="stat-label">Estoque Baixo</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        <div class="stat-value">13</div>
        <div class="stat-label">Vencidos</div>
    </div>
</div>

<div class="grid grid-cols-1">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros e Pesquisa</h3>
        </div>
        <div class="card-content">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="search-toner">Pesquisar Toner</label>
                    <input type="text" id="search-toner" placeholder="Digite o modelo ou código..." data-search="#toners-table">
                </div>
                <div class="filter-group">
                    <label for="filter-status">Status</label>
                    <select id="filter-status">
                        <option value="">Todos</option>
                        <option value="disponivel">Disponível</option>
                        <option value="baixo">Estoque Baixo</option>
                        <option value="vencido">Vencido</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter-marca">Marca</label>
                    <select id="filter-marca">
                        <option value="">Todas</option>
                        <option value="hp">HP</option>
                        <option value="canon">Canon</option>
                        <option value="epson">Epson</option>
                        <option value="brother">Brother</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Novo Toner
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Toners</h3>
            <div class="card-actions">
                <button class="btn btn-primary">
                    <i class="fas fa-download"></i>
                    Exportar
                </button>
            </div>
        </div>
        <div class="card-content">
            <div class="table-container">
                <table class="table" id="toners-table">
                    <thead>
                        <tr>
                            <th data-sortable>Código</th>
                            <th data-sortable>Modelo</th>
                            <th data-sortable>Marca</th>
                            <th data-sortable>Cor</th>
                            <th data-sortable>Quantidade</th>
                            <th data-sortable>Validade</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-searchable>
                            <td>TNR-001</td>
                            <td>CF217A</td>
                            <td>HP</td>
                            <td>Preto</td>
                            <td>25</td>
                            <td>15/03/2025</td>
                            <td><span class="badge success">Disponível</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" title="Histórico">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn-icon danger" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-searchable>
                            <td>TNR-002</td>
                            <td>CE285A</td>
                            <td>HP</td>
                            <td>Preto</td>
                            <td>8</td>
                            <td>22/02/2025</td>
                            <td><span class="badge warning">Estoque Baixo</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" title="Histórico">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn-icon danger" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-searchable>
                            <td>TNR-003</td>
                            <td>CRG-045</td>
                            <td>Canon</td>
                            <td>Ciano</td>
                            <td>15</td>
                            <td>10/04/2025</td>
                            <td><span class="badge success">Disponível</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" title="Histórico">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn-icon danger" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-searchable>
                            <td>TNR-004</td>
                            <td>T664120</td>
                            <td>Epson</td>
                            <td>Preto</td>
                            <td>0</td>
                            <td>05/01/2024</td>
                            <td><span class="badge danger">Vencido</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" title="Histórico">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn-icon danger" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}

.filter-group input,
.filter-group select {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.card-actions {
    display: flex;
    gap: 0.5rem;
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
}

.btn-icon {
    width: 32px;
    height: 32px;
    border: none;
    background-color: #f1f5f9;
    color: #64748b;
    border-radius: 0.375rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-icon:hover {
    background-color: #e2e8f0;
    color: #374151;
}

.btn-icon.danger:hover {
    background-color: #fee2e2;
    color: #dc2626;
}

.badge.danger {
    background-color: #fee2e2;
    color: #991b1b;
}

@media (max-width: 768px) {
    .filters-grid {
        grid-template-columns: 1fr;
    }
}
</style>
