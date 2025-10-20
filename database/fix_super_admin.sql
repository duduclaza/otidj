-- =====================================================
-- CORREÇÃO SUPER ADMINISTRADOR - du.claza@gmail.com
-- Execute esta query para garantir que está funcionando
-- =====================================================

-- 1. DIAGNÓSTICO: Verificar situação atual do usuário
SELECT 
    u.id,
    u.name,
    u.email,
    u.profile_id,
    p.name as perfil_nome,
    u.department,
    u.status,
    'SITUAÇÃO ATUAL' as diagnostico
FROM users u
LEFT JOIN profiles p ON u.profile_id = p.id
WHERE u.email = 'du.claza@gmail.com';

-- 2. Verificar se perfil Super Administrador existe
SELECT 
    id,
    name,
    description,
    'PERFIL SUPER ADMIN' as diagnostico
FROM profiles 
WHERE name = 'Super Administrador';

-- 3. CORREÇÃO: Garantir que o perfil existe
INSERT IGNORE INTO profiles (name, description, created_at) 
VALUES (
    'Super Administrador',
    'Acesso total irrestrito ao sistema, incluindo edição de perfis administrativos',
    NOW()
);

-- 4. CORREÇÃO: Atribuir perfil ao usuário du.claza@gmail.com
UPDATE users 
SET 
    profile_id = (SELECT id FROM profiles WHERE name = 'Super Administrador' LIMIT 1),
    department = 'Compras',
    status = 'active'
WHERE email = 'du.claza@gmail.com';

-- 5. VERIFICAÇÃO FINAL: Confirmar que foi atualizado
SELECT 
    u.id,
    u.name,
    u.email,
    u.profile_id,
    p.name as perfil_nome,
    u.department,
    u.status,
    'RESULTADO FINAL' as diagnostico
FROM users u
LEFT JOIN profiles p ON u.profile_id = p.id
WHERE u.email = 'du.claza@gmail.com';

-- 6. Verificar permissões do perfil
SELECT 
    pp.module,
    pp.can_view,
    pp.can_edit,
    pp.can_delete,
    'PERMISSÕES SUPER ADMIN' as diagnostico
FROM profile_permissions pp
WHERE pp.profile_id = (SELECT id FROM profiles WHERE name = 'Super Administrador' LIMIT 1)
ORDER BY pp.module;

-- =====================================================
-- APÓS EXECUTAR ESTA QUERY:
-- 1. Faça LOGOUT do sistema
-- 2. Faça LOGIN novamente com du.claza@gmail.com
-- 3. Tente editar o perfil Administrador
-- =====================================================
