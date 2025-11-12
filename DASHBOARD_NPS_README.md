# 📊 Dashboard NPS com Gráficos Interativos

## 📋 Índice
- [Visão Geral](#visão-geral)
- [Funcionalidades](#funcionalidades)
- [Gráficos Disponíveis](#gráficos-disponíveis)
- [Como Usar](#como-usar)
- [Permissões](#permissões)
- [Tecnologias](#tecnologias)
- [Estrutura de Arquivos](#estrutura-de-arquivos)

---

## 🎯 Visão Geral

O **Dashboard NPS** oferece uma visão completa e visual de todas as respostas coletadas através dos formulários NPS. Com gráficos interativos e estatísticas em tempo real, permite análise rápida e eficiente do feedback dos clientes.

### 🚀 Principais Recursos

- 📊 **3 Gráficos Interativos** - Pizza, Barras e Linha
- 📈 **Cálculo Automático de NPS** - Score atualizado em tempo real
- 🎨 **Cards de Estatísticas** - Visão rápida dos números principais
- 🔄 **Atualização Automática** - Dados sempre atualizados
- 📱 **Responsivo** - Funciona em desktop, tablet e mobile

---

## ✨ Funcionalidades

### 1. Cards de Estatísticas

#### **Card 1: NPS Score**
- Exibe o **score NPS** calculado automaticamente
- Classificação visual:
  - **≥75**: Excelente! 🎉
  - **≥50**: Muito Bom! 👍
  - **≥0**: Bom 😊
  - **<0**: Precisa Melhorar 📈
- Cor de destaque: Azul gradiente

#### **Card 2: Total de Respostas**
- Quantidade total de respostas coletadas
- Ícone de documento
- Borda verde lateral

#### **Card 3: Formulários**
- Total de formulários criados
- Quantidade de formulários ativos
- Ícone de lista
- Borda roxa lateral

#### **Card 4: Promotores**
- Quantidade de promotores (notas 9-10)
- Indicador de satisfação
- Ícone de like
- Borda amarela lateral

---

## 📊 Gráficos Disponíveis

### 1. 🍰 Gráfico de Pizza: Distribuição NPS

**Objetivo**: Visualizar a proporção entre Promotores, Neutros e Detratores

**Características**:
- Tipo: Doughnut (Pizza com furo)
- Cores:
  - 🟢 Verde: Promotores (notas 9-10)
  - 🟡 Amarelo: Neutros (notas 7-8)
  - 🔴 Vermelho: Detratores (notas 0-6)
- Legenda na parte inferior
- Contadores numéricos abaixo do gráfico

**Uso**: Identificar rapidamente a satisfação geral dos clientes

---

### 2. 📊 Gráfico de Barras: Distribuição de Notas

**Objetivo**: Detalhar quantas vezes cada nota (0-10) foi dada

**Características**:
- Tipo: Barras verticais
- Eixo X: Notas de 0 a 10
- Eixo Y: Quantidade de respostas
- Cores automáticas:
  - 🟢 Verde: Notas 9-10 (Promotores)
  - 🟡 Amarelo: Notas 7-8 (Neutros)
  - 🔴 Vermelho: Notas 0-6 (Detratores)
- Bordas arredondadas

**Uso**: Identificar padrões e tendências nas avaliações

---

### 3. 📈 Gráfico de Linha: Evolução Temporal

**Objetivo**: Mostrar a evolução das respostas ao longo dos últimos 30 dias

**Características**:
- Tipo: Linha com área preenchida
- Eixo X: Últimos 30 dias (formato: dd/mm)
- Eixo Y: Quantidade de respostas por dia
- Cor: Azul com área semi-transparente
- Curva suavizada (tension: 0.4)
- Pontos interativos ao passar o mouse

**Uso**: Acompanhar tendências e identificar picos de feedback

---

## 🔢 Cálculo do NPS

### Fórmula

```
NPS = ((Promotores - Detratores) / Total de Avaliações) × 100
```

### Classificação

| Score | Avaliação | Descrição |
|-------|-----------|-----------|
| 75-100 | 🎉 Excelente | Clientes extremamente satisfeitos |
| 50-74 | 👍 Muito Bom | Alta satisfação geral |
| 0-49 | 😊 Bom | Satisfação positiva |
| < 0 | 📈 Precisa Melhorar | Mais detratores que promotores |

### Categorias

- **Promotores**: Notas 9 ou 10 (clientes leais)
- **Neutros**: Notas 7 ou 8 (satisfeitos mas não leais)
- **Detratores**: Notas 0 a 6 (insatisfeitos)

---

## 🚀 Como Usar

### Acessar o Dashboard

1. Faça login no sistema
2. Acesse **NPS** no menu
3. Clique no botão **"Dashboard"** (roxo)
4. Ou acesse diretamente: `/nps/dashboard`

### Interpretar os Dados

#### **Análise Rápida** (Cards)
- Olhe primeiro o **NPS Score** para ter noção geral
- Verifique o **Total de Respostas** para validar amostra
- Compare **Promotores vs Detratores** nos cards

#### **Análise Detalhada** (Gráficos)
1. **Gráfico de Pizza**:
   - Se verde dominar: Excelente! 🎉
   - Se vermelho for significativo: Atenção! ⚠️
   
2. **Gráfico de Barras**:
   - Procure concentração nas notas altas (9-10)
   - Identifique notas problemáticas com muitas ocorrências
   
3. **Gráfico de Linha**:
   - Linha ascendente: Mais engajamento 📈
   - Picos: Campanhas ou eventos específicos
   - Vales: Períodos de baixo feedback

### Ações Baseadas no Dashboard

#### **Se NPS ≥ 75** (Excelente)
- ✅ Continue com as práticas atuais
- ✅ Peça depoimentos dos promotores
- ✅ Use como referência de qualidade

#### **Se 50 ≤ NPS < 75** (Muito Bom)
- 🔄 Identifique pontos de melhoria com neutros
- 🔄 Reduza detratores focando em problemas principais
- 🔄 Transforme neutros em promotores

#### **Se 0 ≤ NPS < 50** (Bom)
- ⚠️ Analise feedback dos detratores urgentemente
- ⚠️ Implemente melhorias nos pontos fracos
- ⚠️ Aumente comunicação com clientes

#### **Se NPS < 0** (Precisa Melhorar)
- 🚨 Ação imediata necessária
- 🚨 Contate detratores diretamente
- 🚨 Revise processos completamente

---

## 🔐 Permissões

### Quem Pode Acessar

- ✅ **Todos os usuários logados** podem ver o dashboard
- ✅ **Administradores** veem dados de todos os formulários
- ✅ **Usuários normais** veem apenas seus formulários

### Configurar Permissões

Execute o script SQL para configurar permissões do módulo NPS:

```sql
-- Executar este script no banco de dados
source SQL_NPS_PERMISSOES.sql;
```

#### Permissões Padrão

| Ação | Admin | Usuário Normal |
|------|-------|----------------|
| Visualizar Dashboard | ✅ | ✅ |
| Ver Formulários | ✅ Todos | ✅ Próprios |
| Criar Formulários | ✅ | ✅ |
| Editar Formulários | ✅ | ❌ |
| Excluir Respostas | ✅ | ❌ |
| Exportar CSV | ✅ | ✅ |

---

## 🛠️ Tecnologias

### Frontend

- **Chart.js 4.x** - Biblioteca de gráficos
- **Tailwind CSS** - Framework CSS
- **JavaScript ES6** - Interatividade

### Backend

- **PHP 7.4+** - Linguagem de programação
- **JSON Files** - Armazenamento de dados

### Bibliotecas

```html
<!-- Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Tailwind CSS via CDN -->
<script src="https://cdn.tailwindcss.com"></script>
```

---

## 📁 Estrutura de Arquivos

```
sgqpro/
├── src/
│   └── Controllers/
│       └── NpsController.php         # Método dashboard() e coletarEstatisticas()
│
├── views/
│   └── pages/
│       └── nps/
│           ├── index.php             # Lista de formulários + botão Dashboard
│           ├── dashboard.php         # ★ View do Dashboard com gráficos
│           ├── responder.php         # Formulário público
│           └── respostas.php         # Tabela de respostas
│
├── public/
│   └── index.php                     # Rota /nps/dashboard
│
├── storage/
│   └── formularios/
│       ├── formulario_*.json         # Arquivos de formulários
│       └── respostas/
│           └── resposta_*.json       # Arquivos de respostas
│
├── SQL_NPS_PERMISSOES.sql            # Script de permissões
└── DASHBOARD_NPS_README.md           # ★ Este arquivo
```

---

## 🎨 Customização

### Alterar Cores dos Gráficos

Edite o arquivo `views/pages/nps/dashboard.php`:

```javascript
// Gráfico de Pizza - Linha ~160
backgroundColor: ['#10B981', '#F59E0B', '#EF4444']
// Verde, Amarelo, Vermelho

// Gráfico de Barras - Linha ~175
backgroundColor: function(context) {
  const value = context.dataIndex;
  if (value >= 9) return '#10B981'; // Verde
  if (value >= 7) return '#F59E0B'; // Amarelo
  return '#EF4444'; // Vermelho
}

// Gráfico de Linha - Linha ~200
borderColor: '#3B82F6',
backgroundColor: 'rgba(59, 130, 246, 0.1)'
```

### Alterar Período do Gráfico Temporal

No controller `NpsController.php`, método `coletarEstatisticas()`:

```php
// Linha ~662 - Mudar de 30 para 60 dias
for ($i = 59; $i >= 0; $i--) {  // Era 29
    $data = date('Y-m-d', strtotime("-$i days"));
    // ...
}
```

### Adicionar Novos Cards

Em `views/pages/nps/dashboard.php`:

```html
<!-- Adicione após os cards existentes -->
<div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-indigo-500">
  <div class="flex items-center justify-between mb-2">
    <h3 class="text-sm font-medium text-gray-600">Novo Card</h3>
    <svg class="w-8 h-8 text-indigo-500" ...></svg>
  </div>
  <p class="text-4xl font-bold text-gray-900"><?= $stats['novo_dado'] ?></p>
  <p class="text-xs text-gray-500 mt-1">Descrição</p>
</div>
```

---

## 📊 Exemplos de Uso

### Caso 1: Lançamento de Produto

**Situação**: Nova linha de produtos lançada

**Como usar**:
1. Criar formulário NPS específico
2. Enviar para clientes que compraram
3. Monitorar gráfico temporal diariamente
4. Identificar reação inicial (primeiros 7 dias)

**Análise**:
- Pico no gráfico de linha = boa adesão
- NPS alto = produto bem recebido
- Concentração em notas baixas = problemas

---

### Caso 2: Atendimento ao Cliente

**Situação**: Avaliar qualidade do suporte

**Como usar**:
1. Formulário NPS pós-atendimento
2. Link automático após fechar ticket
3. Dashboard mensal para acompanhamento

**Análise**:
- Gráfico de barras mostra padrões
- Notas concentradas em 9-10 = excelente
- Detratores = casos para revisar

---

### Caso 3: Pesquisa Trimestral

**Situação**: Satisfação geral dos clientes

**Como usar**:
1. Formulário NPS amplo (4-5 perguntas)
2. Envio massivo por email
3. Comparar com trimestre anterior

**Análise**:
- Gráfico de pizza mostra evolução
- Comparar NPS Score trimestral
- Linha temporal identifica sazonalidade

---

## 🐛 Troubleshooting

### Dashboard vazio ou sem dados

**Problema**: Cards mostram zeros

**Solução**:
1. Verifique se há formulários criados
2. Verifique se há respostas coletadas
3. Confirme que o usuário tem permissão
4. Admin vê todos, usuário vê apenas seus formulários

---

### Gráficos não aparecem

**Problema**: Área dos gráficos em branco

**Solução**:
1. Verifique console do navegador (F12)
2. Confirme que Chart.js carregou:
   ```javascript
   typeof Chart !== 'undefined'
   ```
3. Limpe cache do navegador
4. Verifique conexão com CDN

---

### NPS Score incorreto

**Problema**: Cálculo parece errado

**Verificar**:
1. Apenas notas de 0-10 são consideradas
2. Respostas texto livre não afetam NPS
3. Fórmula: `((Promotores - Detratores) / Total) × 100`
4. Neutros não entram no cálculo direto

---

### Permissões não funcionam

**Problema**: Usuário vê/não vê o que deveria

**Solução**:
1. Execute o script SQL de permissões
2. Verifique tabela `profile_permissions`
3. Confirme módulo 'nps' existe
4. Admin sempre tem acesso total

---

## 🎯 Melhores Práticas

### ✅ Fazer

- ✅ Acompanhar dashboard semanalmente
- ✅ Comparar períodos (mês a mês)
- ✅ Agir sobre feedback negativo
- ✅ Celebrar NPS alto com equipe
- ✅ Exportar dados para relatórios

### ❌ Evitar

- ❌ Ignorar detratores
- ❌ Focar apenas no score
- ❌ Não ler comentários
- ❌ Comparar formulários diferentes
- ❌ Tomar decisões com amostra pequena

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Consulte a documentação completa
2. Verifique o changelog no sistema
3. Entre em contato com o administrador

---

## 📝 Changelog

### v2.7.1 (2024-11-12)
- ✨ Dashboard NPS com 3 gráficos interativos
- 📊 Cards de estatísticas principais
- 📈 Cálculo automático de NPS
- 🎨 Interface moderna e responsiva
- 📱 Suporte completo mobile
- 🔐 Sistema de permissões integrado

---

## 🎉 Conclusão

O Dashboard NPS é uma ferramenta poderosa para:
- **Medir** a satisfação dos clientes
- **Visualizar** tendências rapidamente
- **Agir** baseado em dados reais
- **Melhorar** continuamente o serviço

**Acesse agora**: `/nps/dashboard` 🚀
