<!-- Controle de Descartes -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon blue">
                <i class="fas fa-trash-alt"></i>
            </div>
        </div>
        <div class="stat-value">156</div>
        <div class="stat-label">Itens Descartados</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon green">
                <i class="fas fa-recycle"></i>
            </div>
        </div>
        <div class="stat-value">89</div>
        <div class="stat-label">Reciclados</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon yellow">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-value">23</div>
        <div class="stat-label">Aguardando Descarte</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon red">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-value">5</div>
        <div class="stat-label">Resíduos Perigosos</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Registro de Descartes</h3>
        <button class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Novo Descarte
        </button>
    </div>
    <div class="card-content">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item</th>
                        <th>Tipo</th>
                        <th>Data Descarte</th>
                        <th>Responsável</th>
                        <th>Destino</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>DESC-001</td>
                        <td>Toner HP Vencido</td>
                        <td>Resíduo Químico</td>
                        <td>15/03/2024</td>
                        <td>João Silva</td>
                        <td>Reciclagem Especializada</td>
                        <td><span class="badge success">Concluído</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Certificado">
                                    <i class="fas fa-certificate"></i>
                                </button>
                                <button class="btn-icon" title="Relatório">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>DESC-002</td>
                        <td>Papel Contaminado</td>
                        <td>Resíduo Comum</td>
                        <td>20/03/2024</td>
                        <td>Maria Santos</td>
                        <td>Coleta Seletiva</td>
                        <td><span class="badge warning">Pendente</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="Editar">
                                    <i class="fas fa-edit"></i>
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
