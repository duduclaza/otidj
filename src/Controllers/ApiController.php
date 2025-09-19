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
            $setores = [];
            
            // Tenta várias tabelas possíveis
            $tables = ['departments', 'departamentos', 'setores'];
            
            foreach ($tables as $table) {
                try {
                    $stmt = $this->db->query("SELECT name FROM {$table} WHERE name IS NOT NULL AND name <> '' ORDER BY name");
                    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (!empty($result)) {
                        $setores = $result;
                        break;
                    }
                } catch (\Exception $e) {
                    // Tabela não existe, continua
                    continue;
                }
            }
            
            // Se não encontrar em tabelas específicas, busca dos usuários
            if (empty($setores)) {
                $stmt = $this->db->query("SELECT DISTINCT setor as name FROM users WHERE setor IS NOT NULL AND setor <> '' ORDER BY setor");
                $setores = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            
            error_log('Setores encontrados: ' . json_encode($setores));
            
            echo json_encode([
                'success' => true,
                'data' => $setores,
                'count' => count($setores)
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao carregar setores: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar setores: ' . $e->getMessage()
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
            $filiais = [];
            
            // Tenta várias tabelas possíveis
            $tables = ['filiais', 'branches', 'subsidiarias'];
            
            foreach ($tables as $table) {
                try {
                    $stmt = $this->db->query("SELECT name FROM {$table} WHERE name IS NOT NULL AND name <> '' ORDER BY name");
                    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (!empty($result)) {
                        $filiais = $result;
                        break;
                    }
                } catch (\Exception $e) {
                    // Tabela não existe, continua
                    continue;
                }
            }
            
            // Se não encontrar em tabelas específicas, busca dos usuários
            if (empty($filiais)) {
                $stmt = $this->db->query("SELECT DISTINCT filial as name FROM users WHERE filial IS NOT NULL AND filial <> '' ORDER BY filial");
                $filiais = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            
            error_log('Filiais encontradas: ' . json_encode($filiais));
            
            echo json_encode([
                'success' => true,
                'data' => $filiais,
                'count' => count($filiais)
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao carregar filiais: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar filiais: ' . $e->getMessage()
            ]);
        }
    }
}
