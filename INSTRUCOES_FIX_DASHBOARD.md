# INSTRUÇÕES - FIX ERRO 500 DASHBOARD

**Data**: 07/11/2025  
**Erro**: #1452 - Cannot add or update a child row: a foreign key constraint fails

---

## ❌ PROBLEMA

O erro acontece porque os perfis com IDs 1, 2, 3, 4, 5 **não existem** no seu banco de dados.

A tabela `profiles` no seu banco tem **IDs diferentes** (provavelmente 7, 8, 9 ou outros números).

---

## ✅ SOLUÇÃO - 3 OPÇÕES

### **OPÇÃO 1: Automático (Mais Fácil)** ⚡

Use o arquivo: **`FIX_DASHBOARD_AUTOMATICO.sql`**

**Passos:**
1. Abra o arquivo `FIX_DASHBOARD_AUTOMATICO.sql`
2. Execute **TODO o conteúdo** de uma vez
3. Vai adicionar permissão para **TODOS os perfis** automaticamente

**Vantagem**: Funciona independente dos IDs dos perfis

---

### **OPÇÃO 2: Passo a Passo (Mais Seguro)** 👣

Use o arquivo: **`FIX_DASHBOARD_PASSO_A_PASSO.sql`

**Etapa 1 - Ver os IDs:**
```sql
SELECT id, name FROM profiles ORDER BY id;
```

**Resultado (exemplo):**
```
id | name
7  | Administrador
8  | Usuário Comum
9  | Supervisor
```

**Etapa 2 - Adicionar permissões (ajuste os números!):**
```sql
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES 
    (7, 'dashboard', 1, 1, 1, 1, 1),  -- Use o ID REAL do Administrador
    (8, 'dashboard', 1, 0, 0, 0, 0),  -- Use o ID REAL do Usuário
    (9, 'dashboard', 1, 0, 0, 0, 0)   -- Use o ID REAL do Supervisor
ON DUPLICATE KEY UPDATE can_view = 1;
```

**Etapa 3 - Verificar:**
```sql
SELECT 
    p.name as perfil,
    CASE WHEN pp.can_view = 1 THEN '✅' ELSE '❌' END as 'Ver Dashboard'
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id AND pp.module = 'dashboard'
ORDER BY p.id;
```

---

### **OPÇÃO 3: Via phpMyAdmin (Visual)** 🖱️

**Passos:**
1. Acesse phpMyAdmin
2. Vá em `profile_permissions`
3. Clique em "Inserir"
4. Preencha:
   - **profile_id**: Escolha da lista (vai mostrar os perfis válidos)
   - **module**: `dashboard`
   - **can_view**: `1`
   - **can_edit**: `0` (ou `1` se for admin)
   - **can_delete**: `0` (ou `1` se for admin)
   - **can_import**: `0` (ou `1` se for admin)
   - **can_export**: `0` (ou `1` se for admin)
5. Clique em "Executar"
6. Repita para cada perfil

---

## 📊 COMPARAÇÃO DAS OPÇÕES

| Opção | Dificuldade | Velocidade | Segurança |
|-------|-------------|------------|-----------|
| **Opção 1** (Automático) | ⭐ Fácil | ⚡ Rápida | ✅ Segura |
| **Opção 2** (Passo a Passo) | ⭐⭐ Média | ⚡⚡ Média | ✅✅ Muito Segura |
| **Opção 3** (Visual) | ⭐⭐⭐ Manual | 🐌 Lenta | ✅ Segura |

---

## 🎯 RECOMENDAÇÃO

### **Use a OPÇÃO 1** (FIX_DASHBOARD_AUTOMATICO.sql)

É a mais fácil e funciona automaticamente!

```sql
-- Execute ESTE BLOCO completo:

INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT 
    id,
    'dashboard',
    1,
    CASE WHEN name LIKE '%Admin%' THEN 1 ELSE 0 END,
    CASE WHEN name LIKE '%Admin%' THEN 1 ELSE 0 END,
    CASE WHEN name LIKE '%Admin%' THEN 1 ELSE 0 END,
    CASE WHEN name LIKE '%Admin%' THEN 1 ELSE 0 END
FROM profiles
WHERE NOT EXISTS (
    SELECT 1 FROM profile_permissions pp 
    WHERE pp.profile_id = profiles.id AND pp.module = 'dashboard'
);
```

---

## ✅ APÓS EXECUTAR O SQL

1. ✅ **Logout** do sistema
2. ✅ **Login** novamente
3. ✅ **Acesse** o dashboard
4. ✅ **Deve funcionar** sem erro 500!

---

## 🔍 VERIFICAR SE DEU CERTO

Execute:
```sql
SELECT 
    p.id,
    p.name,
    COALESCE(pp.can_view, 0) as dashboard_view
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id AND pp.module = 'dashboard'
ORDER BY p.id;
```

**Todos os perfis devem ter `dashboard_view = 1`**

---

## ❓ SE AINDA NÃO FUNCIONAR

1. **Verifique se o SQL executou sem erros**
2. **Confirme que as permissões foram inseridas**:
   ```sql
   SELECT COUNT(*) FROM profile_permissions WHERE module = 'dashboard';
   ```
   Deve retornar o **número de perfis** que você tem

3. **Confirme que você fez logout/login**

4. **Ative o debug** em `.env`:
   ```env
   APP_DEBUG=true
   ```

5. **Veja o erro completo** na tela

---

## 📁 ARQUIVOS CRIADOS

1. ⚡ **`FIX_DASHBOARD_AUTOMATICO.sql`** - SQL automático (RECOMENDADO)
2. 👣 **`FIX_DASHBOARD_PASSO_A_PASSO.sql`** - SQL manual
3. ❌ ~~`FIX_DASHBOARD_RAPIDO.sql`~~ - Não use! (IDs errados)
4. 📖 **`INSTRUCOES_FIX_DASHBOARD.md`** - Este arquivo

---

## 🎓 EXPLICAÇÃO TÉCNICA

### **Por que deu erro?**

O SQL anterior tentava inserir:
```sql
VALUES (1, 'dashboard', ...)  -- Perfil ID 1
```

Mas no seu banco, o perfil ID 1 **não existe**!

Os IDs dos seus perfis são provavelmente **7, 8, 9** ou outros números.

### **Como o SQL automático resolve?**

Ele usa um `SELECT` dos perfis existentes:
```sql
INSERT INTO profile_permissions (...)
SELECT id, 'dashboard', 1, ...
FROM profiles  -- ← Pega os IDs REAIS
WHERE NOT EXISTS (...)
```

Assim funciona independente dos IDs!

---

**🚀 Execute a OPÇÃO 1 agora e teste!**

**Status**: ⏳ **AGUARDANDO EXECUÇÃO DO SQL**
