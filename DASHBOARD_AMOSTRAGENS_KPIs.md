# Dashboard Amostragens 2.0 - KPIs e Gráficos

## 📊 Estrutura com Abas

### Aba 1: Retornados (ATUAL)
- Mantém todo conteúdo existente
- Gráficos de Retornados por Mês
- Destino dos Retornados
- Valor Recuperado

### Aba 2: Amostragens 2.0 (NOVO)

## 📈 KPIs - Cards de Métricas

### Card 1: Total de Amostragens
- **Fonte**: COUNT(*) FROM amostragens_2
- **Ícone**: 🧪 Clipboard
- **Cor**: Teal (verde-azulado)
- **Unidade**: testes realizados

### Card 2: Taxa de Aprovação
- **Cálculo**: (Aprovado + Aprovado Parcialmente) / Total * 100
- **Ícone**: ✅ Check Circle
- **Cor**: Verde
- **Unidade**: Percentual

### Card 3: Produtos Testados
- **Fonte**: SUM(quantidade_testada) FROM amostragens_2
- **Ícone**: 📦 Box
- **Cor**: Azul
- **Unidade**: itens testados

### Card 4: Pendentes
- **Fonte**: COUNT(*) WHERE status_final = 'Pendente'
- **Ícone**: ⏳ Clock
- **Cor**: Laranja
- **Unidade**: aguardando análise

## 📊 Gráficos

### 1. Gráfico de Pizza - Status das Amostragens
```sql
SELECT 
    status_final, 
    COUNT(*) as total 
FROM amostragens_2 
GROUP BY status_final
```
- **Cores**:
  - Aprovado: Verde (#10B981)
  - Aprovado Parcialmente: Amarelo (#F59E0B)
  - Reprovado: Vermelho (#EF4444)
  - Pendente: Laranja (#F97316)

### 2. Gráfico de Barras - Amostragens por Tipo de Produto
```sql
SELECT 
    tipo_produto, 
    COUNT(*) as total 
FROM amostragens_2 
GROUP BY tipo_produto
ORDER BY total DESC
```
- **Tipos**: Toner, Peça, Máquina
- **Cores**: Azul (#3B82F6)

### 3. Gráfico de Barras Horizontais - Top 5 Fornecedores
```sql
SELECT 
    forn.nome as fornecedor,
    COUNT(*) as total_amostragens
FROM amostragens_2 a
LEFT JOIN fornecedores forn ON a.fornecedor_id = forn.id
GROUP BY forn.nome
ORDER BY total_amostragens DESC
LIMIT 5
```
- **Cor**: Roxo (#8B5CF6)

### 4. Gráfico de Linha - Evolução Temporal
```sql
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as mes,
    COUNT(*) as total
FROM amostragens_2
GROUP BY mes
ORDER BY mes
LIMIT 12
```
- **Cor**: Teal (#14B8A6)
- **Tendência**: Mostra crescimento ao longo dos meses

### 5. Gráfico de Barras - Amostragens por Filial
```sql
SELECT 
    u.filial,
    COUNT(*) as total
FROM amostragens_2 a
LEFT JOIN users u ON a.user_id = u.id
GROUP BY u.filial
ORDER BY total DESC
```
- **Cores**: Gradient (azul-verde)

### 6. Gráfico de Barras Empilhadas - Quantidades por Status
```sql
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as mes,
    SUM(quantidade_aprovada) as aprovadas,
    SUM(quantidade_reprovada) as reprovadas,
    SUM(quantidade_testada) as testadas
FROM amostragens_2
WHERE quantidade_testada IS NOT NULL
GROUP BY mes
ORDER BY mes
LIMIT 12
```
- **Datasets**:
  - Aprovadas: Verde
  - Reprovadas: Vermelho
  - Testadas: Azul

## 🔍 Filtros

### Filtros Disponíveis
1. **Filial**: Dropdown (todas as filiais)
2. **Data Inicial**: Date picker
3. **Data Final**: Date picker
4. **Tipo de Produto**: Dropdown (Toner, Peça, Máquina)
5. **Status**: Dropdown (Todos, Aprovado, Reprovado, Pendente)

## 🎨 Design

### Sistema de Abas
```html
<div class="tabs">
  <button class="tab active">📦 Retornados</button>
  <button class="tab">🧪 Amostragens 2.0</button>
</div>
```

### Cores do Tema
- **Principal**: Teal (#14B8A6)
- **Secundária**: Azul (#3B82F6)
- **Sucesso**: Verde (#10B981)
- **Alerta**: Laranja (#F97316)
- **Erro**: Vermelho (#EF4444)

## 📡 Endpoints da API

### Dados do Dashboard Amostragens
```
GET /admin/dashboard/amostragens-data
```

**Resposta**:
```json
{
  "success": true,
  "data": {
    "cards": {
      "total_amostragens": 150,
      "taxa_aprovacao": 75.5,
      "produtos_testados": 1250,
      "pendentes": 12
    },
    "status": {
      "labels": ["Aprovado", "Aprovado Parcialmente", "Reprovado", "Pendente"],
      "data": [80, 15, 20, 12]
    },
    "tipos_produto": {
      "labels": ["Toner", "Peça", "Máquina"],
      "data": [90, 40, 20]
    },
    "fornecedores": {
      "labels": ["Fornecedor A", "Fornecedor B", ...],
      "data": [45, 38, 25, 22, 20]
    },
    "evolucao": {
      "labels": ["2024-01", "2024-02", ...],
      "data": [12, 15, 20, 25, 30, 35, ...]
    },
    "filiais": {
      "labels": ["Jundiaí", "São Paulo", ...],
      "data": [50, 45, 30, 25]
    }
  }
}
```

## 🚀 Implementação

### Passos
1. ✅ Análise dos dados disponíveis (schema amostragens_2)
2. ⏳ Adicionar sistema de abas no dashboard
3. ⏳ Criar endpoint `/admin/dashboard/amostragens-data`
4. ⏳ Implementar gráficos Chart.js
5. ⏳ Adicionar filtros e interatividade
6. ⏳ Testar e validar KPIs

## 📝 Observações

- Todos os gráficos devem ser responsivos
- Usar Chart.js (já está no projeto)
- Manter consistência visual com aba Retornados
- Implementar loading states
- Adicionar tooltips informativos
- Possibilitar expansão dos gráficos (modal)
