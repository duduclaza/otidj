<?php
namespace App\Controllers;

class ImplantacaoController
{
    /**
     * Verificar se usuário é admin ou super_admin
     */
    private function requireAdmin()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, ['admin', 'super_admin'])) {
            http_response_code(403);
            echo "<h1>⛔ Acesso Negado</h1>";
            echo "<p>Este módulo é exclusivo para Administradores.</p>";
            echo "<p><a href='/inicio' style='color: #3B82F6;'>← Voltar para Início</a></p>";
            exit;
        }
    }
    
    /**
     * Página DPO
     */
    public function dpo()
    {
        $this->requireAdmin();
        
        $title = 'DPO - Gestão de Implantação - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/implantacao/dpo.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Página Ordem de Serviços de Implantação
     */
    public function ordemServicos()
    {
        $this->requireAdmin();
        
        $title = 'Ordem de Serviços de Implantação - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/implantacao/ordem-servicos.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Página Fluxo de Implantação
     */
    public function fluxo()
    {
        $this->requireAdmin();
        
        $title = 'Fluxo de Implantação - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/implantacao/fluxo.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Página Relatórios
     */
    public function relatorios()
    {
        $this->requireAdmin();
        
        $title = 'Relatórios - Gestão de Implantação - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/implantacao/relatorios.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
}
