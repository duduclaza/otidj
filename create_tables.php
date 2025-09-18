<?php

require_once __DIR__ . '/src/Config/Database.php';

use App\Config\Database;

try {
    echo "Conectando ao banco de dados...\n";
    $db = Database::getInstance();
    
    echo "Criando tabelas de solicitações de melhorias...\n";
    
    // Create solicitacoes_melhorias table
    $db->exec("
        CREATE TABLE IF NOT EXISTS solicitacoes_melhorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            usuario_nome VARCHAR(255) NOT NULL,
            data_solicitacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            setor VARCHAR(255) NOT NULL,
            processo TEXT NOT NULL,
            descricao_melhoria TEXT NOT NULL,
            status ENUM('pendente', 'em_analise', 'aprovado', 'rejeitado', 'implementado') NOT NULL DEFAULT 'pendente',
            observacoes TEXT,
            resultado_esperado TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "Tabela solicitacoes_melhorias criada com sucesso!\n";

    // Create solicitacoes_melhorias_responsaveis table (many-to-many)
    $db->exec("
        CREATE TABLE IF NOT EXISTS solicitacoes_melhorias_responsaveis (
            id INT AUTO_INCREMENT PRIMARY KEY,
            solicitacao_id INT NOT NULL,
            usuario_id INT NOT NULL,
            usuario_nome VARCHAR(255) NOT NULL,
            usuario_email VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_solicitacao_responsavel (solicitacao_id, usuario_id)
        )
    ");
    echo "Tabela solicitacoes_melhorias_responsaveis criada com sucesso!\n";

    // Create solicitacoes_melhorias_anexos table
    $db->exec("
        CREATE TABLE IF NOT EXISTS solicitacoes_melhorias_anexos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            solicitacao_id INT NOT NULL,
            nome_arquivo VARCHAR(255) NOT NULL,
            nome_original VARCHAR(255) NOT NULL,
            tipo_arquivo VARCHAR(100) NOT NULL,
            tamanho_arquivo INT NOT NULL,
            caminho_arquivo VARCHAR(500) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Tabela solicitacoes_melhorias_anexos criada com sucesso!\n";
    
    // Add solicitacao_melhorias module to existing profiles
    echo "Adicionando permissões aos perfis existentes...\n";
    
    // Get all profiles
    $stmt = $db->prepare("SELECT id, name FROM profiles");
    $stmt->execute();
    $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($profiles as $profile) {
        // Add solicitacao_melhorias permissions based on profile type
        $canEdit = 0;
        $canDelete = 0;
        $canView = 1;

        if ($profile['name'] === 'Administrador') {
            $canEdit = 1;
            $canDelete = 1;
        } elseif (in_array($profile['name'], ['Supervisor', 'Analista de Qualidade'])) {
            $canEdit = 1;
        }

        $stmt = $db->prepare("
            INSERT IGNORE INTO profile_permissions 
            (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
            VALUES (?, 'solicitacao_melhorias', ?, ?, ?, 0, 1)
        ");
        $stmt->execute([$profile['id'], $canView, $canEdit, $canDelete]);
        
        echo "Permissões adicionadas para o perfil: " . $profile['name'] . "\n";
    }
    
    echo "\nTodas as tabelas e permissões foram criadas com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
