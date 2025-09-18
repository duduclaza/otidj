<?php

require_once __DIR__ . '/src/Config/Database.php';

use App\Config\Database;

try {
    echo "Conectando ao banco de dados...\n";
    $db = Database::getInstance();
    
    echo "Atualizando permissões...\n";
    
    // Remove o módulo antigo 'melhoria_continua' se existir
    $stmt = $db->prepare("DELETE FROM profile_permissions WHERE module = 'melhoria_continua'");
    $stmt->execute();
    echo "Módulo 'melhoria_continua' removido das permissões.\n";
    
    // Verifica se o módulo 'solicitacao_melhorias' já existe
    $stmt = $db->prepare("SELECT COUNT(*) FROM profile_permissions WHERE module = 'solicitacao_melhorias'");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "Adicionando módulo 'solicitacao_melhorias' aos perfis...\n";
        
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
                INSERT INTO profile_permissions 
                (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, 'solicitacao_melhorias', ?, ?, ?, 0, 1)
            ");
            $stmt->execute([$profile['id'], $canView, $canEdit, $canDelete]);
            
            echo "Permissões adicionadas para o perfil: " . $profile['name'] . "\n";
        }
    } else {
        echo "Módulo 'solicitacao_melhorias' já existe nas permissões.\n";
    }
    
    // Lista todos os módulos atuais para verificação
    echo "\n=== MÓDULOS ATUAIS NO SISTEMA ===\n";
    $stmt = $db->prepare("SELECT DISTINCT module FROM profile_permissions ORDER BY module");
    $stmt->execute();
    $modules = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($modules as $module) {
        echo "• $module\n";
    }
    
    echo "\n=== PERMISSÕES POR PERFIL ===\n";
    $stmt = $db->prepare("
        SELECT p.name as profile_name, pp.module, pp.can_view, pp.can_edit, pp.can_delete 
        FROM profiles p 
        LEFT JOIN profile_permissions pp ON p.id = pp.profile_id 
        WHERE pp.module = 'solicitacao_melhorias'
        ORDER BY p.name
    ");
    $stmt->execute();
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($permissions as $perm) {
        $actions = [];
        if ($perm['can_view']) $actions[] = 'View';
        if ($perm['can_edit']) $actions[] = 'Edit';
        if ($perm['can_delete']) $actions[] = 'Delete';
        
        echo "• " . $perm['profile_name'] . ": " . implode(', ', $actions) . "\n";
    }
    
    echo "\n✅ Permissões atualizadas com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
