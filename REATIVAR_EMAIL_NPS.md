# 📧 Como Reativar Notificação Email NPS

**Data:** 17/11/2025  
**Status:** ⏸️ Notificação DESABILITADA temporariamente

---

## 📍 Onde Está

**Arquivo:** `src/Controllers/NpsController.php`  
**Linhas:** 422-429

---

## 🔄 Para Reativar

### **Descomentar o código:**

```php
// Linha 422-429 - Remover os /* e */

// DE:
// Notificação por email DESABILITADA temporariamente
/*
try {
    $this->notificarAdminsNovaResposta($formulario, $resposta);
} catch (\Exception $emailError) {
    error_log('NPS: Erro ao enviar notificação, mas resposta foi salva: ' . $emailError->getMessage());
}
*/

// PARA:
// Enviar email para todos admins e super admins
try {
    $this->notificarAdminsNovaResposta($formulario, $resposta);
} catch (\Exception $emailError) {
    error_log('NPS: Erro ao enviar notificação, mas resposta foi salva: ' . $emailError->getMessage());
}
```

---

## ✅ Antes de Reativar, Verificar:

```
□ EmailService está configurado
□ SMTP está funcionando
□ Há admins com email cadastrado
□ Testou envio manual de email
□ APP_URL está correto no .env
```

---

## 🧪 Testar Após Reativar

```
1. Responder formulário NPS
2. Verificar inbox dos admins
3. Ver logs: tail -f /var/log/php/error.log | grep NPS
4. Confirmar: "X email(s) enviado(s) para admins"
```

---

## 🎯 Função Completa da Notificação

**O que faz:**
- Busca TODOS admins e super admins do banco
- Envia email HTML bonito para cada um
- Contém: nome formulário, quem respondeu, respostas, link
- Não quebra salvamento se email falhar

**Localização da função:**
- `src/Controllers/NpsController.php`
- Linhas 975-1066
- Método: `notificarAdminsNovaResposta()`

---

**Quando estiver pronto, descomente e teste!** 📧
