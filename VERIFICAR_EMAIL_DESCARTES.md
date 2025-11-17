# 🔍 Verificar Por Que Email Não Foi Enviado - Descartes

**Data:** 17/11/2025  
**Problema:** Email não foi recebido ao criar novo descarte  
**Status:** Guia de Troubleshooting

---

## 🎯 O Que Deve Acontecer

Ao criar um novo descarte:
1. ✅ Sistema salva com status "Aguardando Descarte"
2. ✅ Busca todos admins + super_admins + perfil qualidade
3. ✅ Envia email HTML para cada um
4. ✅ Registra no log: "Controle Descartes: X email(s) enviado(s)..."

---

## 🔍 Passo a Passo de Verificação

### **1. Verificar se SQL Foi Executado**

```sql
-- Ver estrutura da tabela
DESCRIBE controle_descartes;

-- Deve ter estas colunas:
-- status (VARCHAR)
-- status_alterado_por (INT)
-- status_alterado_em (DATETIME)
-- justificativa_status (TEXT)
```

**Se não tiver essas colunas:**
```bash
mysql -u root -p sgq_db < database/add_status_controle_descartes.sql
```

---

### **2. Verificar Destinatários de Email**

```sql
-- Buscar quem receberia o email
SELECT DISTINCT u.id, u.name, u.email, u.role
FROM users u
LEFT JOIN user_profiles up ON u.id = up.user_id
LEFT JOIN profiles p ON up.profile_id = p.id
WHERE (
    u.role IN ('admin', 'super_admin')
    OR LOWER(p.nome) = 'qualidade'
)
AND u.email IS NOT NULL 
AND u.email != '';

-- Se retornar 0 linhas = PROBLEMA!
-- Ninguém para receber email
```

**Soluções:**
- Adicionar email aos admins
- Criar perfil "Qualidade"
- Associar usuários ao perfil

---

### **3. Verificar Logs do PHP**

**Localização dos logs:**
```
Windows (XAMPP): C:\xampp\php\logs\php_error_log
Linux: /var/log/php_errors.log
Apache: error.log
```

**Buscar por:**
```
"Controle Descartes: Erro ao enviar email"
"Controle Descartes: X email(s) enviado(s)"
"Controle Descartes: Nenhum destinatário encontrado"
```

**Se aparecer "Nenhum destinatário":**
- Executar query do passo 2
- Verificar se admins têm email
- Verificar se perfil "Qualidade" existe

---

### **4. Verificar se EmailService Existe**

**Arquivo:** `src/Services/EmailService.php`

```php
// O código verifica antes de enviar:
if (class_exists('\App\Services\EmailService')) {
    \App\Services\EmailService::send(...);
} else {
    // Email não será enviado!
}
```

**Verificar:**
```
ls src/Services/EmailService.php
```

**Se não existir:**
- EmailService precisa ser criado/configurado
- Ou instalar PHPMailer

---

### **5. Verificar Configuração SMTP**

**Arquivo:** `.env` ou `src/Config/Email.php`

**Deve ter:**
```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=seu-email@gmail.com
SMTP_PASS=sua-senha-app
SMTP_FROM=noreply@seusite.com
SMTP_FROM_NAME=SGQ OTI DJ
```

**Se não tiver:**
- Configurar SMTP
- Ou usar `mail()` do PHP
- Ou usar serviço como SendGrid/Mailgun

---

### **6. Testar Envio Manual**

**Criar arquivo:** `test_email_descarte.php`

