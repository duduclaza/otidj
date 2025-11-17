-- =====================================================
-- PERMISSÕES DE NÃO CONFORMIDADES PARA ADMINS
-- =====================================================
-- Data: 2025-11-17
-- Descrição: Dar permissão total aos admins para usar NC
-- =====================================================

-- OPÇÃO 1: Se você tem tabela profile_permissions
-- =====================================================
/*
-- Dar todas as permissões para perfil de Admin e Super Admin
INSERT INTO profile_permissions (profile_id, module_key, can_view, can_create, can_edit, can_delete)
SELECT 
  p.id,
  'nao_conformidades',
  1, 1, 1, 1
FROM profiles p
WHERE p.name IN ('Super Administrador', 'Administrador', 'Admin')
ON DUPLICATE KEY UPDATE 
  can_view = 1, 
  can_create = 1, 
  can_edit = 1, 
  can_delete = 1;
*/

-- OPÇÃO 2: Se você tem tabela permissions
-- =====================================================
/*
-- Inserir permissões individuais
INSERT INTO permissions (profile_id, module_id, action)
SELECT 
  p.id,
  m.id,
  action
FROM profiles p
CROSS JOIN modules m
CROSS JOIN (
  SELECT 'view' as action
  UNION SELECT 'create'
  UNION SELECT 'edit'
  UNION SELECT 'delete'
) actions
WHERE p.name IN ('Super Administrador', 'Administrador')
  AND m.key = 'nao_conformidades'
ON DUPLICATE KEY UPDATE action = action;
*/

-- OPÇÃO 3: Se você usa JSON de permissões no perfil
-- =====================================================
/*
-- Atualizar campo JSON de permissões no perfil
UPDATE profiles 
SET permissions = JSON_SET(
  COALESCE(permissions, '{}'),
  '$.nao_conformidades.view', true,
  '$.nao_conformidades.create', true,
  '$.nao_conformidades.edit', true,
  '$.nao_conformidades.delete', true
)
WHERE name IN ('Super Administrador', 'Administrador');
*/

-- =====================================================
-- CRIAR USUÁRIOS ADMIN SE NÃO EXISTIREM
-- =====================================================

-- Verificar usuários admin
SELECT u.id, u.name, u.email, u.role 
FROM users u 
WHERE u.role IN ('admin', 'super_admin')
ORDER BY u.role DESC, u.name;

-- =====================================================
-- NOTAS IMPORTANTES
-- =====================================================
/*
📝 COMO FUNCIONA O MÓDULO NC:

1. PERMISSÕES NO CÓDIGO (NaoConformidadesController.php):
   - Verifica: $_SESSION['user_role'] === 'admin' OU 'super_admin'
   - Super Admin tem acesso total via PermissionService::isSuperAdmin()

2. QUEM PODE USAR:
   ✅ Admins (role = 'admin')
   ✅ Super Admins (role = 'super_admin')
   ✅ Email hardcoded: du.claza@gmail.com (sempre super admin)

3. FUNCIONALIDADES:
   - Criar NC: apenas admins e super admins
   - Ver NC: todos que têm acesso ao módulo
   - Registrar Ação: responsável da NC ou admins
   - Marcar Solucionada: criador, responsável ou admins

4. NÃO PRECISA DE TABELA DE PERMISSÕES:
   O sistema já verifica role diretamente!
   Mas o módulo precisa estar na lista do profiles.php
   para aparecer em "Gerenciar Perfis"
*/

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
