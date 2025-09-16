<?php

namespace App\Controllers;

use App\Config\Database;

class AmostragemController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM amostragens ORDER BY data_registro DESC");
            $stmt->execute();
            $amostragens = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return view('pages/toners/amostragens', ['amostragens' => $amostragens]);
        } catch (\Exception $e) {
            return view('pages/toners/amostragens', ['error' => 'Erro ao carregar amostragens: ' . $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            $numero_nf = $_POST['numero_nf'] ?? '';
            $status = $_POST['status'] ?? '';
            $observacao = $_POST['observacao'] ?? '';
            
            if (empty($numero_nf) || empty($status)) {
                return json_encode(['success' => false, 'message' => 'Número da NF e status são obrigatórios']);
            }

            // Handle file upload for NF PDF
            $arquivo_nf = '';
            if (isset($_FILES['arquivo_nf']) && $_FILES['arquivo_nf']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/nf/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid() . '_' . $_FILES['arquivo_nf']['name'];
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['arquivo_nf']['tmp_name'], $uploadPath)) {
                    $arquivo_nf = 'nf/' . $fileName;
                }
            }

            // Handle evidence files for rejected samples
            $evidencias = [];
            if ($status === 'reprovado' && isset($_FILES['evidencias'])) {
                $uploadDir = 'uploads/evidencias/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                foreach ($_FILES['evidencias']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['evidencias']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileName = uniqid() . '_' . $_FILES['evidencias']['name'][$key];
                        $uploadPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            $evidencias[] = 'evidencias/' . $fileName;
                        }
                    }
                }
            }

            $stmt = $this->db->prepare("
                INSERT INTO amostragens (numero_nf, status, observacao, arquivo_nf, evidencias, data_registro) 
                VALUES (:numero_nf, :status, :observacao, :arquivo_nf, :evidencias, NOW())
            ");

            $stmt->execute([
                ':numero_nf' => $numero_nf,
                ':status' => $status,
                ':observacao' => $observacao,
                ':arquivo_nf' => $arquivo_nf,
                ':evidencias' => json_encode($evidencias)
            ]);

            return json_encode(['success' => true, 'message' => 'Amostragem registrada com sucesso!']);

        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => 'Erro ao salvar amostragem: ' . $e->getMessage()]);
        }
    }

    public function update($id)
    {
        try {
            $numero_nf = $_POST['numero_nf'] ?? '';
            $status = $_POST['status'] ?? '';
            $observacao = $_POST['observacao'] ?? '';
            
            if (empty($numero_nf) || empty($status)) {
                return json_encode(['success' => false, 'message' => 'Número da NF e status são obrigatórios']);
            }

            $stmt = $this->db->prepare("
                UPDATE amostragens 
                SET numero_nf = :numero_nf, status = :status, observacao = :observacao 
                WHERE id = :id
            ");

            $stmt->execute([
                ':numero_nf' => $numero_nf,
                ':status' => $status,
                ':observacao' => $observacao,
                ':id' => $id
            ]);

            return json_encode(['success' => true, 'message' => 'Amostragem atualizada com sucesso!']);

        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => 'Erro ao atualizar amostragem: ' . $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        try {
            // Get file paths before deletion
            $stmt = $this->db->prepare("SELECT arquivo_nf, evidencias FROM amostragens WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $amostragem = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($amostragem) {
                // Delete files
                if (!empty($amostragem['arquivo_nf']) && file_exists('uploads/' . $amostragem['arquivo_nf'])) {
                    unlink('uploads/' . $amostragem['arquivo_nf']);
                }

                if (!empty($amostragem['evidencias'])) {
                    $evidencias = json_decode($amostragem['evidencias'], true);
                    foreach ($evidencias as $evidencia) {
                        if (file_exists('uploads/' . $evidencia)) {
                            unlink('uploads/' . $evidencia);
                        }
                    }
                }
            }

            // Delete record
            $stmt = $this->db->prepare("DELETE FROM amostragens WHERE id = :id");
            $stmt->execute([':id' => $id]);

            return json_encode(['success' => true, 'message' => 'Amostragem excluída com sucesso!']);

        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => 'Erro ao excluir amostragem: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM amostragens WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $amostragem = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$amostragem) {
                return json_encode(['success' => false, 'message' => 'Amostragem não encontrada']);
            }

            return json_encode(['success' => true, 'data' => $amostragem]);

        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => 'Erro ao buscar amostragem: ' . $e->getMessage()]);
        }
    }
}
