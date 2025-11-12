# 🔧 CORREÇÃO: Função de Edição NPS

**Data:** 12 de novembro de 2025
**Problema:** Botão de editar não funcionava

---

## 🐛 Problemas Identificados e Corrigidos

### 1. **Comparação de Tipos no JavaScript**

**Problema:**
```javascript
// ❌ Comparação falhava se total_respostas viesse como string
${f.total_respostas === 0 ? ... }
```

**Solução:**
```javascript
// ✅ Usa parseInt() para garantir comparação numérica
${parseInt(f.total_respostas) === 0 ? ... }
```

### 2. **Falta de Validação no Backend**

**Problema:**
- Não havia verificação se formulário tinha respostas antes de permitir edição
- Frontend bloqueava, mas backend permitia edição via POST direto

**Solução:**
```php
// Verificar se tem respostas (não pode editar se tiver)
$respostaFiles = glob($this->respostasDir . '/resposta_*.json');
$totalRespostas = 0;
foreach ($respostaFiles as $file) {
    $resposta = json_decode(file_get_contents($file), true);
    if ($resposta['formulario_id'] == $formularioId) {
        $totalRespostas++;
    }
}

if ($totalRespostas > 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Não é possível editar formulário com respostas!'
    ]);
    exit;
}
```

### 3. **Falta de Feedback de Erro**

**Problema:**
- Função `editarFormulario()` não mostrava mensagens de erro
- Difícil diagnosticar problemas

**Solução:**
```javascript
function editarFormulario(id) {
  console.log('Editando formulário:', id);
  
  fetch(`/nps/${id}/detalhes`)
  .then(r => r.json())
  .then(data => {
    console.log('Resposta detalhes:', data);
    
    if (data.success) {
      // ... código de edição
    } else {
      alert('Erro ao carregar formulário: ' + data.message);
    }
  })
  .catch(err => {
    console.error('Erro ao editar:', err);
    alert('Erro de conexão ao carregar formulário');
  });
}
```

---

## ✅ Correções Implementadas

### Arquivo: `src/Controllers/NpsController.php`

**Método `editar()` - Linhas 210-226:**
- ✅ Adicionada validação para bloquear edição se houver respostas
- ✅ Conta total de respostas antes de permitir edição
- ✅ Retorna mensagem clara de erro com quantidade de respostas

### Arquivo: `views/pages/nps/index.php`

**Renderização de Botões - Linhas 239-254:**
```javascript
// ✅ Botão Editar com parseInt()
${parseInt(f.total_respostas) === 0 ? `
  <button onclick="editarFormulario('${f.id}')" class="p-2 text-blue-600">
    <!-- Ícone editar -->
  </button>
` : `
  <button class="p-2 text-gray-300 cursor-not-allowed" disabled>
    <!-- Ícone cadeado -->
  </button>
`}

// ✅ Botão Excluir com parseInt()
${parseInt(f.total_respostas) === 0 ? ... }
```

**Função editarFormulario() - Linhas 401-435:**
- ✅ Adicionados `console.log()` para debug
- ✅ Tratamento de erro com `.catch()`
- ✅ Mensagens de alerta descritivas
- ✅ Log de resposta da API

---

## 🧪 Como Testar

### Teste 1: Editar Formulário SEM Respostas

1. **Acessar:** `/nps`
2. **Identificar:** Formulário com **0 respostas**
3. **Verificar:** Botão de editar deve estar **azul** ✏️
4. **Clicar:** No botão de editar
5. **Resultado Esperado:**
   - Modal abre com dados do formulário
   - Título, descrição e perguntas carregados
   - Console mostra: `"Editando formulário: form_xxx"`
   - Console mostra: `"Resposta detalhes: {success: true, ...}"`

### Teste 2: Editar Formulário COM Respostas

1. **Acessar:** `/nps`
2. **Identificar:** Formulário com **≥1 resposta**
3. **Verificar:** Botão de editar deve estar **cinza com cadeado** 🔒
4. **Tooltip:** "🔒 Não é possível editar formulário com respostas (X respostas)"
5. **Clicar:** Botão está **desabilitado** (nada acontece)

