# 🔔 Resolver Problema de Notificações POPs/ITs - Rafael Camargo

## 📋 Problema Identificado

**Usuário:** Rafael Camargo (rafael.camargo@djlocacao.com.br)  
**Sintoma:** Não está recebendo emails quando alguém insere IT ou POP para aprovação  
**Data:** 03/11/2024

---

## 🔍 Causa Raiz

O sistema de notificações POPs/ITs busca administradores com uma **flag específica** no banco de dados:

### Como Funciona

```php
// PopItsController.php - Linha 1902-1910
$stmt = $this->db->prepare("
    SELECT id, name, email, pode_aprovar_pops_its, status
    FROM users 
    WHERE role = 'admin' 
    AND pode_aprovar_pops_its = 1  <-- FLAG OBRIGATÓRIA
    AND status = 'active'
");
```

**O Rafael provavelmente tem `pode_aprovar_pops_its = 0` ou `NULL`**, por isso não recebe os emails.

---

## ✅ Solução Rápida

### **Opção 1: Via phpMyAdmin (RECOMENDADO)**

1. Acesse phpMyAdmin do banco `u230868210_djsgqpro`
2. Execute o script SQL: `verificar_e_ativar_rafael_pops.sql`
3. Siga os passos numerados do script

### **Opção 2: Comando SQL Direto**

```sql
-- Ativar notificações para Rafael
UPDATE users 
SET pode_aprovar_pops_its = 1
WHERE email = 'rafael.camargo@djlocacao.com.br'
AND role = 'admin';

-- Verificar se funcionou
SELECT id, name, email, pode_aprovar_pops_its
FROM users 
WHERE email = 'rafael.camargo@djlocacao.com.br';
```

---

## 📝 Passo a Passo Detalhado

### **PASSO 1: Verificar se a coluna existe**

```sql
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'users'
AND COLUMN_NAME = 'pode_aprovar_pops_its';
```

**Se não retornar nada**, a coluna não existe e precisa criar:

```sql
ALTER TABLE users 
ADD COLUMN pode_aprovar_pops_its TINYINT(1) DEFAULT 0 AFTER role;
```

### **PASSO 2: Verificar dados do Rafael**

```sql
SELECT 
    id,
    name,
    email,
    role,
    pode_aprovar_pops_its,
    status
FROM users 
WHERE email = 'rafael.camargo@djlocacao.com.br';
```

**Verificar:**
- ✅ `role` deve ser `'admin'`
- ✅ `status` deve ser `'active'`
- ❌ `pode_aprovar_pops_its` provavelmente é `0` ou `NULL`

### **PASSO 3: Ativar a permissão**

```sql
UPDATE users 
SET pode_aprovar_pops_its = 1
WHERE email = 'rafael.camargo@djlocacao.com.br';
```

### **PASSO 4: Verificar outros admins**

```sql
SELECT 
    id,
    name,
    email,
    pode_aprovar_pops_its,
    status
FROM users 
WHERE role = 'admin' 
ORDER BY pode_aprovar_pops_its DESC;
```

**Ative para todos os admins que devem receber notificações:**

```sql
UPDATE users 
SET pode_aprovar_pops_its = 1
WHERE role = 'admin' 
AND status = 'active'
AND email IN (
    'rafael.camargo@djlocacao.com.br',
    'outro.admin@djlocacao.com.br'  -- adicione outros
);
```

---

## 🧪 Teste de Funcionamento

### **1. Criar um POP/IT de Teste**

1. Login no sistema com um usuário qualquer
2. Acesse **POPs e ITs → Meus Registros**
3. Crie um novo registro (qualquer arquivo)
4. O status será **PENDENTE**

### **2. Verificar Logs do Sistema**

O sistema gera logs detalhados. Verifique em:
- `storage/logs/error.log`
- Logs do servidor PHP

Procure por:
```
🔔 INICIANDO PROCESSO DE NOTIFICAÇÃO
✅ ADMINS COM PERMISSÃO ENCONTRADOS: X
📧 ENVIANDO EMAIL PARA X ADMINISTRADORES
✅ EMAIL ENVIADO COM SUCESSO
```

