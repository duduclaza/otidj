<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plano 5W2H - <?= htmlspecialchars($plano['titulo']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #2563eb;
        }
        
        .header .subtitle {
            font-size: 14px;
            color: #666;
        }
        
        .plano-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .info-value {
            font-size: 14px;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .metodologia-section {
            margin: 30px 0;
            page-break-inside: avoid;
        }
        
        .metodologia-title {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 20px;
            text-align: center;
            border-top: 2px solid #2563eb;
            border-bottom: 2px solid #2563eb;
            padding: 10px 0;
        }
        
        .metodologia-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .metodologia-item {
            border: 1px solid #d1d5db;
            padding: 15px;
            border-radius: 8px;
            background: #f9fafb;
        }
        
        .metodologia-item h3 {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .metodologia-item p {
            font-size: 13px;
            line-height: 1.5;
        }
        
        .anexos-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .anexos-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 15px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 5px;
        }
        
        .anexos-list {
            list-style: none;
        }
        
        .anexos-list li {
            padding: 8px 0;
            border-bottom: 1px dotted #d1d5db;
            font-size: 12px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pendente { background: #fef3c7; color: #92400e; }
        .status-em_andamento { background: #dbeafe; color: #1e40af; }
        .status-concluido { background: #d1fae5; color: #065f46; }
        .status-cancelado { background: #fee2e2; color: #991b1b; }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #d1d5db;
            font-size: 11px;
            color: #6b7280;
        }
        
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
        }
        
        .signature-box {
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
            font-size: 12px;
        }
        
        @media print {
            body { font-size: 12px; }
            .container { padding: 10px; }
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Imprimir</button>
    
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            <h1>PLANO DE AÇÃO 5W2H</h1>
            <div class="subtitle">Sistema de Gestão da Qualidade - OTI DJ</div>
            <div class="subtitle">Gerado em: <?= date('d/m/Y H:i:s') ?></div>
        </div>

        <!-- Informações Básicas -->
        <div class="plano-info">
            <div class="info-item full-width">
                <div class="info-label">Título do Plano</div>
                <div class="info-value"><?= htmlspecialchars($plano['titulo']) ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Responsável</div>
                <div class="info-value"><?= htmlspecialchars($plano['responsavel_nome']) ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Departamento</div>
                <div class="info-value"><?= htmlspecialchars($plano['setor_nome']) ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Data de Execução</div>
                <div class="info-value"><?= date('d/m/Y', strtotime($plano['when_inicio'])) ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="status-badge status-<?= $plano['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $plano['status'])) ?>
                    </span>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Custo Estimado</div>
                <div class="info-value">R$ <?= number_format($plano['how_much'], 2, ',', '.') ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Criado por</div>
                <div class="info-value"><?= htmlspecialchars($plano['criado_por_nome']) ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Data de Criação</div>
                <div class="info-value"><?= date('d/m/Y H:i', strtotime($plano['created_at'])) ?></div>
            </div>
        </div>

        <!-- Metodologia 5W2H -->
        <div class="metodologia-section">
            <h2 class="metodologia-title">METODOLOGIA 5W2H</h2>
            
            <div class="metodologia-grid">
                <div class="metodologia-item">
                    <h3>🎯 O QUE (What)</h3>
                    <p><?= nl2br(htmlspecialchars($plano['what'])) ?></p>
                </div>
                
                <div class="metodologia-item">
                    <h3>❓ POR QUE (Why)</h3>
                    <p><?= nl2br(htmlspecialchars($plano['why'])) ?></p>
                </div>
                
                <div class="metodologia-item">
                    <h3>👤 QUEM (Who)</h3>
                    <p><?= htmlspecialchars($plano['responsavel_nome']) ?></p>
                </div>
                
                <div class="metodologia-item">
                    <h3>📅 QUANDO (When)</h3>
                    <p><?= date('d/m/Y', strtotime($plano['when_inicio'])) ?></p>
                </div>
                
                <div class="metodologia-item">
                    <h3>📍 ONDE (Where)</h3>
                    <p><?= htmlspecialchars($plano['where_local']) ?></p>
                </div>
                
                <div class="metodologia-item">
                    <h3>⚙️ COMO (How)</h3>
                    <p><?= nl2br(htmlspecialchars($plano['how'])) ?></p>
                </div>
                
                <div class="metodologia-item full-width" style="grid-column: 1 / -1;">
                    <h3>💰 QUANTO CUSTA (How Much)</h3>
                    <p>R$ <?= number_format($plano['how_much'], 2, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Anexos -->
        <?php if (!empty($plano['anexos'])): ?>
        <div class="anexos-section">
            <h3 class="anexos-title">📎 Anexos (<?= count($plano['anexos']) ?>)</h3>
            <ul class="anexos-list">
                <?php foreach ($plano['anexos'] as $anexo): ?>
                <li>
                    <strong><?= htmlspecialchars($anexo['nome_original']) ?></strong>
                    <br>
                    Tipo: <?= htmlspecialchars($anexo['tipo_arquivo']) ?> | 
                    Tamanho: <?= number_format($anexo['tamanho_arquivo'] / 1024, 1) ?> KB | 
                    Enviado em: <?= date('d/m/Y H:i', strtotime($anexo['uploaded_at'])) ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Assinaturas -->
        <div class="signatures">
            <div class="signature-box">
                <div>Responsável</div>
                <div style="margin-top: 5px; font-weight: bold;"><?= htmlspecialchars($plano['responsavel_nome']) ?></div>
            </div>
            <div class="signature-box">
                <div>Supervisor</div>
                <div style="margin-top: 5px;">_____________________</div>
            </div>
            <div class="signature-box">
                <div>Gestor da Qualidade</div>
                <div style="margin-top: 5px;">_____________________</div>
            </div>
        </div>

        <!-- Rodapé -->
        <div class="footer">
            <p><strong>Sistema de Gestão da Qualidade - OTI DJ</strong></p>
            <p>Documento gerado automaticamente em <?= date('d/m/Y H:i:s') ?></p>
            <p>ID do Plano: #<?= $plano['id'] ?> | Versão: 1.0</p>
        </div>
    </div>

    <script>
        // Auto print quando abrir a página
        window.onload = function() {
            // Pequeno delay para garantir que a página carregou completamente
            setTimeout(function() {
                if (window.location.search.includes('autoprint=1')) {
                    window.print();
                }
            }, 1000);
        }
    </script>
</body>
</html>
