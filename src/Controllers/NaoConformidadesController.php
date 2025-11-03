<?php

namespace App\Controllers;

class NaoConformidadesController
{
    public function index()
    {
        // Verificar autenticação
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Usar o layout padrão com a página em construção
        $title = 'Não Conformidades - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/nao-conformidades/index.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
}