### **3. Verificar Notificações no Banco**

```sql
-- Ver últimas notificações criadas
SELECT 
    n.id,
    u.name,
    u.email,
    n.title,
    n.created_at
FROM notifications n
LEFT JOIN users u ON n.user_id = u.id
WHERE n.type = 'pops_its_pendente'
ORDER BY n.created_at DESC
LIMIT 10;
```

---

## 📊 Diagnóstico Completo

Execute o script completo para diagnóstico:

```bash
mysql -u u230868210_dusouza -p u230868210_djsgqpro < verificar_e_ativar_rafael_pops.sql
```

O script vai mostrar:
- ✅ Se a coluna existe
- ✅ Status do Rafael
- ✅ Todos os admins e suas permissões
- ✅ Registros pendentes
- ✅ Últimas notificações enviadas

---

## 🔧 Solução Alternativa (Se o problema persistir)

### **Problema: Coluna não existe**

Se a coluna `pode_aprovar_pops_its` não existir, o sistema tem um **fallback** que busca **TODOS** os admins ativos:

```php
// PopItsController.php - Linha 1918-1922
$stmt = $this->db->prepare("
    SELECT id, name, email, status 
    FROM users 
    WHERE role = 'admin' 
    AND status = 'active'
");
```

Neste caso, verifique:
1. Rafael tem `role = 'admin'`?
2. Rafael tem `status = 'active'`?
3. Email está correto no cadastro?

---

## 📧 Verificar Configurações de Email

### **1. Testar envio de email manualmente**

Crie um arquivo `test_email_rafael.php` na raiz:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$emailService = new \App\Services\EmailService();

$resultado = $emailService->send(
    'rafael.camargo@djlocacao.com.br',
    'TESTE - Notificações POPs/ITs',
    '<h1>Teste de Email</h1><p>Se você recebeu este email, o sistema está funcionando!</p>',
    'Teste de Email - Se você recebeu este email, o sistema está funcionando!'
);

echo $resultado ? "✅ Email enviado com sucesso!" : "❌ Erro ao enviar email";
if (!$resultado) {
    echo "\nErro: " . $emailService->getLastError();
}
```

Execute:
```bash
php test_email_rafael.php
```

### **2. Verificar configurações SMTP**

No `.env`, verifique:
```env
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=suporte@djbr.sgqoti.com.br
MAIL_PASSWORD=Pandora@1989
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=suporte@djbr.sgqoti.com.br
```

---

## 📌 Checklist Final

- [ ] Coluna `pode_aprovar_pops_its` existe
- [ ] Rafael tem `role = 'admin'`
- [ ] Rafael tem `status = 'active'`
- [ ] Rafael tem `pode_aprovar_pops_its = 1`
- [ ] Email `rafael.camargo@djlocacao.com.br` está correto
- [ ] Servidor SMTP configurado corretamente
- [ ] Teste de email funcionou
- [ ] Criado POP/IT de teste e verificado notificação

---

## 🎯 Resultado Esperado

Após ativar a flag, quando alguém criar um POP/IT:

1. **Sistema cria notificação** na tabela `notifications`
2. **Sistema envia email** para rafael.camargo@djlocacao.com.br
3. **Rafael recebe email** com título: "SGQ - Novo POP/IT Pendente de Aprovação 📋"
4. **Email contém**:
   - Título do POP/IT
   - Mensagem de pendência
   - Link para acessar o sistema
   - Botão "Acessar POPs e ITs"

---

## 📞 Suporte

Se o problema persistir após seguir todos os passos:

1. Verifique os **logs do sistema** (`storage/logs/error.log`)
2. Verifique os **logs de email** no PHPMailer (ativado no EmailService)
3. Teste com **outro admin** para isolar o problema
4. Verifique se o **domínio @djlocacao.com.br** está ativo e recebendo emails

---

**Arquivo criado em:** 03/11/2024  
**Por:** Sistema de Análise SGQ-OTI DJ