```php
<?php
require_once 'vendor/autoload.php';
require_once 'src/Config/Database.php';

// Teste simples
if (class_exists('\App\Services\EmailService')) {
    echo "✅ EmailService existe\n";
    
    $teste = \App\Services\EmailService::send(
        'seu-email@teste.com',
        'Teste Descarte',
        'Este é um teste de email do sistema de descartes.'
    );
    
    if ($teste) {
        echo "✅ Email enviado com sucesso!\n";
    } else {
        echo "❌ Erro ao enviar email\n";
    }
} else {
    echo "❌ EmailService NÃO existe\n";
}

// Testar query de destinatários
$db = \App\Config\Database::getInstance();
$stmt = $db->prepare("
    SELECT DISTINCT u.id, u.name, u.email, u.role
    FROM users u
    LEFT JOIN user_profiles up ON u.id = up.user_id
    LEFT JOIN profiles p ON up.profile_id = p.id
    WHERE (
        u.role IN ('admin', 'super_admin')
        OR LOWER(p.nome) = 'qualidade'
    )
    AND u.email IS NOT NULL 
    AND u.email != ''
");
$stmt->execute();
$destinatarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n📧 Destinatários encontrados: " . count($destinatarios) . "\n";
foreach ($destinatarios as $dest) {
    echo "  - {$dest['name']} ({$dest['email']}) - {$dest['role']}\n";
}
```

**Executar:**
```bash
php test_email_descarte.php
```

---

### **7. Verificar Perfil "Qualidade"**

```sql
-- Ver se perfil existe
SELECT * FROM profiles WHERE LOWER(nome) = 'qualidade';

-- Se não existir, criar:
INSERT INTO profiles (nome, descricao, created_at, updated_at)
VALUES ('Qualidade', 'Perfil para equipe de qualidade', NOW(), NOW());

-- Associar usuário ao perfil:
INSERT INTO user_profiles (user_id, profile_id)
VALUES (
    (SELECT id FROM users WHERE email = 'usuario@exemplo.com'),
    (SELECT id FROM profiles WHERE LOWER(nome) = 'qualidade')
);
```

---

## 🐛 Problemas Comuns

### **Problema 1: "Nenhum destinatário encontrado"**

**Causa:** Nenhum admin tem email OU perfil "Qualidade" não existe

**Solução:**
```sql
-- Adicionar email ao admin
UPDATE users 
SET email = 'admin@empresa.com' 
WHERE role = 'admin' AND id = 1;

-- Criar perfil qualidade (se não existir)
INSERT INTO profiles (nome) VALUES ('Qualidade');
```

---

### **Problema 2: "EmailService não existe"**

**Causa:** Classe não foi carregada ou não existe

**Solução:**
```bash
# Instalar PHPMailer
composer require phpmailer/phpmailer

# Ou verificar se arquivo existe
ls src/Services/EmailService.php
```

---

### **Problema 3: "SMTP Error"**

**Causa:** Configuração SMTP incorreta

**Solução:**
- Verificar credenciais
- Usar senha de app (Gmail)
- Verificar firewall/porta
- Testar com serviço alternativo

---

### **Problema 4: Email vai para SPAM**

**Causa:** Configuração SPF/DKIM ou remetente não confiável

**Solução:**
- Usar domínio real no SMTP_FROM
- Configurar SPF no DNS
- Usar serviço de email transacional

---

### **Problema 5: Código não chama notificação**

**Causa:** Código está comentado ou try/catch silencia erro

**Verificar:**
```php
// Linha 204-210 em ControleDescartesController.php
try {
    $this->notificarNovoDescarte($descarte_id);
} catch (\Exception $emailError) {
    error_log('Erro ao enviar notificação: ' . $emailError->getMessage());
}

// Se estiver comentado, descomentar!
```

---

## 📝 Checklist de Verificação

```
⬜ SQL executado (colunas status adicionadas)
⬜ Destinatários existem (query retorna > 0)
⬜ Admins têm email cadastrado
⬜ Perfil "Qualidade" existe
⬜ Usuários associados ao perfil
⬜ EmailService existe (arquivo)
⬜ SMTP configurado (.env)
⬜ Log não mostra erros
⬜ Código de notificação ativo (não comentado)
⬜ Try/catch não silencia erro
⬜ Teste manual funciona
```

---

## 🧪 Teste Rápido

### **Criar Descarte de Teste:**

