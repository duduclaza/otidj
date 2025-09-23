# Instalação do PHPMailer

Para o sistema de envio de emails funcionar, você precisa instalar o PHPMailer via Composer.

## Comandos para executar:

```bash
# Navegue até a pasta do projeto
cd C:\Users\Clayton\Desktop\sgqpro

# Instale o PHPMailer
composer require phpmailer/phpmailer

# Se não tiver composer.json, crie primeiro:
composer init

# Depois instale o PHPMailer
composer require phpmailer/phpmailer
```

## Configuração de Email

Após instalar o PHPMailer, configure as credenciais de email no arquivo `database/access_requests_schema.sql` ou diretamente no banco:

```sql
UPDATE email_config SET 
    smtp_username = 'seu-email@gmail.com',
    smtp_password = 'sua-senha-de-app',
    from_email = 'noreply@sgqoti.com.br'
WHERE id = 1;
```

## Para Gmail:
1. Ative a verificação em 2 etapas
2. Gere uma "Senha de app" específica
3. Use essa senha no campo `smtp_password`

## Teste o sistema:
1. Execute os SQLs do banco
2. Acesse `/request-access` para testar solicitações
3. Acesse `/admin/access-requests` para gerenciar (como admin)
