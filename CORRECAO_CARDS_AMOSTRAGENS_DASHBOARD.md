# 🔧 CORREÇÃO - Cards de Amostragens Dashboard

## 📋 PROBLEMA REPORTADO

> "Os cards da amostragens não estão batendo com os dados reais do grid"

---

## 🔍 DIAGNÓSTICO

### **Localização dos Cards:**
- **Arquivo**: `views/admin/dashboard.php`
- **Aba**: "🧪 Amostragens 2.0"
- **Função JS**: `loadDashboardAmostragens()`
- **Endpoint**: `/admin/dashboard/amostragens-data`
- **Controller**: `AdminController::getAmostragemsDashboardData()`

### **Cards Existentes:**
1. **Total de Amostragens** (ID: `totalAmostragens`)
2. **Aprovadas** (ID: `totalAprovadas`)
3. **Reprovadas** (ID: `totalReprovadas`)
4. **Pendentes** (ID: `totalPendentes`)

---

## 🎯 CAUSAS POSSÍVEIS

### **1. Filtro de Visualização (Não-Admin)**

**Código Atual (linhas 1874-1879):**
```php
if ($userRole !== 'admin') {
    // Usuário comum: só vê amostragens onde está na lista de responsáveis
    $where[] = "(FIND_IN_SET(:user_id_responsavel, a.responsaveis) > 0 OR a.user_id = :user_id_criador)";
    $params[':user_id_responsavel'] = $userId;
    $params[':user_id_criador'] = $userId;
}
```

**Problema:**
- Usuários **não-admin** veem **apenas** amostragens onde são responsáveis
- Grid pode estar mostrando TODAS as amostragens (sem filtro)
- Resultado: **Cards mostram menos** que o grid

---

### **2. Status com Nomes Inconsistentes**

**Query Aprovadas (linha 1896):**
```php
$whereClause AND a.status_final IN ('Aprovado', 'Aprovado Parcialmente')
```

**Query Reprovadas (linha 1905):**
```php
$whereClause AND a.status_final = 'Reprovado'
```

**Query Pendentes (linha 1914):**
```php
$whereClause AND a.status_final = 'Pendente'
```

**Possível Problema:**
- Status no banco pode estar com nome diferente
- Ex: "Aprovado Parcial" vs "Aprovado Parcialmente"
- Ex: "Pendente" vs "Em Análise"

---

### **3. Filtros de Data**

**Código (linhas 1860-1868):**
```php
if (!empty($dataInicial)) {
    $where[] = "DATE(a.created_at) >= :data_inicial";
}

if (!empty($dataFinal)) {
    $where[] = "DATE(a.created_at) <= :data_final";
}
```

**Problema:**
- Se filtros estão ativos, cards mostram APENAS período filtrado
- Grid pode estar mostrando TUDO (sem respeitar filtro)

---

## ✅ SOLUÇÕES

### **Solução 1: Verificar Nomes de Status no Banco**

Execute no MySQL:

```sql
-- Ver todos os status únicos no banco
SELECT DISTINCT status_final, COUNT(*) as total
FROM amostragens_2
GROUP BY status_final
ORDER BY total DESC;
```

**Resultado Esperado:**
```
status_final          | total
----------------------|------
Pendente              |  150
Aprovado              |   80
Aprovado Parcialmente |   20
Reprovado             |   10
```

**Se aparecer diferente:**
- "Aprovado Parcial" (sem "mente")
- "Em Analise" (sem acento)
- Ajustar query do controller

---

### **Solução 2: Remover Filtro de Permissão nos Cards**

Se você quer que os cards mostrem **TODAS** as amostragens (independente de ser responsável):

```php
// COMENTAR estas linhas (1874-1879)
/*
if ($userRole !== 'admin') {
    $where[] = "(FIND_IN_SET(:user_id_responsavel, a.responsaveis) > 0 OR a.user_id = :user_id_criador)";
    $params[':user_id_responsavel'] = $userId;
    $params[':user_id_criador'] = $userId;
}
*/
```

**Efeito:**
- Cards mostram totais gerais
- Todos usuários veem mesmas estatísticas

---

### **Solução 3: Aplicar Mesmo Filtro no Grid**

Se você quer manter o filtro, garantir que o **grid também filtre**.

**No Controller de Amostragens (index):**

Adicionar mesmo filtro de responsáveis na query principal.

---

### **Solução 4: Query Melhorada (Case-Insensitive)**

Para evitar problemas com maiúsculas/minúsculas:

```php
// Aprovadas - Case insensitive
$stmtAprovadas = $this->db->prepare("
    SELECT COUNT(*) as aprovadas FROM amostragens_2 a
    LEFT JOIN users u ON a.user_id = u.id
    $whereClause AND (
        LOWER(a.status_final) = 'aprovado' OR 
        LOWER(a.status_final) LIKE 'aprovado parcial%'
    )
");

// Reprovadas - Case insensitive
$stmtReprovadas = $this->db->prepare("
    SELECT COUNT(*) as reprovadas FROM amostragens_2 a
    LEFT JOIN users u ON a.user_id = u.id
    $whereClause AND LOWER(a.status_final) = 'reprovado'
");

// Pendentes - Case insensitive
$stmtPendentes = $this->db->prepare("
    SELECT COUNT(*) as pendentes FROM amostragens_2 a
    LEFT JOIN users u ON a.user_id = u.id
    $whereClause AND LOWER(a.status_final) = 'pendente'
");
```

