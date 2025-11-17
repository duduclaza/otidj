# 🔧 Correção: Erro 500 ao Salvar Resposta NPS

**Data:** 17/11/2025  
**Erro:** POST https://djbr.sgqoti.com.br/nps/salvar-resposta 500 (Internal Server Error)

---

## ✅ Correções Aplicadas

### **1. Proteção do Envio de Email**
O erro 500 provavelmente está acontecendo na notificação de admins.

**ANTES:**
```php
// Se o email falhar, quebra o salvamento
$this->notificarAdminsNovaResposta($formulario, $resposta);
```

**DEPOIS:**
```php
// Email falhar não impede salvamento
try {
    $this->notificarAdminsNovaResposta($formulario, $resposta);
} catch (\Exception $emailError) {
    error_log('NPS: Erro ao enviar notificação, mas resposta foi salva');
}
```

### **2. Melhor Log de Erros**
Agora mostra exatamente qual erro aconteceu:

```php
catch (\Exception $e) {
    error_log('Erro ao salvar resposta NPS: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao enviar resposta: ' . $e->getMessage()
    ]);
}
```

---

## 🔍 Como Ver o Erro Real

### **Verificar Logs PHP:**

**Linux/Mac:**
```bash
tail -f /var/log/php/error.log
```

**Windows (XAMPP):**
```
C:\xampp\apache\logs\error.log
```

**Via cPanel:**
```
Error Log no painel
```

---

## 🐛 Possíveis Causas do Erro 500

### **1. Database Não Conecta**
```php
// Linha 979 do NpsController.php
$db = Database::getInstance();
```

**Verificar:**
```bash
# Ver se banco está rodando
systemctl status mysql  # Linux
# ou
services.msc  # Windows
```

---

### **2. Classe EmailService Não Existe**
```php
// Linha 1052 do NpsController.php
if (class_exists('\App\Services\EmailService')) {
    // ...
}
```

**Verificar:**
```bash
# Arquivo existe?
ls -la src/Services/EmailService.php
```

---

### **3. Pasta de Respostas Sem Permissão**
```php
// Linha 420
file_put_contents($respostaFilename, json_encode($resposta));
```

**Corrigir Permissões:**
```bash
# Linux/Mac
chmod -R 755 storage/formularios/respostas

# Ver permissões
ls -la storage/formularios/
```

---

### **4. Memória PHP Esgotada**
```php
// php.ini
memory_limit = 128M  // Muito pouco
```

**Aumentar:**
```ini
memory_limit = 256M
max_execution_time = 60
```

---

## 🧪 Testar Agora

### **Passo 1: Limpar Logs**
```bash
# Apagar logs antigos
> /var/log/php/error.log
```

### **Passo 2: Responder Formulário**
```
1. Abrir formulário NPS público
2. Preencher dados
3. Enviar resposta
4. Observar erro
```

### **Passo 3: Ver Logs**
```bash
tail -f /var/log/php/error.log
```

**Deve mostrar:**
```
Erro ao salvar resposta NPS: [mensagem do erro]
Stack trace: [trace completo]
```

---

## 🔧 Soluções Rápidas

### **Solução 1: Desabilitar Email Temporariamente**

**Comentar notificação:**
```php
// Linha 422-427
// try {
//     $this->notificarAdminsNovaResposta($formulario, $resposta);
// } catch (\Exception $emailError) {
//     error_log('...');
// }
```

**Testar novamente:**
- Se funcionar → problema é no email
- Se não funcionar → problema é em outro lugar

---

### **Solução 2: Verificar Pasta Existe**

**Criar pastas manualmente:**
```bash
mkdir -p storage/formularios/respostas
chmod -R 755 storage/formularios
chown -R www-data:www-data storage/formularios  # Linux
```

---

### **Solução 3: Testar Database**

**Script de teste:**
```php
<?php
// teste-db.php
require_once 'src/Config/Database.php';

try {
    $db = \App\Config\Database::getInstance();
    echo "✅ Database conectado!\n";
    
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    echo "✅ Query funciona! Total users: " . $stmt->fetchColumn() . "\n";
    
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
```

**Executar:**
```bash
php teste-db.php
```

