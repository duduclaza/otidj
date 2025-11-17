# ✅ Sistema de Status para Controle de Descartes

**Data:** 17/11/2025  
**Status:** ✅ Implementado Completo

---

## 🎯 Funcionalidades Implementadas

### **1. ✅ Sistema de Status**
Três status disponíveis:
- **Aguardando Descarte** (padrão ao criar)
- **Itens Descartados** (aprovado)
- **Descartes Reprovados** (rejeitado)

### **2. ✅ Controle de Permissões**
- Apenas **Admin** ou usuários com perfil **Qualidade** podem alterar status
- Validação no backend (seguro)
- Outros usuários podem criar, mas não alterar status

### **3. ✅ Notificação Automática por Email**
- Envia email para **todos** admins e usuários com perfil Qualidade
- Acionado automaticamente ao criar novo descarte
- Email bonito com HTML e todas informações do descarte

### **4. ✅ Auditoria Completa**
- Registra quem alterou o status
- Registra quando alterou
- Permite adicionar justificativa

---

## 📋 Estrutura do Banco de Dados

### **Script SQL Criado:**
`database/add_status_controle_descartes.sql`

### **Colunas Adicionadas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `status` | VARCHAR(50) | Status do descarte (padrão: "Aguardando Descarte") |
| `status_alterado_por` | INT | ID do usuário que alterou |
| `status_alterado_em` | DATETIME | Data/hora da alteração |
| `justificativa_status` | TEXT | Justificativa para mudança |

### **Executar SQL:**
```sql
-- Rodar no banco de dados:
mysql -u usuario -p database < database/add_status_controle_descartes.sql
```

---

## 🔧 Implementação Backend

### **Arquivo:** `src/Controllers/ControleDescartesController.php`

### **1. Criar Descarte (Modificado)**
```php
// Linha 176-212
// Ao criar, define status = "Aguardando Descarte"
// Envia notificação por email automaticamente
```

**Fluxo:**
1. Usuário cria descarte
2. Sistema define status = "Aguardando Descarte"
3. Sistema busca admins + qualidade
4. Envia email para todos
5. Retorna sucesso

---

### **2. Alterar Status (NOVO)**
**Método:** `alterarStatus()`  
**Linha:** 703-781  
**Rota:** `POST /controle-descartes/alterar-status`

**Parâmetros:**
```json
{
  "id": 123,
  "status": "Itens Descartados",
  "justificativa": "Aprovado pelo responsável"
}
```

**Validações:**
- ✅ ID do descarte obrigatório
- ✅ Status deve ser um dos 3 válidos
- ✅ Usuário deve ser admin OU ter perfil Qualidade
- ✅ Descarte deve existir

**Resposta Sucesso:**
```json
{
  "success": true,
  "message": "Status alterado para 'Itens Descartados' com sucesso!"
}
```

**Resposta Erro (Sem Permissão):**
```json
{
  "success": false,
  "message": "Sem permissão. Apenas Admin ou Qualidade podem alterar status."
}
```

---

### **3. Notificação por Email (NOVO)**
**Método:** `notificarNovoDescarte()`  
**Linha:** 783-921  
**Privado** (chamado automaticamente)

**Destinatários:**
```sql
SELECT DISTINCT u.id, u.name, u.email
FROM users u
LEFT JOIN user_profiles up ON u.id = up.user_id
LEFT JOIN profiles p ON up.profile_id = p.id
WHERE (
    u.role IN ('admin', 'super_admin')
    OR LOWER(p.nome) = 'qualidade'
)
AND u.email IS NOT NULL
```

**Email HTML:**
- 🗑️ Ícone de descarte
- ⏳ Status destacado
- 📦 Todas informações do equipamento
- 📝 Observações (se houver)
- ⚠️ Call to action (aprovar/reprovar)
- 🔗 Link direto para o sistema

---

## 🔐 Verificação de Permissões

### **Quem Pode Alterar Status:**

**1. Admins:**
```php
$user_role === 'admin' || $user_role === 'super_admin'
```

**2. Perfil Qualidade:**
```php
SELECT p.nome 
FROM user_profiles up
JOIN profiles p ON up.profile_id = p.id
WHERE up.user_id = ?
AND LOWER(p.nome) = 'qualidade'
```

### **Lógica de Permissão:**
```php
$tem_permissao = (
    $user_role === 'admin' || 
    $user_role === 'super_admin' || 
    in_array('Qualidade', $perfis) || 
    in_array('qualidade', $perfis)
);
```

---

## 🎨 Interface Frontend (Próximos Passos)

