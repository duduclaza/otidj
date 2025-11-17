# 🔧 Correção: Definir du.claza@gmail.com como Super Admin

**Data:** 17/11/2025  
**Usuário:** du.claza@gmail.com  
**Ação:** Corrigir role para super_admin

---

## 🎯 Escolha UMA das opções abaixo:

### ✅ Opção 1: Script PHP (MAIS FÁCIL)

1. **Acesse via navegador:**
   ```
   https://djbr.sgqoti.com.br/fix_super_admin.php
   ```

2. **Aguarde processar** (alguns segundos)

3. **Verifique o resultado** na tela

4. **Faça logout e login** novamente

5. **DELETE o arquivo** `fix_super_admin.php` por segurança

---

### ✅ Opção 2: SQL Manual (phpMyAdmin)

1. **Acesse phpMyAdmin** do Hostinger

2. **Selecione o banco** `u230868210_djsgqpro`

3. **Clique em "SQL"** no menu superior

4. **Cole este comando:**
   ```sql
   UPDATE users 
   SET role = 'super_admin' 
   WHERE email = 'du.claza@gmail.com';
   ```

5. **Clique "Executar"**

6. **Faça logout e login** no sistema

---

## 🧪 Como Testar

### Após corrigir:

1. **Faça logout** no sistema SGQ

2. **Faça login** com du.claza@gmail.com

3. **Acesse** menu "🆘 Suporte"

4. **Verificar:**
   - ✅ NÃO deve aparecer botão "+ Nova Solicitação"
   - ✅ Deve aparecer descrição "Gerenciar solicitações dos administradores"
   - ✅ Deve ver coluna "Solicitante" na tabela
   - ✅ Deve ver botão "⚙️ Gerenciar" nas solicitações

---

## 📊 Verificar Role no Banco

Execute este SQL para confirmar:

```sql
SELECT id, name, email, role 
FROM users 
WHERE email = 'du.claza@gmail.com';
```

**Resultado esperado:**
- role = `super_admin` (exato, sem espaços)

---

## 🔒 Verificar Sessão

Se mesmo após correção não funcionar:

1. **Limpar cookies do navegador**
2. **Usar navegação anônima** para testar
3. **Verificar se fez logout/login**

---

## ❓ Problemas Comuns

### Problema: Ainda diz "Apenas admins têm acesso"

**Solução:**
- Confirme que fez **logout e login** novamente
- Limpe **cache e cookies** do navegador
- Verifique no banco se role = 'super_admin'

### Problema: Botão "Nova Solicitação" ainda aparece

**Solução:**
- Isso é NORMAL para super_admin se role não foi atualizado
- Refaça os passos acima
- Aguarde propagação da sessão (pode levar alguns segundos)

### Problema: Não vê outras solicitações

**Solução:**
- Confirme que role = 'super_admin' no banco
- Faça logout/login
- Crie uma solicitação de teste com outro usuário admin

---

## 🎉 Resultado Final

### Como Super Admin você deve:

- ✅ Ver menu "🆘 Suporte"
- ✅ Acessar /suporte normalmente
- ✅ Ver TODAS as solicitações de todos os admins
- ✅ Ver coluna "Solicitante" na tabela
- ✅ Ver botão "⚙️ Gerenciar" em solicitações não concluídas
- ❌ NÃO ver botão "+ Nova Solicitação"

### Como Super Admin você NÃO pode:

- ❌ Criar novas solicitações de suporte
- ❌ O botão de criar não deve aparecer

---

## 🔐 Garantir que é o ÚNICO Super Admin

Execute este SQL:

```sql
-- Ver todos super_admins
SELECT id, name, email, role 
FROM users 
WHERE role = 'super_admin';
```

**Deve retornar apenas 1 registro:**
- du.claza@gmail.com

**Se houver mais de 1, execute:**

```sql
-- Transformar outros em admins comuns
UPDATE users 
SET role = 'admin' 
WHERE role = 'super_admin' 
AND email != 'du.claza@gmail.com';
```

---

## ⚠️ IMPORTANTE

### Após usar fix_super_admin.php:

1. **DELETE o arquivo imediatamente:**
   ```
   fix_super_admin.php
   ```

2. **Motivo:** Script permite alteração direta no banco

3. **Segurança:** Nunca deixe scripts de correção em produção

---

## 📝 Checklist Final

- [ ] Role corrigido no banco de dados
- [ ] Logout e login realizado
- [ ] Menu Suporte acessível
- [ ] NÃO aparece botão criar solicitação
- [ ] Aparece botão "Gerenciar"
- [ ] Vê todas as solicitações
- [ ] Script fix_super_admin.php deletado (se usado)

---

**Status:** 🔧 Aguardando Correção  
**Prioridade:** Alta  
**Tempo estimado:** 2 minutos
