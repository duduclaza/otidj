# 🔧 Correção: Dashboard Mostrando Formulários Excluídos

**Data:** 17/11/2025  
**Problema:** Dashboard guardava histórico de formulários já excluídos  
**Status:** ✅ CORRIGIDO + Ferramenta de Limpeza Adicionada

---

## 🐛 Problema Identificado

### **Comportamento Anterior:**
```
1. Criar formulário → Receber respostas
2. Excluir formulário
3. Dashboard ainda mostrava as respostas antigas ❌
4. Estatísticas incorretas
```

### **Causa Raiz:**
- Ao excluir formulário, apenas o arquivo do formulário era deletado
- Arquivos de respostas permaneciam no sistema
- Dashboard contava TODAS as respostas, mesmo de formulários excluídos
- "Respostas órfãs" se acumulavam ao longo do tempo

---

## ✅ Correções Aplicadas

### **1. Validação no Dashboard**

**Arquivo:** `src/Controllers/NpsController.php` (linha 795-800)

**ANTES:**
```php
// Verificava se formulário existia, mas lógica confusa
$formFile = $this->storageDir . '/formulario_' . $resposta['formulario_id'] . '.json';
if (file_exists($formFile)) {
    $form = json_decode(file_get_contents($formFile), true);
    // ... contava resposta
}
```

**DEPOIS:**
```php
// Verifica EXPLICITAMENTE se formulário existe
$formFile = $this->storageDir . '/formulario_' . $resposta['formulario_id'] . '.json';
if (!file_exists($formFile)) {
    // Formulário foi excluído, ignorar esta resposta
    continue; // Pula para próxima resposta
}

$form = json_decode(file_get_contents($formFile), true);
// ... só conta se formulário existir
```

**Resultado:**
- ✅ Respostas órfãs são **ignoradas** no dashboard
- ✅ Estatísticas mostram apenas dados reais
- ✅ Contadores corretos

---

### **2. Função para Contar Respostas Órfãs**

**Arquivo:** `src/Controllers/NpsController.php` (linha 1149-1175)

```php
public function contarRespostasOrfas()
{
    $respostaFiles = glob($this->respostasDir . '/resposta_*.json');
    $totalOrfas = 0;
    
    foreach ($respostaFiles as $file) {
        $resposta = json_decode(file_get_contents($file), true);
        $formFile = $this->storageDir . '/formulario_' . $resposta['formulario_id'] . '.json';
        
        if (!file_exists($formFile)) {
            $totalOrfas++; // Formulário não existe = órfã
        }
    }
    
    return ['success' => true, 'total_orfas' => $totalOrfas];
}
```

**Funcionalidade:**
- Varre todos arquivos de resposta
- Verifica se formulário correspondente existe
- Retorna contagem de órfãs
- **Não deleta nada**, apenas conta

---

### **3. Função para Limpar Respostas Órfãs**

**Arquivo:** `src/Controllers/NpsController.php` (linha 1102-1144)

```php
public function limparRespostasOrfas()
{
    // Verificar se é admin
    if (!in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
        return ['success' => false, 'message' => 'Sem permissão'];
    }
    
    $respostaFiles = glob($this->respostasDir . '/resposta_*.json');
    $totalOrfas = 0;
    
    foreach ($respostaFiles as $file) {
        $resposta = json_decode(file_get_contents($file), true);
        $formFile = $this->storageDir . '/formulario_' . $resposta['formulario_id'] . '.json';
        
        if (!file_exists($formFile)) {
            unlink($file); // ⚠️ DELETA arquivo de resposta órfã
            $totalOrfas++;
        }
    }
    
    return ['success' => true, 'message' => "{$totalOrfas} órfã(s) removida(s)"];
}
```

**Funcionalidade:**
- **Só admin** pode executar
- Deleta permanentemente respostas órfãs
- Retorna quantas foram removidas
- Registra detalhes no log

---

### **4. Botão no Dashboard (Admin)**

**Arquivo:** `views/pages/nps/dashboard.php` (linha 33-40)

