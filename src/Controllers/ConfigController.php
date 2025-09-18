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
            $migration->run();
            
            // Create solicitacoes_melhorias tables
            $this->createSolicitacoesMelhoriasTables();
            
            // Update permissions
            $this->updatePermissions();
            
            flash('success', 'Setup do banco executado com sucesso! Tabelas criadas/atualizadas, dados padrão inseridos e permissões atualizadas.');
        } catch (\Exception $e) {
            flash('error', 'Erro ao executar setup: ' . $e->getMessage());
        }
        redirect('/configuracoes');
    }
    
    private function createSolicitacoesMelhoriasTables(): void
    {
        // Create solicitacoes_melhorias table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS solicitacoes_melhorias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                usuario_nome VARCHAR(255) NOT NULL,
                data_solicitacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                setor VARCHAR(255) NOT NULL,
                processo TEXT NOT NULL,
                descricao_melhoria TEXT NOT NULL,
                status ENUM('pendente', 'em_analise', 'aprovado', 'rejeitado', 'implementado') NOT NULL DEFAULT 'pendente',
                observacoes TEXT,
                resultado_esperado TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Create solicitacoes_melhorias_responsaveis table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS solicitacoes_melhorias_responsaveis (
                id INT AUTO_INCREMENT PRIMARY KEY,
                solicitacao_id INT NOT NULL,
                usuario_id INT NOT NULL,
                usuario_nome VARCHAR(255) NOT NULL,
                usuario_email VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_solicitacao_responsavel (solicitacao_id, usuario_id)
            )
        ");

        // Create solicitacoes_melhorias_anexos table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS solicitacoes_melhorias_anexos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                solicitacao_id INT NOT NULL,
                nome_arquivo VARCHAR(255) NOT NULL,
                nome_original VARCHAR(255) NOT NULL,
                tipo_arquivo VARCHAR(100) NOT NULL,
                tamanho_arquivo INT NOT NULL,
                caminho_arquivo VARCHAR(500) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    private function updatePermissions(): void
    {
        // Remove old module if exists
        $stmt = $this->db->prepare("DELETE FROM profile_permissions WHERE module = 'melhoria_continua'");
        $stmt->execute();
        
        // Check if solicitacao_melhorias module already exists
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM profile_permissions WHERE module = 'solicitacao_melhorias'");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Get all profiles
            $stmt = $this->db->prepare("SELECT id, name FROM profiles");
            $stmt->execute();
            $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($profiles as $profile) {
                // Add solicitacao_melhorias permissions based on profile type
                $canEdit = 0;
                $canDelete = 0;
                $canView = 1;

                if ($profile['name'] === 'Administrador') {
                    $canEdit = 1;
                    $canDelete = 1;
                } elseif (in_array($profile['name'], ['Supervisor', 'Analista de Qualidade'])) {
                    $canEdit = 1;
                }

                $stmt = $this->db->prepare("
                    INSERT INTO profile_permissions 
                    (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                    VALUES (?, 'solicitacao_melhorias', ?, ?, ?, 0, 1)
                ");
                $stmt->execute([$profile['id'], $canView, $canEdit, $canDelete]);
            }
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
