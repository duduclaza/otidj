# 🔍 DIAGNÓSTICO - Aprovação Não Aparece

## ❌ PROBLEMA RELATADO

> "Eu atualizei um status para aprovado mas não apareceu o meu nome de usuário no aprovado por"

---

## 🎯 POSSÍVEIS CAUSAS

### **1. Migration Não Executada** ⚠️
Colunas `aprovado_por` e `aprovado_em` não existem no banco.

### **2. Cache do Navegador** 💾
Página antiga em cache, não recarregou dados novos.

### **3. Código PHP Não Atualizou** 🔄
Controller antigo ainda em execução (cache do servidor).

### **4. Erro no UPDATE** ❌
Query falhou silenciosamente sem registrar.

---

## ✅ DIAGNÓSTICO PASSO A PASSO

### **PASSO 1: Verificar se Colunas Existem**

Execute no phpMyAdmin:

```sql
DESCRIBE amostragens_2;
```

**✅ Deve mostrar:**
- `aprovado_por` (int)
- `aprovado_em` (datetime)

**❌ Se NÃO aparecer:**
→ Execute a migration primeiro!

---

### **PASSO 2: Verificar Amostragens**

Execute:

```sql
SELECT 
    id,
    numero_nf,
    status_final,
    aprovado_por,
    aprovado_em
FROM amostragens_2
WHERE status_final IN ('Aprovado', 'Aprovado Parcialmente', 'Reprovado')
ORDER BY updated_at DESC
LIMIT 10;
```

**✅ Se `aprovado_por` tem número:**
- UPDATE funcionou
- Problema pode ser no JOIN ou cache

**❌ Se `aprovado_por` = NULL:**
- UPDATE não está funcionando
- Código PHP não está executando corretamente

---

### **PASSO 3: Verificar JOIN**

Execute:

```sql
SELECT 
    a.id,
    a.numero_nf,
    a.status_final,
    a.aprovado_por,
    aprovador.name as aprovador_nome,
    a.aprovado_em
FROM amostragens_2 a
LEFT JOIN users aprovador ON a.aprovado_por = aprovador.id
WHERE a.status_final = 'Aprovado'
ORDER BY a.id DESC
LIMIT 5;
```

**✅ Se `aprovador_nome` aparece:**
- JOIN funcionando
- Problema é no frontend ou cache

**❌ Se `aprovador_nome` = NULL mas `aprovado_por` tem número:**
- Usuário não existe ou ID errado

---

### **PASSO 4: Teste Manual**

#### **4.1 - Descubra seu ID:**

```sql
-- TROQUE 'seu@email.com' pelo seu email real
SELECT id, name, email FROM users WHERE email = 'seu@email.com';
```

Anote o **ID** que aparecer (ex: 5)

#### **4.2 - Atualize manualmente:**

```sql
-- TROQUE os valores:
-- 1 = ID da amostragem que você alterou
-- 5 = SEU ID de usuário

UPDATE amostragens_2 SET 
    aprovado_por = 5,
    aprovado_em = NOW()
WHERE id = 1 AND status_final = 'Aprovado';
```

#### **4.3 - Verifique:**

```sql
SELECT 
    a.id,
    a.status_final,
    a.aprovado_por,
    aprovador.name,
    a.aprovado_em
FROM amostragens_2 a
LEFT JOIN users aprovador ON a.aprovado_por = aprovador.id
WHERE a.id = 1;  -- TROQUE pelo ID que você usou
```

**✅ Se apareceu seu nome:**
- Banco OK
- Problema é no código PHP

---

### **PASSO 5: Limpar Cache**

#### **5.1 - Cache do Navegador:**
1. **Ctrl + Shift + R** (Windows/Linux)
2. **Cmd + Shift + R** (Mac)
3. Ou **Ctrl + F5**

#### **5.2 - Recarregar Página:**
1. Feche a aba de Amostragens
2. Abra novamente
3. Faça logout/login

---

### **PASSO 6: Verificar Logs**

Se você tem acesso aos logs do servidor PHP:

1. Acesse `/var/log/php-errors.log` ou similar
2. Procure por:
   ```
   🔍 DEBUG - Amostragem #X - Status: Aprovado - User ID: Y
   ✅ Registrando aprovação - User ID: Y
   📝 UPDATE executado - Linhas afetadas: 1
   ✅ Verificação - aprovado_por: Y | aprovado_em: 2025-XX-XX
   ```

**✅ Se ver esses logs:**
- UPDATE funcionou
- Problema é cache ou frontend

**❌ Se NÃO ver logs:**
- Código não está sendo executado
- Pode ser cache do OPcache/servidor

---

## 🔧 SOLUÇÕES

### **SOLUÇÃO 1: Executar Migration**

Se colunas não existem:

```sql
ALTER TABLE amostragens_2 
ADD COLUMN aprovado_por INT(11) NULL DEFAULT NULL 
  COMMENT 'ID do usuário que aprovou/reprovou' AFTER status_final,
ADD COLUMN aprovado_em DATETIME NULL DEFAULT NULL 
  COMMENT 'Data e hora da aprovação/reprovação' AFTER aprovado_por,
ADD CONSTRAINT fk_amostragens_2_aprovado_por 
  FOREIGN KEY (aprovado_por) REFERENCES users(id) ON DELETE SET NULL;

CREATE INDEX idx_amostragens_2_aprovado_por ON amostragens_2(aprovado_por);
```

