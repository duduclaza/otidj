# 📋 CHANGELOG - Cadastro Flexível de Toners

**Data:** 14/10/2025  
**Versão:** 2.3.0  
**Tipo:** Feature + Enhancement

---

## 🎯 Objetivo

Permitir o cadastro de toners **sem informar Peso Cheio e Peso Vazio**, para casos onde essa informação ainda não está disponível. O sistema agora identifica visualmente cadastros incompletos no grid.

---

## ✅ Alterações Implementadas

### 1. 📊 **Banco de Dados - Estrutura Atualizada**

**Arquivo:** `database/migrations/alter_toners_optional_weights.sql`

#### Colunas modificadas para permitir NULL:
- ✅ `peso_cheio` → `DECIMAL(8,2) NULL`
- ✅ `peso_vazio` → `DECIMAL(8,2) NULL`
- ✅ `gramatura` → `DECIMAL(8,2) NULL` (calculado automaticamente)
- ✅ `gramatura_por_folha` → `DECIMAL(8,4) NULL` (calculado automaticamente)

#### Query de atualização:
```sql
ALTER TABLE toners 
MODIFY COLUMN peso_cheio DECIMAL(8,2) NULL 
COMMENT 'Peso em gramas do toner cheio (opcional)';

ALTER TABLE toners 
MODIFY COLUMN peso_vazio DECIMAL(8,2) NULL 
COMMENT 'Peso em gramas do toner vazio (opcional)';

ALTER TABLE toners 
MODIFY COLUMN gramatura DECIMAL(8,2) NULL 
COMMENT 'Calculado automaticamente: peso_cheio - peso_vazio';

ALTER TABLE toners 
MODIFY COLUMN gramatura_por_folha DECIMAL(8,4) NULL 
COMMENT 'Calculado automaticamente: gramatura / capacidade_folhas';
```

---

### 2. 🎮 **Controller - Validações Atualizadas**

**Arquivo:** `src/Controllers/TonersController.php`

#### Método `store()` - Cadastro de Toners:
- ❌ **Removido:** Validação obrigatória de `peso_cheio` e `peso_vazio`
- ✅ **Adicionado:** Campos obrigatórios agora são apenas: `modelo`, `capacidade_folhas`, `preco_toner`, `cor`, `tipo`
- ✅ **Validação inteligente:** Se um peso for informado, ambos devem ser preenchidos
- ✅ **Validação condicional:** Peso cheio > peso vazio (apenas se ambos informados)

#### Método `update()` - Edição de Toners:
- Mesmas validações aplicadas ao cadastro
- Permite atualizar toners existentes adicionando pesos posteriormente

#### Código:
```php
// Campos opcionais - permite NULL
$peso_cheio = !empty($_POST['peso_cheio']) ? (float)$_POST['peso_cheio'] : null;
$peso_vazio = !empty($_POST['peso_vazio']) ? (float)$_POST['peso_vazio'] : null;

// Validação: se um peso foi informado, ambos devem ser informados
if (($peso_cheio !== null && $peso_vazio === null) || 
    ($peso_cheio === null && $peso_vazio !== null)) {
    flash('error', 'Se informar peso, ambos Peso Cheio e Peso Vazio devem ser preenchidos.');
    return;
}

// Validação: peso cheio > peso vazio (apenas se ambos informados)
if ($peso_cheio !== null && $peso_vazio !== null && $peso_cheio <= $peso_vazio) {
    flash('error', 'O peso cheio deve ser maior que o peso vazio.');
    return;
}
```

---

### 3. 🎨 **Formulário - Interface Atualizada**

**Arquivo:** `views/pages/toners/cadastro.php`

#### Alterações no formulário:
- ❌ **Removido:** Atributo `required` dos campos `peso_cheio` e `peso_vazio`
- ✅ **Adicionado:** Label com indicação "(opcional)" nos campos de peso
- ✅ **Adicionado:** Texto auxiliar: "Se informar peso, ambos devem ser preenchidos"

#### Visual:
```html
<label>Peso Cheio (g) <span class="text-gray-500 text-xs">(opcional)</span></label>
<input type="number" step="0.01" name="peso_cheio" placeholder="Ex: 850.50" 
       class="w-full border rounded px-3 py-2" onchange="calcularCampos()">
<p class="text-xs text-gray-500 mt-1">Se informar peso, ambos devem ser preenchidos</p>
```

---

### 4. 🔴 **Grid - Indicação Visual de Cadastros Incompletos**

**Arquivo:** `views/pages/toners/cadastro.php`

#### Funcionalidades implementadas:

✅ **Linha avermelhada** para cadastros incompletos:
- `class="bg-red-50 border-l-4 border-l-red-400"`
- Tooltip ao passar o mouse: "Cadastro incompleto: Peso Cheio e Peso Vazio não preenchidos"

✅ **Indicação visual nas células** de peso:
- **Peso não informado:** `⚠️ Não informado` (texto vermelho)
- **Peso informado:** Valor formatado normalmente

✅ **Campos calculados sem dados:**
- **Gramatura:** Mostra `-` quando não há pesos
- **Gramatura por Folha:** Mostra `-` quando não calculável
- **Custo por Folha:** Mostra `-` quando não calculável

#### Código:
```php
<?php 
  // Verificar se o cadastro está incompleto
  $cadastroIncompleto = empty($t['peso_cheio']) || empty($t['peso_vazio']);
  $rowClass = $cadastroIncompleto ? 'bg-red-50 border-l-4 border-l-red-400' : '';
?>
<tr class="<?= $rowClass ?>" 
    <?= $cadastroIncompleto ? 'title="Cadastro incompleto: Peso Cheio e Peso Vazio não preenchidos"' : '' ?>>
```

