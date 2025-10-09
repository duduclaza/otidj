# ⚠️ AÇÃO NECESSÁRIA - Execute a Migration AGORA

## 🚨 ERRO ATUAL

```
Column not found: Unknown column 'u.notificacoes_ativadas' in 'SELECT'
```

**Causa**: A coluna ainda não foi criada no banco de dados.

---

## ✅ SOLUÇÃO EM 2 PASSOS

### **PASSO 1: Acessar phpMyAdmin**

1. Acesse: https://djbr.sgqoti.com.br:2083 (ou seu link de acesso ao cPanel/phpMyAdmin)
2. Entre no phpMyAdmin
3. Selecione o banco: `u230868210_djsgqpro`

---

### **PASSO 2: Executar a Migration**

1. Clique na aba **"SQL"** no topo
2. Cole o código abaixo:

```sql
/* Adicionar coluna notificacoes_ativadas */
ALTER TABLE users 
ADD COLUMN notificacoes_ativadas TINYINT(1) NOT NULL DEFAULT 1 
COMMENT '1 = Notificações ativadas, 0 = Notificações desativadas' 
AFTER status;
```

3. Clique em **"Executar"**

---

## 🎯 RESULTADO ESPERADO

Você deve ver:

✅ **"1 linha afetada"** ou **"Query OK"**

---

## 🔍 VERIFICAR SE FUNCIONOU

Cole esta query para confirmar:

```sql
SHOW COLUMNS FROM users LIKE 'notificacoes_ativadas';
```

**✅ Sucesso**: Deve mostrar 1 linha com a coluna  
**❌ Erro**: Deve mostrar 0 linhas (execute novamente o PASSO 2)

---

## 🚀 APÓS EXECUTAR

1. **Recarregue a página** do sistema (Ctrl + F5)
2. O erro deve desaparecer
3. Sistema de notificações estará funcionando

---

## 📝 SE DER ERRO

**Erro: "Duplicate column"**
- Significa que a coluna já existe
- Ignore o erro e recarregue a página

**Erro: "Access denied"**
- Você não tem permissão de ALTER TABLE
- Entre em contato com suporte da Hostinger

**Erro: "Table doesn't exist"**
- Verifique se selecionou o banco correto

---

## 🔧 CORREÇÃO ADICIONAL APLICADA

Também corrigi o código PHP para **não quebrar** se a coluna não existir.

**Comportamento agora**:
- **Antes da migration**: Notificações ativadas para todos (padrão)
- **Depois da migration**: Controle individual por usuário

---

## ⏱️ TEMPO ESTIMADO

**30 segundos** para executar a migration completa.

---

**Data**: 09/10/2025 12:58  
**Prioridade**: 🔴 ALTA - Sistema com erro até executar  
**Arquivo de backup**: `EXECUTAR_MIGRATION_PHPMYADMIN.sql`
