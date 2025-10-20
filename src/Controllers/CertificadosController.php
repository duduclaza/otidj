<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class CertificadosController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }

        // Títulos já cadastrados (para autocompletar/select)
        $titulos = [];
        try {
            $q = $this->db->query("SELECT id, titulo FROM certificados_titulos ORDER BY uso_count DESC, titulo ASC");
            $titulos = $q->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) { $titulos = []; }

        // Lista de certificados (grid)
        $certificados = [];
        try {
            $q = $this->db->query("SELECT c.id, c.titulo_id, c.titulo_text, c.nome_arquivo, c.tipo_arquivo, c.tamanho_arquivo, c.data_registro, c.created_at FROM certificados c ORDER BY c.created_at DESC LIMIT 100");
            $certificados = $q->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) { $certificados = []; }

        $title = 'Certificados - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/certificados/index.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    public function store()
    {
        header('Content-Type: application/json');
        try {
            if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Não autenticado']); exit; }

            $tituloId = isset($_POST['titulo_id']) && $_POST['titulo_id'] !== '' ? (int)$_POST['titulo_id'] : null;
            $tituloText = trim($_POST['titulo_text'] ?? '');
            $dataRegistro = trim($_POST['data_registro'] ?? '');

            if ($tituloId === null && $tituloText === '') { echo json_encode(['success'=>false,'message'=>'Informe um título ou selecione um existente']); exit; }
            if ($dataRegistro === '') { echo json_encode(['success'=>false,'message'=>'Informe a data de registro']); exit; }
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) { echo json_encode(['success'=>false,'message'=>'Anexe o PDF do certificado']); exit; }

            $file = $_FILES['arquivo'];
            $nomeArquivo = $file['name'];
            $tipoArquivo = $file['type'] ?: 'application/pdf';
            $tamanhoArquivo = (int)$file['size'];
            $blob = file_get_contents($file['tmp_name']);

            if ($tamanhoArquivo <= 0 || !$blob) { echo json_encode(['success'=>false,'message'=>'Arquivo inválido']); exit; }

            $this->db->beginTransaction();

            // Se não veio titulo_id, criar/obter do catálogo
            if ($tituloId === null) {
                // Tentar encontrar título igual (case insensitive)
                $stmt = $this->db->prepare("SELECT id FROM certificados_titulos WHERE LOWER(titulo) = LOWER(?) LIMIT 1");
                $stmt->execute([$tituloText]);
                $found = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($found) {
                    $tituloId = (int)$found['id'];
                } else {
                    $ins = $this->db->prepare("INSERT INTO certificados_titulos (titulo, uso_count, last_used, created_at) VALUES (?, 0, NOW(), NOW())");
                    $ins->execute([$tituloText]);
                    $tituloId = (int)$this->db->lastInsertId();
                }
            }

            // Inserir certificado
            $insC = $this->db->prepare("INSERT INTO certificados (titulo_id, titulo_text, arquivo_blob, nome_arquivo, tipo_arquivo, tamanho_arquivo, data_registro, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $insC->execute([
                $tituloId,
                ($tituloText !== '' ? $tituloText : $this->getTituloById($tituloId)),
                $blob,
                $nomeArquivo,
                $tipoArquivo,
                $tamanhoArquivo,
                $dataRegistro,
                (int)$_SESSION['user_id']
            ]);

            // Atualizar contador de uso
            $this->db->prepare("UPDATE certificados_titulos SET uso_count = uso_count + 1, last_used = NOW() WHERE id = ?")->execute([$tituloId]);

            $this->db->commit();
            echo json_encode(['success'=>true,'message'=>'Certificado registrado com sucesso']);
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            error_log('Erro ao salvar certificado: '.$e->getMessage());
            echo json_encode(['success'=>false,'message'=>'Erro ao salvar certificado']);
        }
        exit;
    }

    public function download($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT nome_arquivo, arquivo_blob, tipo_arquivo FROM certificados WHERE id = ?");
            $stmt->execute([(int)$id]);
            $c = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$c) { http_response_code(404); echo 'Arquivo não encontrado'; exit; }
            header('Content-Type: '.$c['tipo_arquivo']);
            header('Content-Disposition: attachment; filename="'.$c['nome_arquivo'].'"');
            header('Content-Length: '.strlen($c['arquivo_blob']));
            echo $c['arquivo_blob'];
        } catch (\Exception $e) { http_response_code(500); echo 'Erro ao baixar'; }
        exit;
    }

    public function delete()
    {
        header('Content-Type: application/json');
        try {
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID inválido']); exit; }
            $this->db->prepare("DELETE FROM certificados WHERE id = ?")->execute([$id]);
            echo json_encode(['success'=>true,'message'=>'Certificado excluído']);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false,'message'=>'Erro ao excluir']);
        }
        exit;
    }

    private function getTituloById(int $id): string
    {
        try {
            $s = $this->db->prepare("SELECT titulo FROM certificados_titulos WHERE id = ?");
            $s->execute([$id]);
            $r = $s->fetch(PDO::FETCH_ASSOC);
            return $r ? (string)$r['titulo'] : '';
        } catch (\Exception $e) { return ''; }
    }
}
