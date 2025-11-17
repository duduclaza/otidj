# 📧 Sistema de Notificação Email NPS

**Data:** 17/11/2025  
**Status:** ✅ Implementado

---

## 🎯 Objetivo

**TODA VEZ** que alguém responder **QUALQUER** formulário NPS, **TODOS** os admins e super admins são notificados por email automaticamente.

---

## ✅ Como Funciona

### **Fluxo Automático:**

```
1. USUÁRIO RESPONDE FORMULÁRIO NPS
   └─ Preenche formulário público
   └─ Clica "Enviar Resposta"

2. SISTEMA SALVA RESPOSTA
   └─ Salva em arquivo JSON
   └─ Gera ID único

3. BUSCA TODOS ADMINS
   └─ Query SQL: role IN ('admin', 'super_admin')
   └─ Apenas com email válido

4. ENVIA EMAIL PARA CADA UM
   └─ Assunto: 📊 Nova Resposta NPS
   └─ Conteúdo: Detalhes completos
   └─ Link direto para ver respostas

5. CONFIRMA ENVIO
   └─ Grava log de quantos emails enviados
   └─ Trata erros individualmente
```

---

## 📧 Conteúdo do Email

### **Assunto:**
```
📊 Nova Resposta NPS: [Título do Formulário]
```

### **Corpo do Email:**

```
┌──────────────────────────────────────────┐
│  📊 Nova Resposta NPS                    │
├──────────────────────────────────────────┤
│                                          │
│  📋 Formulário:                          │
│  "Satisfação do Cliente 2025"            │
│  "Pesquisa sobre atendimento"            │
│                                          │
│  👤 Respondido por:                      │
│  Nome: João Silva                        │
│  Email: joao@email.com                   │
│  Data: 17/11/2025 06:30                  │
│                                          │
│  💬 Respostas:                           │
│  • Satisfação geral: 5                   │
│  • Recomendaria?: Sim                    │
│  • Comentários: Excelente atendimento    │
│                                          │
│  ┌────────────────────────────┐         │
│  │  Ver Todas as Respostas    │ (Botão) │
│  └────────────────────────────┘         │
│                                          │
│  Sistema NPS - SGQ OTI DJ                │
└──────────────────────────────────────────┘
```

---

## 🔧 Implementação Técnica

### **Arquivo Modificado:**
- `src/Controllers/NpsController.php`

### **Função Adicionada:**
```php
private function notificarAdminsNovaResposta($formulario, $resposta)
{
    // 1. Busca todos admins e super admins
    $stmt = $db->prepare("
        SELECT id, name, email 
        FROM users 
        WHERE role IN ('admin', 'super_admin')
        AND email IS NOT NULL 
        AND email != ''
    ");
    
    // 2. Prepara email HTML bonito
    $assunto = "📊 Nova Resposta NPS: {$formulario['titulo']}";
    $mensagem = "..."; // HTML completo
    
    // 3. Envia para cada admin
    foreach ($admins as $admin) {
        EmailService::send($admin['email'], $assunto, $mensagem);
    }
}
```

### **Chamada no Método `salvarResposta()`:**
```php
// Salvar resposta
file_put_contents($respostaFilename, json_encode($resposta));

// 👇 NOVA LINHA ADICIONADA
$this->notificarAdminsNovaResposta($formulario, $resposta);

echo json_encode(['success' => true, 'message' => '...']);
```

---

## 📊 Quem Recebe Email

### **Critérios:**
- ✅ Usuários com `role = 'admin'`
- ✅ Usuários com `role = 'super_admin'`
- ✅ Que tenham email cadastrado (não nulo, não vazio)

### **Exemplo SQL:**
```sql
SELECT id, name, email 
FROM users 
WHERE role IN ('admin', 'super_admin')
AND email IS NOT NULL 
AND email != '';
```

---

## 🎨 Design do Email

### **Características:**
- ✅ Gradiente roxo no cabeçalho
- ✅ Layout responsivo
- ✅ Cards brancos com informações
- ✅ Botão colorido para ação
- ✅ Fonte profissional (Arial)
- ✅ Máximo 600px de largura
- ✅ HTML inline CSS

### **Seções:**
1. **Cabeçalho** - Título com gradiente
2. **Formulário** - Nome e descrição
3. **Respondente** - Nome, email, data
4. **Respostas** - Lista de perguntas e respostas
5. **Ação** - Botão para ver todas
6. **Rodapé** - Texto informativo

---

## 🧪 Como Testar

### **Passo 1: Verificar Admins**
```sql
-- Ver quem vai receber os emails
SELECT id, name, email, role 
FROM users 
WHERE role IN ('admin', 'super_admin')
AND email IS NOT NULL;
```

### **Passo 2: Criar Formulário**
```
1. Login como usuário
2. Ir para /nps
3. Criar novo formulário
4. Ativar formulário
5. Copiar link público
```

### **Passo 3: Responder Formulário**
```
1. Abrir link público (sem login)
2. Preencher nome e email
3. Responder perguntas
4. Enviar resposta
5. ✅ Sistema envia emails
```

### **Passo 4: Verificar Emails**
```
1. Abrir email de cada admin
2. Verificar recebimento
3. Verificar formatação
4. Testar botão "Ver Todas"
```

---

## 📝 Logs

### **Logs Gerados:**

