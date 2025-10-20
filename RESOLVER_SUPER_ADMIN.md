# 🔧 RESOLVER PROBLEMA DO SUPER ADMINISTRADOR

## **❌ PROBLEMA IDENTIFICADO**

Você está com perfil "Super Administrador" mas não consegue editar as permissões do perfil "Administrador".

**Possíveis causas:**
1. O perfil Super Administrador não está corretamente atribuído ao seu usuário
2. Cache da sessão está desatualizado
3. As permissões não foram criadas corretamente

---

## **✅ SOLUÇÃO PASSO A PASSO**

### **PASSO 1: Executar Query de Correção no phpMyAdmin**

Copie e execute esta query completa:

```sql
-- =====================================================
-- CORREÇÃO SUPER ADMINISTRADOR - du.claza@gmail.com
-- =====================================================

-- 1. Criar perfil Super Administrador se não existe
INSERT IGNORE INTO profiles (name, description, created_at) 
VALUES (
    'Super Administrador',
    'Acesso total irrestrito ao sistema, incluindo edição de perfis administrativos',
    NOW()
);

-- 2. Garantir que o usuário está com o perfil correto
UPDATE users 
SET 
    profile_id = (SELECT id FROM profiles WHERE name = 'Super Administrador' LIMIT 1),
    department = 'Compras',
    status = 'active'
WHERE email = 'du.claza@gmail.com';

-- 3. Inserir TODAS as permissões para Super Administrador
SET @super_admin_profile_id = (SELECT id FROM profiles WHERE name = 'Super Administrador' LIMIT 1);

INSERT IGNORE INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES
    (@super_admin_profile_id, 'dashboard', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'inicio', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'toners_cadastro', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'toners_retornados', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'amostragens', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'amostragens_2', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'homologacoes', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'garantias', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'controle_descartes', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'cadastro_maquinas', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'cadastro_pecas', 1, 1, 1, 1, 1),
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
    (@super_admin_profile_id, 'registros_filiais', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'registros_departamentos', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'registros_fornecedores', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'registros_parametros', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'configuracoes_gerais', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_usuarios', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_perfis', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_convites', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_painel', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'api_powerbi', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'profile', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'financeiro', 1, 1, 1, 1, 1);

-- 4. VERIFICAR se ficou correto
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

**Resultado esperado da verificação:**
- Perfil: **Super Administrador**
- Department: **Compras**
- Status: **active**

---

### **PASSO 2: Fazer LOGOUT do Sistema**

1. Acesse seu sistema
2. Clique no seu nome/avatar no canto superior direito
3. Clique em **Sair/Logout**
4. **IMPORTANTE:** Aguarde a página carregar completamente

---

### **PASSO 3: Fazer LOGIN Novamente**

1. Faça login com: **du.claza@gmail.com**
2. Digite sua senha
3. Aguarde o sistema carregar

---

### **PASSO 4: Verificar se Funcionou**

1. Acesse: **Administrativo > Gerenciar Perfis**
2. Localize o perfil **Administrador** na lista
3. Clique em **Editar** (ícone de lápis)
4. **DEVE ABRIR O FORMULÁRIO** de edição

---

## **🔍 VERIFICAÇÕES ADICIONAIS**

### **Se ainda não funcionar, execute estas queries de diagnóstico:**

```sql
-- Verificar seu perfil atual
SELECT 
    u.id,
    u.name,
    u.email,
    u.profile_id,
    p.name as perfil_nome
FROM users u
LEFT JOIN profiles p ON u.profile_id = p.id
WHERE u.email = 'du.claza@gmail.com';

-- Verificar permissões do Super Admin
SELECT COUNT(*) as total_permissoes
FROM profile_permissions pp
WHERE pp.profile_id = (SELECT id FROM profiles WHERE name = 'Super Administrador');

-- Deve retornar: 36 permissões
```

---

## **💡 PROTEÇÃO DUPLA IMPLEMENTADA**

O código agora tem **DUAS verificações** para garantir que você pode editar:

### **Verificação 1: Por Perfil**
```php
PermissionService::isSuperAdmin($_SESSION['user_id'])
```
Verifica se seu perfil é "Super Administrador"

### **Verificação 2: Por Email (FAILSAFE)**
```php
$_SESSION['user_email'] === 'du.claza@gmail.com'
```
Verifica diretamente se é você pelo email

**Isso significa:** Mesmo que o perfil não esteja 100% configurado, você (du.claza@gmail.com) **SEMPRE** poderá editar o perfil Administrador.

---

## **❓ AINDA TEM PROBLEMA?**

### **Teste Manual no Banco:**

```sql
-- Forçar update direto
UPDATE users 
SET profile_id = (SELECT id FROM profiles WHERE name = 'Super Administrador') 
WHERE email = 'du.claza@gmail.com';

-- Verificar resultado
SELECT * FROM users WHERE email = 'du.claza@gmail.com';
```

### **Limpar Cache do Navegador:**

1. **Chrome/Edge:** Ctrl + Shift + Delete
2. **Firefox:** Ctrl + Shift + Delete
3. Marque: Cookies e Cache
4. Clique em "Limpar dados"

---

## **✅ CHECKLIST FINAL**

- [ ] Query SQL executada no phpMyAdmin
- [ ] Resultado da verificação mostra "Super Administrador"
- [ ] Logout realizado
- [ ] Login realizado novamente
- [ ] Acessou "Gerenciar Perfis"
- [ ] Conseguiu clicar em "Editar" no perfil Administrador

---

**Após seguir estes passos, o sistema DEVE funcionar!** 🎉

Se ainda tiver problemas, me avise qual mensagem de erro aparece.
