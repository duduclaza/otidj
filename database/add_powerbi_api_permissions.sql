-- ===== ADICIONAR MÓDULO API POWER BI AO SISTEMA DE PERMISSÕES =====

-- Inserir permissões para API Power BI no perfil Administrador
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, 'api_powerbi', 1, 1, 1, 0, 0
FROM profiles p 
WHERE p.name = 'Administrador'
ON DUPLICATE KEY UPDATE 
    can_view = 1, 
    can_edit = 1, 
    can_delete = 1;

-- Inserir permissões VIEW para Supervisores (podem visualizar mas não editar)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, 'api_powerbi', 1, 0, 0, 0, 0
FROM profiles p 
WHERE p.name = 'Supervisor'
ON DUPLICATE KEY UPDATE 
    can_view = 1;

-- Verificar permissões criadas
SELECT 
    p.name AS perfil,
    pp.module AS modulo,
    pp.can_view AS visualizar,
    pp.can_edit AS editar,
    pp.can_delete AS excluir
FROM profile_permissions pp
JOIN profiles p ON pp.profile_id = p.id
WHERE pp.module = 'api_powerbi'
ORDER BY p.name;
