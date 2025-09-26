# 🔍 Guia de Diagnóstico - Problema de Permissões do Dashboard

## 📋 **Problema Reportado**
Usuário configurou permissão de dashboard para um perfil, mas não consegue acessar mesmo com a permissão configurada.

## 🛠️ **Scripts de Diagnóstico Disponíveis**

### 1. 📊 **Diagnóstico Geral**
**Arquivo:** `debug_dashboard_permissions.php`
**URL:** `http://localhost/sgqpro/debug_dashboard_permissions.php`

**O que faz:**
- ✅ Verifica se módulo "dashboard" existe na tabela
- ✅ Lista todos os módulos disponíveis no sistema
- ✅ Mostra usuários e seus perfis
- ✅ Identifica permissões configuradas

**Quando usar:** Primeiro passo para entender o problema geral

---

### 2. 🔧 **Correção Automática**
**Arquivo:** `fix_dashboard_permissions.php`
**URL:** `http://localhost/sgqpro/fix_dashboard_permissions.php`

**O que faz:**
- ✅ Corrige automaticamente permissões faltantes
- ✅ Adiciona dashboard para perfis administrativos
- ✅ Permite adicionar dashboard a perfis específicos
- ✅ Teste completo do sistema

**Quando usar:** Quando identificar que faltam permissões

---

### 3. 🧪 **Teste Individual**
**Arquivo:** `test_user_dashboard.php`
**URL:** `http://localhost/sgqpro/test_user_dashboard.php`

**O que faz:**
- ✅ Testa usuário específico
- ✅ Mostra informações detalhadas do usuário
- ✅ Verifica permissões diretas no banco
- ✅ Oferece correção automática individual

**Configuração:** Edite o arquivo e altere `$testUserId = 1` para o ID do usuário

---

## 🚀 **Fluxo de Diagnóstico Recomendado**

### **Passo 1: Diagnóstico Geral**
```
1. Acesse: http://localhost/sgqpro/debug_dashboard_permissions.php
2. Verifique se o módulo "dashboard" existe
3. Identifique quais perfis têm permissão
4. Anote os problemas encontrados
```

### **Passo 2: Identificar Usuário Problemático**
```
1. Identifique o ID do usuário com problema
2. Edite test_user_dashboard.php e altere $testUserId
3. Acesse: http://localhost/sgqpro/test_user_dashboard.php
4. Verifique detalhes específicos do usuário
```

### **Passo 3: Aplicar Correção**
```
1. Se o problema for falta de permissão:
   - Use fix_dashboard_permissions.php
   - Clique no perfil para adicionar permissão
   
2. Se o problema for mais complexo:
   - Use o botão "CORRIGIR AUTOMATICAMENTE" no test_user_dashboard.php
```

### **Passo 4: Validar Correção**
```
1. Execute novamente test_user_dashboard.php
2. Verifique se mostra "✅ TEM PERMISSÃO"
3. Teste login do usuário no sistema
4. Confirme se menu Dashboard aparece
```

---

## 🎯 **Possíveis Causas e Soluções**

### **❌ Causa 1: Módulo "dashboard" não existe**
**Sintoma:** Script mostra "Módulo 'dashboard' NÃO existe na tabela"
**Solução:** Use `fix_dashboard_permissions.php` para adicionar automaticamente

### **❌ Causa 2: Perfil sem permissão**
**Sintoma:** Usuário tem perfil, mas perfil não tem permissão de dashboard
**Solução:** Clique no perfil em `fix_dashboard_permissions.php` para adicionar

### **❌ Causa 3: Usuário sem perfil**
**Sintoma:** Script mostra "Usuário não tem perfil associado"
**Solução:** Associe um perfil ao usuário na interface administrativa

### **❌ Causa 4: Cache desatualizado**
**Sintoma:** Permissão existe no banco mas não funciona
**Solução:** Faça logout/login do usuário para limpar cache

---

## 📊 **Interpretando os Resultados**

### **✅ Resultado Positivo:**
```
✅ Módulo 'dashboard' existe na tabela
✅ TEM PERMISSÃO (no PermissionService)
✅ Permissão existe no banco (can_view = 1)
```

### **❌ Resultado Negativo:**
```
❌ Módulo 'dashboard' NÃO existe na tabela
❌ NÃO TEM PERMISSÃO (no PermissionService)
❌ Não existe permissão de 'dashboard' para este perfil
```

---

## 🔧 **Correções Manuais (SQL)**

### **Adicionar módulo dashboard para um perfil:**
```sql
INSERT INTO profile_permissions 
(profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
VALUES (ID_DO_PERFIL, 'dashboard', 1, 0, 0, 0, 0)
ON DUPLICATE KEY UPDATE can_view = 1;
```

### **Verificar permissões existentes:**
```sql
SELECT p.name as profile_name, pp.module, pp.can_view 
FROM profile_permissions pp 
JOIN profiles p ON pp.profile_id = p.id 
WHERE pp.module = 'dashboard';
```

---

## 📞 **Suporte**

Se os scripts não resolverem o problema:

1. **Execute todos os 3 scripts**
2. **Anote os resultados de cada um**
3. **Informe quais mensagens aparecem**
4. **Mencione ID do usuário e nome do perfil**

---

## 📝 **Changelog**

**v2.1.2 - 26/09/2025**
- Criados scripts de diagnóstico para permissões
- Investigação sistemática de problemas de acesso
- Ferramentas de correção automática
- Documentação completa do processo

---

**🎯 Este guia deve resolver 99% dos problemas de permissão do dashboard!**
