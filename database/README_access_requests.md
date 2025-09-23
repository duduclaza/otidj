# Sistema de Solicitações de Acesso - SGQ OTI DJ

## 🎯 **Funcionalidades Implementadas**

### ✅ **Página de Solicitação de Acesso**
- **URL:** `/request-access`
- **Campos:** Nome, Email, Senha (com confirmação e visualização), Setor, Filial, Justificativa
- **Validações:** Email único, senhas coincidem, campos obrigatórios
- **Interface:** TailwindCSS com validação em tempo real

### ✅ **Painel Administrativo**
- **URL:** `/admin/access-requests`
- **Funcionalidades:** Listar, aprovar, rejeitar solicitações
- **Seleção de Perfil:** Admin escolhe perfil ao aprovar
- **Email Automático:** Envio de boas-vindas após aprovação

### ✅ **Sistema de Email**
- **PHPMailer:** Integração completa
- **Template HTML:** Email responsivo de boas-vindas
- **Configuração:** Tabela `email_config` para SMTP

## 📊 **Estrutura do Banco de Dados**

### **Tabela: access_requests**
```sql
- id (PK)
- name (Nome completo)
- email (Email único)
- password_hash (Senha criptografada)
- setor, filial (Opcionais)
- justificativa (Obrigatória)
- status (pendente, aprovado, rejeitado)
- profile_id (Perfil selecionado na aprovação)
- approved_by, approved_at
- rejection_reason
- created_at, updated_at
```

### **Tabela: email_config**
```sql
- Configurações SMTP
- Credenciais de email
- Templates personalizáveis
```

## 🔄 **Fluxo Completo**

### **1. Solicitação de Acesso**
1. Usuário acessa `/request-access`
2. Preenche formulário com senha e confirmação
3. Sistema valida e armazena na tabela `access_requests`
4. Status inicial: "pendente"

### **2. Aprovação pelo Admin**
1. Admin acessa `/admin/access-requests`
2. Visualiza lista de solicitações pendentes
3. Clica em "Aprovar" → seleciona perfil do usuário
4. Sistema:
   - Cria usuário na tabela `users`
   - Atualiza status para "aprovado"
   - Envia email de boas-vindas
   - Remove da lista de pendentes

### **3. Rejeição pelo Admin**
1. Admin clica em "Rejeitar"
2. Pode adicionar motivo da rejeição
3. Status atualizado para "rejeitado"
4. Remove da lista de pendentes

### **4. Email de Boas-vindas**
- **Assunto:** "Bem-vindo ao SGQ OTI DJ"
- **Conteúdo:** Mensagem de aprovação + link do sistema
- **Credenciais:** Email + lembrete da senha definida
- **Design:** Template HTML responsivo

## 🛠️ **Arquivos Criados/Modificados**

### **Backend:**
- `src/Controllers/AccessRequestController.php` - Controller principal
- `database/access_requests_schema.sql` - Schema do banco

### **Frontend:**
- `views/pages/auth/request-access.php` - Formulário de solicitação
- `views/pages/admin/access-requests.php` - Painel administrativo

### **Rotas:**
- `public/index.php` - Rotas adicionadas
- `views/partials/sidebar.php` - Menu atualizado
- `views/auth/login.php` - Link "Solicitar Acesso"

## 🔧 **Configuração Necessária**

### **1. Instalar PHPMailer**
```bash
composer require phpmailer/phpmailer
```

### **2. Executar SQLs**
```sql
source database/access_requests_schema.sql;
```

### **3. Configurar Email**
```sql
UPDATE email_config SET 
    smtp_username = 'seu-email@gmail.com',
    smtp_password = 'sua-senha-de-app',
    from_email = 'noreply@sgqoti.com.br';
```

## 🎨 **Interface e UX**

### **Formulário de Solicitação:**
- ✅ Campos com validação em tempo real
- ✅ Senha com botão de visualização (olho)
- ✅ Confirmação de senha com indicador visual
- ✅ Mensagens de sucesso/erro
- ✅ Loading states
- ✅ Design responsivo

### **Painel Admin:**
- ✅ Estatísticas de solicitações
- ✅ Tabela com informações completas
- ✅ Modais para aprovação/rejeição
- ✅ Seleção de perfil obrigatória
- ✅ Atualização automática da lista

## 🔐 **Segurança Implementada**

- ✅ **Senhas criptografadas** com `password_hash()`
- ✅ **Validação de email único** (não duplica)
- ✅ **Verificação de admin** para aprovar/rejeitar
- ✅ **Sanitização de dados** de entrada
- ✅ **Proteção CSRF** via formulários
- ✅ **Validação server-side** completa

## 📧 **Sistema de Email**

### **Configuração SMTP:**
- Suporte a Gmail, Outlook, servidores customizados
- Criptografia TLS/SSL
- Autenticação segura

### **Template de Email:**
- Design profissional e responsivo
- Informações de acesso claras
- Link direto para o sistema
- Fallback em texto simples

## ✅ **Benefícios Alcançados**

1. **Processo Automatizado:** Solicitação → Aprovação → Criação de usuário → Email
2. **Interface Intuitiva:** Formulários claros e responsivos
3. **Segurança Robusta:** Validações e criptografia
4. **Gestão Centralizada:** Admin controla todas as solicitações
5. **Comunicação Automática:** Emails de boas-vindas
6. **Integração Completa:** Sistema integrado ao SGQ existente

## 🚀 **Como Testar**

1. **Solicitação:**
   - Acesse `/request-access`
   - Preencha o formulário
   - Verifique se aparece em "pendentes"

2. **Aprovação:**
   - Login como admin
   - Acesse `/admin/access-requests`
   - Aprove uma solicitação
   - Verifique se usuário foi criado
   - Confirme recebimento do email

3. **Login do Novo Usuário:**
   - Use as credenciais da solicitação
   - Confirme acesso ao sistema

O sistema está **100% funcional** e pronto para uso em produção! 🎉
