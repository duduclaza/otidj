# 📊 Sistema de Permissões por Aba do Dashboard

## 🎯 Visão Geral

Este sistema permite controlar **quais abas do dashboard cada perfil de usuário pode visualizar**. Administradores podem configurar permissões granulares para cada perfil, ocultando abas que não são relevantes para determinados usuários.

---

## 🗂️ Abas Disponíveis no Dashboard

1. **📦 Retornados** - Análise de retornados (por mês, destinos, valor recuperado)
2. **🧪 Amostragens 2.0** - Análise de amostragens por status e fornecedor
3. **🏭 Fornecedores** - Qualidade de fornecedores (comprados vs garantias)
4. **🛡️ Garantias** - Análise de garantias
5. **🚀 Melhorias** - Análise de melhorias contínuas (por status)

---

## 📝 Como Configurar Permissões

### **Passo 1: Executar o Script SQL**

Execute o script `SQL_DASHBOARD_ABAS_PERMISSOES.sql` no banco de dados:

```bash
# Via phpMyAdmin
1. Acesse phpMyAdmin
2. Selecione o banco de dados do SGQ
3. Clique em "SQL"
4. Cole o conteúdo do arquivo SQL_DASHBOARD_ABAS_PERMISSOES.sql
5. Clique em "Executar"
```

Ou via linha de comando:

```bash
mysql -u seu_usuario -p nome_do_banco < SQL_DASHBOARD_ABAS_PERMISSOES.sql
```

**O que esse script faz:**
- ✅ Cria a tabela `dashboard_tab_permissions`
- ✅ Configura permissões padrão para perfis existentes
- ✅ Administrador tem acesso a TODAS as abas
- ✅ Outros perfis têm permissões personalizadas

### **Passo 2: Configurar Permissões de um Perfil**

1. Acesse **Administrativo → Gerenciar Perfis**
2. Clique em **"Editar"** no perfil desejado
3. Role até a seção **"📊 Permissões de Abas do Dashboard"**
4. **Marque/desmarque** as abas que o perfil pode ver:
   - ✅ **Ativo** = Usuário **VÊ** a aba
   - ❌ **Inativo** = Usuário **NÃO VÊ** a aba
5. Clique em **"Salvar Alterações"**

---

## 🔍 Como Funciona

### **Backend (PHP)**

1. **AdminController::getDashboardTabPermissions()**
   - Busca permissões do perfil do usuário no banco
   - Retorna array com permissões por aba
   - Fallback: todas as abas liberadas se não houver configuração

2. **ProfilesController::saveDashboardTabPermissions()**
   - Salva permissões quando perfil é criado/editado
   - Deleta permissões antigas e insere novas
   - Graceful: não falha se tabela não existir

3. **ProfilesController::getDashboardTabPermissions()**
   - API que retorna permissões de abas para um perfil
   - Usado pelo frontend ao editar perfil

### **Frontend (View)**

1. **dashboard.php**
   - Verifica permissões via `$dashboardTabs`
   - Oculta **botões** de abas sem permissão
   - Oculta **conteúdo** de abas sem permissão
   - Exibe mensagem se usuário não tem nenhuma aba

2. **profiles.php**
   - Interface com toggles para cada aba
   - Carrega permissões ao editar perfil
   - Envia permissões ao salvar via `dashboard_tabs[]`

---

## 🗄️ Estrutura do Banco de Dados

### **Tabela: `dashboard_tab_permissions`**

```sql
CREATE TABLE `dashboard_tab_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INT NOT NULL,
  `tab_name` VARCHAR(50) NOT NULL,  -- retornados, amostragens, fornecedores, garantias, melhorias
  `can_view` TINYINT(1) DEFAULT 1,  -- 1=pode ver, 0=não pode
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_profile_tab` (`profile_id`, `tab_name`),
  FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE CASCADE
);
```

### **Exemplo de Dados**

```sql
-- Perfil Administrador (ID 1) - Vê tudo
INSERT INTO dashboard_tab_permissions VALUES
(NULL, 1, 'retornados', 1),
(NULL, 1, 'amostragens', 1),
(NULL, 1, 'fornecedores', 1),
(NULL, 1, 'garantias', 1),
(NULL, 1, 'melhorias', 1);

-- Perfil Usuário Comum (ID 2) - Vê apenas retornados e amostragens
INSERT INTO dashboard_tab_permissions VALUES
(NULL, 2, 'retornados', 1),
(NULL, 2, 'amostragens', 1),
(NULL, 2, 'fornecedores', 0),
(NULL, 2, 'garantias', 0),
(NULL, 2, 'melhorias', 0);
```

---

## 🎨 Interface do Usuário

### **Gerenciar Perfis**

```
┌────────────────────────────────────────────┐
│ 📊 Permissões de Abas do Dashboard        │
├────────────────────────────────────────────┤
│ 📦 Retornados              [✓] ATIVO       │
│ 🧪 Amostragens 2.0         [✓] ATIVO       │
│ 🏭 Fornecedores            [ ] INATIVO     │
│ 🛡️ Garantias               [ ] INATIVO     │
│ 🚀 Melhorias Contínuas     [✓] ATIVO       │
└────────────────────────────────────────────┘
```

### **Dashboard (Usuário com Permissão Limitada)**

