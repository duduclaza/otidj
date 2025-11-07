# SOLUÇÃO - ERRO 500 NO DASHBOARD

**Data**: 07/11/2025  
**Tipo**: Correção de Erro  
**Erro**: HTTP ERROR 500  
**Causa**: Falta de permissão "dashboard"

---

## 🐛 PROBLEMA IDENTIFICADO

### **Erro:**
```
Esta página não está a funcionar
djbr.sgqoti.com.br não consegue processar este pedido de momento.
HTTP ERROR 500
```

### **Causa Raiz:**
O usuário não possui permissão de **VIEW** para o módulo `dashboard` na tabela `profile_permissions`.

---

## ✅ SOLUÇÃO

### **Passo 1: Execute o SQL**

Arquivo criado: `SQL_ADICIONAR_PERMISSAO_DASHBOARD.sql`

Execute no phpMyAdmin ou cliente MySQL:

```sql
-- Adicionar permissão de dashboard para TODOS os perfis
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES 
    (1, 'dashboard', 1, 1, 1, 1, 1),  -- Administrador
    (2, 'dashboard', 1, 0, 0, 0, 0),  -- Usuário Comum
    (3, 'dashboard', 1, 0, 0, 0, 0),  -- Supervisor
    (4, 'dashboard', 1, 0, 0, 0, 0),  -- Operador
    (5, 'dashboard', 1, 0, 0, 0, 0)   -- Analista
ON DUPLICATE KEY UPDATE can_view = 1;
```

### **Passo 2: Verificar se Funcionou**

```sql
-- Ver permissões de dashboard por perfil
SELECT 
    p.id,
    p.name as perfil,
    pp.module,
    pp.can_view,
    pp.can_edit
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id 
WHERE pp.module = 'dashboard'
ORDER BY p.id;
```

**Resultado Esperado:**
```
id | perfil              | module    | can_view | can_edit
1  | Administrador       | dashboard | 1        | 1
2  | Usuário Comum       | dashboard | 1        | 0
3  | Supervisor          | dashboard | 1        | 0
4  | Operador de Toners  | dashboard | 1        | 0
5  | Analista Qualidade  | dashboard | 1        | 0
```

### **Passo 3: Fazer Logout e Login**

Após executar o SQL, o usuário precisa:
1. **Fazer logout** do sistema
2. **Fazer login** novamente
3. **Tentar acessar** o dashboard

---

## 🔧 PERMISSÕES DO SISTEMA

### **Módulos que Precisam de Permissão:**

| Rota | Módulo | Verificação |
|------|--------|-------------|
| `/` | `dashboard` | ✅ Necessário |
| `/admin` | `admin_painel` | ✅ Necessário |
| `/admin/dashboard/data` | `dashboard` | ✅ Necessário |
| `/admin/dashboard/melhorias-data` | `dashboard` | ✅ Necessário |

### **Como Funciona:**

1. Usuário tenta acessar `/` (dashboard)
2. `PermissionMiddleware` verifica rota → módulo `dashboard`
3. `PermissionService::hasPermission($userId, 'dashboard', 'view')`
4. Busca em `profile_permissions` se o perfil do usuário tem `can_view = 1`
5. Se **NÃO** tiver → **HTTP 500** ou **Acesso Negado**
6. Se **SIM** tiver → **Acesso permitido**

---

## 📊 SCRIPT SQL COMPLETO

O arquivo `SQL_ADICIONAR_PERMISSAO_DASHBOARD.sql` contém:

### **1. Permissões de Dashboard**
- Adiciona `dashboard` para perfis 1-5
- Usa `ON DUPLICATE KEY UPDATE` (não duplica se já existir)
- Define `can_view = 1` para todos

### **2. Permissões de Melhoria Contínua 2.0**
- Adiciona `melhoria_continua_2` para perfis 1-5
- Garante que todos podem visualizar
- Admin pode fazer tudo (edit, delete, import, export)

### **3. Queries de Verificação**
- Lista todos os perfis
- Mostra permissões de dashboard
- Mostra permissões de melhorias

---

## 🧪 TESTE APÓS EXECUTAR O SQL

### **1. Verificar Permissões no Banco**
```sql
SELECT * FROM profile_permissions WHERE module = 'dashboard';
```

### **2. Teste de Login**
1. Logout do sistema
2. Login novamente
3. Acessar `/` ou `/admin`
4. Dashboard deve carregar sem erro 500

