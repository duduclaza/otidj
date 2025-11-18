<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Homologação - <?= htmlspecialchars($homologacao['cod_referencia']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .relatorio-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
        }
        
        .info-box h3 {
            margin: 0 0 15px 0;
            color: #1e40af;
            font-size: 18px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 8px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #374151;
            display: inline-block;
            min-width: 120px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-aprovado { background: #dcfce7; color: #166534; }
        .status-reprovado { background: #fee2e2; color: #dc2626; }
        .status-em-andamento { background: #fef3c7; color: #92400e; }
        
        .timeline {
            margin: 30px 0;
        }
        
        .timeline-item {
            display: flex;
            margin-bottom: 25px;
            position: relative;
        }
        
        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 20px;
            top: 45px;
            width: 2px;
            height: calc(100% + 10px);
            background: #e2e8f0;
        }
        
        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            margin-right: 20px;
            flex-shrink: 0;
            z-index: 1;
            position: relative;
        }
        
        .timeline-content {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
        }
        
        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .timeline-title {
            font-weight: bold;
            color: #1e40af;
            font-size: 16px;
        }
        
        .timeline-date {
            color: #6b7280;
            font-size: 14px;
        }
        
        .timeline-details {
            margin-top: 10px;
        }
        
        .dados-etapa {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .dados-etapa h4 {
            margin: 0 0 10px 0;
            color: #374151;
            font-size: 14px;
            font-weight: bold;
        }
        
        .campo-valor {
            display: flex;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .campo-nome {
            font-weight: bold;
            color: #4b5563;
            min-width: 150px;
            margin-right: 15px;
        }
        
        .campo-valor-text {
            color: #374151;
            flex: 1;
        }
        
        .anexos-lista {
            margin-top: 15px;
        }
        
        .anexo-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: #f3f4f6;
            border-radius: 6px;
            margin-bottom: 8px;
        }
        
        .anexo-icon {
            margin-right: 10px;
            font-size: 16px;
        }
        
        .tempo-etapa {
            background: #eff6ff;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .resumo-final {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: 2px solid #3b82f6;
            border-radius: 8px;
            padding: 25px;
            margin-top: 30px;
            text-align: center;
        }
        
        .resumo-final h2 {
            margin: 0 0 15px 0;
            color: #1e40af;
            font-size: 24px;
        }
        
        .tempo-total {
            font-size: 18px;
            color: #374151;
            margin: 10px 0;
        }
        
        @media print {
            body { background: white; }
            .relatorio-container { box-shadow: none; }
            .no-print { display: none; }
        }
        
        .btn-print {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px 0;
        }
        
        .btn-print:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="relatorio-container">
        <!-- Header -->
        <div class="header">
            <h1>📋 Relatório de Homologação</h1>
            <p>Sistema de Gestão da Qualidade - OTI DJ</p>
        </div>
        
        <!-- Botão de Impressão -->
        <div class="content no-print">
            <button onclick="window.print()" class="btn-print">🖨️ Imprimir Relatório</button>
        </div>
        
        <!-- Conteúdo Principal -->
        <div class="content">
            <!-- Informações Gerais -->
            <div class="info-grid">
                <div class="info-box">
                    <h3>📦 Dados do Material</h3>
                    <div class="info-item">
                        <span class="info-label">Código:</span>
                        <strong><?= htmlspecialchars($homologacao['cod_referencia']) ?></strong>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Descrição:</span>
                        <?= nl2br(htmlspecialchars($homologacao['descricao'])) ?>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="status-badge status-<?= $homologacao['status'] === 'aprovado' ? 'aprovado' : ($homologacao['status'] === 'reprovado' ? 'reprovado' : 'em-andamento') ?>">
                            <?= ucfirst(str_replace('_', ' ', $homologacao['status'])) ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-box">
                    <h3>👤 Informações do Processo</h3>
                    <div class="info-item">
                        <span class="info-label">Criado por:</span>
                        <?= htmlspecialchars($homologacao['criador_nome'] ?? 'N/A') ?>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Departamento:</span>
                        <?= htmlspecialchars($homologacao['departamento_nome'] ?? 'N/A') ?>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Data Início:</span>
                        <?= htmlspecialchars($homologacao['data_inicio_br'] ?? date('d/m/Y H:i', strtotime($homologacao['created_at']))) ?>
                    </div>
                    <?php if ($homologacao['data_finalizacao']): ?>
                    <div class="info-item">
                        <span class="info-label">Data Finalização:</span>
                        <?= htmlspecialchars($homologacao['data_finalizacao_br'] ?? date('d/m/Y H:i', strtotime($homologacao['data_finalizacao']))) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Timeline do Processo -->
            <div class="timeline">
                <h2 style="color: #1e40af; margin-bottom: 25px;">📈 Histórico Detalhado do Processo</h2>
                
                <?php 
                $etapaNumero = 1;
                foreach ($historico as $item): 
                    $tempoFormatado = '';
                    if ($item['tempo_etapa']) {
                        $horas = floor($item['tempo_etapa'] / 60);
                        $minutos = $item['tempo_etapa'] % 60;
                        $tempoFormatado = $horas > 0 ? "{$horas}h {$minutos}min" : "{$minutos}min";
                    }
                ?>
                <div class="timeline-item">
                    <div class="timeline-marker"><?= $etapaNumero++ ?></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <div class="timeline-title">
                                <?= htmlspecialchars($item['acao_realizada'] ?? 'Mudança de status') ?>
                                <?php if ($tempoFormatado): ?>
                                    <span class="tempo-etapa">⏱️ <?= $tempoFormatado ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="timeline-date">
                                <?= htmlspecialchars($item['data_acao_br'] ?? date('d/m/Y H:i', strtotime($item['data_acao_real'] ?? $item['created_at']))) ?>
                            </div>
                        </div>
                        
                        <div class="timeline-details">
                            <p><strong>Responsável:</strong> <?= htmlspecialchars($item['usuario_nome'] ?? 'Usuário não identificado') ?></p>
                            
                            <?php if ($item['detalhes_acao']): ?>
                                <p><strong>Detalhes:</strong> <?= htmlspecialchars($item['detalhes_acao']) ?></p>
                            <?php endif; ?>
                            
                            <?php if ($item['observacoes']): ?>
                                <p><strong>Observações:</strong> <?= nl2br(htmlspecialchars($item['observacoes'])) ?></p>
                            <?php endif; ?>
                            
                            <!-- Dados específicos da etapa -->
                            <?php if (isset($etapasOrganizadas[$item['etapa_nova']])): ?>
                                <div class="dados-etapa">
                                    <h4>📋 Dados Registrados na Etapa:</h4>
                                    <?php foreach ($etapasOrganizadas[$item['etapa_nova']] as $dado): ?>
                                        <div class="campo-valor">
                                            <div class="campo-nome"><?= htmlspecialchars($this->formatarNomeCampo($dado['campo'])) ?>:</div>
                                            <div class="campo-valor-text"><?= nl2br(htmlspecialchars($dado['valor'])) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Anexos da etapa -->
                            <?php if (isset($anexosOrganizados[$item['etapa_nova']])): ?>
                                <div class="anexos-lista">
                                    <h4>📎 Anexos Adicionados:</h4>
                                    <?php foreach ($anexosOrganizados[$item['etapa_nova']] as $anexo): ?>
                                        <div class="anexo-item">
                                            <span class="anexo-icon">
                                                <?= strpos($anexo['tipo_arquivo'], 'image') !== false ? '🖼️' : 
                                                   (strpos($anexo['tipo_arquivo'], 'pdf') !== false ? '📄' : '📎') ?>
                                            </span>
                                            <span><?= htmlspecialchars($anexo['nome_arquivo']) ?></span>
                                            <span style="margin-left: auto; color: #6b7280; font-size: 12px;">
                                                <?= number_format($anexo['tamanho_bytes'] / 1024, 1) ?> KB
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Resumo Final -->
            <div class="resumo-final">
                <h2>📊 Resumo Final</h2>
                <div class="tempo-total">
                    <strong>⏱️ Tempo Total do Processo:</strong> 
                    <?= $tempoTotal->days ?> dias, <?= $tempoTotal->h ?> horas e <?= $tempoTotal->i ?> minutos
                </div>
                <div style="margin-top: 15px;">
                    <strong>🎯 Status Final:</strong>
                    <span class="status-badge status-<?= $homologacao['status'] === 'aprovado' ? 'aprovado' : ($homologacao['status'] === 'reprovado' ? 'reprovado' : 'em-andamento') ?>">
                        <?= ucfirst(str_replace('_', ' ', $homologacao['status'])) ?>
                    </span>
                </div>
                
                <?php if ($homologacao['resultado_final']): ?>
                <div style="margin-top: 15px; text-align: left;">
                    <strong>📝 Resultado Final:</strong><br>
                    <?= nl2br(htmlspecialchars($homologacao['resultado_final'])) ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Rodapé -->
            <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #e2e8f0; text-align: center; color: #6b7280;">
                <p>📄 Relatório gerado automaticamente pelo Sistema SGQ OTI DJ</p>
                <p>🕐 Data/Hora: <?= date('d/m/Y H:i:s') ?></p>
                <p>👤 Gerado por: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Sistema') ?></p>
            </div>
        </div>
    </div>
</body>
</html>