---

## 🧪 TESTE - PASSO A PASSO

### **Passo 1: Verificar Status**

```sql
SELECT status_final, COUNT(*) as qtd
FROM amostragens_2
GROUP BY status_final;
```

Anote os resultados reais.

---

### **Passo 2: Verificar Total de Cada Status**

```sql
-- Total Geral
SELECT COUNT(*) FROM amostragens_2;

-- Aprovadas
SELECT COUNT(*) FROM amostragens_2 
WHERE status_final IN ('Aprovado', 'Aprovado Parcialmente');

-- Reprovadas
SELECT COUNT(*) FROM amostragens_2 
WHERE status_final = 'Reprovado';

-- Pendentes
SELECT COUNT(*) FROM amostragens_2 
WHERE status_final = 'Pendente';
```

---

### **Passo 3: Comparar com Cards**

1. Vá no **Dashboard** → Aba **"Amostragens 2.0"**
2. Veja os números nos cards
3. **Compare** com os números do SQL

**Se não bater:**
- Verifique se há filtros ativos (data, filial)
- Limpe filtros clicando "Limpar Filtros"
- Recarregue

---

### **Passo 4: Verificar Permissões**

```sql
-- Ver amostragens que você é responsável (troque USER_ID pelo seu ID)
SELECT COUNT(*) FROM amostragens_2
WHERE FIND_IN_SET(1, responsaveis) > 0 OR user_id = 1;
```

Se você **não é admin**, cards mostram apenas isso.

---

## 📊 QUERY DE DEBUG

Execute esta query para ver exatamente o que os cards deveriam mostrar:

```sql
SELECT 
    'Total' as card,
    COUNT(*) as valor
FROM amostragens_2

UNION ALL

SELECT 
    'Aprovadas' as card,
    COUNT(*) as valor
FROM amostragens_2
WHERE status_final IN ('Aprovado', 'Aprovado Parcialmente')

UNION ALL

SELECT 
    'Reprovadas' as card,
    COUNT(*) as valor
FROM amostragens_2
WHERE status_final = 'Reprovado'

UNION ALL

SELECT 
    'Pendentes' as card,
    COUNT(*) as valor
FROM amostragens_2
WHERE status_final = 'Pendente'

ORDER BY card;
```

**Resultado Esperado:**
```
card        | valor
------------|------
Aprovadas   |   100
Pendentes   |    50
Reprovadas  |    10
Total       |   160
```

---

## 🔧 CORREÇÃO RECOMENDADA

Adicione estas linhas no início do método `getAmostragemsDashboardData()` para debug:

```php
public function getAmostragemsDashboardData()
{
    header('Content-Type: application/json');
    
    try {
        // ... código existente ...
        
        // DEBUG: Log dos totais
        error_log("===== DASHBOARD AMOSTRAGENS DEBUG =====");
        error_log("Total Amostragens: " . $totalAmostragens);
        error_log("Aprovadas: " . $aprovadas);
        error_log("Reprovadas: " . $reprovadas);
        error_log("Pendentes: " . $pendentes);
        error_log("User ID: " . $userId);
        error_log("User Role: " . $userRole);
        error_log("Filtros: " . json_encode($params));
        error_log("=========================================");
        
        // ... resto do código ...
```

Depois verifique o log do PHP (`/var/log/php-errors.log` ou similar).

---

## ⚠️ NOTA IMPORTANTE

### **Cards vs Grid**

| Onde | O que Mostra | Filtro Aplicado |
|------|--------------|-----------------|
| **Cards (Dashboard)** | Estatísticas agregadas | ✅ Filtra por responsável (não-admin) |
| **Grid (Amostragens)** | Lista detalhada | ❓ Verificar se filtra |

**Para consistência:**
- Ambos devem usar **MESMO filtro**
- Ou ambos mostram tudo (admin)
- Ou ambos filtram por responsável (não-admin)

---

## 📁 ARQUIVOS ENVOLVIDOS

1. **`src/Controllers/AdminController.php`**
   - Método: `getAmostragemsDashboardData()` (linha 1841)
   
2. **`views/admin/dashboard.php`**
   - Cards: linhas 263-337
   - Função JS: `loadDashboardAmostragens()` (linha 1741)
   - Função JS: `updateDashboardAmostragens()` (linha 1769)

3. **`src/Controllers/Amostragens2Controller.php`**
   - Método: `index()` (comparar filtro com dashboard)

---

## ✅ CHECKLIST DE VERIFICAÇÃO

- [ ] Executar query de status únicos
- [ ] Verificar nomes exatos dos status
- [ ] Comparar totais SQL com cards
- [ ] Verificar se usuário é admin ou não
- [ ] Limpar filtros de data/filial
- [ ] Adicionar logs de debug
- [ ] Verificar permissões de responsáveis
- [ ] Testar com usuário admin
- [ ] Testar com usuário comum
- [ ] Confirmar se grid usa mesmo filtro

---

**Data**: 09/10/2025 14:45  
**Status**: 🔍 Aguardando diagnóstico do usuário  
**Próximo Passo**: Execute a query de verificação de status
