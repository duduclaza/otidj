<?php

namespace App\Cron;

use PDO;

class FinanceiroCron
{
    private $db;

    public function __construct()
    {
        $this->db = \App\Config\Database::getInstance()->getConnection();
    }

    /**
     * Executar diariamente para verificar pagamentos
     */
    public function verificarPagamentos(): void
    {
        try {
            // 1. Criar pagamento do próximo mês no último dia útil
            $this->criarPagamentoProximoMes();

            // 2. Verificar pagamentos atrasados
            $this->verificarAtrasos();

            // 3. Enviar notificações
            $this->enviarNotificacoes();

            // 4. Bloquear sistema se necessário
            $this->verificarBloqueio();

            error_log("FinanceiroCron executado com sucesso");

        } catch (\Exception $e) {
            error_log("Erro no FinanceiroCron: " . $e->getMessage());
        }
    }

    private function criarPagamentoProximoMes(): void
    {
        // Verificar se hoje é o último dia útil do mês
        $hoje = new \DateTime();
        $ultimoDiaMes = new \DateTime('last day of this month');
        
        // Se não for último dia do mês, retorna
        if ($hoje->format('Y-m-d') !== $ultimoDiaMes->format('Y-m-d')) {
            return;
        }

        // Criar pagamento do próximo mês
        $proximoMes = new \DateTime('first day of next month');
        $mes = (int)$proximoMes->format('n');
        $ano = (int)$proximoMes->format('Y');
        $vencimento = new \DateTime('last day of next month');

        $stmt = $this->db->prepare('
            INSERT INTO financeiro_pagamentos (mes, ano, status, data_vencimento)
            SELECT :mes, :ano, "Em Aberto", :vencimento
            WHERE NOT EXISTS (
                SELECT 1 FROM financeiro_pagamentos 
                WHERE mes = :mes AND ano = :ano
            )
        ');

        $stmt->execute([
            ':mes' => $mes,
            ':ano' => $ano,
            ':vencimento' => $vencimento->format('Y-m-d')
        ]);

        error_log("Pagamento criado para {$mes}/{$ano}");
    }

    private function verificarAtrasos(): void
    {
        // Atualizar status para "Atrasado" se passou do vencimento
        $stmt = $this->db->prepare('
            UPDATE financeiro_pagamentos 
            SET status = "Atrasado"
            WHERE status = "Em Aberto" 
            AND data_vencimento < CURDATE()
        ');
        $stmt->execute();
    }

    private function enviarNotificacoes(): void
    {
        // Buscar pagamentos em aberto
        $stmt = $this->db->prepare('
            SELECT * FROM financeiro_pagamentos 
            WHERE status IN ("Em Aberto", "Atrasado")
            AND comprovante IS NULL
        ');
        $stmt->execute();
        $pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pagamentos as $pag) {
            $vencimento = new \DateTime($pag['data_vencimento']);
            $hoje = new \DateTime();
            $diasUteis = $this->calcularDiasUteis($vencimento, $hoje);

            // Notificação após 3 dias úteis
            if ($diasUteis >= 3 && $diasUteis < 5) {
                $this->enviarNotificacao($pag['id'], '3_dias', 'warning');
            }

            // Notificação após 5 dias úteis (crítica)
            if ($diasUteis >= 5) {
                $this->enviarNotificacao($pag['id'], '5_dias', 'danger');
            }
        }
    }

    private function verificarBloqueio(): void
    {
        // Buscar pagamentos com mais de 5 dias úteis de atraso
        $stmt = $this->db->prepare('
            SELECT * FROM financeiro_pagamentos 
            WHERE status = "Atrasado"
            AND comprovante IS NULL
        ');
        $stmt->execute();
        $pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pagamentos as $pag) {
            $vencimento = new \DateTime($pag['data_vencimento']);
            $hoje = new \DateTime();
            $diasUteis = $this->calcularDiasUteis($vencimento, $hoje);

            if ($diasUteis >= 5) {
                // Verificar se já está bloqueado
                $stmt = $this->db->prepare('
                    SELECT COUNT(*) as total FROM financeiro_bloqueios 
                    WHERE pagamento_id = :id AND desbloqueado_em IS NULL
                ');
                $stmt->execute([':id' => $pag['id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['total'] == 0) {
                    // Bloquear sistema
                    $stmt = $this->db->prepare('
                        INSERT INTO financeiro_bloqueios (pagamento_id, bloqueado_em, motivo)
                        VALUES (:id, NOW(), :motivo)
                    ');
                    $stmt->execute([
                        ':id' => $pag['id'],
                        ':motivo' => "Pagamento não anexado após 5 dias úteis do vencimento"
                    ]);

                    // Enviar notificação de bloqueio
                    $this->enviarNotificacao($pag['id'], 'bloqueio', 'danger');

                    error_log("Sistema bloqueado - Pagamento ID: {$pag['id']}");
                }
            }
        }
    }

    private function enviarNotificacao($pagamentoId, $tipo, $tipoNotif): void
    {
        // Verificar se já foi enviada
        $stmt = $this->db->prepare('
            SELECT COUNT(*) as total FROM financeiro_notificacoes 
            WHERE pagamento_id = :id AND tipo = :tipo AND enviada = TRUE
        ');
        $stmt->execute([':id' => $pagamentoId, ':tipo' => $tipo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['total'] > 0) {
            return; // Já foi enviada
        }

        // Buscar dados do pagamento
        $stmt = $this->db->prepare('SELECT * FROM financeiro_pagamentos WHERE id = :id');
        $stmt->execute([':id' => $pagamentoId]);
        $pag = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pag) return;

        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        $mesNome = $meses[$pag['mes']];

        // Buscar administradores
        $stmt = $this->db->prepare('SELECT id FROM users WHERE role = "admin"');
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mensagens = [
            '3_dias' => "⚠️ Pagamento de {$mesNome}/{$pag['ano']} pendente há 3 dias úteis. Anexe o comprovante.",
            '5_dias' => "🚨 URGENTE: Pagamento de {$mesNome}/{$pag['ano']} pendente há 5 dias úteis. Sistema será bloqueado!",
            'bloqueio' => "🔒 Sistema bloqueado! Pagamento de {$mesNome}/{$pag['ano']} não anexado."
        ];

        foreach ($admins as $admin) {
            $stmt = $this->db->prepare('
                INSERT INTO notifications (user_id, title, message, type, related_type, related_id, created_at)
                VALUES (:user_id, :title, :message, :type, "financeiro", :related_id, NOW())
            ');

            $stmt->execute([
                ':user_id' => $admin['id'],
                ':title' => $tipo === 'bloqueio' ? '🔒 Sistema Bloqueado' : '💰 Pagamento Pendente',
                ':message' => $mensagens[$tipo],
                ':type' => $tipoNotif,
                ':related_id' => $pagamentoId
            ]);
        }

        // Marcar notificação como enviada
        $stmt = $this->db->prepare('
            INSERT INTO financeiro_notificacoes (pagamento_id, tipo, enviada, enviada_em)
            VALUES (:id, :tipo, TRUE, NOW())
        ');
        $stmt->execute([':id' => $pagamentoId, ':tipo' => $tipo]);

        error_log("Notificação enviada - Tipo: {$tipo}, Pagamento ID: {$pagamentoId}");
    }

    private function calcularDiasUteis(\DateTime $inicio, \DateTime $fim): int
    {
        $dias = 0;
        $atual = clone $inicio;

        while ($atual <= $fim) {
            $diaSemana = (int)$atual->format('N'); // 1 = Segunda, 7 = Domingo
            if ($diaSemana < 6) { // Segunda a Sexta
                $dias++;
            }
            $atual->modify('+1 day');
        }

        return $dias;
    }
}
