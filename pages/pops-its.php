<!-- POPs e ITs -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>
        <div class="stat-value">67</div>
        <div class="stat-label">POPs Ativos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
        <div class="stat-value">43</div>
        <div class="stat-label">ITs Vigentes</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-value">8</div>
        <div class="stat-label">Aguardando Revisão</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-value">5</div>
        <div class="stat-label">Vencidos</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Documentos POPs e ITs</h3>
        <button class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Novo Documento
        </button>
    </div>
    <div class="card-content">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Versão</th>
                        <th>Data Criação</th>
                        <th>Próxima Revisão</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>POP-001</td>
                        <td>Recebimento de Materiais</td>
                        <td>POP</td>
                        <td>2.1</td>
                        <td>15/01/2024</td>
                        <td>15/01/2025</td>
                        <td><span class="badge success">Vigente</span></td>
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
                        <td>IT-001</td>
                        <td>Calibração de Balanças</td>
                        <td>IT</td>
                        <td>1.3</td>
                        <td>20/02/2024</td>
                        <td>20/08/2024</td>
                        <td><span class="badge warning">Revisão Pendente</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn-icon" title="Revisar">
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