---

## 📸 Resultado Visual

### ✅ Cadastro Completo (Normal):
- Linha branca
- Todos os valores exibidos normalmente

### ⚠️ Cadastro Incompleto (Destaque):
- **Linha com fundo vermelho claro** (`bg-red-50`)
- **Borda vermelha à esquerda** (4px)
- **Peso Cheio:** `⚠️ Não informado` (texto vermelho)
- **Peso Vazio:** `⚠️ Não informado` (texto vermelho)
- **Gramatura:** `-` (cinza)
- **Tooltip:** "Cadastro incompleto: Peso Cheio e Peso Vazio não preenchidos"

---

## 🚀 Como Executar a Migração

### 1. **Via phpMyAdmin:**
```sql
-- Copie e execute o conteúdo do arquivo:
database/migrations/alter_toners_optional_weights.sql
```

### 2. **Via linha de comando:**
```bash
mysql -u seu_usuario -p seu_banco < database/migrations/alter_toners_optional_weights.sql
```

### 3. **Verificar aplicação:**
```sql
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'toners' AND TABLE_SCHEMA = DATABASE()
ORDER BY ORDINAL_POSITION;
```

---

## ✅ Campos Obrigatórios vs Opcionais

### 📌 **Campos OBRIGATÓRIOS:**
1. ✅ **Modelo** - Identificação do toner
2. ✅ **Capacidade de Folhas** - Rendimento do toner
3. ✅ **Preço do Toner (R$)** - Valor de compra
4. ✅ **Cor** - Yellow, Magenta, Cyan, Black
5. ✅ **Tipo** - Original, Compatível, Remanufaturado

### 📌 **Campos OPCIONAIS:**
1. ⚪ **Peso Cheio (g)** - Pode ser preenchido depois
2. ⚪ **Peso Vazio (g)** - Pode ser preenchido depois

**⚠️ ATENÇÃO:** Se informar um dos pesos, o outro também deve ser preenchido!

---

## 🔄 Fluxo de Cadastro

### Cenário 1: Cadastro com pesos
```
Usuário informa:
├─ Modelo: HP CF280A
├─ Peso Cheio: 850.50g
├─ Peso Vazio: 120.30g
├─ Capacidade: 2700 folhas
├─ Preço: R$ 89,90
├─ Cor: Black
└─ Tipo: Original

✅ Sistema calcula automaticamente:
   ├─ Gramatura: 730.20g
   ├─ Gramatura por Folha: 0.2704g
   └─ Custo por Folha: R$ 0,0333
```

### Cenário 2: Cadastro sem pesos
```
Usuário informa:
├─ Modelo: HP CE285A
├─ Peso Cheio: (vazio)
├─ Peso Vazio: (vazio)
├─ Capacidade: 1600 folhas
├─ Preço: R$ 79,90
├─ Cor: Black
└─ Tipo: Compatível

✅ Sistema permite cadastrar!
⚠️ Grid mostra linha vermelha indicando cadastro incompleto
   ├─ Gramatura: -
   ├─ Gramatura por Folha: -
   └─ Custo por Folha: -
```

---

## 🎯 Benefícios

✅ **Flexibilidade:** Cadastre toners mesmo sem dados completos  
✅ **Visibilidade:** Identificação visual clara de cadastros incompletos  
✅ **Integridade:** Validação garante consistência dos dados  
✅ **UX:** Interface intuitiva com feedbacks claros  
✅ **Manutenibilidade:** Pesos podem ser adicionados posteriormente via edição  

---

## 📝 Notas Importantes

1. **Cálculos automáticos** só funcionam quando os pesos estão preenchidos
2. **Linha vermelha** no grid facilita identificar quais toners precisam completar dados
3. **Edição posterior** permite adicionar pesos a qualquer momento
4. **Importação em massa** continua funcionando normalmente (com ou sem pesos)
5. **Retornados** continuam funcionando - se o modelo não tiver peso cadastrado, faz cálculo básico

---

## 🧪 Testes Recomendados

### ✅ Teste 1: Cadastro sem pesos
- [ ] Cadastrar toner sem informar peso cheio e peso vazio
- [ ] Verificar que o cadastro foi salvo com sucesso
- [ ] Confirmar linha vermelha no grid
- [ ] Verificar tooltip ao passar o mouse

### ✅ Teste 2: Cadastro com pesos
- [ ] Cadastrar toner informando ambos os pesos
- [ ] Verificar cálculos automáticos
- [ ] Confirmar linha normal (sem destaque vermelho)

### ✅ Teste 3: Edição posterior
- [ ] Editar toner sem pesos e adicionar os pesos
- [ ] Verificar que a linha deixa de ser vermelha
- [ ] Confirmar que os cálculos foram atualizados

### ✅ Teste 4: Validação de inconsistência
- [ ] Tentar cadastrar informando apenas peso cheio
- [ ] Verificar mensagem de erro
- [ ] Confirmar que não salvou

---

## 📚 Arquivos Modificados

1. ✅ `database/migrations/alter_toners_optional_weights.sql` - **NOVO**
2. ✅ `src/Controllers/TonersController.php` - Métodos `store()` e `update()`
3. ✅ `views/pages/toners/cadastro.php` - Formulário e grid

---

## 🔗 Próximos Passos

1. **Executar a migration SQL** no banco de dados
2. **Testar o cadastro** de toners sem pesos
3. **Verificar o grid** com linhas vermelhas
4. **Editar toners incompletos** adicionando os pesos posteriormente
5. **Validar importação em massa** com arquivos Excel/CSV

---

**✨ Implementação concluída com sucesso!**
