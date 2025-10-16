# 🔔 Instruções: Configurar Notificações POPs e ITs

## ⚠️ PROBLEMA REPORTADO
Administradores não estão recebendo notificações quando há POPs/ITs pendentes de aprovação.

---

## 🔍 DIAGNÓSTICO

### **PASSO 1: Executar Script de Diagnóstico**

1. Acesse no navegador: `https://djbr.sgqoti.com.br/diagnostico_notificacoes_pops.php`
2. O script irá verificar:
   - ✅ Se a coluna `pode_aprovar_pops_its` existe
   - ✅ Quantos administradores têm a permissão ativa
   - ✅ Se a tabela de notificações existe
   - ✅ Configurações de email

---

## 🛠️ SOLUÇÕES POSSÍVEIS

### **PROBLEMA 1: Coluna não existe**

**Solução:** Execute a migration SQL

```bash
# No banco de dados, execute:
mysql -u usuario -p database < database/migrations/add_pode_aprovar_pops_its_column.sql
```

Ou execute manualmente no phpMyAdmin:

```sql
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS pode_aprovar_pops_its TINYINT(1) DEFAULT 0 
COMMENT 'Indica se o administrador recebe emails de POPs/ITs pendentes';

UPDATE users 
SET pode_aprovar_pops_its = 1 
WHERE role = 'admin';
```

---

### **PROBLEMA 2: Checkbox desmarcado no perfil**

**Solução:** Ativar permissão no perfil do administrador

1. Fazer login com conta do administrador
2. Ir em **Perfil do Usuário** (canto superior direito)
3. Procurar seção **"Configurações de Notificações"**
4. Marcar o checkbox: ☑️ **"Pode Aprovar POPs e ITs"**
5. Salvar alterações

**Importante:** Cada administrador que deseja receber notificações DEVE ter este checkbox marcado!

---

### **PROBLEMA 3: Email não está sendo enviado**

**Verificações:**

1. **Verificar configurações de email no `.env`:**
   ```env
   MAIL_HOST=smtp.hostinger.com
   MAIL_PORT=465
   MAIL_USERNAME=suporte@sgqoti.com.br
   MAIL_PASSWORD=sua_senha
   MAIL_ENCRYPTION=ssl
   MAIL_FROM=suporte@sgqoti.com.br
   MAIL_FROM_NAME="SGQ OTI DJ"
   ```

2. **Verificar logs do sistema:**
   - Abrir: `logs/pops_its_debug.log`
   - Procurar por linhas com "📧 ENVIANDO EMAIL"
   - Ver se há erros de SMTP

3. **Verificar pasta de SPAM:**
   - Emails podem estar indo para lixeira/spam
   - Adicionar `suporte@sgqoti.com.br` nos contatos

---

### **PROBLEMA 4: Notificações no sistema (sininho) não aparecem**

**Solução:** Verificar tabela `notifications`

```sql
-- Ver últimas notificações
SELECT * FROM notifications 
WHERE type LIKE '%pops_its%' 
ORDER BY created_at DESC 
LIMIT 10;

-- Ver se notificações estão sendo criadas
SELECT COUNT(*) as total 
FROM notifications 
WHERE type LIKE '%pops_its%';
```

---

## 🧪 TESTE MANUAL

### **Como testar se está funcionando:**

1. **Login com usuário comum** (não admin)
2. Ir em **POPs e ITs** > **Meus Registros**
3. Criar um novo registro POP ou IT
4. Enviar arquivo e salvar
5. **Verificar logs:**
   - Abrir `logs/pops_its_debug.log`
   - Procurar por:
     ```
     🔔 INICIANDO PROCESSO DE NOTIFICAÇÃO
     ✅ ADMINS COM PERMISSÃO ENCONTRADOS: X
     📧 ENVIANDO EMAIL PARA X ADMINISTRADORES
     ✅ EMAIL ENVIADO COM SUCESSO
     ```

6. **Login com administrador:**
   - Ver se aparece sininho 🔔 com notificação
   - Verificar email

---

## 📊 CHECKLIST DE VERIFICAÇÃO

Use esta lista para confirmar que tudo está configurado:

- [ ] Coluna `pode_aprovar_pops_its` existe na tabela `users`
- [ ] Pelo menos 1 administrador tem `pode_aprovar_pops_its = 1`
- [ ] Administrador está com status `active`
- [ ] Checkbox "Pode Aprovar POPs e ITs" está marcado no perfil
- [ ] Configurações de email estão corretas no `.env`
- [ ] Tabela `notifications` existe no banco
- [ ] Logs mostram "✅ ADMINS COM PERMISSÃO ENCONTRADOS: X" (X > 0)
- [ ] Logs mostram "📧 ENVIANDO EMAIL PARA X ADMINISTRADORES"
- [ ] Logs mostram "✅ EMAIL ENVIADO COM SUCESSO"

---

## 🆘 SE MESMO ASSIM NÃO FUNCIONAR

Execute o teste de notificações diretamente:

```php
// Acessar: /pops-its/teste-notificacoes
// Isso criará uma notificação de teste para verificar
```

**OU** verifique os logs em tempo real:

```bash
tail -f logs/pops_its_debug.log
```

E então crie um novo POP/IT para ver os logs acontecendo.

---

## 📝 LOGS ESPERADOS

Quando funcionar corretamente, você verá:

```
========================================
🔔 INICIANDO PROCESSO DE NOTIFICAÇÃO
Tipo: Novo POP
Título: Exemplo de POP
Versão: v1
Registro ID: 123
========================================
┌─────────────────────────────────────────────────────────┐
│ 🔔 SISTEMA DE NOTIFICAÇÕES POPs e ITs                   │
└─────────────────────────────────────────────────────────┘
📋 Título: 📋 Novo POP Pendente
💬 Mensagem: Um novo registro 'Exemplo' v1 foi criado...
🏷️  Tipo: pops_its_pendente
🔍 Verificando se coluna pode_aprovar_pops_its existe...
✅ Coluna existe!
🔍 Buscando administradores com pode_aprovar_pops_its = 1...
✅ ADMINS COM PERMISSÃO ENCONTRADOS: 2
   👤 Admin1 (ID: 1, Email: admin1@example.com)
   👤 Admin2 (ID: 2, Email: admin2@example.com)
--- CRIANDO NOTIFICAÇÃO PARA Admin1 (ID: 1) ---
✅ NOTIFICAÇÃO CRIADA COM SUCESSO para Admin1
📧 ENVIANDO EMAIL PARA 2 ADMINISTRADORES
✅ EMAIL ENVIADO COM SUCESSO PARA ADMINS
=== RESULTADO FINAL ===
NOTIFICAÇÕES CRIADAS: 2 de 2
EMAILS ENVIADOS: 2
========================================
🔔 RESULTADO FINAL DA NOTIFICAÇÃO: ✅ SUCESSO
========================================
```

---

## 🎯 RESUMO RÁPIDO

**Para CADA administrador que deve receber notificações:**

1. Executar SQL: `UPDATE users SET pode_aprovar_pops_its = 1 WHERE id = X;`
2. OU marcar checkbox no perfil: ☑️ "Pode Aprovar POPs e ITs"
3. Verificar se status é `active`
4. Testar criando um novo POP/IT
5. Ver logs em `logs/pops_its_debug.log`

**Pronto!** 🎉
