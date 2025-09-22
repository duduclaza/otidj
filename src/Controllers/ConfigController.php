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
}
