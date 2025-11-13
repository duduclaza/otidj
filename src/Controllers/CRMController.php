<?php
namespace App\Controllers;

class CRMController
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
     * Página Prospecção
     */
    public function prospeccao()
    {
        $this->requireAdmin();
        
        $title = 'Prospecção - CRM - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/crm/prospeccao.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Página Vendas
     */
    public function vendas()
    {
        $this->requireAdmin();
        
        $title = 'Vendas - CRM - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/crm/vendas.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Página Relacionamento
     */
    public function relacionamento()
    {
        $this->requireAdmin();
        
        $title = 'Relacionamento - CRM - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/crm/relacionamento.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Página Marketing
     */
    public function marketing()
    {
        $this->requireAdmin();
        
        $title = 'Marketing - CRM - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/crm/marketing.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Página Relatórios
     */
    public function relatorios()
    {
        $this->requireAdmin();
        
        $title = 'Relatórios - CRM - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/crm/relatorios.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Página Dashboards
     */
    public function dashboards()
    {
        $this->requireAdmin();
        
        $title = 'Dashboards - CRM - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/crm/dashboards.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
}