### Teste 3: Salvar Edição

1. **Editar** formulário sem respostas
2. **Modificar:** Título, descrição ou perguntas
3. **Salvar**
4. **Resultado Esperado:**
   - Mensagem: "Formulário atualizado com sucesso!"
   - Modal fecha
   - Lista recarrega com alterações

### Teste 4: Tentar Editar Formulário com Respostas (Backend)

Se alguém tentar burlar o frontend:

1. **Criar** formulário
2. **Responder** 1 vez
3. **Tentar editar** via POST direto
4. **Resultado:**
   ```json
   {
     "success": false,
     "message": "Não é possível editar formulário com respostas! Total de respostas: 1"
   }
   ```

---

## 🔍 Debug via Console do Navegador

### Abrir Console

**Chrome/Edge:**
- `F12` ou `Ctrl+Shift+I`
- Aba "Console"

**Firefox:**
- `F12` ou `Ctrl+Shift+K`
- Aba "Console"

### Logs Esperados

**Ao clicar em Editar:**
```
Editando formulário: form_1731418800_abc123
Resposta detalhes: {success: true, formulario: {...}}
```

**Se houver erro:**
```
Erro ao editar: TypeError: ...
```

### Verificar Dados

**No console, digite:**
```javascript
// Ver todos os formulários carregados
fetch('/nps/listar')
  .then(r => r.json())
  .then(d => console.table(d.formularios))

// Ver detalhes de um formulário específico
fetch('/nps/form_1731418800_abc123/detalhes')
  .then(r => r.json())
  .then(d => console.log(d))
```

---

## 📊 Arquivos Modificados

| Arquivo | Linhas | Modificação |
|---------|--------|-------------|
| `src/Controllers/NpsController.php` | 210-226 | Validação backend de edição |
| `views/pages/nps/index.php` | 239-254 | parseInt() nos botões |
| `views/pages/nps/index.php` | 401-435 | Logs e tratamento de erro |

---

## 🎯 Comportamento Final

### Formulário SEM Respostas (0)
```
🔓/🔒 (cadeado) | ✏️ (editar azul) | 🗑️ (excluir vermelho)
```
- ✅ Pode abrir/fechar
- ✅ Pode editar (clica → modal abre)
- ✅ Pode excluir

### Formulário COM Respostas (≥1)
```
🔓/🔒 (cadeado) | 🔒 (cadeado cinza) | 🔒 (cadeado cinza)
```
- ✅ Pode abrir/fechar
- ❌ NÃO pode editar (botão desabilitado)
- ❌ NÃO pode excluir (botão desabilitado)

---

## 🚨 Se Ainda Não Funcionar

### Passo 1: Limpar Cache do Navegador
```
Ctrl + Shift + Delete
→ Marcar "Arquivos em cache"
→ "Limpar dados"
```

### Passo 2: Recarregar Página
```
Ctrl + F5 (hard reload)
```

### Passo 3: Verificar Console
```
F12 → Console
Ver se há erros em vermelho
```

### Passo 4: Testar Endpoint Direto
```
Abrir navegador:
https://djbr.sgqoti.com.br/nps/listar

Deve retornar JSON com formulários
```

### Passo 5: Verificar Permissões
```
- Usuário está logado?
- Formulário pertence ao usuário?
- Role do usuário tem permissão?
```

---

## 📝 Arquivo de Teste Criado

**`public/test-nps-debug.php`**

Para executar:
```
https://djbr.sgqoti.com.br/test-nps-debug.php
```

Mostra:
- Session do usuário
- Resposta do endpoint `/nps/listar`
- Dados do primeiro formulário
- Verificações de tipo e comparação

---

## ✅ Status

**Correções:** ✅ IMPLEMENTADAS

**Arquivos Modificados:** 2 arquivos

**Próximo Passo:** Testar no navegador e verificar console para confirmar funcionamento

---

**Se encontrar qualquer problema, verifique o Console do navegador (F12) e compartilhe os logs/erros!** 🔍
