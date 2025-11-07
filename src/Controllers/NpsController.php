<?php

namespace App\Controllers;

class NpsController
{
    public function index()
    {
        // Verificar autenticação
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Usar o layout padrão com a página em construção
        $title = 'NPS (Net Promoter Score) - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/nps/index.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
}