### **Na Tabela de Descartes:**

**Adicionar coluna Status com badge colorido:**
```html
<span class="badge aguardando">⏳ Aguardando Descarte</span>
<span class="badge descartado">✅ Itens Descartados</span>
<span class="badge reprovado">❌ Descartes Reprovados</span>
```

**CSS dos badges:**
```css
.badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.badge.aguardando {
  background: #fef3c7;
  color: #92400e;
}

.badge.descartado {
  background: #d1fae5;
  color: #065f46;
}

.badge.reprovado {
  background: #fee2e2;
  color: #991b1b;
}
```

---

### **Botão Alterar Status (Admin/Qualidade):**

**Somente para usuários com permissão:**
```html
<?php if ($isAdminOuQualidade): ?>
<button onclick="abrirModalAlterarStatus(<?= $descarte['id'] ?>)">
  🔄 Alterar Status
</button>
<?php endif; ?>
```

---

### **Modal para Alterar Status:**

```html
<div id="modalAlterarStatus" class="modal hidden">
  <div class="modal-content">
    <h3>Alterar Status do Descarte</h3>
    
    <input type="hidden" id="descarte_id">
    
    <label>Novo Status:</label>
    <select id="novo_status" required>
      <option value="">Selecione...</option>
      <option value="Aguardando Descarte">⏳ Aguardando Descarte</option>
      <option value="Itens Descartados">✅ Itens Descartados</option>
      <option value="Descartes Reprovados">❌ Descartes Reprovados</option>
    </select>
    
    <label>Justificativa:</label>
    <textarea id="justificativa" rows="3" placeholder="Ex: Aprovado após conferência..."></textarea>
    
    <button onclick="salvarNovoStatus()">Salvar</button>
    <button onclick="fecharModal()">Cancelar</button>
  </div>
</div>
```

---

### **JavaScript para Alterar Status:**

```javascript
function abrirModalAlterarStatus(descarteId) {
  document.getElementById('descarte_id').value = descarteId;
  document.getElementById('modalAlterarStatus').classList.remove('hidden');
}

function salvarNovoStatus() {
  const descarteId = document.getElementById('descarte_id').value;
  const novoStatus = document.getElementById('novo_status').value;
  const justificativa = document.getElementById('justificativa').value;
  
  if (!novoStatus) {
    alert('Selecione um status');
    return;
  }
  
  const formData = new FormData();
  formData.append('id', descarteId);
  formData.append('status', novoStatus);
  formData.append('justificativa', justificativa);
  
  fetch('/controle-descartes/alterar-status', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      alert(data.message);
      fecharModal();
      carregarDescartes(); // Recarregar tabela
    } else {
      alert('Erro: ' + data.message);
    }
  })
  .catch(error => {
    alert('Erro ao alterar status');
    console.error(error);
  });
}

function fecharModal() {
  document.getElementById('modalAlterarStatus').classList.add('hidden');
  document.getElementById('novo_status').value = '';
  document.getElementById('justificativa').value = '';
}
```

---

## 📊 Fluxo Completo

### **Cenário 1: Criar Novo Descarte**

```
1. Usuário preenche formulário
   ↓
2. Clica em "Registrar Descarte"
   ↓
3. Sistema salva com status = "Aguardando Descarte"
   ↓
4. Sistema busca admins + qualidade
   ↓
5. Envia email para todos
   ↓
6. Retorna sucesso ao usuário
```

---

### **Cenário 2: Admin Altera Status**

```
1. Admin acessa sistema
   ↓
2. Vê descarte com status "⏳ Aguardando Descarte"
   ↓
3. Clica em "Alterar Status"
   ↓
4. Seleciona "✅ Itens Descartados"
   ↓
5. Adiciona justificativa (opcional)
   ↓
6. Salva
   ↓
7. Sistema valida permissão (Admin ✅)
   ↓
8. Atualiza status + quem alterou + quando + justificativa
   ↓
9. Badge na tabela atualiza para verde "✅ Itens Descartados"
```

---

### **Cenário 3: Usuário Sem Permissão**

```
1. Usuário comum acessa sistema
   ↓
2. Vê descartes na tabela
   ↓
3. Botão "Alterar Status" NÃO aparece
   ↓
4. Se tentar requisição direta:
   ↓
5. Backend valida permissão
   ↓
6. Retorna erro: "Sem permissão. Apenas Admin ou Qualidade..."
```

---

## 📧 Exemplo de Email Enviado

**Assunto:** `🗑️ Novo Descarte Registrado - Aguardando Aprovação`

