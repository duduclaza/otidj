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
