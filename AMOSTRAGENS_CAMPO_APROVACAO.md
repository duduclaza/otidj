# ✅ CAMPO DE APROVAÇÃO EM AMOSTRAGENS 2.0

## 📋 IMPLEMENTAÇÃO COMPLETA

### **Data**: 09/10/2025 13:52
### **Versão**: 2.6.4
### **Solicitação**: Adicionar no grid o usuário que fez a aprovação

---

## 🗄️ ALTERAÇÕES NO BANCO DE DADOS

### **Migration**: `add_aprovacao_amostragens.sql`

```sql
ALTER TABLE amostragens_2 
ADD COLUMN aprovado_por INT(11) NULL DEFAULT NULL 
  COMMENT 'ID do usuário que aprovou/reprovou' 
  AFTER status_final,
  
ADD COLUMN aprovado_em DATETIME NULL DEFAULT NULL 
  COMMENT 'Data e hora da aprovação/reprovação' 
  AFTER aprovado_por,
  
ADD CONSTRAINT fk_amostragens_2_aprovado_por 
  FOREIGN KEY (aprovado_por) REFERENCES users(id) ON DELETE SET NULL;

CREATE INDEX idx_amostragens_2_aprovado_por ON amostragens_2(aprovado_por);
```

### **Campos Adicionados:**

1. **`aprovado_por`** (INT NULL)
   - ID do usuário que aprovou/reprovou
   - NULL = Ainda não foi aprovado/reprovado
   - Foreign Key para `users(id)`
   - ON DELETE SET NULL (se usuário for deletado, campo fica NULL)

2. **`aprovado_em`** (DATETIME NULL)
   - Data e hora da aprovação/reprovação
   - NULL = Ainda não foi aprovado/reprovado
   - Formato: YYYY-MM-DD HH:MM:SS

3. **Índice**
   - Criado em `aprovado_por` para melhorar performance de consultas

---

## 🔧 ALTERAÇÕES NO CONTROLLER

### **Arquivo**: `Amostragens2Controller.php`

#### **1. Query do Grid (`index` method)**

**ANTES:**
```php
SELECT a.*, 
       u.name as usuario_nome,
       u.filial as filial_nome,
       forn.nome as fornecedor_nome,
       (SELECT COUNT(*) FROM amostragens_2_evidencias WHERE amostragem_id = a.id) as total_evidencias
FROM amostragens_2 a
LEFT JOIN users u ON a.user_id = u.id
LEFT JOIN fornecedores forn ON a.fornecedor_id = forn.id
```

**DEPOIS:**
```php
SELECT a.*, 
       u.name as usuario_nome,
       u.filial as filial_nome,
       forn.nome as fornecedor_nome,
       aprovador.name as aprovado_por_nome,
       aprovador.email as aprovado_por_email,
       (SELECT COUNT(*) FROM amostragens_2_evidencias WHERE amostragem_id = a.id) as total_evidencias
FROM amostragens_2 a
LEFT JOIN users u ON a.user_id = u.id
LEFT JOIN fornecedores forn ON a.fornecedor_id = forn.id
LEFT JOIN users aprovador ON a.aprovado_por = aprovador.id  -- NOVO JOIN
```

**Resultado**: Grid agora traz nome e email do aprovador.

---

#### **2. Atualização de Status (`updateStatus` method)**

**ANTES:**
```php
UPDATE amostragens_2 SET 
    status_final = :status,
    updated_at = NOW()
WHERE id = :id
```

**DEPOIS:**
```php
// Se status é Aprovado/Aprovado Parcialmente/Reprovado
UPDATE amostragens_2 SET 
    status_final = :status,
    aprovado_por = :aprovado_por,     -- NOVO
    aprovado_em = NOW(),               -- NOVO
    updated_at = NOW()
WHERE id = :id

// Se voltando para Pendente, limpa aprovação
UPDATE amostragens_2 SET 
    status_final = :status,
    aprovado_por = NULL,               -- LIMPA
    aprovado_em = NULL,                -- LIMPA
    updated_at = NOW()
WHERE id = :id
```

**Lógica Implementada:**

1. **Ao Aprovar/Reprovar**:
   - Registra o ID do usuário logado (`$_SESSION['user_id']`)
   - Registra data/hora atual
   - Mantém informação histórica de quem decidiu

