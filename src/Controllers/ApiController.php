<?php

namespace App\Controllers;

use App\Config\Database;

class ApiController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all departments/setores
     */
    public function getSetores()
    {
        header('Content-Type: application/json');
        
        try {
            // Primeiro tenta buscar da tabela departments
            $stmt = $this->db->query("SELECT name FROM departments WHERE name IS NOT NULL AND name <> '' ORDER BY name");
            $setores = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Se não encontrar, busca dos usuários como fallback
            if (empty($setores)) {
                $stmt = $this->db->query("SELECT DISTINCT setor as name FROM users WHERE setor IS NOT NULL AND setor <> '' ORDER BY setor");
                $setores = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $setores
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar setores'
            ]);
        }
    }
    
    /**
     * Get all filiais
     */
    public function getFiliais()
    {
        header('Content-Type: application/json');
        
        try {
            // Primeiro tenta buscar da tabela filiais
            $stmt = $this->db->query("SELECT name FROM filiais WHERE name IS NOT NULL AND name <> '' ORDER BY name");
            $filiais = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Se não encontrar, busca dos usuários como fallback
            if (empty($filiais)) {
                $stmt = $this->db->query("SELECT DISTINCT filial as name FROM users WHERE filial IS NOT NULL AND filial <> '' ORDER BY filial");
                $filiais = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $filiais
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar filiais'
            ]);
        }
    }
}
