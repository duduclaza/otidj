# 📋 Módulo Controle de RC - Documentação Completa

## ✅ Status: **IMPLEMENTADO E FUNCIONAL**

---

## 📌 Visão Geral

Sistema completo para **Controle de Registros de Reclamação (RC)** com:
- ✅ Formulário inline com tema escuro
- ✅ Grid inteligente com busca avançada
- ✅ Sistema de upload de evidências (MEDIUMBLOB)
- ✅ Exportação de relatórios (Excel)
- ✅ Impressão de relatório individual (PDF)
- ✅ Permissões integradas

---

## 🗂️ Arquivos Criados

### 1. **Controller**
📁 `src/Controllers/ControleRcController.php` (12.5KB)

**Métodos principais:**
- `index()` - Página principal
- `list()` - Listar registros (AJAX)
- `create()` - Criar novo registro
- `update()` - Atualizar registro
- `delete()` - Excluir registro
- `show($id)` - Detalhes de um registro
- `print($id)` - Imprimir relatório individual
- `exportReport()` - Exportar múltiplos registros (Excel)
- `downloadEvidencia($id)` - Download de evidência
- `uploadEvidencias()` - Upload de arquivos (MEDIUMBLOB)

### 2. **Views**
📁 `views/pages/controle-rc/index.php` (17.2KB)
- Formulário inline com tema escuro (bg-gray-800)
- Grid responsivo com TailwindCSS
- Busca inteligente com normalização de acentos
- Seleção múltipla para exportação

📁 `views/pages/controle-rc/print.php` (8.1KB)
- Layout profissional para impressão
- Suporte a impressão direta ou salvar PDF
- Design otimizado para papel A4
- Seções organizadas com assinaturas

### 3. **Migration SQL**
📁 `database/migrations/create_controle_rc_tables.sql`

**Tabelas criadas:**
- `controle_rc` - Registros principais
- `controle_rc_evidencias` - Evidências em MEDIUMBLOB

### 4. **Rotas Configuradas**
📁 `public/index.php` (linhas 188-197)

```php
// Controle de RC routes
$router->get('/controle-de-rc', [ControleRcController::class, 'index']);
$router->get('/controle-rc/list', [ControleRcController::class, 'list']);
$router->post('/controle-rc/create', [ControleRcController::class, 'create']);
$router->post('/controle-rc/update', [ControleRcController::class, 'update']);
$router->post('/controle-rc/delete', [ControleRcController::class, 'delete']);
$router->get('/controle-rc/{id}', [ControleRcController::class, 'show']);
$router->get('/controle-rc/{id}/print', [ControleRcController::class, 'print']);
$router->post('/controle-rc/export', [ControleRcController::class, 'exportReport']);
$router->get('/controle-rc/evidencia/{id}', [ControleRcController::class, 'downloadEvidencia']);
```

### 5. **Middleware de Permissões**
📁 `src/Middleware/PermissionMiddleware.php` (linhas 87-94)

Módulo: `controle_rc`

---

## 📊 Estrutura do Banco de Dados

### Tabela: `controle_rc`
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT UNSIGNED | ID auto-incremento |
| numero_registro | VARCHAR(50) | Formato: RC-2025-0001 (UNIQUE) |
| data_abertura | DATE | Data de abertura da RC |
| origem | ENUM | Telefone, E-mail, Presencial, etc. |
| cliente_nome | VARCHAR(255) | Nome do cliente/empresa |
| categoria | ENUM | Técnica, Atendimento, Logística, etc. |
| numero_serie | VARCHAR(100) | Número de série (opcional) |
| fornecedor_id | INT UNSIGNED | FK para fornecedores (NULL) |
| testes_realizados | TEXT | Descrição dos testes |
| acoes_realizadas | TEXT | Ações tomadas |
| conclusao | TEXT | Conclusão final |
| usuario_id | INT UNSIGNED | FK para users |
| created_at | TIMESTAMP | Data de criação |
| updated_at | TIMESTAMP | Data de atualização |

### Tabela: `controle_rc_evidencias`
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT UNSIGNED | ID auto-incremento |
| rc_id | INT UNSIGNED | FK para controle_rc |
| arquivo_blob | MEDIUMBLOB | Arquivo binário (máx 16MB) |
| nome_arquivo | VARCHAR(255) | Nome original do arquivo |
| tipo_arquivo | VARCHAR(100) | MIME type |
| tamanho | INT UNSIGNED | Tamanho em bytes |
| created_at | TIMESTAMP | Data de upload |

**Relações:**
- `controle_rc.usuario_id` → `users.id` (RESTRICT)
- `controle_rc.fornecedor_id` → `fornecedores.id` (SET NULL)
- `controle_rc_evidencias.rc_id` → `controle_rc.id` (CASCADE DELETE)

