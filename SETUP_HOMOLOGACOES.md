# 🚀 Setup Módulo Homologações - SGQ OTI DJ

## 📋 Visão Geral

Este guia explica como instalar corretamente o módulo de Homologações no sistema SGQ OTI DJ usando o sistema de migrations.

## ✅ Opção 1: Instalação Automática (Recomendado)

### Via Navegador

1. **Acesse o script de setup:**
   ```
   http://seu-site.com/setup_homologacoes.php
   ```

2. **Aguarde a execução automática**
   - O script irá conectar ao banco
   - Executar todas as migrations
   - Verificar a estrutura criada
   - Exibir relatório completo

3. **Delete o arquivo por segurança:**
   ```bash
   rm setup_homologacoes.php
   ```

### Via Terminal (SSH)

```bash
cd /caminho/do/projeto/sgqpro
php setup_homologacoes.php
```

## 📝 Opção 2: Instalação Manual

Se preferir executar manualmente via phpMyAdmin:

1. **Acesse o phpMyAdmin**

2. **Selecione seu banco de dados**

3. **Execute o arquivo SQL:**
   - Vá em: **Importar** ou **SQL**
   - Selecione: `database/migrations/create_homologacoes_module.sql`
   - Clique em **Executar**

## 🔍 O que será criado?

### Tabelas

1. **`homologacoes`** - Dados principais das homologações
2. **`homologacoes_responsaveis`** - Múltiplos responsáveis (many-to-many)
3. **`homologacoes_historico`** - Log de mudanças de status
4. **`homologacoes_anexos`** - Arquivos em BLOB (até 16MB cada)

### Colunas Adicionais

- **`users.department`** - Identifica departamento (Compras, Logistica)

### Permissões

- Módulo `homologacoes` configurado para perfis Admin e Super Admin

## ✨ Funcionalidades do Módulo

### Interface Kanban

- 4 colunas: Pendente Recebimento, Em Análise, Aprovado, Reprovado
- Drag & drop de cartões
- Cores por status
- Filtros e busca

### Formulário de Solicitação

- Código produto (com tooltip ERP)
- Descrição completa
- Fornecedor
- Motivo da homologação
- Múltiplos responsáveis
- Checkbox "Avisar Logística"
- Upload de anexos

### Notificações

- Email para responsáveis
- Email para departamento Logística (se marcado)
- Histórico de notificações

### Histórico

- Rastreamento completo de mudanças
- Quem moveu, quando, de/para qual status
- Observações em cada movimentação

## 🔧 Configuração Pós-Instalação

### 1. Configurar Departamentos dos Usuários

Execute no SQL:

```sql
-- Configurar usuários do departamento Compras
UPDATE users 
SET department = 'Compras' 
WHERE email IN ('compras@empresa.com', 'comprador@empresa.com');

-- Configurar usuários do departamento Logística
UPDATE users 
SET department = 'Logistica' 
WHERE email IN ('logistica@empresa.com', 'expedição@empresa.com');
```

### 2. Configurar Permissões Adicionais

Via Admin > Gerenciar Perfis:

- **Supervisor**: `view` + `edit`
- **Analista de Qualidade**: `view` + `edit`
- **Operador**: `view` apenas

### 3. Testar Funcionalidades

1. Acesse: `/homologacoes`
2. Crie uma solicitação teste
3. Mova cartão entre colunas
4. Verifique emails de notificação
5. Anexe documentos

## 🐛 Solução de Problemas

### Erro: "Table already exists"

**Normal!** A migration usa `CREATE TABLE IF NOT EXISTS`, então tabelas existentes são ignoradas.

### Erro: "Cannot add foreign key constraint"

**Causa:** Tabela `users` ou `profiles` não existe.

**Solução:** Execute as migrations base do sistema primeiro.

### Erro: "Access denied for user"

**Causa:** Usuário do .env não tem permissões.

**Solução:** Verifique credenciais em `.env`:
```
DB_HOST=localhost
DB_NAME=seu_banco
DB_USER=seu_usuario
DB_PASS=sua_senha
```

### Departamentos não aparecem

**Causa:** Coluna `department` não foi criada.

**Solução:** Execute manualmente:
```sql
ALTER TABLE users ADD COLUMN department VARCHAR(100) DEFAULT NULL AFTER email;
```

## 📊 Verificação da Instalação

Execute no SQL para verificar:

```sql
-- Verificar tabelas
SHOW TABLES LIKE 'homologacoes%';

-- Deve retornar 4 tabelas:
-- homologacoes
-- homologacoes_anexos
-- homologacoes_historico
-- homologacoes_responsaveis

-- Verificar coluna department
SHOW COLUMNS FROM users LIKE 'department';

-- Verificar permissões
SELECT * FROM profile_permissions WHERE module = 'homologacoes';
```

## 🔒 Segurança

Após instalação bem-sucedida:

1. **Delete** o arquivo `setup_homologacoes.php`
2. **Não commit** arquivos de setup no Git
3. **Backup** do banco antes de qualquer migration

## 📚 Arquivos Relacionados

```
sgqpro/
├── database/
│   ├── migrations/
│   │   └── create_homologacoes_module.sql  ← Migration principal
│   ├── homologacoes_kanban.sql             ← SQL original (referência)
│   └── fix_homologacoes.sql                ← Fix rápido (alternativa)
├── src/
│   └── Controllers/
│       └── HomologacoesController.php      ← Controller
├── views/
│   └── homologacoes/
│       └── kanban.php                       ← Interface Kanban
└── setup_homologacoes.php                   ← Script de setup (delete após uso)
```

## ✅ Checklist Pós-Instalação

- [ ] Tabelas criadas (4 tabelas)
- [ ] Coluna `department` existe em `users`
- [ ] Permissões configuradas
- [ ] Departamentos dos usuários configurados
- [ ] Módulo acessível em `/homologacoes`
- [ ] Testes de criação funcionando
- [ ] Emails de notificação configurados
- [ ] Arquivo `setup_homologacoes.php` deletado

## 🆘 Suporte

Se encontrar problemas:

1. Verifique logs de erro PHP
2. Verifique logs do banco de dados
3. Execute verificação SQL acima
4. Revise configurações do `.env`

## 📝 Notas Importantes

- ✅ **Idempotente**: Pode executar múltiplas vezes sem causar erros
- ✅ **Seguro**: Usa `IF NOT EXISTS` e `INSERT IGNORE`
- ✅ **Verificado**: Validação automática ao final
- ⚠️ **Requer**: PHP 8.0+, MySQL 5.7+, Composer instalado

---

**Desenvolvido para SGQ OTI DJ** 🎯
**Versão do Módulo:** 1.0.0
**Data:** Outubro 2024
