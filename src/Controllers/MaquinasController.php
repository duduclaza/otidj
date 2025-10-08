<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class MaquinasController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * API: Lista todas as máquinas para seleção em dropdowns
     * Usado em: Amostragens 2.0, Garantias
     */
    public function apiListMaquinas(): void
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->query("
                SELECT 
                    id,
                    modelo,
                    cod_referencia,
                    CONCAT(modelo, ' (Ref: ', cod_referencia, ')') as nome
                FROM cadastro_maquinas
                ORDER BY modelo
            ");
            
            $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($maquinas);
            
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erro ao buscar máquinas',
                'message' => $e->getMessage()
            ]);
        }
    }
}
