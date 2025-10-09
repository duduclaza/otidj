# 🔧 SOLUÇÃO - Notificações de POPs/ITs Não Chegam

## 📋 PROBLEMA REPORTADO

1. Usuária adicionou um POP/IT para aprovação ✅
2. **Administradores NÃO receberam notificação** ❌
3. **Checkbox "Pode Aprovar POPs e ITs" não salva** ❌

---

## 🔍 CAUSA RAIZ

O sistema verifica se a coluna `pode_aprovar_pops_its` existe no banco:
- ✅ **Se existir**: Envia email APENAS para admins com valor `1` (ativado)
- ❌ **Se não existir**: Tenta enviar para todos admins (fallback)
- ⚠️ **Se ninguém tiver valor `1`**: Não envia para ninguém!

**Código relevante (PopItsController.php):**
```php
// Linha 1838
WHERE role = 'admin' 
AND pode_aprovar_pops_its = 1  // ← AQUI ESTÁ O FILTRO
AND status = 'active'
```

---

## ✅ SOLUÇÃO RÁPIDA (3 PASSOS)

### **PASSO 1: Execute esta Query no phpMyAdmin**

```sql
-- Criar coluna se não existir
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS pode_aprovar_pops_its TINYINT(1) DEFAULT 0;

-- Ativar para TODOS os admins
UPDATE users 
SET pode_aprovar_pops_its = 1 
WHERE role = 'admin';
```

---

### **PASSO 2: Verificar se funcionou**

```sql
-- Ver quais admins têm a permissão
SELECT 
    name,
    email,
    pode_aprovar_pops_its,
    CASE 
        WHEN pode_aprovar_pops_its = 1 THEN '✅ Receberá emails'
        ELSE '❌ NÃO receberá'
    END as status
FROM users
WHERE role = 'admin';
```

**Resultado esperado:** Todos admins mostram `✅ Receberá emails`

---

### **PASSO 3: Testar no Sistema**

1. Vá em **Admin** → **Gerenciar Usuários**
2. Clique em **✏️ Editar** em qualquer admin
3. **Marque** o checkbox "🔐 Pode Aprovar POPs e ITs"
4. Clique **Salvar**
5. Edite novamente
6. **Verifique:** Checkbox deve estar marcado ✅

---

## 📊 ARQUIVO COMPLETO DE DIAGNÓSTICO

Criei o arquivo **`CORRIGIR_NOTIFICACOES_POPS_ITS.sql`** com:

- ✅ Query para verificar se coluna existe
- ✅ Query para criar coluna (se não existir)
- ✅ Query para ativar permissão
- ✅ Query para verificar notificações recentes
- ✅ Query para ver POPs/ITs pendentes
- ✅ Query de teste completo

---

## 🎯 COMO FUNCIONA O SISTEMA

### **Fluxo de Notificação:**

```
1. Usuária cria POP/IT
   ↓
2. Sistema marca como "Pendente"
   ↓
3. PopItsController busca admins:
   SELECT * FROM users 
   WHERE role = 'admin' 
   AND pode_aprovar_pops_its = 1  ← FILTRO AQUI
   ↓
4. Se encontrar admins:
   - Cria notificação no sistema
   - Envia email para cada um
   ↓
5. Se NÃO encontrar:
   - ❌ Nenhuma notificação enviada
   - ❌ Nenhum email enviado
```

---

## 🧪 TESTE COMPLETO

### **Cenário 1: Verificar POPs/ITs Pendentes**

```sql
SELECT 
    t.titulo,
    t.tipo,
    r.versao,
    r.status,
    r.created_at,
    u.name as criador
FROM pops_its_registros r
LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
LEFT JOIN users u ON r.criado_por = u.id
WHERE r.status = 'Pendente'
ORDER BY r.created_at DESC;
```

---

### **Cenário 2: Verificar Quem Recebe Notificações**

```sql
SELECT 
    u.name,
    u.email,
    u.pode_aprovar_pops_its,
    CASE 
        WHEN u.pode_aprovar_pops_its = 1 
        THEN '✅ SIM - Receberá emails'
        ELSE '❌ NÃO - Não receberá'
    END as resultado
FROM users u
WHERE u.role = 'admin'
  AND u.status = 'active';
```