---

## 🎨 Interface do Usuário

### Formulário Inline
- **Tema**: Escuro (bg-gray-800) para diferenciar do grid
- **Layout**: Grid responsivo (1-3 colunas)
- **Campos obrigatórios**: Data abertura, Origem, Cliente, Categoria
- **Upload**: Múltiplos arquivos (JPG, PNG, GIF, PDF - 5MB cada)
- **Botões**: Salvar, Limpar, Cancelar Edição

### Grid de Registros
- **Colunas**: Checkbox, Nº Registro, Data, Origem, Cliente, Categoria, Nº Série, Fornecedor, Evidências, Usuário, Ações
- **Ações**: Editar ✏️, Imprimir 🖨️, Excluir 🗑️
- **Indicadores**: Badge de evidências (📎 N)

### Busca Inteligente
- **Funcionalidades**:
  - Case-insensitive
  - Normalização de acentos (café = cafe)
  - Múltiplas palavras (operador AND)
  - Busca por coluna específica ou todas
  - Debounce de 150ms
  - Enter para busca imediata

- **Colunas pesquisáveis**:
  - Todas as colunas
  - Número Registro
  - Origem
  - Cliente/Empresa
  - Categoria
  - Fornecedor

### Seleção e Exportação
- **Checkbox individual** em cada linha
- **Selecionar todos** visíveis
- **Exportar selecionados** para Excel
- **Contador de resultados**: "Mostrando X de Y registros"

---

## 📄 Sistema de Impressão

### Relatório Individual
- **Rota**: `/controle-rc/{id}/print`
- **Formato**: HTML otimizado para impressão
- **Características**:
  - Cabeçalho com logo e número RC
  - Seções organizadas
  - Informações completas
  - Lista de evidências
  - Área para assinaturas
  - Rodapé com data/hora de geração

### Exportação Múltipla
- **Rota**: `/controle-rc/export`
- **Formato**: Excel (.xls)
- **Nome do arquivo**: `controle-rc-YYYY-MM-DD-HHMMSS.xls`
- **Colunas**: Todas as informações principais

---

## 🔐 Sistema de Permissões

### Módulo
`controle_rc`

### Ações Disponíveis
- **view**: Visualizar registros
- **edit**: Criar e editar registros
- **delete**: Excluir registros
- **export**: Exportar relatórios

### Configuração
Adicionar o módulo `controle_rc` nas permissões dos perfis desejados:

```sql
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_export)
VALUES 
(1, 'controle_rc', 1, 1, 1, 1), -- Administrador
(2, 'controle_rc', 1, 1, 0, 1), -- Supervisor
(3, 'controle_rc', 1, 0, 0, 0); -- Usuário
```

---

## 🚀 Como Usar

### 1. Executar Migration
```bash
# No MySQL, executar:
source database/migrations/create_controle_rc_tables.sql
```

Ou via PhpMyAdmin: Importar o arquivo SQL.

**⚠️ IMPORTANTE:** A migration foi otimizada sem foreign keys para evitar erros de compatibilidade. As tabelas serão criadas com índices, mas sem constraints de integridade referencial. O sistema funciona normalmente mesmo sem as foreign keys.

### 2. Configurar Permissões
Adicionar o módulo `controle_rc` aos perfis no admin.

### 3. Acessar o Sistema
**URL**: `https://djbr.sgqoti.com.br/controle-de-rc`

### 4. Criar Registro
1. Preencher formulário inline
2. Selecionar origem e categoria
3. Upload de evidências (opcional)
4. Clicar em "Salvar Registro"

### 5. Editar Registro
1. Clicar em ✏️ no grid
2. Formulário será preenchido
3. Modificar campos necessários
4. Clicar em "Atualizar Registro"

### 6. Imprimir
1. Clicar em 🖨️ no grid
2. Abre em nova janela
3. Clicar em "Imprimir / Salvar PDF"

### 7. Exportar Múltiplos
1. Selecionar registros no grid
2. Clicar em "Exportar Selecionados"
3. Download automático do Excel

---

## 📋 Campos do Formulário

### Obrigatórios (*)
- **Data de Abertura**: Date picker
- **Origem da Reclamação**: Dropdown (7 opções)
- **Nome do Cliente/Empresa**: Text input
- **Categoria da Reclamação**: Dropdown (9 opções)

### Opcionais
- **Número de Série/Identificação**: Text input
- **Fornecedor**: Dropdown (lista de fornecedores cadastrados)
- **Testes Realizados**: Textarea
- **Ações Realizadas**: Textarea
- **Conclusão**: Textarea
- **Evidências**: File upload múltiplo

---

## 🎯 Funcionalidades Técnicas