```
┌───────────────────────────────────────────────────────┐
│ 📊 Dashboard - Análise de Dados                       │
├───────────────────────────────────────────────────────┤
│ [📦 Retornados] [🧪 Amostragens] [🚀 Melhorias]       │
│                                                        │
│ (Abas Fornecedores e Garantias OCULTAS)              │
└───────────────────────────────────────────────────────┘
```

---

## 🔒 Regras de Segurança

### **1. Administradores**
- ✅ **Sempre** têm acesso a TODAS as abas
- ✅ Verificação por `$_SESSION['user_role']` 
- ✅ Bypass de permissões de perfil

### **2. Usuários Comuns**
- ✅ Veem apenas abas permitidas no perfil
- ❌ Abas sem permissão são **completamente ocultas**
- ⚠️ Sem permissão nenhuma = mensagem de aviso

### **3. Fallback Seguro**
- Se tabela não existir → **todas as abas liberadas**
- Se perfil sem permissões → **todas as abas liberadas**
- Se erro ao buscar → **todas as abas liberadas**

---

## 🧪 Testes

### **Teste 1: Criar Novo Perfil**

```
1. Acesse Gerenciar Perfis
2. Clique em "Novo Perfil"
3. Preencha nome e descrição
4. Na seção "Permissões de Abas do Dashboard":
   - Marque apenas "Retornados" e "Amostragens"
5. Configure permissões de módulos
6. Salve o perfil
7. Atribua perfil a um usuário de teste
8. Faça login com o usuário
9. Acesse Dashboard
10. Verifique que apenas 2 abas aparecem
```

### **Teste 2: Editar Perfil Existente**

```
1. Acesse Gerenciar Perfis
2. Edite "Usuário Comum"
3. Desmarque "Melhorias"
4. Salve
5. Usuários com esse perfil não verão mais aba Melhorias
```

### **Teste 3: Usuário sem Permissões**

```
1. Crie perfil com todas as abas desmarcadas
2. Atribua a um usuário
3. Faça login
4. Acesse Dashboard
5. Deve ver mensagem: "⚠️ Sem permissão para visualizar abas"
```

---

## 🐛 Troubleshooting

### **Problema: Abas não aparecem**

**Diagnóstico:**
```sql
-- Verificar se tabela existe
SHOW TABLES LIKE 'dashboard_tab_permissions';

-- Verificar permissões do perfil
SELECT * FROM dashboard_tab_permissions WHERE profile_id = X;

-- Verificar perfil do usuário
SELECT u.name, u.profile_id, p.name as perfil
FROM users u
LEFT JOIN profiles p ON u.profile_id = p.id
WHERE u.id = Y;
```

**Soluções:**
1. ✅ Execute o script SQL de criação
2. ✅ Verifique se perfil tem permissões configuradas
3. ✅ Configure permissões via interface
4. ✅ Verifique console do navegador (F12)

### **Problema: Todas as abas aparecem mesmo sem permissão**

**Diagnóstico:**
```sql
-- Verificar se é administrador
SELECT role FROM users WHERE id = X;
```

**Explicação:**
- Administradores **sempre** veem todas as abas
- Isso é by design para garantir acesso total

### **Problema: Erro ao salvar permissões**

**Diagnóstico:**
```
1. Abra console do navegador (F12)
2. Vá em Network
3. Tente salvar perfil
4. Verifique resposta da API
```

**Logs no servidor:**
```
# Verificar logs PHP
tail -f storage/logs/error.log

# Procurar por:
"Saving dashboard tab permissions"
"Error saving dashboard tab permissions"
```

---

## 📋 Checklist de Implementação

- [x] Criar tabela `dashboard_tab_permissions`
- [x] Adicionar método `getDashboardTabPermissions()` no AdminController
- [x] Adicionar método `saveDashboardTabPermissions()` no ProfilesController
- [x] Adicionar método `getDashboardTabPermissions()` no ProfilesController (API)
- [x] Adicionar rota `/admin/profiles/{id}/dashboard-tabs`
- [x] Atualizar view `dashboard.php` com verificações
- [x] Interface já existe em `profiles.php`
- [x] Adicionar função JavaScript `loadDashboardTabPermissions()`
- [x] Testar criação de perfil
- [x] Testar edição de perfil
- [x] Testar visualização no dashboard
- [x] Documentação completa

---

## 🎓 Exemplos de Uso

### **Caso 1: Operador de Toners**
```
Permissões:
- ✅ Retornados (vê análise de retornados)
- ✅ Amostragens (vê amostragens)
- ❌ Fornecedores (não precisa)
- ❌ Garantias (não precisa)
- ❌ Melhorias (não precisa)
```

### **Caso 2: Analista de Qualidade**
```
Permissões:
- ✅ Retornados
- ✅ Amostragens
- ✅ Fornecedores (análise de qualidade)
- ✅ Garantias (acompanhamento)
- ✅ Melhorias (gestão de melhorias)
```

### **Caso 3: Supervisor**
```
Permissões:
- ✅ Todas as abas (visão completa)
```

---

## 🚀 Versionamento

**Versão:** 1.0.0  
**Data:** 12/11/2025  
**Autor:** Sistema SGQ-OTI DJ  

**Changelog:**
- ✅ v1.0.0 - Sistema completo implementado
  - Tabela de permissões
  - Backend completo (controller + API)
  - Frontend completo (dashboard + profiles)
  - Documentação completa

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique este README
2. Execute os testes sugeridos
3. Verifique logs do sistema
4. Verifique console do navegador

---

**✨ Sistema pronto para uso em produção!**