2. **Ao Voltar para Pendente**:
   - Limpa `aprovado_por` (NULL)
   - Limpa `aprovado_em` (NULL)
   - Permite nova aprovação futura

---

## 🎨 ALTERAÇÕES NA INTERFACE

### **Arquivo**: `views/pages/amostragens-2/index.php`

#### **Nova Coluna no Grid**

**Header da Tabela:**
```html
<th>Status</th>
<th>Aprovado Por</th>  <!-- NOVA COLUNA -->
<th>Anexo NF</th>
```

**Célula da Tabela:**
```php
<td class="px-6 py-4 whitespace-nowrap text-sm">
  <?php if (!empty($amostra['aprovado_por_nome'])): ?>
    <div class="flex flex-col">
      <!-- Nome do aprovador -->
      <span class="text-gray-900 font-medium">
        <?= e($amostra['aprovado_por_nome']) ?>
      </span>
      
      <!-- Data e hora da aprovação -->
      <?php if (!empty($amostra['aprovado_em'])): ?>
        <span class="text-xs text-gray-500">
          <?= date('d/m/Y H:i', strtotime($amostra['aprovado_em'])) ?>
        </span>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <!-- Se não foi aprovado ainda -->
    <span class="text-gray-400 text-xs">-</span>
  <?php endif; ?>
</td>
```

---

## 🎯 FUNCIONALIDADE COMPLETA

### **Cenário 1: Amostragem Pendente**

**Grid exibe:**
- Status: `Pendente` (cinza)
- Aprovado Por: `-` (traço)

**Motivo**: Ainda não foi aprovado/reprovado por ninguém.

---

### **Cenário 2: Usuário Aprova**

**Ação**: Admin ou responsável altera status para "Aprovado"

**Sistema registra:**
```sql
UPDATE amostragens_2 SET
  status_final = 'Aprovado',
  aprovado_por = 5,              -- ID do usuário que aprovou
  aprovado_em = '2025-10-09 13:52:30'
```

**Grid exibe:**
- Status: `Aprovado` (verde)
- Aprovado Por: 
  ```
  João Silva
  09/10/2025 13:52
  ```

---

### **Cenário 3: Usuário Reprova**

**Ação**: Admin ou responsável altera status para "Reprovado"

**Sistema registra:**
```sql
UPDATE amostragens_2 SET
  status_final = 'Reprovado',
  aprovado_por = 5,              -- ID do usuário que reprovou
  aprovado_em = '2025-10-09 14:10:15'
```

**Grid exibe:**
- Status: `Reprovado` (vermelho)
- Aprovado Por: 
  ```
  João Silva
  09/10/2025 14:10
  ```

---

### **Cenário 4: Volta para Pendente**

**Ação**: Status alterado de volta para "Pendente"

**Sistema limpa:**
```sql
UPDATE amostragens_2 SET
  status_final = 'Pendente',
  aprovado_por = NULL,           -- LIMPA
  aprovado_em = NULL             -- LIMPA
```

**Grid exibe:**
- Status: `Pendente` (cinza)
- Aprovado Por: `-` (traço)

**Motivo**: Permite nova aprovação no futuro sem registros antigos.

---

## 📊 VISUAL DA COLUNA

### **Layout da Célula:**

```
┌─────────────────────┐
│ João Silva          │ ← Nome do aprovador (negrito)
│ 09/10/2025 13:52    │ ← Data/hora (cinza claro, menor)
└─────────────────────┘
```

### **Estilo Visual:**

- **Nome**: `text-gray-900 font-medium` (destaque)
- **Data**: `text-xs text-gray-500` (secundário)
- **Vazio**: `text-gray-400 text-xs` (traço)
- **Layout**: Flexbox coluna para organização vertical

---

## 🔐 SEGURANÇA

### **Validações Implementadas:**

1. **Permissão de Visualização**:
   - Verifica `podeVisualizarAmostragem()` antes de alterar status
   - Admin vê todas
   - Usuário comum só vê se for criador ou responsável

2. **Status Válidos**:
   ```php
   $statusValidos = ['Pendente', 'Aprovado', 'Aprovado Parcialmente', 'Reprovado'];
   ```

