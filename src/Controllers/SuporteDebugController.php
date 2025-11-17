<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class SuporteDebugController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function debug(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        
        echo "<h1>🔍 Diagnóstico do Sistema de Suporte</h1>";
        echo "<hr>";
        
        // 1. Verificar sessão
        echo "<h2>1️⃣ Dados da Sessão</h2>";
        echo "<pre>";
        echo "user_id: " . ($_SESSION['user_id'] ?? 'NÃO DEFINIDO') . "\n";
        echo "user_role: " . ($_SESSION['user_role'] ?? 'NÃO DEFINIDO') . "\n";
        echo "user_email: " . ($_SESSION['user_email'] ?? 'NÃO DEFINIDO') . "\n";
        echo "</pre>";
        
        // 2. Verificar usuário no banco
        echo "<h2>2️⃣ Dados do Usuário no Banco</h2>";
        if (isset($_SESSION['user_email'])) {
            $stmt = $this->db->prepare('SELECT id, name, email, user_role FROM users WHERE email = :email');
            $stmt->execute([':email' => $_SESSION['user_email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo "<pre>";
                print_r($user);
                echo "</pre>";
            } else {
                echo "<p style='color: red;'>❌ Usuário não encontrado no banco!</p>";
            }
        }
        
        // 3. Verificar super admin específico
        echo "<h2>3️⃣ Verificar du.claza@gmail.com</h2>";
        $stmt = $this->db->prepare('SELECT id, name, email, user_role FROM users WHERE email = :email');
        $stmt->execute([':email' => 'du.claza@gmail.com']);
        $duclaza = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($duclaza) {
            echo "<pre>";
            print_r($duclaza);
            echo "</pre>";
            
            if ($duclaza['user_role'] !== 'super_admin') {
                echo "<p style='color: orange;'>⚠️ PROBLEMA: user_role = '{$duclaza['user_role']}' mas deveria ser 'super_admin'</p>";
            } else {
                echo "<p style='color: green;'>✅ user_role correto: super_admin</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Usuário du.claza@gmail.com não encontrado!</p>";
        }
        
        // 4. Listar todos admins e super admins
        echo "<h2>4️⃣ Todos Admins e Super Admins</h2>";
        $stmt = $this->db->query("SELECT id, name, email, user_role FROM users WHERE user_role IN ('admin', 'super_admin') ORDER BY user_role, name");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Role</th></tr>";
        foreach ($admins as $admin) {
            $color = $admin['user_role'] === 'super_admin' ? '#e0ffe0' : '#fff';
            echo "<tr style='background: {$color};'>";
            echo "<td>{$admin['id']}</td>";
            echo "<td>{$admin['name']}</td>";
            echo "<td>{$admin['email']}</td>";
            echo "<td><strong>{$admin['user_role']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 5. Verificar tabela de solicitações
        echo "<h2>5️⃣ Solicitações de Suporte Existentes</h2>";
        $stmt = $this->db->query("
            SELECT s.id, s.titulo, s.status, s.created_at,
                   u.name as solicitante_nome, u.email as solicitante_email, u.id as solicitante_id
            FROM suporte_solicitacoes s
            LEFT JOIN users u ON s.solicitante_id = u.id
            ORDER BY s.created_at DESC
        ");
        $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($solicitacoes)) {
            echo "<p>Nenhuma solicitação encontrada.</p>";
        } else {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Título</th><th>Status</th><th>Solicitante</th><th>Email</th><th>Data</th></tr>";
            foreach ($solicitacoes as $sol) {
                echo "<tr>";
                echo "<td>{$sol['id']}</td>";
                echo "<td>{$sol['titulo']}</td>";
                echo "<td>{$sol['status']}</td>";
                echo "<td>{$sol['solicitante_nome']}</td>";
                echo "<td>{$sol['solicitante_email']}</td>";
                echo "<td>{$sol['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // 6. Testar query do super admin
        echo "<h2>6️⃣ Teste: Query que Super Admin Deveria Ver</h2>";
        $stmt = $this->db->query("
            SELECT s.*, u.name as solicitante_nome, u.email as solicitante_email
            FROM suporte_solicitacoes s
            LEFT JOIN users u ON s.solicitante_id = u.id
            ORDER BY FIELD(s.status, 'Pendente', 'Em Análise', 'Concluído'), s.created_at DESC
        ");
        $todasSolicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Total de solicitações: " . count($todasSolicitacoes) . "</strong></p>";
        
        if (count($todasSolicitacoes) > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Título</th><th>Status</th><th>Solicitante</th></tr>";
            foreach ($todasSolicitacoes as $sol) {
                echo "<tr>";
                echo "<td>{$sol['id']}</td>";
                echo "<td>{$sol['titulo']}</td>";
                echo "<td>{$sol['status']}</td>";
                echo "<td>{$sol['solicitante_nome']} ({$sol['solicitante_email']})</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // 7. Solução
        echo "<hr>";
        echo "<h2>💡 Solução</h2>";
        
        if ($duclaza && $duclaza['user_role'] !== 'super_admin') {
            echo "<p style='background: #ffeeee; padding: 10px; border-left: 4px solid red;'>";
            echo "<strong>PROBLEMA ENCONTRADO:</strong> O usuário du.claza@gmail.com tem user_role = '{$duclaza['user_role']}' mas precisa ser 'super_admin'.<br><br>";
            echo "<strong>Execute este SQL:</strong><br>";
            echo "<code style='background: #f0f0f0; padding: 10px; display: block; margin-top: 10px;'>";
            echo "UPDATE users SET user_role = 'super_admin' WHERE email = 'du.claza@gmail.com';";
            echo "</code>";
            echo "</p>";
        } else {
            echo "<p style='background: #eeffee; padding: 10px; border-left: 4px solid green;'>";
            echo "✅ user_role está correto. Verifique se você fez logout e login novamente para atualizar a sessão.";
            echo "</p>";
        }
        
        echo "<hr>";
        echo "<p><a href='/suporte'>← Voltar para Suporte</a></p>";
    }
}
