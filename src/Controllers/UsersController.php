<?php

namespace App\Controllers;

use App\Config\Database;

class UsersController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getUsers()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        
        try {
            $stmt = $this->db->prepare("SELECT id, name, email, setor, filial FROM users ORDER BY name ASC");
            $stmt->execute();
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode($users);
        } catch (\Exception $e) {
            echo json_encode(['error' => 'Erro ao carregar usuários: ' . $e->getMessage()]);
        }
    }
}