---

### **SOLUÇÃO 2: Limpar Cache PHP (OPcache)**

Se você tem acesso SSH:

```bash
# Reiniciar PHP-FPM
sudo systemctl restart php8.2-fpm

# Ou limpar cache do servidor web
sudo systemctl restart apache2
# OU
sudo systemctl restart nginx
```

Via cPanel/Hostinger:
- Vá em "PHP Settings" ou "PHP OPcache"
- Clique em "Clear Cache" ou "Reset OPcache"

---

### **SOLUÇÃO 3: Forçar Atualização Manual**

Para amostragens já aprovadas, corrija manualmente:

```sql
-- Liste amostragens aprovadas sem registro
SELECT id, numero_nf, status_final, aprovado_por
FROM amostragens_2
WHERE status_final IN ('Aprovado', 'Aprovado Parcialmente', 'Reprovado')
AND aprovado_por IS NULL;

-- Para cada uma, se souber quem aprovou:
UPDATE amostragens_2 SET 
    aprovado_por = X,        -- ID de quem aprovou
    aprovado_em = NOW()
WHERE id = Y;                -- ID da amostragem
```

---

### **SOLUÇÃO 4: Testar Novamente**

1. **Limpe cache** (Ctrl + Shift + R)
2. **Recarregue a página** de Amostragens
3. **Escolha uma amostragem Pendente**
4. **Altere para "Aprovado"**
5. **Aguarde 2 segundos**
6. **Recarregue a página** (F5)
7. **Verifique a coluna "Aprovado Por"**

---

## 📊 CHECKLIST DE VERIFICAÇÃO

Execute cada item e marque:

- [ ] Colunas `aprovado_por` e `aprovado_em` existem
- [ ] Migration foi executada sem erros
- [ ] Amostragens aprovadas têm `aprovado_por` preenchido no banco
- [ ] JOIN traz nome do usuário corretamente
- [ ] Cache do navegador foi limpo (Ctrl+Shift+R)
- [ ] Página foi recarregada completamente
- [ ] Logout/Login foi feito
- [ ] Teste manual funcionou
- [ ] Logs mostram UPDATE sendo executado

---

## 🎯 TESTE RÁPIDO - 60 SEGUNDOS

### **Passo 1 (10s):**
```sql
DESCRIBE amostragens_2;
```
✅ Vê `aprovado_por`? → Continua
❌ Não vê? → Execute migration

### **Passo 2 (10s):**
```sql
SELECT aprovado_por FROM amostragens_2 WHERE status_final = 'Aprovado' LIMIT 1;
```
✅ Retorna número? → Próximo passo
❌ Retorna NULL? → Código não está funcionando

### **Passo 3 (20s):**
1. Ctrl + Shift + R
2. Vá em Amostragens
3. Veja se coluna "Aprovado Por" existe
4. Veja se tem dados

### **Passo 4 (20s):**
1. Altere uma amostragem Pendente para Aprovado
2. Aguarde 2 segundos
3. Recarregue (F5)
4. Veja se seu nome aparece

---

## 📁 ARQUIVOS DE DIAGNÓSTICO

Criei 3 arquivos para te ajudar:

### **1. VERIFICAR_APROVACAO_AMOSTRAGENS.sql**
- 5 queries de diagnóstico completo
- Verifica colunas, dados, JOINs
- **Execute este primeiro!**

### **2. TESTAR_APROVACAO_MANUAL.sql**
- Teste manual no banco
- Atualiza diretamente sem código PHP
- Confirma se banco está OK

### **3. Este arquivo (DIAGNOSTICO_APROVACAO_NAO_APARECE.md)**
- Guia completo de troubleshooting
- Todas as soluções possíveis

---

## 💡 CAUSA MAIS PROVÁVEL

### **Se você executou a migration AGORA:**
- **Cache do PHP (OPcache)**
- Controller antigo ainda em memória
- Código novo não foi carregado

**Solução:**
1. Reinicie PHP-FPM ou Apache
2. Ou aguarde 2-5 minutos (cache expira)
3. Limpe cache do navegador
4. Teste novamente

### **Se migration NÃO foi executada:**
- **Colunas não existem**
- UPDATE falha silenciosamente

**Solução:**
1. Execute migration
2. Recarregue página
3. Teste novamente

---

## 📞 AINDA NÃO FUNCIONA?

### **Me envie:**

1. **Screenshot do resultado:**
   ```sql
   DESCRIBE amostragens_2;
   ```

2. **Screenshot do resultado:**
   ```sql
   SELECT id, status_final, aprovado_por, aprovado_em 
   FROM amostragens_2 
   WHERE status_final = 'Aprovado' 
   ORDER BY id DESC LIMIT 5;
   ```

3. **Screenshot da tela de Amostragens** mostrando o grid

4. **Logs do console do navegador** (F12 → Console)

---

**Data**: 09/10/2025 14:06  
**Versão**: 2.6.4  
**Status**: 🔍 Aguardando diagnóstico
