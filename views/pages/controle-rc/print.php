<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de RC - <?= htmlspecialchars($registro['numero_registro']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 40px;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #1e40af;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header .subtitle {
            color: #6b7280;
            font-size: 14px;
        }

        .info-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .info-section h2 {
            background: #eff6ff;
            color: #1e40af;
            padding: 10px 15px;
            border-left: 4px solid #2563eb;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            padding: 12px;
            background: #f9fafb;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .info-item label {
            display: block;
            font-weight: 600;
            color: #4b5563;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-item .value {
            font-size: 14px;
            color: #111827;
        }

        .info-full {
            grid-column: 1 / -1;
        }

        .text-section {
            padding: 15px;
            background: #f9fafb;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            margin-bottom: 15px;
            min-height: 100px;
        }

        .text-section label {
            display: block;
            font-weight: 600;
            color: #4b5563;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .text-section .content {
            font-size: 14px;
            color: #111827;
            white-space: pre-wrap;
            line-height: 1.8;
        }

        .evidencias-list {
            list-style: none;
            padding: 0;
        }

        .evidencias-list li {
            padding: 8px 12px;
            background: #f9fafb;
            border-radius: 4px;
            margin-bottom: 8px;
            border-left: 3px solid #10b981;
            font-size: 14px;
        }

        .evidencias-list li::before {
            content: "📎 ";
            margin-right: 8px;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }

        .signatures {
            margin-top: 60px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin: 60px 20px 10px 20px;
        }

        .signature-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .no-print {
            text-align: center;
            margin: 30px 0;
        }

        .btn-print {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-close {
            background: #6b7280;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-close:hover {
            background: #4b5563;
        }

        @media print {
            body {
                padding: 20px;
            }

            .no-print {
                display: none;
            }

            .info-section {
                page-break-inside: avoid;
            }

            .signatures {
                page-break-before: auto;
            }
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-green {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-yellow {
            background: #fef3c7;
            color: #92400e;
        }

        .empty-value {
            color: #9ca3af;
            font-style: italic;
        }

        /* Estilos para imagens */
        .image-container {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            background: #f9fafb;
            page-break-inside: avoid;
            margin-bottom: 20px;
        }

        .image-container img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: block;
            margin: 0 auto;
        }

        .image-label {
            font-size: 12px;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 8px;
            text-align: center;
        }

        .images-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        @media print {
            .image-container {
                page-break-inside: avoid;
            }
            
            .images-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Botões de Ação -->
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Imprimir / Salvar PDF</button>
        <button onclick="window.close()" class="btn-close">❌ Fechar</button>
    </div>

    <!-- Cabeçalho -->
    <div class="header">
        <h1>📋 RELATÓRIO DE REGISTRO DE RECLAMAÇÃO</h1>
        <div class="subtitle">SGQ OTI DJ - Sistema de Gestão da Qualidade</div>
        <div class="subtitle" style="margin-top: 10px; font-weight: 600; color: #1e40af;">
            <?= htmlspecialchars($registro['numero_registro']) ?>
        </div>
    </div>

    <!-- Informações Básicas -->
    <div class="info-section">
        <h2>📌 Informações Básicas</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Número do Registro</label>
                <div class="value">
                    <span class="badge badge-blue"><?= htmlspecialchars($registro['numero_registro']) ?></span>
                </div>
            </div>

            <div class="info-item">
                <label>Data de Abertura</label>
                <div class="value"><?= date('d/m/Y', strtotime($registro['data_abertura'])) ?></div>
            </div>

            <div class="info-item">
                <label>Origem da Reclamação</label>
                <div class="value">
                    <span class="badge badge-green"><?= htmlspecialchars($registro['origem']) ?></span>
                </div>
            </div>

            <div class="info-item">
                <label>Categoria</label>
                <div class="value">
                    <span class="badge badge-yellow"><?= htmlspecialchars($registro['categoria']) ?></span>
                </div>
            </div>

            <?php if (!empty($registro['detalhamento'])): ?>
            <div class="info-item">
                <label>Detalhamento</label>
                <div class="value"><?= nl2br(htmlspecialchars($registro['detalhamento'])) ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($registro['qual_produto'])): ?>
            <div class="info-item">
                <label>Qual Produto</label>
                <div class="value"><?= htmlspecialchars($registro['qual_produto']) ?></div>
            </div>
            <?php endif; ?>

            <div class="info-item info-full">
                <label>Cliente / Empresa</label>
                <div class="value"><?= htmlspecialchars($registro['cliente_nome']) ?></div>
            </div>

            <div class="info-item">
                <label>Número de Série / Identificação</label>
                <div class="value">
                    <?php if (!empty($registro['numero_serie'])): ?>
                        <?= htmlspecialchars($registro['numero_serie']) ?>
                    <?php else: ?>
                        <span class="empty-value">Não informado</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-item">
                <label>Fornecedor</label>
                <div class="value">
                    <?php if (!empty($registro['fornecedor_nome'])): ?>
                        <?= htmlspecialchars($registro['fornecedor_nome']) ?>
                    <?php else: ?>
                        <span class="empty-value">Não informado</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-item">
                <label>Responsável pelo Registro</label>
                <div class="value"><?= htmlspecialchars($registro['usuario_nome']) ?></div>
            </div>

            <div class="info-item">
                <label>Data de Criação</label>
                <div class="value"><?= date('d/m/Y H:i', strtotime($registro['created_at'])) ?></div>
            </div>
        </div>
    </div>

    <!-- Testes Realizados -->
    <div class="info-section">
        <h2>🔬 Testes Realizados</h2>
        <div class="text-section">
            <div class="content">
                <?php if (!empty($registro['testes_realizados'])): ?>
                    <?= nl2br(htmlspecialchars($registro['testes_realizados'])) ?>
                <?php else: ?>
                    <span class="empty-value">Nenhum teste descrito</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Ações Realizadas -->
    <div class="info-section">
        <h2>⚙️ Ações Realizadas</h2>
        <div class="text-section">
            <div class="content">
                <?php if (!empty($registro['acoes_realizadas'])): ?>
                    <?= nl2br(htmlspecialchars($registro['acoes_realizadas'])) ?>
                <?php else: ?>
                    <span class="empty-value">Nenhuma ação descrita</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Conclusão -->
    <div class="info-section">
        <h2>✅ Conclusão</h2>
        <div class="text-section">
            <div class="content">
                <?php if (!empty($registro['conclusao'])): ?>
                    <?= nl2br(htmlspecialchars($registro['conclusao'])) ?>
                <?php else: ?>
                    <span class="empty-value">Conclusão não informada</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Evidências -->
    <?php if (!empty($registro['evidencias'])): ?>
    <div class="info-section">
        <h2>📎 Evidências Anexadas</h2>
        <ul class="evidencias-list">
            <?php foreach ($registro['evidencias'] as $evidencia): ?>
                <li><?= htmlspecialchars($evidencia['nome_arquivo']) ?></li>
            <?php endforeach; ?>
        </ul>
        <p style="margin-top: 15px; font-size: 12px; color: #6b7280;">
            <strong>Total de evidências:</strong> <?= count($registro['evidencias']) ?>
        </p>
        
        <!-- Exibir imagens dos anexos -->
        <div style="margin-top: 30px;">
            <h3 style="font-size: 16px; color: #1e40af; margin-bottom: 20px; font-weight: 600;">🖼️ Visualização das Imagens:</h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <?php foreach ($registro['evidencias'] as $evidencia): ?>
                    <?php if (!empty($evidencia['arquivo_blob']) && strpos($evidencia['tipo_arquivo'], 'image/') === 0): ?>
                        <div style="border: 2px solid #e5e7eb; border-radius: 8px; padding: 10px; background: #f9fafb; page-break-inside: avoid;">
                            <p style="font-size: 12px; font-weight: 600; color: #4b5563; margin-bottom: 8px; text-align: center;">
                                📷 <?= htmlspecialchars($evidencia['nome_arquivo']) ?>
                            </p>
                            <div style="text-align: center;">
                                <img src="data:<?= htmlspecialchars($evidencia['tipo_arquivo']) ?>;base64,<?= base64_encode($evidencia['arquivo_blob']) ?>" 
                                     alt="<?= htmlspecialchars($evidencia['nome_arquivo']) ?>" 
                                     style="max-width: 100%; height: auto; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Assinaturas -->
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Responsável Técnico</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Gestor da Qualidade</div>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <p>
            <strong>SGQ OTI DJ</strong> - Sistema de Gestão da Qualidade<br>
            Relatório gerado em <?= date('d/m/Y H:i:s') ?><br>
            Este documento é uma via eletrônica e possui validade conforme legislação vigente.
        </p>
    </div>

    <script>
        // Auto-print quando abre em nova janela (opcional)
        // window.onload = () => window.print();
    </script>
</body>
</html>
