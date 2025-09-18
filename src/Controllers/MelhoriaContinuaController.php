<?php
namespace App\Controllers;

use App\Config\Database;

class MelhoriaContinuaController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/melhoria-continua/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        include $layout;
    }

    // Página com ABAS
    public function index(): void
    {
        $setores = $this->getSetores();
        $usuarios = $this->getUsuarios();
        $this->render('index', compact('setores','usuarios'));
    }

    // Páginas
    public function solicitacoes(): void
    {
        // Carregar dados básicos para selects
        $setores = $this->getSetores();
        $usuarios = $this->getUsuarios();
        $this->render('solicitacoes', compact('setores','usuarios'));
    }

    public function pendentes(): void
    {
        $this->render('pendentes');
    }

    public function historico(): void
    {
        $this->render('historico');
    }

    // APIs (stubs para não quebrar enquanto banco não está pronto)
    public function apiListSolicitacoes(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success'=>true, 'data'=>[]]);
    }

    public function apiCreateSolicitacao(): void
    {
        header('Content-Type: application/json');
        // TODO: implementar criação com uploads e envio de email
        echo json_encode(['success'=>true,'message'=>'Solicitação registrada (stub).']);
    }

    public function apiListPendentes(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'data'=>[]]);
    }

    public function apiUpdateStatus(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'message'=>'Status atualizado (stub).']);
    }

    public function apiDelete(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'message'=>'Solicitação excluída (stub).']);
    }

    public function apiLogs(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'data'=>[]]);
    }

    public function details($params = []): void
    {
        header('Content-Type: application/json');
        $id = $params['id'] ?? null;
        echo json_encode(['success'=>true,'data'=>['id'=>$id]]);
    }

    public function print($params = []): void
    {
        $id = $params['id'] ?? null;
        echo '<html><head><title>Impressão Solicitação #'.htmlspecialchars((string)$id)."</title></head><body>";
        echo '<h1>Solicitação #'.htmlspecialchars((string)$id).'</h1>';
        echo '<p>Esta é uma página de impressão (stub).</p>';
        echo '</body></html>';
    }

    // Helpers
    private function getSetores(): array
    {
        try {
            $stmt = $this->db->query("SELECT DISTINCT setor FROM users WHERE setor IS NOT NULL AND setor <> '' ORDER BY setor");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        } catch (\Throwable $e) { return []; }
    }

    private function getUsuarios(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { return []; }
    }
}