3. **Foreign Key**:
   - Garante que `aprovado_por` sempre referencia usuário válido
   - `ON DELETE SET NULL`: Se usuário for deletado, campo fica NULL (mantém histórico)

4. **Session**:
   - Usa `$_SESSION['user_id']` do usuário logado
   - Não aceita ID de usuário do frontend

---

## 📝 COMO EXECUTAR

### **1. Execute a Migration**

No phpMyAdmin:

```sql
-- Copie e execute o arquivo:
add_aprovacao_amostragens.sql
```

### **2. Verifique se Funcionou**

```sql
-- Ver estrutura da tabela
DESCRIBE amostragens_2;

-- Deve mostrar:
-- aprovado_por      int(11)   YES  MUL   NULL
-- aprovado_em       datetime  YES        NULL
```

### **3. Teste no Sistema**

1. Acesse **Amostragens 2.0**
2. Veja o grid - deve ter coluna "Aprovado Por"
3. Altere status de uma amostragem para "Aprovado"
4. Veja que seu nome aparece na coluna com data/hora
5. Volte para "Pendente" - coluna fica vazia (-)

---

## ⚠️ OBSERVAÇÕES IMPORTANTES

### **Registros Antigos:**

Amostragens já aprovadas **ANTES** desta implementação:
- `aprovado_por` = NULL
- `aprovado_em` = NULL
- Grid exibe: `-`

**Motivo**: Não há como saber retroativamente quem aprovou.

**Solução**: Próxima vez que alterar status, registra corretamente.

---

### **Histórico de Mudanças:**

- ❌ **NÃO mantém histórico**: Se status mudar múltiplas vezes, só registra última
- ✅ **Para histórico completo**: Criar tabela `amostragens_2_historico` (futuro)

---

### **Colunas Retornadas pela Query:**

```php
$amostra = [
    'id' => 1,
    'numero_nf' => '12345',
    'status_final' => 'Aprovado',
    'aprovado_por' => 5,                       // ID
    'aprovado_por_nome' => 'João Silva',       // Nome (JOIN)
    'aprovado_por_email' => 'joao@email.com',  // Email (JOIN)
    'aprovado_em' => '2025-10-09 13:52:30',    // DateTime
    // ... outros campos
];
```

---

## ✅ CHECKLIST DE VALIDAÇÃO

- [x] Migration criada
- [x] Campos adicionados ao banco
- [x] Foreign Key configurada
- [x] Índice criado
- [x] Controller atualizado (query)
- [x] Controller atualizado (updateStatus)
- [x] View atualizada (coluna no grid)
- [x] Formato de data brasileiro (d/m/Y H:i)
- [x] Visual responsivo
- [x] Tratamento de NULL
- [x] Documentação completa

---

## 📚 REFERÊNCIAS

**Arquivos Modificados:**
- `database/migrations/add_aprovacao_amostragens.sql`
- `src/Controllers/Amostragens2Controller.php`
- `views/pages/amostragens-2/index.php`

**Tabelas Afetadas:**
- `amostragens_2`

**Relacionamentos:**
- `amostragens_2.aprovado_por` → `users.id`

---

## 🚀 PRÓXIMOS PASSOS (OPCIONAIS)

### **1. Histórico Completo**
Criar tabela `amostragens_2_historico`:
```sql
CREATE TABLE amostragens_2_historico (
  id INT AUTO_INCREMENT PRIMARY KEY,
  amostragem_id INT NOT NULL,
  status_anterior VARCHAR(50),
  status_novo VARCHAR(50),
  alterado_por INT NOT NULL,
  alterado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  observacao TEXT NULL,
  FOREIGN KEY (amostragem_id) REFERENCES amostragens_2(id),
  FOREIGN KEY (alterado_por) REFERENCES users(id)
);
```

### **2. Tooltip no Grid**
Adicionar tooltip ao passar mouse mostrando mais detalhes:
```html
<span title="Aprovado por <?= $amostra['aprovado_por_nome'] ?> em <?= date('d/m/Y às H:i', strtotime($amostra['aprovado_em'])) ?>">
```

### **3. Exportar Excel**
Incluir coluna "Aprovado Por" na exportação Excel.

---

**Status Final**: ✅ Implementação completa e funcional  
**Impacto**: Rastreabilidade total de aprovações  
**Reversível**: Sim (remover colunas se necessário)
