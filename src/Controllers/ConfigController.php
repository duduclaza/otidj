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
            
            
            flash('success', 'Setup do banco executado com sucesso! Tabelas criadas/atualizadas, dados padrão inseridos e permissões atualizadas.');
        } catch (\Exception $e) {
            flash('error', 'Erro ao executar setup: ' . $e->getMessage());
        }
        redirect('/configuracoes');
    }
    


    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        include $layout;
    }
}
