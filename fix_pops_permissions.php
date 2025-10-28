<?php
/**
 * Script para garantir permissões de POPs e ITs para Admin e Super Admin
 * Execute: php fix_pops_permissions.php
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Config/Database.php';

try {
    echo "🔧 Verificando permissões de POPs e ITs para Admin e Super Admin...\n\n";
    
    $db = \App\Config\Database::getInstance()->getConnection();
    
    // Buscar perfis de Admin e Super Admin
    $stmt = $db->query("SELECT id, name FROM profiles WHERE name IN ('Administrador', 'Super Admin')");
    $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($profiles)) {
        echo "⚠️  Nenhum perfil Admin encontrado!\n";
        exit(1);
    }
    
    echo "✅ Perfis encontrados:\n";
    foreach ($profiles as $profile) {
        echo "   - {$profile['name']} (ID: {$profile['id']})\n";
    }
    echo "\n";
    
    // Módulos POPs e ITs
    $modules = [
        'pops_its_visualizacao',
        'pops_its_cadastro_titulos',
        'pops_its_meus_registros',
        'pops_its_pendente_aprovacao',
        'pops_its_solicitacoes'
    ];
    
    $permissions = ['view', 'edit', 'delete'];
    
    echo "🔐 Garantindo permissões...\n\n";
    
    foreach ($profiles as $profile) {
        foreach ($modules as $module) {
            foreach ($permissions as $permission) {
                // Verificar se já existe
                $stmt = $db->prepare("
                    SELECT id FROM profile_permissions 
                    WHERE profile_id = ? AND module_name = ? AND permission_type = ?
                ");
                $stmt->execute([$profile['id'], $module, $permission]);
                
                if (!$stmt->fetch()) {
                    // Inserir permissão
                    $stmt = $db->prepare("
                        INSERT INTO profile_permissions (profile_id, module_name, permission_type)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$profile['id'], $module, $permission]);
                    echo "   ✅ {$profile['name']}: {$module} - {$permission}\n";
                }
            }
        }
    }
    
    echo "\n✅ Permissões configuradas com sucesso!\n";
    echo "\n💡 Faça logout e login novamente para as mudanças terem efeito.\n";
    echo "💡 Limpe o cache do navegador se necessário (Ctrl+Shift+Delete).\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
