<?php

namespace App\Controllers;

/**
 * Controller de teste para diagnóstico
 */
class HomologacoesTestController
{
    public function test()
    {
        echo "<h1>✅ TESTE FUNCIONANDO!</h1>";
        echo "<p>Se você está vendo esta mensagem, o problema não é com o controller.</p>";
        
        // Verificar se as tabelas existem
        try {
            $db = \App\Config\Database::getInstance();
            
            echo "<h2>Verificando tabelas:</h2>";
            
            $tables = ['homologacoes', 'homologacoes_responsaveis', 'homologacoes_historico', 'homologacoes_anexos'];
            foreach ($tables as $table) {
                $stmt = $db->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    echo "✅ Tabela '$table' existe<br>";
                } else {
                    echo "❌ Tabela '$table' NÃO existe<br>";
                }
            }
            
            echo "<h2>Verificando usuário:</h2>";
            $stmt = $db->prepare("SELECT id, name, email, department FROM users WHERE email = 'du.claza@gmail.com'");
            $stmt->execute();
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                echo "✅ Usuário encontrado:<br>";
                echo "ID: " . $user['id'] . "<br>";
                echo "Nome: " . $user['name'] . "<br>";
                echo "Email: " . $user['email'] . "<br>";
                echo "Department: " . ($user['department'] ?? 'NULL') . "<br>";
            } else {
                echo "❌ Usuário não encontrado<br>";
            }
            
        } catch (\Exception $e) {
            echo "<h2 style='color: red;'>❌ ERRO:</h2>";
            echo "<pre>" . $e->getMessage() . "</pre>";
            echo "<p>Linha: " . $e->getLine() . "</p>";
            echo "<p>Arquivo: " . $e->getFile() . "</p>";
        }
        
        exit;
    }
}