---

## 📋 Checklist de Verificação

```
□ Logs PHP habilitados
□ Pasta storage/formularios/respostas existe
□ Permissões 755 nas pastas
□ Database está rodando
□ Arquivo Database.php existe
□ Classe EmailService existe (opcional)
□ memory_limit adequado (256M+)
□ max_execution_time adequado (60s+)
□ Erro aparece nos logs
□ Stack trace mostra linha exata
```

---

## 🎯 Debug Passo a Passo

### **1. Ver Se Chega no Método**
```php
// Adicionar no início de salvarResposta():
error_log('NPS: Iniciando salvamento de resposta');
error_log('NPS: Formulario ID: ' . ($_POST['formulario_id'] ?? 'vazio'));
```

### **2. Ver Onde Para**
```php
// Adicionar em cada etapa:
error_log('NPS: Validações OK');
error_log('NPS: Formulário carregado');
error_log('NPS: Resposta criada');
error_log('NPS: Arquivo salvo');
error_log('NPS: Email enviado');
```

### **3. Ver Logs em Tempo Real**
```bash
tail -f /var/log/php/error.log | grep NPS
```

---

## 📊 Exemplo de Log Correto

**Funcionando:**
```
[17-Nov-2025 06:56:12] NPS: Iniciando salvamento de resposta
[17-Nov-2025 06:56:12] NPS: Formulario ID: form_1763373296_691af0f010fb1
[17-Nov-2025 06:56:12] NPS: Validações OK
[17-Nov-2025 06:56:12] NPS: Formulário carregado
[17-Nov-2025 06:56:12] NPS: Resposta criada
[17-Nov-2025 06:56:12] NPS: Arquivo salvo
[17-Nov-2025 06:56:13] NPS: 3 email(s) enviado(s) para admins
```

**Com Erro:**
```
[17-Nov-2025 06:56:12] NPS: Iniciando salvamento de resposta
[17-Nov-2025 06:56:12] NPS: Formulario ID: form_1763373296_691af0f010fb1
[17-Nov-2025 06:56:12] NPS: Validações OK
[17-Nov-2025 06:56:12] Erro ao salvar resposta NPS: Class 'App\Config\Database' not found
[17-Nov-2025 06:56:12] Stack trace: #0 NpsController.php(979)...
```

---

## ✅ Após Correção

**Testar:**
```
1. ✅ Responder formulário
2. ✅ Ver mensagem de sucesso
3. ✅ Resposta salva em storage/formularios/respostas/
4. ✅ Admin recebe email (se configurado)
5. ✅ Logs mostram sucesso
```

---

## 🆘 Se Nada Funcionar

### **Criar Versão Mínima:**

**salvar-resposta-simples.php:**
```php
<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Log tudo
    error_log('POST recebido: ' . print_r($_POST, true));
    
    // Apenas salvar arquivo
    $respostaId = 'resp_' . time() . '_' . uniqid();
    $arquivo = __DIR__ . '/../../storage/formularios/respostas/resposta_' . $respostaId . '.json';
    
    $dados = [
        'id' => $respostaId,
        'post' => $_POST,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT));
    
    echo json_encode(['success' => true, 'message' => 'Salvo!', 'file' => $arquivo]);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
```

**Testar com curl:**
```bash
curl -X POST https://djbr.sgqoti.com.br/salvar-resposta-simples.php \
  -d "formulario_id=teste" \
  -d "respostas={}" \
  -d "nome=Teste"
```

---

## 📁 Arquivos Modificados

✅ `src/Controllers/NpsController.php`
- Linha 422-427: Try/catch no envio de email
- Linha 434-441: Logs detalhados de erro

✅ Documentação:
- `CORRECAO_ERRO_NPS_500.md` (este arquivo)

---

## 🎯 Próximos Passos

1. ✅ **Ver logs de erro PHP**
2. ✅ **Responder formulário novamente**
3. ✅ **Copiar erro exato dos logs**
4. ✅ **Enviar erro para análise**

---

**Versão:** 1.0  
**Status:** 🔧 Correção Aplicada  
**Sistema:** SGQ-OTI DJ

**Teste novamente e veja os logs!** 📝
