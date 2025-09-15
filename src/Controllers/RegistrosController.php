<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class RegistrosController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function index(): void
    {
        // Load current rows
        $filiais = $this->db->query('SELECT id, nome FROM filiais ORDER BY nome')->fetchAll();
        $departamentos = $this->db->query('SELECT id, nome FROM departamentos ORDER BY nome')->fetchAll();
        $fornecedores = $this->db->query('SELECT id, nome, contato, rma FROM fornecedores ORDER BY nome')->fetchAll();
        $parametros = $this->db->query('SELECT id, nome, faixa_min, faixa_max, orientacao FROM parametros_retornados ORDER BY faixa_min')->fetchAll();

        $this->render('registros_gerais', [
            'title' => 'Registros Gerais',
            'filiais' => $filiais,
            'departamentos' => $departamentos,
            'fornecedores' => $fornecedores,
            'parametros' => $parametros,
        ]);
    }

    public function storeFilial(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '') { flash('error', 'Informe o nome da filial.'); return redirect('/registros-gerais#filiais'); }
        $stmt = $this->db->prepare('INSERT INTO filiais (nome) VALUES (:nome)');
        $stmt->execute([':nome' => $nome]);
        flash('success', 'Filial cadastrada com sucesso.');
        redirect('/registros-gerais#filiais');
    }

    public function storeDepartamento(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '') { flash('error', 'Informe o nome do departamento.'); return redirect('/registros-gerais#departamentos'); }
        $stmt = $this->db->prepare('INSERT INTO departamentos (nome) VALUES (:nome)');
        $stmt->execute([':nome' => $nome]);
        flash('success', 'Departamento cadastrado com sucesso.');
        redirect('/registros-gerais#departamentos');
    }

    public function storeFornecedor(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        $contato = trim($_POST['contato'] ?? '');
        $rma = trim($_POST['rma'] ?? '');
        if ($nome === '') { flash('error', 'O nome do fornecedor é obrigatório.'); return redirect('/registros-gerais#fornecedores'); }
        $stmt = $this->db->prepare('INSERT INTO fornecedores (nome, contato, rma) VALUES (:n, :c, :r)');
        $stmt->execute([':n' => $nome, ':c' => $contato, ':r' => $rma]);
        flash('success', 'Fornecedor cadastrado com sucesso.');
        redirect('/registros-gerais#fornecedores');
    }

    public function storeParametro(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        $faixa_min = (int)($_POST['faixa_min'] ?? 0);
        $faixa_max = isset($_POST['faixa_max']) && $_POST['faixa_max'] !== '' ? (int)$_POST['faixa_max'] : null;
        $orientacao = trim($_POST['orientacao'] ?? '');
        if ($nome === '' || $orientacao === '') { flash('error', 'Preencha nome e orientação.'); return redirect('/registros-gerais#parametros'); }
        $stmt = $this->db->prepare('INSERT INTO parametros_retornados (nome, faixa_min, faixa_max, orientacao) VALUES (:n, :min, :max, :o)');
        $stmt->bindValue(':n', $nome);
        $stmt->bindValue(':min', $faixa_min, \PDO::PARAM_INT);
        $stmt->bindValue(':max', $faixa_max, $faixa_max === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $stmt->bindValue(':o', $orientacao);
        $stmt->execute();
        flash('success', 'Parâmetro cadastrado com sucesso.');
        redirect('/registros-gerais#parametros');
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        include $layout;
    }
}