**Sucesso:**
```
NPS: 3 email(s) enviado(s) para admins sobre resposta do formulário form_1234567890_abc123
```

**Nenhum Admin:**
```
NPS: Nenhum admin encontrado para notificar
```

**Erro Individual:**
```
NPS: Erro ao enviar email para admin@email.com: [mensagem do erro]
```

**Erro Geral:**
```
NPS: Erro ao notificar admins: [mensagem do erro]
```

### **Onde Ver Logs:**
```bash
# Linux/Mac
tail -f /var/log/php/error.log

# Logs do sistema (se existir)
tail -f storage/logs/app_YYYY-MM-DD.log
```

---

## 🔒 Segurança

### **Validações:**
- ✅ Verifica se EmailService existe antes de usar
- ✅ Trata exceção individual para cada email
- ✅ Não interrompe salvamento se email falhar
- ✅ HTML escapado para prevenir XSS
- ✅ Apenas admins com email válido recebem

### **Tratamento de Erros:**
```php
try {
    EmailService::send($admin['email'], $assunto, $mensagem);
    $emailsEnviados++;
} catch (\Exception $e) {
    // Apenas loga, não interrompe
    error_log("Erro ao enviar para {$admin['email']}: {$e->getMessage()}");
}
```

---

## ⚙️ Configuração

### **Pré-requisitos:**

**1. EmailService Configurado:**
```php
// Arquivo: src/Services/EmailService.php
// Deve ter método estático: send($email, $assunto, $mensagem)
```

**2. SMTP Configurado:**
```php
// Configurar no sistema:
- Host SMTP
- Porta SMTP
- Usuário
- Senha
- Email remetente
```

**3. APP_URL Definido:**
```php
// .env ou config
$_ENV['APP_URL'] = 'https://djpr.sgqoti.com.br';
```

---

## 📈 Estatísticas

### **Informações no Email:**
- 📋 Nome do formulário
- 📝 Descrição do formulário
- 👤 Nome do respondente
- 📧 Email do respondente (se fornecido)
- 📅 Data e hora da resposta
- 💬 Todas as perguntas e respostas
- 🔗 Link direto para dashboard

### **Exemplo de Resposta:**
```json
{
  "nome": "João Silva",
  "email": "joao@email.com",
  "respostas": [
    {
      "pergunta": "Qual sua satisfação?",
      "resposta": "5"
    },
    {
      "pergunta": "Comentários",
      "resposta": "Excelente atendimento!"
    }
  ]
}
```

---

## 🎯 Benefícios

### **Para Admins:**
- ✅ Notificação imediata de novas respostas
- ✅ Não precisa ficar verificando o sistema
- ✅ Email profissional e bem formatado
- ✅ Link direto para ver detalhes
- ✅ Todas as informações importantes

### **Para o Sistema:**
- ✅ Aumenta engajamento dos admins
- ✅ Resposta mais rápida a feedbacks
- ✅ Facilita análise de NPS
- ✅ Melhora comunicação interna
- ✅ Histórico por email

---

## 🐛 Troubleshooting

### **Problema: Emails não chegam**

**Verificar:**
```php
// 1. EmailService está configurado?
class_exists('\App\Services\EmailService'); // deve ser true

// 2. SMTP está funcionando?
// Testar envio manual

// 3. Há admins cadastrados?
SELECT COUNT(*) FROM users WHERE role IN ('admin', 'super_admin');
```

**Soluções:**
```
✅ Configurar SMTP corretamente
✅ Adicionar usuários como admin
✅ Verificar spam/lixo eletrônico
✅ Verificar logs de erro
```

---

### **Problema: Email com formatação quebrada**

**Causa:** Cliente de email não suporta HTML

**Solução:**
- Criar versão texto alternativa (multipart)
- Usar HTML mais simples
- Testar em diferentes clientes

---

### **Problema: Muitos admins recebendo**

**Filtrar:**
```sql
-- Ver quantos admins há
SELECT COUNT(*) FROM users WHERE role IN ('admin', 'super_admin');

-- Remover role admin de alguns
UPDATE users SET role = 'user' WHERE id = X;
```

---

## 📊 Exemplo Real

### **Cenário:**
```
Sistema tem:
- 2 super_admins
- 5 admins
- Total: 7 pessoas

Cliente responde formulário "Satisfação Q4 2025"
```

### **Resultado:**
```
✅ 7 emails enviados
✅ Todos recebem simultaneamente
✅ Cada um pode acessar respostas
✅ Log registra: "7 email(s) enviado(s)"
```

---

## ✅ Checklist de Implementação

```
□ Código adicionado ao NpsController.php
□ EmailService está funcionando
□ SMTP está configurado
□ Há admins com email cadastrado
□ APP_URL está correto
□ Testou envio de resposta
□ Verificou recebimento de email
□ Email está bem formatado
□ Botão "Ver Todas" funciona
□ Logs estão sendo gravados
```

---

## 🎉 Resultado Final

**AGORA:**
- ✅ Toda resposta NPS → Email automático
- ✅ Todos admins notificados
- ✅ Email bonito e profissional
- ✅ Link direto para dashboard
- ✅ Logs de auditoria
- ✅ Tratamento de erros robusto
- ✅ Pronto para produção!

---

**Versão:** 1.0  
**Status:** ✅ Implementado  
**Sistema:** SGQ-OTI DJ