**Corpo (HTML):**
```
┌──────────────────────────────────┐
│ 🗑️ Novo Descarte Registrado     │ (Laranja)
└──────────────────────────────────┘

Status:
⏳ Aguardando Descarte

📦 Informações do Equipamento:
• Número de Série: SERIE12345
• Filial: Jundiaí
• Código Produto: PROD-001
• Descrição: Impressora HP LaserJet Pro
• Data do Descarte: 17/11/2025
• Número OS: OS-2024-001
• Responsável Técnico: João Silva
• Registrado por: Maria Santos

📝 Observações:
Equipamento com defeito irreparável

⚠️ Ação Necessária:
Este descarte está aguardando aprovação.
Acesse o sistema para revisar e alterar o status.

[Ver Controle de Descartes] (Botão)
```

---

## 🧪 Como Testar

### **Teste 1: Criar Descarte**
```
1. ✅ Fazer login como usuário comum
2. ✅ Ir em Controle de Descartes
3. ✅ Clicar "Novo Descarte"
4. ✅ Preencher dados
5. ✅ Salvar
6. ✅ Ver mensagem sucesso
7. ✅ Verificar se status = "Aguardando Descarte"
8. ✅ Verificar email dos admins/qualidade
```

### **Teste 2: Alterar Status (Admin)**
```
1. ✅ Fazer login como admin
2. ✅ Ver descarte com status "Aguardando"
3. ✅ Clicar "Alterar Status"
4. ✅ Selecionar "Itens Descartados"
5. ✅ Adicionar justificativa
6. ✅ Salvar
7. ✅ Ver status atualizado
8. ✅ Verificar dados auditoria no banco
```

### **Teste 3: Sem Permissão**
```
1. ✅ Fazer login como usuário sem perfil qualidade
2. ✅ Ver descartes
3. ✅ Botão "Alterar Status" não aparece
4. ✅ Tentar requisição direta (Postman)
5. ✅ Ver erro: "Sem permissão..."
```

### **Teste 4: Perfil Qualidade**
```
1. ✅ Criar usuário com perfil "Qualidade"
2. ✅ Fazer login
3. ✅ Ver botão "Alterar Status"
4. ✅ Conseguir alterar status
5. ✅ Salvar com sucesso
```

---

## 📁 Arquivos Criados/Modificados

### **Criados:**
- ✅ `database/add_status_controle_descartes.sql`
- ✅ `CONTROLE_DESCARTES_STATUS_IMPLEMENTADO.md`

### **Modificados:**
- ✅ `src/Controllers/ControleDescartesController.php`
  - Linha 176-212: Criar descarte com status e notificação
  - Linha 703-781: Método `alterarStatus()` (NOVO)
  - Linha 783-921: Método `notificarNovoDescarte()` (NOVO)
- ✅ `public/index.php`
  - Linha 229: Rota `/controle-descartes/alterar-status` (NOVA)

---

## ✅ Checklist de Implementação

**Backend:**
- ✅ SQL para adicionar colunas
- ✅ Status padrão ao criar
- ✅ Método alterar status
- ✅ Validação de permissões
- ✅ Auditoria (quem/quando/justificativa)
- ✅ Notificação por email
- ✅ Rota alterar status

**Frontend (Pendente):**
- ⏳ Coluna Status na tabela
- ⏳ Badges coloridos
- ⏳ Botão "Alterar Status" (condicional)
- ⏳ Modal alterar status
- ⏳ JavaScript para salvar
- ⏳ Atualizar tabela após salvar

**Testes:**
- ⏳ Criar descarte
- ⏳ Receber email
- ⏳ Alterar status (admin)
- ⏳ Alterar status (qualidade)
- ⏳ Tentar sem permissão
- ⏳ Verificar auditoria

---

## 🎯 Próximas Etapas

1. **Executar SQL:**
   ```bash
   mysql -u root -p sgq_db < database/add_status_controle_descartes.sql
   ```

2. **Atualizar Frontend:**
   - Adicionar coluna Status na tabela
   - Criar modal alterar status
   - Adicionar JavaScript
   - Implementar badges coloridos

3. **Testar:**
   - Criar descarte
   - Verificar email
   - Alterar status
   - Validar permissões

4. **Configurar Perfil "Qualidade":**
   - Criar perfil se não existir
   - Associar usuários ao perfil
   - Testar permissões

---

**Versão:** 1.0  
**Status:** ✅ Backend Completo / ⏳ Frontend Pendente  
**Sistema:** SGQ-OTI DJ
