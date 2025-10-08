<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class PecasController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * API: Lista todas as peças para seleção em dropdowns
     * Usado em: Amostragens 2.0, Garantias
     */
    public function apiListPecas(): void
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->query("
                SELECT 
                    id,
                    codigo_referencia,
                    descricao,
                    CONCAT(codigo_referencia, ' - ', descricao) as nome
                FROM cadastro_pecas
                ORDER BY codigo_referencia
            ");
            
            $pecas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($pecas);
            
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erro ao buscar peças',
                'message' => $e->getMessage()
            ]);
        }
    }
}
