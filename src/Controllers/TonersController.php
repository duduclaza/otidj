<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class TonersController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function cadastro(): void
    {
        try {
            $toners = $this->db->query('SELECT * FROM toners ORDER BY modelo')->fetchAll();
        } catch (\PDOException $e) {
            $toners = [];
        }
        $this->render('toners/cadastro', ['title' => 'Cadastro de Toners', 'toners' => $toners]);
    }

    public function retornados(): void
    {
        $this->render('toners/retornados', ['title' => 'Registro de Retornados']);
    }

    public function store(): void
    {
        $modelo = trim($_POST['modelo'] ?? '');
        $peso_cheio = (float)($_POST['peso_cheio'] ?? 0);
        $peso_vazio = (float)($_POST['peso_vazio'] ?? 0);
        $capacidade_folhas = (int)($_POST['capacidade_folhas'] ?? 0);
        $preco_toner = (float)($_POST['preco_toner'] ?? 0);
        $cor = $_POST['cor'] ?? '';
        $tipo = $_POST['tipo'] ?? '';

        if ($modelo === '' || $peso_cheio <= 0 || $peso_vazio <= 0 || $capacidade_folhas <= 0 || $preco_toner <= 0 || $cor === '' || $tipo === '') {
            flash('error', 'Todos os campos são obrigatórios e devem ter valores válidos.');
            redirect('/toners/cadastro');
            return;
        }

        if ($peso_cheio <= $peso_vazio) {
            flash('error', 'O peso cheio deve ser maior que o peso vazio.');
            redirect('/toners/cadastro');
            return;
        }

        try {
            $stmt = $this->db->prepare('INSERT INTO toners (modelo, peso_cheio, peso_vazio, capacidade_folhas, preco_toner, cor, tipo) VALUES (:modelo, :peso_cheio, :peso_vazio, :capacidade_folhas, :preco_toner, :cor, :tipo)');
            $stmt->execute([
                ':modelo' => $modelo,
                ':peso_cheio' => $peso_cheio,
                ':peso_vazio' => $peso_vazio,
                ':capacidade_folhas' => $capacidade_folhas,
                ':preco_toner' => $preco_toner,
                ':cor' => $cor,
                ':tipo' => $tipo
            ]);
            flash('success', 'Toner cadastrado com sucesso.');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao cadastrar toner: ' . $e->getMessage());
        }

        redirect('/toners/cadastro');
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $modelo = trim($_POST['modelo'] ?? '');
        $peso_cheio = (float)($_POST['peso_cheio'] ?? 0);
        $peso_vazio = (float)($_POST['peso_vazio'] ?? 0);
        $capacidade_folhas = (int)($_POST['capacidade_folhas'] ?? 0);
        $preco_toner = (float)($_POST['preco_toner'] ?? 0);
        $cor = $_POST['cor'] ?? '';
        $tipo = $_POST['tipo'] ?? '';

        if ($id <= 0 || $modelo === '' || $peso_cheio <= 0 || $peso_vazio <= 0 || $capacidade_folhas <= 0 || $preco_toner <= 0 || $cor === '' || $tipo === '') {
            flash('error', 'Dados inválidos.');
            redirect('/toners/cadastro');
            return;
        }

        if ($peso_cheio <= $peso_vazio) {
            flash('error', 'O peso cheio deve ser maior que o peso vazio.');
            redirect('/toners/cadastro');
            return;
        }

        try {
            $stmt = $this->db->prepare('UPDATE toners SET modelo = :modelo, peso_cheio = :peso_cheio, peso_vazio = :peso_vazio, capacidade_folhas = :capacidade_folhas, preco_toner = :preco_toner, cor = :cor, tipo = :tipo WHERE id = :id');
            $stmt->execute([
                ':modelo' => $modelo,
                ':peso_cheio' => $peso_cheio,
                ':peso_vazio' => $peso_vazio,
                ':capacidade_folhas' => $capacidade_folhas,
                ':preco_toner' => $preco_toner,
                ':cor' => $cor,
                ':tipo' => $tipo,
                ':id' => $id
            ]);
            flash('success', 'Toner atualizado com sucesso.');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao atualizar toner: ' . $e->getMessage());
        }

        redirect('/toners/cadastro');
    }

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'ID inválido.');
            redirect('/toners/cadastro');
            return;
        }

        try {
            $stmt = $this->db->prepare('DELETE FROM toners WHERE id = :id');
            $stmt->execute([':id' => $id]);
            flash('success', 'Toner excluído com sucesso.');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao excluir toner: ' . $e->getMessage());
        }

        redirect('/toners/cadastro');
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        include $layout;
    }
}
