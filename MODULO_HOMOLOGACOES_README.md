# 📋 MÓDULO DE HOMOLOGAÇÕES - KANBAN

## **VISÃO GERAL**

Sistema Kanban moderno estilo Trello para gerenciar homologações de produtos e serviços no SGQ-OTI DJ.

---

## ✨ **FUNCIONALIDADES**

### **1. Formulário de Solicitação**
- **Acesso restrito:** Apenas **Administradores** e usuários do departamento **Compras**
- **Campos obrigatórios:**
  - ✅ Código do produto/serviço (com tooltip informativo)
  - ✅ Descrição
  - ✅ Fornecedor
  - ✅ Motivo da homologação
  - ✅ Responsável(is) pela homologação (múltiplos)
  - ✅ Avisar logística (checkbox opcional)

### **2. Kanban Board (4 Colunas)**
- **Pendente Recebimento** (amarelo)
- **Em Análise** (azul)
- **Aprovado** (verde)
- **Reprovado** (vermelho)

### **3. Cartões Informativos**
Cada cartão exibe:
- 🏷️ Código do produto
- 📝 Descrição
- 🏢 Fornecedor
- 🔖 Motivo da homologação
- 👥 Número de responsáveis
- 📎 Anexos (se houver)
- 🚚 Indicador de notificação à logística
- 📅 Data da solicitação

### **4. Sistema de Notificações**
- **Email** para responsáveis designados
- **Email** para equipe de logística (se solicitado)
- **Sininho** (notificações in-app) para todos

### **5. Histórico de Movimentações**
- Registro completo de mudanças de status
- Quem moveu e quando
- Observações em cada movimentação

### **6. Sistema de Anexos**
- Upload de documentos
- Armazenamento em MEDIUMBLOB
- Download e visualização

---

## 🗄️ **ESTRUTURA DO BANCO DE DADOS**

### **Tabelas Criadas:**

#### **1. `homologacoes`**
- Informações principais da homologação
- Status atual no Kanban
- Referências ao criador

#### **2. `homologacoes_responsaveis`**
- Múltiplos responsáveis por homologação
- Controle de notificações enviadas

#### **3. `homologacoes_historico`**
- Log de todas as mudanças de status
- Rastreabilidade completa

#### **4. `homologacoes_anexos`**
- Documentos relacionados
- Armazenamento seguro

---

## 🔐 **PERMISSÕES**

### **Criar Homologação:**
- ✅ Administradores (Super Admin)
- ✅ Usuários com departamento = **"Compras"**
- ✅ Usuários com departamento = **"Administrativo"** ou **"Admin"**

### **Visualizar:**
- ✅ Todos com permissão `homologacoes.view`

### **Editar/Mover Cartões:**
- ✅ Usuários com permissão `homologacoes.edit`

### **Excluir:**
- ✅ Usuários com permissão `homologacoes.delete`

---

## 📊 **MOTIVOS DE HOMOLOGAÇÃO DISPONÍVEIS**

| Valor | Label |
|-------|-------|
| `novo_item` | Novo Item |
| `troca_fornecedor` | Troca de Fornecedor |
| `atualizacao_tecnica` | Atualização Técnica |
| `melhoria_custo` | Melhoria de Custo |
| `descontinuacao` | Descontinuação de Produto |
| `novo_fornecedor` | Novo Fornecedor |

---

## 🚀 **INSTALAÇÃO**

### **Passo 1: Executar SQL**
```bash
# Via phpMyAdmin ou MySQL CLI
mysql -u usuario -p sgqoti_db < database/homologacoes_kanban.sql
```

### **Passo 2: Verificar Coluna `department`**
A coluna `department` na tabela `users` é necessária para identificar usuários de Compras e Logística.

```sql
-- Verificar se existe
SHOW COLUMNS FROM users LIKE 'department';

-- Se não existir, adicionar:
ALTER TABLE users ADD COLUMN department VARCHAR(100) DEFAULT NULL AFTER email;

-- Atualizar usuários de Compras
UPDATE users SET department = 'Compras' WHERE id IN (1, 2, 3); -- IDs dos usuários de compras

-- Atualizar usuários de Logística
UPDATE users SET department = 'Logistica' WHERE id IN (4, 5, 6); -- IDs dos usuários de logística
```

### **Passo 3: Configurar Permissões**
1. Acesse **Administrativo > Gerenciar Perfis**
2. Edite os perfis que devem ter acesso
3. Marque as permissões para o módulo **"homologacoes"**:
   - ✅ **Visualizar** - Ver o Kanban
   - ✅ **Editar** - Criar e mover cartões
   - ✅ **Excluir** - Deletar homologações

### **Passo 4: Atualizar Departamentos**
Via interface web ou SQL:
```sql
-- Exemplo de atualização
UPDATE users 
SET department = 'Compras' 
WHERE email IN ('usuario1@empresa.com', 'usuario2@empresa.com');

UPDATE users 
SET department = 'Logistica' 
WHERE email IN ('usuario3@empresa.com', 'usuario4@empresa.com');
```

---

## 📋 **FLUXO DE USO**