```
1. ✅ Acessar Controle de Descartes
2. ✅ Clicar "Novo Descarte"
3. ✅ Preencher dados
4. ✅ Salvar
5. ✅ Ver mensagem "Descarte registrado com sucesso!"
6. ✅ Aguardar 1-2 minutos
7. ✅ Verificar email dos admins/qualidade
```

### **Verificar Log Imediatamente:**

```bash
# Ver últimas linhas do log
tail -f /caminho/para/php_error_log

# Buscar mensagem específica
grep "Controle Descartes" php_error_log
```

**Resultado esperado:**
```
Controle Descartes: 3 email(s) enviado(s) sobre novo descarte ID 123
```

**Se aparecer 0 emails:**
- Query de destinatários retornou vazio
- Verificar passo 2

---

## 🔧 Forçar Teste Direto

**Criar arquivo:** `force_test_descarte_email.php`

```php
<?php
session_start();
require_once 'vendor/autoload.php';

// Simular sessão admin
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

// Criar controller
$controller = new \App\Controllers\ControleDescartesController();

// Simular POST de criação
$_POST = [
    'numero_serie' => 'TESTE123',
    'filial_id' => 1,
    'codigo_produto' => 'TEST-001',
    'descricao_produto' => 'Teste de email',
    'responsavel_tecnico' => 'João Teste',
    'observacoes' => 'Teste para verificar envio de email'
];

// Chamar método create
$controller->create();

// Ver resultado
// Deve aparecer no log: "Controle Descartes: X email(s)..."
```

**Executar:**
```bash
php force_test_descarte_email.php
```

---

## 📊 Diagnóstico por Sintoma

### **Sintoma:** "Nenhum email chega"

**Possíveis causas:**
1. Query de destinatários vazia
2. EmailService não existe
3. SMTP não configurado
4. Código comentado
5. Try/catch silencia erro

### **Sintoma:** "Log diz 0 emails enviados"

**Causa:** Query não retorna destinatários

**Solução:** Executar query manualmente (passo 2)

### **Sintoma:** "Log diz 3 emails enviados, mas não chega"

**Causa:** SMTP configurado errado ou SPAM

**Solução:**
- Verificar caixa SPAM
- Verificar config SMTP
- Testar com outro email

### **Sintoma:** "Erro na criação do descarte"

**Causa:** SQL não foi executado (coluna status não existe)

**Solução:** Executar SQL (passo 1)

---

## ✅ Solução Rápida

Se nenhum email chega, faça isso:

```sql
-- 1. Verificar/adicionar email aos admins
UPDATE users 
SET email = 'seu-email@teste.com' 
WHERE role IN ('admin', 'super_admin') 
AND (email IS NULL OR email = '');

-- 2. Criar perfil qualidade
INSERT INTO profiles (nome, descricao, created_at, updated_at)
VALUES ('Qualidade', 'Equipe de Qualidade', NOW(), NOW())
ON DUPLICATE KEY UPDATE nome=nome;

-- 3. Associar seu usuário ao perfil
INSERT INTO user_profiles (user_id, profile_id)
SELECT 1, id FROM profiles WHERE LOWER(nome) = 'qualidade'
ON DUPLICATE KEY UPDATE user_id=user_id;

-- 4. Verificar se funcionou
SELECT DISTINCT u.name, u.email, u.role
FROM users u
LEFT JOIN user_profiles up ON u.id = up.user_id
LEFT JOIN profiles p ON up.profile_id = p.id
WHERE (u.role IN ('admin', 'super_admin') OR LOWER(p.nome) = 'qualidade')
AND u.email IS NOT NULL;
-- Deve retornar pelo menos 1 linha!
```

Depois:
```
1. Criar novo descarte
2. Verificar log PHP
3. Verificar email
```

---

**Status:** Guia Completo de Troubleshooting  
**Próximo:** Executar passos 1-7 até encontrar o problema  
**Sistema:** SGQ-OTI DJ