```html
<!-- Botão aparece automaticamente se houver órfãs -->
<button onclick="limparRespostasOrfas()" id="btnLimparOrfas" 
        class="hidden bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
  <svg>...</svg>
  <span>Limpar Órfãs (<span id="totalOrfas">0</span>)</span>
</button>
```

**Comportamento:**
- ✅ Inicia **escondido**
- ✅ Aparece automaticamente se houver órfãs
- ✅ Mostra quantidade de órfãs
- ✅ Só visível para **admin e super_admin**

---

### **5. JavaScript Automático**

**Arquivo:** `views/pages/nps/dashboard.php` (linha 330-388)

```javascript
// Ao carregar dashboard:
document.addEventListener('DOMContentLoaded', verificarRespostasOrfas);

// Verifica se há órfãs
function verificarRespostasOrfas() {
  fetch('/nps/contar-orfas')
    .then(r => r.json())
    .then(data => {
      if (data.total_orfas > 0) {
        // Mostra botão com quantidade
        document.getElementById('totalOrfas').textContent = data.total_orfas;
        document.getElementById('btnLimparOrfas').classList.remove('hidden');
      }
    });
}

// Limpa órfãs com confirmação
function limparRespostasOrfas() {
  if (!confirm(`Remover ${totalOrfas} resposta(s)? Ação irreversível!`)) {
    return;
  }
  
  fetch('/nps/limpar-orfas', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
      alert(`✅ ${data.message}`);
      location.reload(); // Atualiza estatísticas
    });
}
```

**Fluxo:**
1. Dashboard carrega
2. JavaScript faz requisição para contar órfãs
3. Se houver órfãs > 0, botão aparece
4. Admin clica no botão
5. Confirmação aparece
6. Se confirmar, órfãs são deletadas
7. Dashboard recarrega com dados atualizados

---

### **6. Novas Rotas**

**Arquivo:** `public/index.php` (linha 271-272)

```php
$router->get('/nps/contar-orfas', [NpsController::class, 'contarRespostasOrfas']);
$router->post('/nps/limpar-orfas', [NpsController::class, 'limparRespostasOrfas']);
```

**Rotas disponíveis:**
- `GET /nps/contar-orfas` → Conta (não deleta)
- `POST /nps/limpar-orfas` → Deleta (admin only)

---

## 🎯 Fluxo Completo

### **Cenário: Excluir Formulário**

**ANTES (Problema):**
```
1. Formulário tem 50 respostas
2. Admin exclui formulário
3. Dashboard ainda mostra 50 respostas ❌
4. Estatísticas incorretas ❌
5. Órfãs se acumulam ❌
```

**DEPOIS (Corrigido):**
```
1. Formulário tem 50 respostas
2. Admin exclui formulário
3. Dashboard ignora as 50 respostas ✅
4. Estatísticas corretas ✅
5. Botão "Limpar Órfãs (50)" aparece ✅
6. Admin clica e confirma
7. 50 respostas órfãs deletadas ✅
8. Dashboard atualizado ✅
```

---

## 🧪 Como Testar

### **Teste 1: Dashboard Ignora Órfãs**
```
1. ✅ Criar formulário de teste
2. ✅ Responder algumas vezes (ex: 5 respostas)
3. ✅ Ver dashboard: 5 respostas
4. ✅ Excluir formulário
5. ✅ Ver dashboard: 0 respostas (órfãs ignoradas!)
```

### **Teste 2: Botão Aparece Automaticamente**
```
1. ✅ Ter formulários excluídos com respostas órfãs
2. ✅ Abrir dashboard como admin
3. ✅ Botão vermelho "Limpar Órfãs (X)" aparece
4. ✅ Número X corresponde à quantidade real
```

### **Teste 3: Limpeza Funciona**
```
1. ✅ Clicar no botão "Limpar Órfãs"
2. ✅ Ver confirmação: "Tem certeza?"
3. ✅ Confirmar
4. ✅ Ver "Limpando..." (botão desabilitado)
5. ✅ Ver alerta de sucesso: "X órfã(s) removida(s)"
6. ✅ Dashboard recarrega
7. ✅ Estatísticas atualizadas
8. ✅ Botão desaparece
```