### **1. Solicitação (Compras)**
1. Usuário de **Compras** acessa `/homologacoes`
2. Preenche o formulário "Solicitação de Homologação"
3. Seleciona **responsáveis** (pode selecionar múltiplos com Ctrl)
4. Marca **"Avisar logística"** se necessário
5. Clica em **"Solicitar Homologação"**

### **2. Notificações Automáticas**
- ✉️ Email enviado para responsáveis
- ✉️ Email enviado para logística (se marcado)
- 🔔 Notificação no sininho para todos

### **3. Movimentação no Kanban**
- Responsáveis **movem o cartão** conforme andamento:
  - **Pendente Recebimento** → **Em Análise**
  - **Em Análise** → **Aprovado** ou **Reprovado**

### **4. Histórico e Rastreabilidade**
- Cada movimentação é registrada
- Histórico completo disponível ao clicar no cartão

---

## 🎨 **INTERFACE**

### **Design Moderno:**
- 🎨 Cards estilo Trello
- 🌈 Cores por status (amarelo, azul, verde, vermelho)
- 🖱️ Hover effects e transições suaves
- 📱 Responsivo (mobile-friendly)
- ✨ Ícones visuais para melhor UX

### **Tooltip Informativo:**
- 💡 Ícone de informação no campo "Código do Produto"
- Mensagem: "Coloque o código de referência cadastrado no seu ERP"

---

## 🔧 **ARQUIVOS DO SISTEMA**

```
sgqpro/
├── database/
│   └── homologacoes_kanban.sql          # Script SQL
├── src/
│   ├── Controllers/
│   │   └── HomologacoesController.php   # Lógica do módulo
│   └── Middleware/
│       └── PermissionMiddleware.php     # Mapeamento de rotas (atualizado)
├── views/
│   └── homologacoes/
│       └── kanban.php                   # Interface Kanban
└── public/
    └── index.php                        # Rotas (atualizado)
```

---

## 📧 **NOTIFICAÇÕES POR EMAIL**

### **Template para Responsáveis:**
```
Assunto: Você foi designado como responsável - Homologação #123

Olá [Nome],

Você foi designado como responsável pela homologação:

Código: PROD-12345
Descrição: [Descrição do produto]
Fornecedor: [Nome do fornecedor]
Motivo: Novo Item

Acesse o sistema para acompanhar:
https://djbr.sgqoti.com.br/homologacoes

Atenciosamente,
Sistema SGQ-OTI DJ
```

### **Template para Logística:**
```
Assunto: Nova homologação pendente de recebimento

Olá [Nome],

Uma nova homologação foi criada e está aguardando recebimento:

Código: PROD-12345
Descrição: [Descrição do produto]
Fornecedor: [Nome do fornecedor]

Por favor, fique atento ao recebimento deste item.

Acesse: https://djbr.sgqoti.com.br/homologacoes

Atenciosamente,
Sistema SGQ-OTI DJ
```

---

## ⚡ **API ENDPOINTS**

| Método | Rota | Ação |
|--------|------|------|
| GET | `/homologacoes` | Exibir Kanban |
| POST | `/homologacoes/store` | Criar homologação |
| POST | `/homologacoes/update-status` | Mover cartão |
| GET | `/homologacoes/{id}/details` | Detalhes do cartão |
| POST | `/homologacoes/delete` | Excluir homologação |

---

## 🐛 **TROUBLESHOOTING**

### **Problema: Formulário não aparece**
**Causa:** Usuário não é Admin ou departamento não é "Compras"

**Solução:**
```sql
UPDATE users SET department = 'Compras' WHERE id = [ID_DO_USUARIO];
```

### **Problema: Notificações não são enviadas**
**Causa:** PHPMailer não configurado ou erro no SMTP

**Solução:** Verificar `.env`:
```
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=465
SMTP_USERNAME=seu@email.com
SMTP_PASSWORD=sua_senha
SMTP_FROM_EMAIL=seu@email.com
SMTP_FROM_NAME="Sistema SGQ-OTI DJ"
```

### **Problema: Erro ao criar homologação**
**Causa:** Tabelas não criadas ou permissões incorretas

**Solução:**
1. Executar `database/homologacoes_kanban.sql`
2. Verificar permissões do perfil
3. Verificar logs: `error_log()`

---

## 📈 **MELHORIAS FUTURAS**

- [ ] Drag-and-drop para mover cartões
- [ ] Filtros por fornecedor, motivo, data
- [ ] Exportação para Excel/PDF
- [ ] Dashboard com estatísticas
- [ ] Comentários nos cartões
- [ ] Etiquetas personalizadas
- [ ] Integração com WhatsApp

---

## 📝 **CHANGELOG**

### **v1.0.0 (16/10/2025)**
- ✅ Kanban com 4 colunas
- ✅ Formulário inline de criação
- ✅ Permissão por departamento (Compras)
- ✅ Múltiplos responsáveis
- ✅ Notificação à logística
- ✅ Sistema de notificações (email + sininho)
- ✅ Histórico de movimentações
- ✅ Modal de detalhes
- ✅ Design moderno estilo Trello

---

## 👥 **SUPORTE**

Para dúvidas ou problemas:
1. Verificar logs do sistema
2. Consultar este README
3. Contatar o desenvolvedor

---

**Desenvolvido para SGQ-OTI DJ**  
**Versão:** 1.0.0  
**Data:** 16/10/2025
