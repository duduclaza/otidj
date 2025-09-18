<?php
// Teste super simples para verificar se o controller funciona

namespace App\Controllers;

class TestSolicitacoesMelhoriasController
{
    public function index()
    {
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set test data
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Usuário de Teste';
        
        $setores = ['TI', 'Qualidade', 'Produção', 'Administrativo'];
        $usuarios = [
            ['id' => 1, 'name' => 'Admin', 'email' => 'admin@test.com'],
            ['id' => 2, 'name' => 'User', 'email' => 'user@test.com']
        ];
        
        // Include the view directly
        include __DIR__ . '/../views/melhoria-continua/solicitacoes.php';
    }
}