### **3. Teste de Aba Melhorias**
1. No dashboard, clicar na aba "🚀 Melhorias"
2. Dados devem carregar
3. Gráficos devem renderizar

---

## 🔍 DIAGNÓSTICO DE OUTROS PROBLEMAS

### **Se ainda der erro 500 após o SQL:**

**1. Verificar Logs do PHP:**
```bash
# No servidor
tail -f /var/log/php_errors.log
# ou
tail -f /var/log/apache2/error.log
```

**2. Ativar Debug Temporariamente:**

Em `.env`:
```env
APP_DEBUG=true
```

**3. Verificar Tabela profile_permissions:**
```sql
DESCRIBE profile_permissions;
```

**4. Verificar Se Usuário Tem Perfil:**
```sql
SELECT 
    u.id,
    u.name,
    u.email,
    u.profile_id,
    p.name as perfil
FROM users u
LEFT JOIN profiles p ON u.profile_id = p.id
WHERE u.email = 'SEU_EMAIL_AQUI';
```

**5. Verificar Se Perfil Tem Permissões:**
```sql
SELECT * FROM profile_permissions 
WHERE profile_id = (
    SELECT profile_id FROM users WHERE email = 'SEU_EMAIL_AQUI'
);
```

---

## 📝 ESTRUTURA DAS PERMISSÕES

### **Tabela: profile_permissions**

```sql
CREATE TABLE IF NOT EXISTS profile_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    module VARCHAR(50) NOT NULL,
    can_view TINYINT(1) DEFAULT 0,
    can_edit TINYINT(1) DEFAULT 0,
    can_delete TINYINT(1) DEFAULT 0,
    can_import TINYINT(1) DEFAULT 0,
    can_export TINYINT(1) DEFAULT 0,
    UNIQUE KEY unique_profile_module (profile_id, module),
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
);
```

### **Módulos Importantes:**

| Módulo | Descrição |
|--------|-----------|
| `dashboard` | Acesso ao dashboard principal |
| `melhoria_continua_2` | Melhoria Contínua 2.0 |
| `admin_painel` | Painel administrativo |
| `admin_usuarios` | Gerenciar usuários |
| `admin_perfis` | Gerenciar perfis |

---

## ⚠️ IMPORTANTE

### **Após Executar o SQL:**

1. ✅ **Logout obrigatório** - Cache de sessão precisa ser limpo
2. ✅ **Login novamente** - Novas permissões serão carregadas
3. ✅ **Teste o dashboard** - Deve funcionar sem erro 500
4. ✅ **Teste a aba Melhorias** - Deve carregar dados

### **Usuários Afetados:**

- ✅ **Todos** os perfis recebem permissão de VIEW para dashboard
- ✅ **Admin** recebe todas as permissões (edit, delete, import, export)
- ✅ **Outros perfis** recebem apenas VIEW (visualização)

---

## 🎯 RESUMO DA SOLUÇÃO

| Passo | Ação | Status |
|-------|------|--------|
| 1 | Execute `SQL_ADICIONAR_PERMISSAO_DASHBOARD.sql` | ⏳ Pendente |
| 2 | Verifique se permissões foram adicionadas | ⏳ Pendente |
| 3 | Faça logout do sistema | ⏳ Pendente |
| 4 | Faça login novamente | ⏳ Pendente |
| 5 | Acesse o dashboard (`/` ou `/admin`) | ⏳ Pendente |
| 6 | Teste a aba Melhorias | ⏳ Pendente |

---

## ✅ RESULTADO ESPERADO

Após seguir os passos:

✅ **Dashboard carrega** sem erro 500  
✅ **Todas as abas funcionam** (Retornados, Amostragens, Fornecedores, Garantias, **Melhorias**)  
✅ **Gráficos renderizam** corretamente  
✅ **Dados reais** são exibidos  
✅ **Sem erros** no console do navegador  

---

**Arquivo SQL**: `SQL_ADICIONAR_PERMISSAO_DASHBOARD.sql`  
**Documentação**: `SOLUCAO_ERRO_500_DASHBOARD.md`  
**Status**: ⏳ **AGUARDANDO EXECUÇÃO DO SQL**

**Próximos Passos:**
1. Execute o SQL no banco de dados
2. Faça logout e login
3. Teste o dashboard
4. Se ainda der erro, ative `APP_DEBUG=true` e verifique os logs

**Responsável**: Cascade AI  
**Data**: 07/11/2025
