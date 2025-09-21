# 📊 Exportação de Toners - Documentação

## 🎯 Funcionalidades Implementadas

### **1. Exportação Básica**
- **Rota**: `/toners/export`
- **Método**: `exportExcel()`
- **Arquivo**: `toners_cadastro_YYYY-MM-DD_HH-mm-ss.csv`

**Campos Exportados:**
- Modelo
- Peso Cheio (g)
- Peso Vazio (g)
- Gramatura (g)
- Capacidade Folhas
- Preço Toner (R$)
- Gramatura por Folha (g)
- Custo por Folha (R$)
- Cor
- Tipo
- Data Cadastro
- Última Atualização

### **2. Exportação Avançada (Relatório Completo)**
- **Rota**: `/toners/export-advanced`
- **Método**: `exportExcelAdvanced()`
- **Arquivo**: `toners_relatorio_completo_YYYY-MM-DD_HH-mm-ss.csv`

**Campos Adicionais:**
- Total de Retornados por Modelo
- Valor Total Recuperado (R$)
- Resumo Geral no Topo

## 🔧 Características Técnicas

### **Formato de Arquivo**
- **Tipo**: CSV (Comma-Separated Values)
- **Separador**: Ponto e vírgula (;) para compatibilidade com Excel brasileiro
- **Codificação**: UTF-8 com BOM
- **Formatação**: Números brasileiros (vírgula decimal, ponto milhares)

### **Segurança e Permissões**
- **Middleware**: Verificação automática de permissões
- **Módulo**: `toners_cadastro`
- **Permissão Necessária**: `can_export = 1`
- **Perfis com Acesso**:
  - ✅ Administrador
  - ✅ Supervisor
  - ✅ Operador de Toners
  - ✅ Analista de Qualidade
  - ❌ Usuário Comum

### **Interface do Usuário**
- **Botão Exportar**: Verde com ícone de download
- **Botão Relatório**: Azul com ícone de gráfico
- **Loading State**: Spinner animado durante processamento
- **Notificações**: Toast de sucesso após exportação
- **Responsivo**: Funciona em desktop e mobile

## 📋 Como Usar

### **1. Exportação Simples**
1. Acesse "Cadastro de Toners"
2. Clique no botão verde "Exportar"
3. Aguarde o processamento (2 segundos)
4. Arquivo será baixado automaticamente

### **2. Relatório Completo**
1. Acesse "Cadastro de Toners"
2. Clique no botão azul "Relatório"
3. Aguarde o processamento (2,5 segundos)
4. Arquivo com estatísticas será baixado

## 🎨 Estrutura do Relatório Avançado

```csv
RESUMO - 47 Toners Cadastrados;;;;;;;;Total Retornados: 156;Valor Total: R$ 12.450,00;;;

Modelo;Peso Cheio (g);Peso Vazio (g);...;Total Retornados;Valor Total Recuperado (R$);...
HP CF280A;850,50;120,30;...;12;R$ 1.250,00;...
Canon 045;720,80;110,20;...;8;R$ 890,00;...
```

## 🔍 Consultas SQL Utilizadas

### **Exportação Básica**
```sql
SELECT modelo, peso_cheio, peso_vazio, gramatura, 
       capacidade_folhas, preco_toner, gramatura_por_folha, 
       custo_por_folha, cor, tipo, created_at, updated_at
FROM toners 
ORDER BY modelo
```

### **Exportação Avançada**
```sql
SELECT t.*, 
       COALESCE(r.total_retornados, 0) as total_retornados,
       COALESCE(r.valor_total_recuperado, 0) as valor_total_recuperado
FROM toners t
LEFT JOIN (
    SELECT modelo, COUNT(*) as total_retornados,
           SUM(valor_calculado) as valor_total_recuperado
    FROM retornados 
    WHERE modelo_cadastrado = 1
    GROUP BY modelo
) r ON t.modelo = r.modelo
ORDER BY t.modelo
```

## ⚡ Performance

### **Otimizações Implementadas**
- **Consulta Única**: Todos os dados em uma query
- **Índices**: Aproveitamento de índices existentes
- **Streaming**: Escrita direta no output buffer
- **Memória**: Processamento linha por linha

### **Limites**
- **Registros**: Sem limite (processa todos)
- **Timeout**: 30 segundos (padrão PHP)
- **Memória**: Otimizada para grandes volumes

## 🛠️ Tratamento de Erros

### **Erros Possíveis**
1. **Nenhum registro encontrado**
   - Retorna JSON com erro
   - Não gera arquivo vazio

2. **Erro de banco de dados**
   - Log do erro
   - Resposta JSON com mensagem

3. **Limite de conexões**
   - Tratamento gracioso
   - Mensagem específica

### **Códigos de Resposta**
- **200**: Sucesso (arquivo CSV)
- **200**: Erro (JSON com success: false)
- **403**: Sem permissão (middleware)

## 🔄 Integração com Sistema

### **Arquivos Modificados**
- `TonersController.php`: Métodos de exportação
- `index.php`: Rotas adicionadas
- `PermissionMiddleware.php`: Proteção das rotas
- `cadastro.php`: Interface atualizada

### **Dependências**
- **PHP**: 8.0+ (funções de CSV nativas)
- **PDO**: Para consultas ao banco
- **TailwindCSS**: Para interface
- **JavaScript**: Para interações

## 📊 Estatísticas de Uso

### **Métricas Incluídas no Relatório**
- **Total de Toners**: Quantidade cadastrada
- **Total de Retornados**: Soma de todos os retornados
- **Valor Recuperado**: Soma dos valores calculados
- **Por Modelo**: Estatísticas individuais

### **Formatação Brasileira**
- **Moeda**: R$ 1.234,56
- **Números**: 1.234,56
- **Datas**: DD/MM/AAAA HH:mm

## 🚀 Próximas Melhorias

### **Funcionalidades Futuras**
- [ ] Filtros por data/período
- [ ] Exportação em XLSX nativo
- [ ] Agendamento de relatórios
- [ ] Templates personalizáveis
- [ ] Gráficos incorporados
- [ ] Compressão ZIP para grandes volumes

### **Otimizações Planejadas**
- [ ] Cache de consultas complexas
- [ ] Processamento assíncrono
- [ ] Progress bar para grandes volumes
- [ ] Paginação na exportação

---

**📝 Nota**: Esta documentação está atualizada com a versão atual do sistema. Para dúvidas ou sugestões, consulte a equipe de desenvolvimento.
