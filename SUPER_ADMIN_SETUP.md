# 🔐 CONFIGURAÇÃO DO SUPER ADMINISTRADOR

## **Usuário Master: du.claza@gmail.com**

Este documento descreve como configurar o **Super Administrador** com poderes totais no sistema SGQ-OTI DJ.

---

## **🎯 O QUE É O SUPER ADMINISTRADOR?**

O **Super Administrador** é um perfil especial que tem:

✅ **Acesso Total Irrestrito** - Todas as permissões em todos os módulos  
✅ **Pode Editar Perfil Administrador** - Único perfil que pode modificar permissões de Administradores  
✅ **Pode Excluir Perfil Administrador** - Se necessário (raro)  
✅ **Acesso a Funcionalidades Master** - Recursos exclusivos  

---

## **📋 PASSO A PASSO PARA ATIVAR**

### **1. Executar Query SQL para Criar Super Administrador**

Acesse o **phpMyAdmin** e execute a seguinte query:

```sql
-- =====================================================
-- CRIAR SUPER ADMINISTRADOR - ACESSO TOTAL
-- Usuário: du.claza@gmail.com
-- =====================================================

-- 1. Criar perfil "Super Administrador" se não existir
INSERT IGNORE INTO profiles (name, description, created_at) 
VALUES (
    'Super Administrador',
    'Acesso total irrestrito ao sistema, incluindo edição de perfis administrativos',
    NOW()
);

-- 2. Obter ID do perfil Super Administrador
SET @super_admin_profile_id = (SELECT id FROM profiles WHERE name = 'Super Administrador' LIMIT 1);

-- 3. Inserir TODAS as permissões para Super Administrador
INSERT IGNORE INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES
    -- Dashboard e Início
    (@super_admin_profile_id, 'dashboard', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'inicio', 1, 1, 1, 1, 1),
    
    -- Operacionais
    (@super_admin_profile_id, 'toners_cadastro', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'toners_retornados', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'amostragens', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'amostragens_2', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'homologacoes', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'garantias', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'controle_descartes', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'cadastro_maquinas', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'cadastro_pecas', 1, 1, 1, 1, 1),
    
    -- Gestão da Qualidade
    (@super_admin_profile_id, 'femea', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'pops_its_visualizacao', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'pops_its_cadastro_titulos', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'pops_its_meus_registros', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'pops_its_pendente_aprovacao', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'pops_its_logs_visualizacao', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'fluxogramas', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, '5w2h', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'auditorias', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'melhoria_continua', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'melhoria_continua_2', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'solicitacao_melhorias', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'controle_rc', 1, 1, 1, 1, 1),
    
    -- Registros
    (@super_admin_profile_id, 'registros_filiais', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'registros_departamentos', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'registros_fornecedores', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'registros_parametros', 1, 1, 1, 1, 1),
    
    -- Administrativo
    (@super_admin_profile_id, 'configuracoes_gerais', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_usuarios', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_perfis', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_convites', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_painel', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'api_powerbi', 1, 1, 1, 1, 1),
    
    -- Outros módulos
    (@super_admin_profile_id, 'profile', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'financeiro', 1, 1, 1, 1, 1);

-- 4. Atualizar usuário du.claza@gmail.com para Super Administrador
UPDATE users 
SET 
    profile_id = @super_admin_profile_id,
    department = 'Compras',
    status = 'active'
WHERE email = 'du.claza@gmail.com';

-- 5. Verificar se o usuário foi atualizado
SELECT 
    u.id,
    u.name,
    u.email,
    p.name as perfil,
    u.department,
    u.status
FROM users u
LEFT JOIN profiles p ON u.profile_id = p.id
WHERE u.email = 'du.claza@gmail.com';
```

---

### **2. Executar Query SQL para Homologações (se ainda não executou)**

```sql
-- Execute a query do arquivo: homologacoes_kanban_complete.sql
-- Localização: database/migrations/homologacoes_kanban_complete.sql
```

---

## **✅ VERIFICAÇÕES PÓS-INSTALAÇÃO**

### **Verificar Perfil Criado:**
```sql
SELECT * FROM profiles WHERE name = 'Super Administrador';
```

### **Verificar Permissões:**
```sql
SELECT 
    p.name as perfil,
    COUNT(pp.id) as total_permissoes,
    SUM(pp.can_view) as total_view,
    SUM(pp.can_edit) as total_edit,
    SUM(pp.can_delete) as total_delete
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id
WHERE p.name = 'Super Administrador'
GROUP BY p.id;
```

### **Verificar Usuário:**
```sql
SELECT 
    u.id,
    u.name,
    u.email,
    p.name as perfil,
    u.department
FROM users u
LEFT JOIN profiles p ON u.profile_id = p.id
WHERE u.email = 'du.claza@gmail.com';
```

---

## **🔒 DIFERENÇAS ENTRE PERFIS**

| Funcionalidade | Usuário Comum | Administrador | Super Administrador |
|---------------|---------------|---------------|---------------------|
| Visualizar módulos | ✅ (limitado) | ✅ (todos) | ✅ (todos) |
| Editar dados | ✅ (limitado) | ✅ (todos) | ✅ (todos) |
| Gerenciar usuários | ❌ | ✅ | ✅ |
| Gerenciar perfis | ❌ | ✅ (exceto Admin) | ✅ (inclusive Admin) |
| Editar permissões Admin | ❌ | ❌ | ✅ |
| Excluir perfil Admin | ❌ | ❌ | ✅ |
| Acesso Master | ❌ | ❌ | ✅ |

---

## **⚠️ SEGURANÇA E BOAS PRÁTICAS**

### **Recomendações:**

1. **Use o Super Admin com responsabilidade** - É o perfil mais poderoso do sistema
2. **Não compartilhe a senha** - Mantenha a senha segura
3. **Faça backup regular** - Antes de fazer alterações críticas
4. **Documente mudanças** - Mantenha registro de alterações em perfis
5. **Use 2FA se disponível** - Adicione camada extra de segurança

### **O que o Super Admin pode fazer:**

✅ Alterar permissões do perfil Administrador  
✅ Criar/editar/excluir qualquer perfil  
✅ Acessar todos os módulos sem restrição  
✅ Modificar configurações críticas do sistema  
✅ Gerenciar todos os usuários  
✅ Acessar logs e auditorias completas  

---

## **🎓 COMO USAR**

### **Para Editar Permissões do Administrador:**

1. Faça login como **du.claza@gmail.com**
2. Acesse **Administrativo > Gerenciar Perfis**
3. Localize o perfil **Administrador**
4. Clique em **Editar** (agora permitido para Super Admin)
5. Modifique as permissões conforme necessário
6. Salve as alterações

### **Para Criar Novo Perfil:**

1. Acesse **Administrativo > Gerenciar Perfis**
2. Clique em **Novo Perfil**
3. Configure nome, descrição e permissões
4. Salve

---

## **📞 SUPORTE**

Em caso de problemas:

1. Verifique se as queries SQL foram executadas corretamente
2. Confirme que o usuário `du.claza@gmail.com` existe no banco
3. Verifique os logs do sistema em `storage/logs/`
4. Teste o login e acesso aos módulos

---

## **📝 CHANGELOG**

**v3.0.0 - 20/10/2024**
- ✅ Criado perfil Super Administrador
- ✅ Configurado usuário du.claza@gmail.com como Super Admin
- ✅ Implementado controle de edição/exclusão de perfil Admin
- ✅ Adicionado módulo Homologações Kanban

---

**Sistema atualizado e pronto para uso!** 🚀