### Upload de Evidências
- **Tipos aceitos**: image/jpeg, image/png, image/gif, application/pdf
- **Tamanho máximo**: 5MB por arquivo
- **Armazenamento**: MEDIUMBLOB no banco (até 16MB)
- **Múltiplos uploads**: Sim
- **Validação**: Server-side e client-side

### Numeração Automática
**Formato**: `RC-YYYY-NNNN`
- **RC**: Prefixo fixo
- **YYYY**: Ano atual (2025)
- **NNNN**: Sequencial de 4 dígitos (0001, 0002, etc.)
- **Exemplo**: RC-2025-0001, RC-2025-0002

### Busca Inteligente
**Função JavaScript**: `window.searchRC()`
```javascript
// Normalização de texto
const normalized = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

// Busca com múltiplas palavras (AND)
const searchTerms = searchTerm.split(' ').filter(t => t.length > 0);
const matches = searchTerms.every(term => normalizedText.includes(term));
```

### AJAX Forms
- **Class**: `ajax-form` (evita loading overlay)
- **FormData**: Suporte a upload de arquivos
- **Callbacks**: Success/error com feedback visual

---

## 🔧 Troubleshooting

### Problema: Erro 403 ao acessar
**Solução**: Verificar se o módulo `controle_rc` está nas permissões do perfil do usuário.

### Problema: Upload de evidências falha
**Solução**: 
1. Verificar limite `upload_max_filesize` no PHP (recomendado: 10M)
2. Verificar `post_max_size` no PHP (recomendado: 20M)
3. Verificar tipo de arquivo (apenas imagens e PDF)

### Problema: Número de registro duplicado
**Solução**: Verificar se a transação foi commitada corretamente. O campo tem constraint UNIQUE.

### Problema: Fornecedor não aparece no dropdown
**Solução**: Verificar se há fornecedores cadastrados na tabela `fornecedores`.

---

## 📊 Métricas do Módulo

### Arquivos
- **Controllers**: 1 (ControleRcController.php - 12.5KB)
- **Views**: 2 (index.php - 17.2KB, print.php - 8.1KB)
- **Migrations**: 1 (create_controle_rc_tables.sql)
- **Total**: ~38KB de código

### Funcionalidades
- **Rotas**: 9
- **Métodos controller**: 10
- **Campos formulário**: 10
- **Tipos de origem**: 7
- **Tipos de categoria**: 9
- **Colunas grid**: 11

### Performance
- **Conexões persistentes**: Sim (PDO)
- **Lazy loading**: Sim (Database)
- **Cache de permissões**: Sim (PermissionService)
- **AJAX**: Sim (listagem assíncrona)

---

## 🎨 Screenshots (Descrição)

### 1. Página Principal
- Formulário escuro no topo
- Grid branco abaixo
- Barra de busca e ações entre eles

### 2. Grid Populado
- Linhas alternadas para leitura
- Badges coloridos para categorias
- Ícones de ação em cada linha
- Checkbox para seleção múltipla

### 3. Relatório de Impressão
- Layout profissional A4
- Seções bem definidas
- Espaço para assinaturas
- Rodapé com informações

---

## ✅ Checklist de Implementação

- [x] Controller criado com todos os métodos CRUD
- [x] View com formulário inline tema escuro
- [x] Grid responsivo com TailwindCSS
- [x] Busca inteligente implementada
- [x] Upload de evidências (MEDIUMBLOB)
- [x] Relatório de impressão profissional
- [x] Exportação para Excel
- [x] Migration SQL criada
- [x] Rotas configuradas no index.php
- [x] Middleware de permissões configurado
- [x] Validações server-side
- [x] Validações client-side
- [x] Tratamento de erros
- [x] Feedback visual
- [x] Documentação completa

---

## 🚀 Próximos Passos (Opcional)

### Melhorias Futuras
1. **Dashboard de Estatísticas**
   - Gráfico de RCs por categoria
   - Gráfico de RCs por origem
   - Top 5 clientes com mais reclamações
   - Tendências mensais

2. **Notificações**
   - Email para responsáveis ao criar RC
   - Alertas de RCs pendentes há muito tempo
   - Resumo semanal/mensal

3. **Workflow**
   - Status: Aberto, Em Análise, Resolvido, Fechado
   - Atribuição de responsáveis
   - Prazos e SLA

4. **Relatórios Avançados**
   - Relatório por período
   - Relatório por categoria
   - Relatório por cliente
   - Gráficos de performance

---

## 👨‍💻 Autor

**Desenvolvido para**: SGQ OTI DJ  
**Data**: Janeiro 2025  
**Versão**: 1.0.0  
**Status**: ✅ Produção

---

## 📞 Suporte

Para dúvidas ou problemas, contactar o administrador do sistema.

**Sistema**: https://djbr.sgqoti.com.br  
**Módulo**: /controle-de-rc
