<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melhoria Contínua #<?= $melhoria['id'] ?? 'N/A' ?> - SGQ OTI DJ</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header .subtitle {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .info-item {
            border: 1px solid #e5e7eb;
            padding: 12px;
            border-radius: 6px;
            background: #f9fafb;
        }
        
        .info-item label {
            font-weight: bold;
            color: #374151;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
        }
        
        .info-item .value {
            color: #111827;
            font-size: 14px;
            word-wrap: break-word;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .description-section {
            margin: 25px 0;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #f9fafb;
        }
        
        .description-section h3 {
            margin: 0 0 10px 0;
            color: #2563eb;
            font-size: 16px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        
        .description-text {
            font-size: 14px;
            line-height: 1.6;
            color: #374151;
            white-space: pre-wrap;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pendente { background: #fef3c7; color: #92400e; }
        .status-em_andamento { background: #dbeafe; color: #1e40af; }
        .status-concluido { background: #d1fae5; color: #065f46; }
        .status-cancelado { background: #fee2e2; color: #991b1b; }
        
        .responsaveis-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .responsaveis-list li {
            padding: 4px 0;
            border-bottom: 1px dotted #d1d5db;
        }
        
        .responsaveis-list li:last-child {
            border-bottom: none;
        }
        
        .anexos-section {
            margin: 25px 0;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #f9fafb;
        }
        
        .anexos-list {
            list-style: none;
            padding: 0;
            margin: 10px 0 0 0;
        }
        
        .anexos-list li {
            padding: 8px;
            margin: 5px 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .signatures {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
        }
        
        .signature-box {
            text-align: center;
            padding: 20px 0;
        }
        
        .signature-line {
            border-top: 1px solid #374151;
            margin-bottom: 5px;
            height: 40px;
        }
        
        .signature-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #1d4ed8;
        }
        
        .reference-section {
            margin: 25px 0;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #f0f9ff;
        }
        
        .reference-section h3 {
            margin: 0 0 10px 0;
            color: #0369a1;
            font-size: 14px;
        }
        
        .reference-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        
        .reference-table th,
        .reference-table td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            text-align: left;
        }
        
        .reference-table th {
            background: #e0f2fe;
            font-weight: bold;
            color: #0369a1;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Imprimir</button>
    
    <div class="header">
        <h1>REGISTRO DE MELHORIA CONTÍNUA</h1>
        <p class="subtitle">Sistema de Gestão da Qualidade - OTI DJ</p>
        <p class="subtitle">Registro #<?= $melhoria['id'] ?? 'N/A' ?></p>
    </div>
    
    <div class="info-grid">
        <div class="info-item">
            <label>Data de Registro</label>
            <div class="value"><?= isset($melhoria['data_registro']) ? date('d/m/Y H:i', strtotime($melhoria['data_registro'])) : 'N/A' ?></div>
        </div>
        
        <div class="info-item">
            <label>Status</label>
            <div class="value">
                <span class="status-badge status-<?= $melhoria['status'] ?? 'pendente' ?>">
                    <?= ucfirst(str_replace('_', ' ', $melhoria['status'] ?? 'Pendente')) ?>
                </span>
            </div>
        </div>
        
        <div class="info-item">
            <label>Departamento</label>
            <div class="value"><?= $melhoria['departamento_nome'] ?? 'N/A' ?></div>
        </div>
        
        <div class="info-item">
            <label>Processo</label>
            <div class="value"><?= $melhoria['processo'] ?? 'N/A' ?></div>
        </div>
        
        <?php if (!empty($melhoria['pontuacao'])): ?>
        <div class="info-item">
            <label>Pontuação</label>
            <div class="value"><?= $melhoria['pontuacao'] ?> pontos</div>
        </div>
        <?php endif; ?>
        
        <div class="info-item">
            <label>Criado por</label>
            <div class="value"><?= $melhoria['created_by_name'] ?? 'Sistema' ?></div>
        </div>
        
        <?php if (!empty($melhoria['idealizador'])): ?>
        <div class="info-item">
            <label>💡 Idealizador</label>
            <div class="value"><?= $melhoria['idealizador'] ?></div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="description-section">
        <h3>📝 Descrição da Melhoria</h3>
        <div class="description-text"><?= $melhoria['descricao_melhoria'] ?? 'Não informado' ?></div>
    </div>
    
    <?php if (!empty($melhoria['responsaveis'])): ?>
    <div class="info-item full-width">
        <label>👥 Responsáveis pela Melhoria</label>
        <ul class="responsaveis-list">
            <?php foreach ($melhoria['responsaveis'] as $responsavel): ?>
                <li>• <?= $responsavel['name'] ?? 'N/A' ?> (<?= $responsavel['email'] ?? 'N/A' ?>)</li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($melhoria['observacao'])): ?>
    <div class="description-section">
        <h3>💭 Observações</h3>
        <div class="description-text"><?= $melhoria['observacao'] ?></div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($melhoria['resultado'])): ?>
    <div class="description-section">
        <h3>🎯 Resultado</h3>
        <div class="description-text"><?= $melhoria['resultado'] ?></div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($melhoria['anexos'])): ?>
    <div class="anexos-section">
        <h3>📎 Anexos (<?= count($melhoria['anexos']) ?> arquivo(s))</h3>
        <ul class="anexos-list">
            <?php foreach ($melhoria['anexos'] as $anexo): ?>
                <li>
                    <strong><?= $anexo['nome_arquivo'] ?></strong><br>
                    Tipo: <?= $anexo['tipo_arquivo'] ?> | 
                    Tamanho: <?= number_format($anexo['tamanho_arquivo'] / 1024 / 1024, 2) ?> MB |
                    Enviado em: <?= date('d/m/Y H:i', strtotime($anexo['created_at'])) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="reference-section">
        <h3>📊 Referência de Status</h3>
        <table class="reference-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Descrição</th>
                    <th>Ação Requerida</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="status-badge status-pendente">Pendente</span></td>
                    <td>Melhoria registrada, aguardando análise</td>
                    <td>Análise pela equipe responsável</td>
                </tr>
                <tr>
                    <td><span class="status-badge status-em_andamento">Em Andamento</span></td>
                    <td>Melhoria aprovada e em implementação</td>
                    <td>Execução das ações planejadas</td>
                </tr>
                <tr>
                    <td><span class="status-badge status-concluido">Concluído</span></td>
                    <td>Melhoria implementada com sucesso</td>
                    <td>Monitoramento dos resultados</td>
                </tr>
                <tr>
                    <td><span class="status-badge status-cancelado">Cancelado</span></td>
                    <td>Melhoria não aprovada ou cancelada</td>
                    <td>Documentação dos motivos</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Solicitante</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Responsável</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Aprovação</div>
        </div>
    </div>
    
    <div class="footer">
        <p>
            Documento gerado automaticamente pelo Sistema SGQ OTI DJ em <?= date('d/m/Y H:i:s') ?><br>
            Este documento é válido sem assinatura digital conforme política interna de qualidade
        </p>
    </div>
    
    <script>
        // Auto-print quando solicitado via URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto_print') === 'true') {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>
</html>
