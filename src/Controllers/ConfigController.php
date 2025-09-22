<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class ConfigController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $this->render('configuracoes', ['title' => 'Configurações']);
    }

    public function setupBanco(): void
    {
        try {
            // Run migrations
            $migration = new \App\Core\Migration();
            $migration->runMigrations();
            
            
            flash('success', 'Setup do banco executado com sucesso! Tabelas criadas/atualizadas, dados padrão inseridos e permissões atualizadas.');
        } catch (\Exception $e) {
            flash('error', 'Erro ao executar setup: ' . $e->getMessage());
        }
        redirect('/configuracoes');
    }
    
    // Endpoint: aplica patch de colunas em amostragens (modo simples, compatível)
    public function patchAmostragens(): void
    {
        header('Content-Type: application/json');
        // Segurança básica: requer sessão ativa (middleware já protege, mas reforçamos)
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            return;
        }

        $results = [];
        try {
            // 1) responsaveis TEXT
            try {
                $sql = 'ALTER TABLE amostragens ADD COLUMN responsaveis TEXT NULL AFTER observacao';
                $this->db->exec($sql);
                $results['responsaveis'] = 'added';
            } catch (\PDOException $e) {
                if (stripos($e->getMessage(), 'Duplicate column name') !== false) {
                    $results['responsaveis'] = 'exists';
                } else {
                    $results['responsaveis'] = 'error: ' . $e->getMessage();
                }
            }

            // 2) fotos TEXT
            try {
                $sql = 'ALTER TABLE amostragens ADD COLUMN fotos TEXT NULL AFTER responsaveis';
                $this->db->exec($sql);
                $results['fotos'] = 'added';
            } catch (\PDOException $e) {
                if (stripos($e->getMessage(), 'Duplicate column name') !== false) {
                    $results['fotos'] = 'exists';
                } else {
                    $results['fotos'] = 'error: ' . $e->getMessage();
                }
            }

            // 3) status enum pendente/aprovado/reprovado
            try {
                $sql = "ALTER TABLE amostragens MODIFY COLUMN status ENUM('pendente','aprovado','reprovado') NOT NULL DEFAULT 'pendente'";
                $this->db->exec($sql);
                $results['status'] = 'modified';
            } catch (\PDOException $e) {
                $results['status'] = 'error: ' . $e->getMessage();
            }

            // 4) PDF em MEDIUMBLOB + metadados
            try {
                $this->db->exec("ALTER TABLE amostragens ADD COLUMN arquivo_nf_blob MEDIUMBLOB NULL AFTER arquivo_nf");
                $results['arquivo_nf_blob'] = 'added';
            } catch (\PDOException $e) {
                if (stripos($e->getMessage(), 'Duplicate column name') !== false) {
                    $results['arquivo_nf_blob'] = 'exists';
                } else { $results['arquivo_nf_blob'] = 'error: ' . $e->getMessage(); }
            }

            try {
                $this->db->exec("ALTER TABLE amostragens ADD COLUMN arquivo_nf_name VARCHAR(255) NULL AFTER arquivo_nf_blob");
                $results['arquivo_nf_name'] = 'added';
            } catch (\PDOException $e) {
                if (stripos($e->getMessage(), 'Duplicate column name') !== false) {
                    $results['arquivo_nf_name'] = 'exists';
                } else { $results['arquivo_nf_name'] = 'error: ' . $e->getMessage(); }
            }

            try {
                $this->db->exec("ALTER TABLE amostragens ADD COLUMN arquivo_nf_type VARCHAR(100) NULL AFTER arquivo_nf_name");
                $results['arquivo_nf_type'] = 'added';
            } catch (\PDOException $e) {
                if (stripos($e->getMessage(), 'Duplicate column name') !== false) {
                    $results['arquivo_nf_type'] = 'exists';
                } else { $results['arquivo_nf_type'] = 'error: ' . $e->getMessage(); }
            }

            try {
                $this->db->exec("ALTER TABLE amostragens ADD COLUMN arquivo_nf_size INT NULL AFTER arquivo_nf_type");
                $results['arquivo_nf_size'] = 'added';
            } catch (\PDOException $e) {
                if (stripos($e->getMessage(), 'Duplicate column name') !== false) {
                    $results['arquivo_nf_size'] = 'exists';
                } else { $results['arquivo_nf_size'] = 'error: ' . $e->getMessage(); }
            }

            // 5) Tabela de evidências com MEDIUMBLOB
            try {
                $this->db->exec("CREATE TABLE IF NOT EXISTS amostragens_evidencias (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    amostragem_id INT NOT NULL,
                    image MEDIUMBLOB NULL,
                    name VARCHAR(255) NULL,
                    type VARCHAR(100) NULL,
                    size INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (amostragem_id) REFERENCES amostragens(id) ON DELETE CASCADE,
                    INDEX idx_amostragem_id (amostragem_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                $results['evidencias_table'] = 'ok';
            } catch (\PDOException $e) {
                $results['evidencias_table'] = 'error: ' . $e->getMessage();
            }

            echo json_encode(['success' => true, 'message' => 'Patch aplicado', 'results' => $results]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Falha ao aplicar patch: ' . $e->getMessage(), 'results' => $results]);
        }
    }


    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        include $layout;
    }

    // Forçar execução das migrations
    public function runMigrations(): void
    {
        header('Content-Type: application/json');
        
        try {
            $migration = new \App\Core\Migration();
            $migration->runMigrations();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Migrations executadas com sucesso!',
                'current_version' => 19
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao executar migrations: ' . $e->getMessage()
            ]);
        }
    }

    // Sincroniza e garante que o perfil Administrador tenha acesso total e que usuários admin estejam associados a ele
    public function syncAdminPermissions(): void
    {
        header('Content-Type: application/json');
        $results = [
            'profile' => null,
            'assigned_users' => 0,
            'permissions_inserted' => 0,
        ];
        try {
            // 1) Garantir perfil Administrador (is_admin=1)
            $stmt = $this->db->prepare("SELECT id FROM profiles WHERE name = 'Administrador'");
            $stmt->execute();
            $adminProfileId = $stmt->fetchColumn();
            if (!$adminProfileId) {
                $ins = $this->db->prepare("INSERT INTO profiles (name, description, is_admin, is_default) VALUES ('Administrador', 'Perfil com acesso total ao sistema', 1, 0)");
                $ins->execute();
                $adminProfileId = (int)$this->db->lastInsertId();
                $results['profile'] = 'created';
            } else {
                // Garantir flag is_admin
                $this->db->prepare("UPDATE profiles SET is_admin = 1 WHERE id = ?")->execute([$adminProfileId]);
                $results['profile'] = 'updated';
            }

            // 2) Atribuir perfil Administrador para usuários com role='admin'
            $upd = $this->db->prepare("UPDATE users SET profile_id = ? WHERE role = 'admin' AND (profile_id IS NULL OR profile_id <> ?)");
            $upd->execute([$adminProfileId, $adminProfileId]);
            $results['assigned_users'] = $upd->rowCount();

            // 3) Garantir permissões completas para TODOS módulos
            $modules = [
                // Operacionais/Qualidade
                'dashboard','toners_cadastro','toners_retornados','amostragens','homologacoes','garantias','controle_descartes','auditorias','femea','fluxogramas','melhoria_continua','controle_rc',
                // POPs e ITs (granular - 4 abas separadas)
                'pops_its_cadastro_titulos',    // Aba 1: Cadastro de Títulos
                'pops_its_meus_registros',      // Aba 2: Meus Registros  
                'pops_its_pendente_aprovacao',  // Aba 3: Pendente Aprovação (só admin)
                'pops_its_visualizacao',        // Aba 4: Visualização
                // 5W2H
                '5w2h_planos',                  // Módulo 5W2H
                // Registros
                'registros_filiais','registros_departamentos','registros_fornecedores','registros_parametros',
                // Administrativo
                'configuracoes_gerais','admin_usuarios','admin_perfis','admin_convites','admin_painel',
                // Outros
                'profile','email_config','solicitacao_melhorias','melhorias_pendentes','historico_melhorias'
            ];

            // Remover duplicadas para esse profile e reinserir full access
            $this->db->prepare("DELETE FROM profile_permissions WHERE profile_id = ?")
                ->execute([$adminProfileId]);

            $insPerm = $this->db->prepare("INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, 1, 1, 1, 1)");
            foreach ($modules as $module) {
                $insPerm->execute([$adminProfileId, $module]);
                $results['permissions_inserted']++;
            }

            // 4) Limpar cache de permissões
            \App\Services\PermissionService::clearAllPermissions();

            echo json_encode(['success' => true, 'message' => 'Permissões do Administrador sincronizadas com sucesso.', 'results' => $results]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Falha ao sincronizar permissões: ' . $e->getMessage(), 'results' => $results]);
        }
    }

    // Debug temporário para POPs e ITs
    public function debugPopIts(): void
    {
        header('Content-Type: application/json');
        
        $debug = [
            'session_user_id' => $_SESSION['user_id'] ?? 'NOT_SET',
            'session_profile' => $_SESSION['profile'] ?? 'NOT_SET',
            'session_user_profile' => $_SESSION['user_profile'] ?? 'NOT_SET',
            'autoload_exists' => class_exists('App\Services\PermissionService'),
            'database_connection' => 'OK',
            'tables_check' => []
        ];
        
        try {
            // Verificar tabelas
            $tables = ['pops_its_titulos', 'pops_its_registros', 'pops_its_departamentos_permitidos', 'departamentos', 'users', 'profiles', 'profile_permissions'];
            foreach ($tables as $table) {
                try {
                    $stmt = $this->db->query("SELECT COUNT(*) FROM {$table}");
                    $debug['tables_check'][$table] = $stmt->fetchColumn();
                } catch (\Exception $e) {
                    $debug['tables_check'][$table] = 'ERROR: ' . $e->getMessage();
                }
            }
            
            // Verificar permissões POPs e ITs
            if (isset($_SESSION['user_id'])) {
                try {
                    $debug['is_admin'] = \App\Services\PermissionService::isAdmin($_SESSION['user_id']);
                    $debug['user_permissions'] = \App\Services\PermissionService::getUserPermissions($_SESSION['user_id']);
                } catch (\Exception $e) {
                    $debug['permission_error'] = $e->getMessage();
                }
            }
            
            echo json_encode(['success' => true, 'debug' => $debug]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'debug' => $debug]);
        }
    }
}
