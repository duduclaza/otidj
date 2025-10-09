# 🧪 COMO TESTAR NO PHPMYADMIN

## ⚠️ PROBLEMA RESOLVIDO

O erro `#1064 - Você tem um erro de sintaxe` acontece porque o phpMyAdmin não executa bem arquivos com comentários `--` quando colado diretamente.

## ✅ SOLUÇÃO: Execute os testes separadamente

### **TESTE 1: Verificar se coluna existe**

Cole esta query no phpMyAdmin:

```sql
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
AND COLUMN_NAME = 'notificacoes_ativadas';
```

**✅ Resultado esperado:**
```
COLUMN_NAME: notificacoes_ativadas
COLUMN_TYPE: tinyint(1)
COLUMN_DEFAULT: 1
IS_NULLABLE: NO
```

---

### **TESTE 2: Ver status de todos os usuários**

```sql
SELECT 
    id,
    name,
    email,
    role,
    notificacoes_ativadas,
    CASE 
        WHEN notificacoes_ativadas = 1 THEN '🔔 Sino ATIVO'
        ELSE '🔕 Sino DESATIVADO'
    END as status_sino
FROM users
ORDER BY id;
```

**✅ Resultado esperado:**
- Ver todos os usuários com coluna `notificacoes_ativadas` (0 ou 1)
- Coluna `status_sino` mostra emoji

---

### **TESTE 3: Estatísticas**

```sql
SELECT 
    COUNT(*) as total_usuarios,
    SUM(CASE WHEN notificacoes_ativadas = 1 THEN 1 ELSE 0 END) as com_sino,
    SUM(CASE WHEN notificacoes_ativadas = 0 THEN 1 ELSE 0 END) as sem_sino
FROM users;
```

**✅ Resultado esperado:**
```
total_usuarios: 10
com_sino: 9
sem_sino: 1
```

---

### **TESTE 4: Verificar Admins**

```sql
SELECT 
    name,
    email,
    role,
    notificacoes_ativadas,
    CASE 
        WHEN notificacoes_ativadas = 1 THEN '✅ Receberá notificações'
        ELSE '❌ NÃO receberá notificações'
    END as status
FROM users
WHERE role = 'admin';
```

---

### **TESTE 5: Desativar notificações de um usuário**

⚠️ **CUIDADO**: Isso altera dados reais!

```sql
UPDATE users 
SET notificacoes_ativadas = 0 
WHERE email = 'usuario@example.com';
```

Depois verifique:
```sql
SELECT name, email, notificacoes_ativadas 
FROM users 
WHERE email = 'usuario@example.com';
```

---

### **TESTE 6: Reativar notificações**

```sql
UPDATE users 
SET notificacoes_ativadas = 1 
WHERE email = 'usuario@example.com';
```

---

## 🎯 TESTE RÁPIDO VISUAL

Cole APENAS isto no phpMyAdmin:

```sql
SELECT 
    id,
    name,
    email,
    notificacoes_ativadas as sino,
    CASE 
        WHEN notificacoes_ativadas = 1 THEN '🔔'
        ELSE '🔕'
    END as status
FROM users
ORDER BY id;
```

---

## 📁 ARQUIVOS CRIADOS

Também criei arquivos separados para facilitar:

1. `TESTE_1_COLUNA_EXISTE.sql` - Verifica se coluna existe
2. `TESTE_2_STATUS_USUARIOS.sql` - Lista todos os usuários
3. `TESTE_3_ESTATISTICAS.sql` - Estatísticas gerais

Basta abrir cada arquivo e copiar o conteúdo para o phpMyAdmin!

---

## ⚡ VALIDAÇÃO FINAL

Se a query abaixo retornar resultados, **está tudo OK**:

```sql
SHOW COLUMNS FROM users LIKE 'notificacoes_ativadas';
```

✅ **Sucesso**: Retorna 1 linha  
❌ **Erro**: Retorna 0 linhas → Execute a migration primeiro

---

## 🔧 SE AINDA DER ERRO

1. Execute a migration primeiro:
   ```sql
   ALTER TABLE users 
   ADD COLUMN notificacoes_ativadas TINYINT(1) NOT NULL DEFAULT 1 
   COMMENT '1 = Notificações ativadas, 0 = Notificações desativadas';
   ```

2. Verifique novamente:
   ```sql
   SHOW COLUMNS FROM users LIKE 'notificacoes_ativadas';
   ```

---

**Data**: 09/10/2025  
**Status**: ✅ Pronto para usar