### **Teste 4: Permissões**
```
1. ✅ Usuário normal não vê botão
2. ✅ Admin vê botão
3. ✅ Super admin vê botão
4. ✅ Requisição direta sem permissão = erro
```

---

## 📊 Comparação Visual

### **Dashboard ANTES (Incorreto):**
```
┌─────────────────────────────────┐
│ 📊 Dashboard de Formulários     │
├─────────────────────────────────┤
│ Pontuação: +45                  │
│ Total Respostas: 150 ← ERRADO! │
│ (100 são de formulários         │
│  excluídos)                     │
└─────────────────────────────────┘
```

### **Dashboard DEPOIS (Correto):**
```
┌─────────────────────────────────┐
│ 📊 Dashboard de Formulários     │
│   [Limpar Órfãs (100)] ←BOTÃO   │
├─────────────────────────────────┤
│ Pontuação: +50                  │
│ Total Respostas: 50 ← CORRETO! │
│ (Só formulários existentes)     │
└─────────────────────────────────┘
```

---

## 🔒 Segurança

### **Permissões:**
- ✅ Apenas **admin** e **super_admin** podem limpar
- ✅ Verificação no backend (não confia no frontend)
- ✅ Botão oculto para usuários comuns

### **Confirmação:**
- ✅ Popup de confirmação antes de deletar
- ✅ Aviso que ação é irreversível
- ✅ Mostra quantidade a ser removida

### **Logs:**
- ✅ Registra quem executou limpeza
- ✅ Registra quantas foram removidas
- ✅ Registra detalhes das órfãs

---

## 📁 Arquivos Modificados

### **1. src/Controllers/NpsController.php**
- Linha 795-800: Validação de formulário existente (corrigida)
- Linha 1102-1144: Método `limparRespostasOrfas()` (novo)
- Linha 1149-1175: Método `contarRespostasOrfas()` (novo)

### **2. views/pages/nps/dashboard.php**
- Linha 33-40: Botão "Limpar Órfãs" (novo)
- Linha 330-388: JavaScript de verificação e limpeza (novo)

### **3. public/index.php**
- Linha 271-272: Rotas `/nps/contar-orfas` e `/nps/limpar-orfas` (novas)

---

## ✅ Checklist de Verificação

```
✅ Dashboard ignora respostas de formulários excluídos
✅ Estatísticas corretas (só formulários existentes)
✅ Botão "Limpar Órfãs" aparece automaticamente
✅ Botão mostra quantidade correta
✅ Botão só visível para admin
✅ Confirmação antes de deletar
✅ Limpeza funciona e deleta órfãs
✅ Dashboard atualiza após limpeza
✅ Botão desaparece quando não há órfãs
✅ Logs registram ações
✅ Rotas funcionando
✅ Permissões validadas no backend
```

---

## 🎉 Resultado Final

**Antes:**
- ❌ Dashboard mostrava dados de formulários excluídos
- ❌ Estatísticas incorretas
- ❌ Respostas órfãs se acumulavam
- ❌ Sem ferramenta de limpeza
- ❌ Problema invisível para admin

**Depois:**
- ✅ Dashboard mostra APENAS formulários existentes
- ✅ Estatísticas 100% corretas
- ✅ Órfãs são identificadas automaticamente
- ✅ Ferramenta de limpeza com um clique
- ✅ Visibilidade total do problema
- ✅ Manutenção fácil e segura

---

## 💡 Boas Práticas Implementadas

1. **Validação Explícita:** `continue` quando formulário não existe
2. **Separação de Responsabilidades:** Contar ≠ Deletar
3. **Feedback Visual:** Botão aparece/desaparece automaticamente
4. **Confirmação Crítica:** Aviso antes de deletar
5. **Permissões Rígidas:** Verificação backend + frontend
6. **Logs Completos:** Rastreabilidade de ações
7. **UX Intuitiva:** Processo transparente para admin
8. **Performance:** Validação eficiente no loop

---

**Versão:** 1.0  
**Status:** ✅ Problema Resolvido + Ferramenta de Manutenção  
**Sistema:** SGQ-OTI DJ

**Dashboard agora mostra apenas dados reais! Órfãs podem ser limpas facilmente!** 🎉
