<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class RegistrosController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Filiais
    public function filiais(): void
    {
        try {
            $filiais = $this->db->query('SELECT id, nome FROM filiais ORDER BY nome')->fetchAll();
        } catch (\PDOException $e) {
            $filiais = [];
        }
        $this->render('registros/filiais', ['title' => 'Filiais', 'filiais' => $filiais]);
    }

    // Departamentos
    public function departamentos(): void
    {
        try {
            $departamentos = $this->db->query('SELECT id, nome FROM departamentos ORDER BY nome')->fetchAll();
        } catch (\PDOException $e) {
            $departamentos = [];
        }
        $this->render('registros/departamentos', ['title' => 'Departamentos', 'departamentos' => $departamentos]);
    }

    // Fornecedores
    public function fornecedores(): void
    {
        try {
            $fornecedores = $this->db->query('SELECT id, nome, contato, rma FROM fornecedores ORDER BY nome')->fetchAll();
        } catch (\PDOException $e) {
            $fornecedores = [];
        }
        $this->render('registros/fornecedores', ['title' => 'Fornecedores', 'fornecedores' => $fornecedores]);
    }

    // Parâmetros
    public function parametros(): void
    {
        try {
            $parametros = $this->db->query('SELECT id, nome, faixa_min, faixa_max, orientacao FROM parametros_retornados ORDER BY faixa_min')->fetchAll();
        } catch (\PDOException $e) {
            $parametros = [];
        }
        $this->render('registros/parametros', ['title' => 'Parâmetros de Retornados', 'parametros' => $parametros]);
    }

    public function storeFilial(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '') { flash('error', 'Informe o nome da filial.'); redirect('/registros/filiais'); return; }
        $stmt = $this->db->prepare('INSERT INTO filiais (nome) VALUES (:nome)');
        $stmt->execute([':nome' => $nome]);
        flash('success', 'Filial cadastrada com sucesso.');
        redirect('/registros/filiais');
    }

    public function storeDepartamento(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '') { flash('error', 'Informe o nome do departamento.'); redirect('/registros/departamentos'); return; }
        $stmt = $this->db->prepare('INSERT INTO departamentos (nome) VALUES (:nome)');
        $stmt->execute([':nome' => $nome]);
        flash('success', 'Departamento cadastrado com sucesso.');
        redirect('/registros/departamentos');
    }

    public function storeFornecedor(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        $contato = trim($_POST['contato'] ?? '');
        $rma = trim($_POST['rma'] ?? '');
        
        if ($nome === '') { 
            flash('error', 'O nome do fornecedor é obrigatório.'); 
            redirect('/registros/fornecedores'); 
            return; 
        }
        
        try {
            $stmt = $this->db->prepare('INSERT INTO fornecedores (nome, contato, rma) VALUES (:nome, :contato, :rma)');
            $stmt->execute([
                ':nome' => $nome, 
                ':contato' => $contato ?: null, 
                ':rma' => $rma ?: null
            ]);
            flash('success', 'Fornecedor cadastrado com sucesso.');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao cadastrar fornecedor: ' . $e->getMessage());
        }
        
        redirect('/registros/fornecedores');
    }

    public function storeParametro(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        $faixa_min = (float)($_POST['faixa_min'] ?? 0);
        $faixa_max = isset($_POST['faixa_max']) && $_POST['faixa_max'] !== '' ? (float)$_POST['faixa_max'] : null;
        $orientacao = trim($_POST['orientacao'] ?? '');
        if ($nome === '' || $orientacao === '') { flash('error', 'Preencha nome e orientação.'); redirect('/registros/parametros'); return; }
        $stmt = $this->db->prepare('INSERT INTO parametros_retornados (nome, faixa_min, faixa_max, orientacao) VALUES (:n, :min, :max, :o)');
        $stmt->bindValue(':n', $nome);
        $stmt->bindValue(':min', $faixa_min);
        $stmt->bindValue(':max', $faixa_max, $faixa_max === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
        $stmt->bindValue(':o', $orientacao);
        $stmt->execute();
        flash('success', 'Parâmetro cadastrado com sucesso.');
        redirect('/registros/parametros');
    }

    // Update methods
    public function updateFilial(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        if ($id <= 0 || $nome === '') { flash('error', 'Dados inválidos.'); redirect('/registros/filiais'); return; }
        $stmt = $this->db->prepare('UPDATE filiais SET nome = :nome WHERE id = :id');
        $stmt->execute([':nome' => $nome, ':id' => $id]);
        flash('success', 'Filial atualizada com sucesso.');
        redirect('/registros/filiais');
    }

    public function deleteFilial(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { flash('error', 'ID inválido.'); redirect('/registros/filiais'); return; }
        $stmt = $this->db->prepare('DELETE FROM filiais WHERE id = :id');
        $stmt->execute([':id' => $id]);
        flash('success', 'Filial excluída com sucesso.');
        redirect('/registros/filiais');
    }

    public function updateDepartamento(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        if ($id <= 0 || $nome === '') { flash('error', 'Dados inválidos.'); redirect('/registros/departamentos'); return; }
        $stmt = $this->db->prepare('UPDATE departamentos SET nome = :nome WHERE id = :id');
        $stmt->execute([':nome' => $nome, ':id' => $id]);
        flash('success', 'Departamento atualizado com sucesso.');
        redirect('/registros/departamentos');
    }

    public function deleteDepartamento(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { flash('error', 'ID inválido.'); redirect('/registros/departamentos'); return; }
        $stmt = $this->db->prepare('DELETE FROM departamentos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        flash('success', 'Departamento excluído com sucesso.');
        redirect('/registros/departamentos');
    }

    public function updateFornecedor(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $contato = trim($_POST['contato'] ?? '');
        $rma = trim($_POST['rma'] ?? '');
        
        if ($id <= 0 || $nome === '') { 
            flash('error', 'Dados inválidos.'); 
            redirect('/registros/fornecedores'); 
            return; 
        }
        
        try {
            $stmt = $this->db->prepare('UPDATE fornecedores SET nome = :nome, contato = :contato, rma = :rma WHERE id = :id');
            $stmt->execute([
                ':nome' => $nome, 
                ':contato' => $contato ?: null, 
                ':rma' => $rma ?: null, 
                ':id' => $id
            ]);
            flash('success', 'Fornecedor atualizado com sucesso.');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao atualizar fornecedor: ' . $e->getMessage());
        }
        
        redirect('/registros/fornecedores');
    }

    public function deleteFornecedor(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { flash('error', 'ID inválido.'); redirect('/registros/fornecedores'); return; }
        $stmt = $this->db->prepare('DELETE FROM fornecedores WHERE id = :id');
        $stmt->execute([':id' => $id]);
        flash('success', 'Fornecedor excluído com sucesso.');
        redirect('/registros/fornecedores');
    }

    public function updateParametro(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $faixa_min = (float)($_POST['faixa_min'] ?? 0);
        $faixa_max = isset($_POST['faixa_max']) && $_POST['faixa_max'] !== '' ? (float)$_POST['faixa_max'] : null;
        $orientacao = trim($_POST['orientacao'] ?? '');
        if ($id <= 0 || $nome === '' || $orientacao === '') { flash('error', 'Dados inválidos.'); redirect('/registros/parametros'); return; }
        $stmt = $this->db->prepare('UPDATE parametros_retornados SET nome = :nome, faixa_min = :min, faixa_max = :max, orientacao = :orientacao WHERE id = :id');
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':min', $faixa_min);
        $stmt->bindValue(':max', $faixa_max, $faixa_max === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
        $stmt->bindValue(':orientacao', $orientacao);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        flash('success', 'Parâmetro atualizado com sucesso.');
        redirect('/registros/parametros');
    }

    public function deleteParametro(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { flash('error', 'ID inválido.'); redirect('/registros/parametros'); return; }
        $stmt = $this->db->prepare('DELETE FROM parametros_retornados WHERE id = :id');
        $stmt->execute([':id' => $id]);
        flash('success', 'Parâmetro excluído com sucesso.');
        redirect('/registros/parametros');
    }

    // API Methods
    public function getDepartamentos(): void
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare('SELECT id, nome as name FROM departamentos ORDER BY nome');
            $stmt->execute();
            $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $departamentos,
                'count' => count($departamentos)
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar departamentos: ' . $e->getMessage()
            ]);
        }
    }

    public function getFiliais(): void
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare('SELECT id, nome as name FROM filiais ORDER BY nome');
            $stmt->execute();
            $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $filiais,
                'count' => count($filiais)
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar filiais: ' . $e->getMessage()
            ]);
        }
    }

    public function getParametros(): void
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare('SELECT id, nome, faixa_min, faixa_max, orientacao FROM parametros_retornados ORDER BY faixa_min');
            $stmt->execute();
            $parametros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $parametros,
                'count' => count($parametros)
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar parâmetros: ' . $e->getMessage()
            ]);
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
