-- ==========================================
-- MIGRATION PARTE 2: Adicionar Permissões
-- Data: 2024-10-16
-- Descrição: Adiciona permissões do módulo (DML)
-- ATENÇÃO: Execute SOMENTE após 01_create_homologacoes_tables.sql
-- ==========================================

-- 1. VERIFICAR SE TABELA profile_permissions EXISTE
SET @table_exists := (
    SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'profile_permissions'
);

-- 2. VERIFICAR SE TABELA profiles EXISTE
SET @profiles_exists := (
    SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'profiles'
);

-- 3. EXIBIR STATUS DAS TABELAS
SELECT 
    CASE 
        WHEN @table_exists = 1 THEN '✅ profile_permissions existe'
        ELSE '❌ profile_permissions NÃO EXISTE'
    END as Status_Profile_Permissions,
    CASE 
        WHEN @profiles_exists = 1 THEN '✅ profiles existe'
        ELSE '❌ profiles NÃO EXISTE'
    END as Status_Profiles;

-- 4. INSERIR PERMISSÕES (apenas se ambas as tabelas existirem)
INSERT IGNORE INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT 
    p.id,
    'homologacoes' as module,
    CASE 
        WHEN p.name IN ('Administrador', 'Super Admin', 'Admin') THEN 1 
        ELSE 0 
    END as can_view,
    CASE 
        WHEN p.name IN ('Administrador', 'Super Admin', 'Admin') THEN 1 
        ELSE 0 
    END as can_edit,
    CASE 
        WHEN p.name IN ('Administrador', 'Super Admin', 'Admin') THEN 1 
        ELSE 0 
    END as can_delete,
    0 as can_import,
    CASE 
        WHEN p.name IN ('Administrador', 'Super Admin', 'Admin') THEN 1 
        ELSE 0 
    END as can_export
FROM profiles p
WHERE @table_exists = 1 
AND @profiles_exists = 1
AND NOT EXISTS (
    SELECT 1 FROM profile_permissions pp 
    WHERE pp.profile_id = p.id AND pp.module = 'homologacoes'
);

-- 5. VERIFICAÇÃO FINAL
SELECT 
    CASE 
        WHEN @table_exists = 0 THEN '⚠️ AVISO: Tabela profile_permissions não existe. Permissões não adicionadas.'
        WHEN @profiles_exists = 0 THEN '⚠️ AVISO: Tabela profiles não existe. Permissões não adicionadas.'
        WHEN COUNT(*) > 0 THEN CONCAT('✅ Permissões configuradas para ', COUNT(*), ' perfis')
        ELSE '⚠️ Nenhuma permissão adicionada (pode já existir)'
    END as Status
FROM profile_permissions 
WHERE module = 'homologacoes'
AND @table_exists = 1;

-- 6. LISTAR PERMISSÕES CONFIGURADAS
SELECT 
    p.name as Perfil,
    pp.can_view as Ver,
    pp.can_edit as Editar,
    pp.can_delete as Excluir,
    pp.can_export as Exportar
FROM profile_permissions pp
JOIN profiles p ON p.id = pp.profile_id
WHERE pp.module = 'homologacoes'
AND @table_exists = 1
AND @profiles_exists = 1
ORDER BY p.name;

SELECT '🎉 Instalação concluída! Acesse: /homologacoes' as 'Finalizado';