**Esperado:** Todos admins ativos mostram "✅ SIM"

---

### **Cenário 3: Forçar Notificação Manual**

Se você tem POPs/ITs antigos pendentes e quer enviar notificação agora:

1. Vá em **POPs e ITs** → Aba **Pendentes**
2. Edite o registro
3. Clique em **Salvar** (sem mudar nada)
4. Sistema detectará como "recente" e enviará notificação

---

## ⚠️ PROBLEMAS COMUNS

### **Problema 1: Checkbox não salva**

**Causa:** Coluna não existe ou usuário não é admin

**Solução:**
```sql
-- Verificar se coluna existe
SHOW COLUMNS FROM users LIKE 'pode_aprovar_pops_its';

-- Se retornar vazio, criar:
ALTER TABLE users 
ADD COLUMN pode_aprovar_pops_its TINYINT(1) DEFAULT 0;
```

---

### **Problema 2: Emails não chegam**

**Causa:** SMTP não configurado ou emails em spam

**Solução:**
1. Verificar logs em `php_errors.log`
2. Procurar por:
   ```
   📧 ENVIANDO EMAIL PARA X ADMINISTRADORES
   ✅ EMAIL ENVIADO COM SUCESSO
   ```
3. Se aparecer erro de SMTP, verificar configuração de email

---

### **Problema 3: Notificação não aparece no sistema**

**Causa:** Coluna `notifications.related_type` não existe

**Solução:**
```sql
-- Verificar estrutura da tabela
DESCRIBE notifications;

-- Verificar notificações recentes
SELECT * FROM notifications 
WHERE type = 'pops_its_pendente'
ORDER BY created_at DESC
LIMIT 10;
```

---

## 📝 LOGS DE DEBUG

Para verificar o que está acontecendo, procure no log do PHP:

```
=== INICIANDO NOTIFICAÇÃO PARA ADMINS COM PERMISSÃO ===
✅ ADMINS COM PERMISSÃO ENCONTRADOS: 3
--- CRIANDO NOTIFICAÇÃO PARA Clayton (ID: 1) ---
✅ NOTIFICAÇÃO CRIADA COM SUCESSO para Clayton
📧 ENVIANDO EMAIL PARA 3 ADMINISTRADORES
✅ EMAIL ENVIADO COM SUCESSO
```

**Se aparecer:**
```
❌ NENHUM ADMINISTRADOR COM PERMISSÃO ENCONTRADO!
```

**Significa:** Nenhum admin tem `pode_aprovar_pops_its = 1`
**Solução:** Execute o PASSO 1 novamente

---

## ✅ CHECKLIST DE VALIDAÇÃO

Após executar a correção, verifique:

- [ ] Query do PASSO 1 executada com sucesso
- [ ] Query do PASSO 2 mostra todos admins com "✅ Receberá emails"
- [ ] Checkbox "Pode Aprovar POPs e ITs" está marcado na tela
- [ ] Ao editar admin, checkbox permanece marcado ao salvar
- [ ] Criar novo POP/IT de teste gera notificação
- [ ] Email chega na caixa de entrada do admin
- [ ] Notificação aparece no sino 🔔 do sistema

---

## 🎉 RESULTADO ESPERADO

Após correção:

1. ✅ Checkbox salva corretamente
2. ✅ Administradores recebem email automático
3. ✅ Notificação aparece no sino do sistema
4. ✅ Badge mostra número de pendentes

**Email recebido:**
```
🔔 Novo POP Pendente

Um novo registro 'Procedimento de Limpeza' v1.0 
foi criado e aguarda aprovação.

[Ver Detalhes]
```

---

## 📞 SUPORTE

Se após executar ainda não funcionar:

1. **Execute** todas as queries do arquivo `CORRIGIR_NOTIFICACOES_POPS_ITS.sql`
2. **Copie** os resultados
3. **Verifique** os logs do PHP
4. **Me envie** os resultados para análise

---

**Data:** 09/10/2025 15:26  
**Status:** ✅ Solução completa disponível  
**Arquivos:**
- `CORRIGIR_NOTIFICACOES_POPS_ITS.sql` (queries de diagnóstico)
- `SOLUCAO_NOTIFICACOES_POPS_ITS.md` (este arquivo)
