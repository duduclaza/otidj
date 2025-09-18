<?php
namespace App\Setup;

use App\Config\Database;
use PDO;

class MelhoriaContinuaSetup
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function ensure(): void
    {
        $this->createTables();
        $this->seedPermissions();
    }

    private function createTables(): void
    {
        // Tabela principal
        $this->db->exec("CREATE TABLE IF NOT EXISTS solicitacoes_melhorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            usuario_nome VARCHAR(255) NOT NULL,
            data_solicitacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            setor VARCHAR(255) NOT NULL,
            processo TEXT NOT NULL,
            descricao_melhoria TEXT NOT NULL,
            status ENUM('pendente','em_analise','aprovado','aprovado_obs','reprovado','implementado') NOT NULL DEFAULT 'pendente',
            observacoes TEXT,
            resultado_esperado TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_status (status),
            INDEX idx_data (data_solicitacao)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Responsáveis (muitos-para-muitos)
        $this->db->exec("CREATE TABLE IF NOT EXISTS solicitacoes_melhorias_responsaveis (
            id INT AUTO_INCREMENT PRIMARY KEY,
            solicitacao_id INT NOT NULL,
            usuario_id INT NOT NULL,
            usuario_nome VARCHAR(255) NOT NULL,
            usuario_email VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_sol_resp (solicitacao_id, usuario_id),
            INDEX idx_solicitacao (solicitacao_id),
            CONSTRAINT fk_sol_resp_sol FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_melhorias(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Anexos
        $this->db->exec("CREATE TABLE IF NOT EXISTS solicitacoes_melhorias_anexos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            solicitacao_id INT NOT NULL,
            nome_arquivo VARCHAR(255) NOT NULL,
            nome_original VARCHAR(255) NOT NULL,
            tipo_arquivo VARCHAR(100) NOT NULL,
            tamanho_arquivo INT NOT NULL,
            caminho_arquivo VARCHAR(500) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_solicitacao (solicitacao_id),
            CONSTRAINT fk_sol_anx_sol FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_melhorias(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Logs
        $this->db->exec("CREATE TABLE IF NOT EXISTS melhorias_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            solicitacao_id INT NOT NULL,
            usuario_id INT NULL,
            usuario_nome VARCHAR(255) NULL,
            acao VARCHAR(100) NOT NULL,
            detalhes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_solicitacao (solicitacao_id),
            CONSTRAINT fk_logs_sol FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_melhorias(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    private function seedPermissions(): void
    {
        try {
            // Verifica se existe tabela de perfis e permissões
            $this->db->query("SELECT 1 FROM profiles LIMIT 1");
            $this->db->query("SELECT 1 FROM profile_permissions LIMIT 1");
        } catch (\Throwable $e) {
            return; // Sem sistema de permissões
        }

        $modules = [
            'melhoria_continua',
            'solicitacao_melhorias',
            'melhorias_pendentes',
            'historico_melhorias',
        ];

        // Evita duplicar
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM profile_permissions WHERE profile_id = ? AND module = ?");
        $stmtInsert = $this->db->prepare("INSERT INTO profile_permissions (profile_id,module,can_view,can_edit,can_delete,can_import,can_export) VALUES (?,?,?,?,?,?,?)");

        $profiles = $this->db->query("SELECT id, name FROM profiles")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($profiles as $profile) {
            foreach ($modules as $module) {
                $stmtCheck->execute([$profile['id'], $module]);
                if ((int)$stmtCheck->fetchColumn() > 0) continue;
                $canView = 1; $canEdit = 0; $canDelete = 0; $canImport = 0; $canExport = 1;
                if ($profile['name'] === 'Administrador') { $canEdit = 1; $canDelete = 1; }
                if (in_array($profile['name'], ['Supervisor','Analista de Qualidade'])) { $canEdit = 1; }
                $stmtInsert->execute([$profile['id'], $module, $canView, $canEdit, $canDelete, $canImport, $canExport]);
            }
        }
    }
}
