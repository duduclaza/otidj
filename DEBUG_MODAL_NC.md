# 🔧 Debug: Botão "Nova NC" Não Funciona

**Data:** 17/11/2025  
**Problema:** Ao clicar em "Nova NC" nada acontece

---

## 🔍 Diagnóstico Rápido

### **Passo 1: Abrir Console do Navegador**
```
Windows: F12 ou Ctrl+Shift+I
Mac: Cmd+Option+I
```

### **Passo 2: Ir para Aba "Console"**
- Procure por erros em vermelho ❌
- Procure por avisos em amarelo ⚠️

### **Passo 3: Clicar em "Nova NC"**
- Ver o que aparece no console
- Deve mostrar: `🔴 Função abrirModalNovaNC chamada!`

---

## 🐛 Possíveis Problemas

### **Problema 1: Erro JavaScript**

**Sintomas:**
```
❌ Uncaught ReferenceError: abrirModalNovaNC is not defined
❌ Cannot read property 'classList' of null
```

**Causa:** Script não carregou ou modal não existe

**Solução:**
```
✅ Verificar se scripts.php foi incluído
✅ Verificar se modais.php foi incluído
✅ Recarregar página (Ctrl+F5)
```

---

### **Problema 2: Modal Não Encontrado**

**Console mostra:**
```
❌ Modal não encontrado! ID: modalNovaNC
```

**Causa:** HTML do modal não está na página

**Solução:**
```php
// Verificar se está incluído em index.php:
<?php include 'partials/modais.php'; ?>
```

---

### **Problema 3: Botão Sem Onclick**

**HTML do botão deve ser:**
```html
<button onclick="abrirModalNovaNC()" class="px-4 py-2 bg-red-600...">
  Nova NC
</button>
```

**Verificar:**
- Clicar com botão direito no botão
- Inspecionar elemento
- Ver se tem `onclick="abrirModalNovaNC()"`

---

### **Problema 4: JavaScript Bloqueado**

**Sintomas:**
- Nenhuma mensagem no console
- Nada acontece ao clicar

**Verificar:**
```
✅ Extensões do navegador (AdBlock, etc)
✅ Modo de navegação anônima
✅ Configurações de segurança
```

---

## ✅ Teste Passo a Passo

### **Teste 1: Console**
```javascript
// Digitar no console:
abrirModalNovaNC();

// Deve abrir o modal
// Se der erro, função não existe
```

### **Teste 2: Verificar Modal**
```javascript
// Digitar no console:
document.getElementById('modalNovaNC');

// Deve retornar: <div id="modalNovaNC"...>
// Se retornar null, modal não existe
```

### **Teste 3: Verificar Botão**
```javascript
// Digitar no console:
document.querySelector('[onclick*="abrirModalNovaNC"]');

// Deve retornar o botão
// Se retornar null, botão não tem onclick
```

---

## 🔧 Correções Aplicadas

### **1. ✅ Event Listeners no DOMContentLoaded**
```javascript
// ANTES:
document.getElementById('formNovaNC').addEventListener...
// ❌ Executava antes do DOM carregar

// DEPOIS:
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('formNovaNC').addEventListener...
});
// ✅ Aguarda DOM carregar
```

### **2. ✅ Logs de Debug**
```javascript
function abrirModalNovaNC() {
  console.log('🔴 Função chamada!');
  console.log('Modal encontrado:', modal);
  // Mostra exatamente onde está o problema
}
```

### **3. ✅ Verificação de Existência**
```javascript
if (!modal) {
  console.error('❌ Modal não encontrado!');
  alert('Erro: Modal não encontrado');
  return;
}
// Previne erros silenciosos
```

---

## 📋 Checklist

```
□ Console aberto (F12)
□ Sem erros em vermelho
□ Clicar em "Nova NC"
□ Ver mensagem: "🔴 Função abrirModalNovaNC chamada!"
□ Ver mensagem: "Modal encontrado: <div..."
□ Ver mensagem: "✅ Modal aberto com sucesso!"
□ Modal aparece na tela
□ Fundo escurece
□ Formulário visível
```

---

## 🎯 Solução Rápida

### **Se nada funcionar:**

**1. Limpar Cache:**
```
Ctrl+Shift+Delete (Chrome/Edge)
Cmd+Shift+Delete (Mac)
Limpar cache e cookies
```

**2. Hard Reload:**
```
Ctrl+F5 (Windows)
Cmd+Shift+R (Mac)
```

**3. Verificar Arquivos:**
```
✅ views/pages/nao-conformidades/index.php
✅ views/pages/nao-conformidades/partials/modais.php
✅ views/pages/nao-conformidades/partials/scripts.php
```

**4. Verificar Includes:**
```php
// No final de index.php:
<?php include 'partials/modais.php'; ?>
<?php include 'partials/scripts.php'; ?>
```

---

## 🆘 Comandos de Emergência

### **No Console do Navegador:**

**Verificar tudo:**
```javascript
// 1. Função existe?
typeof abrirModalNovaNC
// Deve retornar: "function"

// 2. Modal existe?
!!document.getElementById('modalNovaNC')
// Deve retornar: true

// 3. Botão existe?
!!document.querySelector('[onclick*="abrirModalNovaNC"]')
// Deve retornar: true

// 4. Abrir manualmente:
abrirModalNovaNC()
// Modal deve abrir
```

---

## 📸 O Que Deve Acontecer

### **Ao Clicar "Nova NC":**

**Console:**
```
🔴 Função abrirModalNovaNC chamada!
Modal encontrado: <div id="modalNovaNC" class="modal-overlay hidden">...</div>
✅ Modal aberto com sucesso!
```

**Tela:**
```
1. Fundo escurece (preto 75%)
2. Modal aparece centralizado
3. Formulário visível com:
   - Título *
   - Descrição *
   - Responsável * (lista de usuários)
   - Evidências (upload)
   - Botões: "Criar NC" e "Cancelar"
```

---

## ✅ Resultado Esperado

**Funcionando:**
- ✅ Clica botão → Console mostra logs
- ✅ Modal aparece centralizado
- ✅ Fundo escuro (75%)
- ✅ Formulário completo
- ✅ Lista de responsáveis carregada
- ✅ Pode fechar com ESC ou clicando fora

---

**Versão:** 1.0  
**Status:** 🔧 Debug Ativo  
**Sistema:** SGQ-OTI DJ

---

## 📞 Próximos Passos

1. ✅ Abrir F12 (console)
2. ✅ Clicar em "Nova NC"
3. ✅ Ver mensagens no console
4. ✅ Copiar erros (se houver)
5. ✅ Seguir soluções acima
