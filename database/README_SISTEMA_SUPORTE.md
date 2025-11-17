# 🆘 Sistema de Suporte - Documentação Completa

## 📋 Visão Geral

Sistema que permite **Administradores** solicitarem ajuda ao **Super Administrador**. 

### Fluxo:
1. **Admin** cria solicitação (texto + anexos)
2. **Super Admin** recebe e visualiza
3. **Super Admin** resolve e documenta solução
4. **Admin** acompanha status e resolução

---

## 🗂️ Estrutura do Banco de Dados

### Tabela: `suporte_solicitacoes`

```sql
CREATE TABLE `suporte_solicitacoes` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NOT NULL,
  `anexos` LONGTEXT NULL COMMENT 'JSON',
  `status` ENUM('Pendente', 'Em Análise', 'Concluído') DEFAULT 'Pendente',
  `resolucao` TEXT NULL,
  `solicitante_id` INT(11) NOT NULL,
  `resolvido_por` INT(11) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  `resolvido_em` TIMESTAMP NULL
);
```

### Status Disponíveis

| Status | Descrição | Quem pode definir |
|--------|-----------|-------------------|
| **Pendente** | Aguardando análise | Criação automática |
| **Em Análise** | Super Admin está analisando | Super Admin |
| **Concluído** | Resolvido com sucesso | Super Admin |

---

## 🚀 Instalação

### 1. Criar Tabela no Banco

Execute no phpMyAdmin:

```sql
-- Copie o conteúdo do arquivo:
-- database/create_suporte_system.sql
```

### 2. Verificar Permissões

```sql
-- Super Admin deve ter user_role = 'super_admin'
SELECT id, name, email, user_role FROM users WHERE user_role IN ('admin', 'super_admin');
```

### 3. Criar Diretório de Upload

O sistema cria automaticamente, mas você pode pré-criar:

```bash
mkdir -p storage/uploads/suporte
chmod 755 storage/uploads/suporte
```

---

## 👥 Permissões e Acesso

### Administrador (Admin)

**Pode:**
- ✅ Acessar menu "Suporte"
- ✅ Criar novas solicitações
- ✅ Adicionar texto e anexos
- ✅ Ver suas próprias solicitações
- ✅ Acompanhar status das solicitações
- ✅ Ver resolução quando concluído

**Não pode:**
- ❌ Ver solicitações de outros admins
- ❌ Alterar status
- ❌ Resolver solicitações

### Super Administrador (Super Admin)

**Pode:**
- ✅ Ver TODAS as solicitações
- ✅ Filtrar por status
- ✅ Atualizar status (Pendente → Em Análise → Concluído)
- ✅ Adicionar resolução
- ✅ Ver solicitante de cada pedido
- ✅ Baixar anexos

**Não pode:**
- ❌ Criar solicitações (apenas recebe)

---

## 📝 Funcionalidades Detalhadas

### 1. Criar Solicitação (Admin)

**Campos obrigatórios:**
- ✅ Título (255 caracteres)
- ✅ Descrição (texto longo)

**Campos opcionais:**
- 📎 Anexos (múltiplos arquivos)
- 📅 Data (automática)
- 👤 Usuário (automático)

**Validações:**
- Máximo 10MB por arquivo
- Formatos: Imagens, PDFs, documentos
- Título e descrição obrigatórios

### 2. Sistema de Anexos

**Características:**
- Múltiplos anexos por solicitação
- Armazenamento em `/storage/uploads/suporte/`
- Nome único: `suporte_{timestamp}_{random}.ext`
- Metadados salvos em JSON:
  ```json
  {
    "nome_original": "documento.pdf",
    "arquivo": "suporte_1234567890_abc123.pdf",
    "tamanho": 524288,
    "tipo": "application/pdf"
  }
  ```

**Tipos permitidos:**
- Imagens: JPG, PNG, GIF, WebP
- Documentos: PDF, DOC, DOCX, XLS, XLSX
- Compactados: ZIP, RAR
- Textos: TXT, CSV

### 3. Acompanhamento de Status

**Pendente** (🟡)
- Solicitação criada
- Aguardando análise do Super Admin

**Em Análise** (🔵)
- Super Admin está trabalhando
- Pode demorar para resolver

**Concluído** (🟢)
- Problema resolvido
- Resolução documentada
- Data de conclusão registrada

### 4. Resolução (Super Admin)

**Campos:**
- **Status**: Selecionar novo status
- **O que foi feito?**: Texto obrigatório para "Concluído"

**Informações salvas:**
- Descrição da solução
- Quem resolveu (ID do Super Admin)
- Data/hora da resolução

---

## 🎨 Interface

### Menu Sidebar

```
🏠 Início
📊 Dashboard
🆘 Suporte          ← NOVO MENU
📋 Gestão da Qualidade
⚙️ Administrativo
```

**Visível para:**
- Admin
- Super Admin

### Tela Principal (Admin)

```
┌─────────────────────────────────────┐
│ 🆘 Suporte Técnico                  │
│ [+ Nova Solicitação]                │
├─────────────────────────────────────┤
│ Data | Título | Status | Ações      │
├─────────────────────────────────────┤
│ 17/11 | Dúvida X | Pendente | 👁️ Ver│
│ 16/11 | Erro Y | Concluído | 👁️ Ver │
└─────────────────────────────────────┘
```

### Tela Principal (Super Admin)

```
┌──────────────────────────────────────────┐
│ 🆘 Suporte Técnico                       │
│ Gerenciar solicitações dos admins       │
├──────────────────────────────────────────┤
│ Data | Título | Solicitante | Status    │
├──────────────────────────────────────────┤
│ 17/11 | Dúvida X | João | 🟡 Pendente   │
│      [👁️ Ver] [✅ Resolver]              │
└──────────────────────────────────────────┘
```

### Modal de Detalhes

```
┌─────────────────────────────────┐
│ 📋 Detalhes da Solicitação      │
├─────────────────────────────────┤
│ Título: [título]                │
│ Descrição: [descrição]          │
│ Solicitante: [nome]             │
│ Data: [dd/mm/yyyy hh:mm]        │
│ Status: [badge status]          │
│                                 │
│ Anexos (2):                     │
│ • screenshot.png [Baixar]       │
│ • documento.pdf [Baixar]        │
│                                 │
│ Resolução:                      │
│ [texto da resolução]            │
│ Concluído em: [data]            │
└─────────────────────────────────┘
```

### Modal de Resolução (Super Admin)

```
┌─────────────────────────────────┐
│ ✅ Resolver Solicitação         │
├─────────────────────────────────┤
│ Status: [dropdown]              │
│ • Pendente                      │
│ • Em Análise                    │
│ • Concluído                     │
│                                 │
│ O que foi feito? *              │
│ [textarea]                      │
│                                 │
│ [Salvar] [Cancelar]             │
└─────────────────────────────────┘
```

---

## 🔗 Rotas do Sistema

### Listagem
```
GET /suporte
```
- Admin: Ver suas solicitações
- Super Admin: Ver todas

### Criar Solicitação
```
POST /suporte/store
Body: FormData (titulo, descricao, anexos[])
```

### Detalhes
```
GET /suporte/{id}/details
Response: JSON
```

### Atualizar Status
```
POST /suporte/update-status
Body: id, status, resolucao
```

### Download Anexo
```
GET /suporte/anexo/{anexoId}
```

---

## 💾 Estrutura de Arquivos

```
sgqpro/
├── database/
│   ├── create_suporte_system.sql
│   └── README_SISTEMA_SUPORTE.md
├── src/
│   └── Controllers/
│       └── SuporteController.php
├── views/
│   └── pages/
│       └── suporte/
│           └── index.php
├── public/
│   └── index.php (rotas adicionadas)
└── storage/
    └── uploads/
        └── suporte/
            └── [arquivos anexados]
```

---

## 🧪 Testes

### Testar como Admin

1. Login como Admin
2. Acessar menu "🆘 Suporte"
3. Clicar "+ Nova Solicitação"
4. Preencher formulário
5. Adicionar anexo (opcional)
6. Enviar
7. Verificar que aparece na lista
8. Clicar "👁️ Ver" para ver detalhes

### Testar como Super Admin

1. Login como Super Admin
2. Acessar menu "🆘 Suporte"
3. Ver lista de todas solicitações
4. Clicar "👁️ Ver" para ver detalhes
5. Clicar "✅ Resolver"
6. Alterar status para "Em Análise"
7. Salvar
8. Novamente "✅ Resolver"
9. Alterar para "Concluído"
10. Adicionar resolução
11. Salvar
12. Verificar que admin vê a resolução

---

## 📊 Queries Úteis

### Todas pendentes
```sql
SELECT * FROM suporte_solicitacoes 
WHERE status = 'Pendente' 
ORDER BY created_at DESC;
```

### Solicitações por admin
```sql
SELECT s.*, u.name as solicitante
FROM suporte_solicitacoes s
JOIN users u ON s.solicitante_id = u.id
WHERE s.solicitante_id = 1
ORDER BY s.created_at DESC;
```

### Estatísticas
```sql
SELECT 
    status,
    COUNT(*) as total,
    COUNT(CASE WHEN resolucao IS NOT NULL THEN 1 END) as com_resolucao
FROM suporte_solicitacoes
GROUP BY status;
```

### Tempo médio de resolução
```sql
SELECT 
    AVG(TIMESTAMPDIFF(HOUR, created_at, resolvido_em)) as horas_media
FROM suporte_solicitacoes
WHERE status = 'Concluído';
```

---

## 🐛 Troubleshooting

### Problema: Não aparece menu Suporte

**Solução:**
- Verificar que usuário tem `user_role = 'admin'` ou `'super_admin'`
- Limpar cache do navegador

### Problema: Erro ao enviar anexo

**Solução:**
- Verificar permissões do diretório `/storage/uploads/suporte/`
- Verificar tamanho máximo de upload no PHP
- Limite: 10MB por arquivo

### Problema: Super Admin não vê todas solicitações

**Solução:**
- Verificar que `user_role = 'super_admin'` (não só 'admin')

### Problema: Erro ao baixar anexo

**Solução:**
- Verificar que arquivo existe em `/storage/uploads/suporte/`
- Verificar permissões de leitura

---

## 🔐 Segurança

### Validações Implementadas

- ✅ Verificação de role (admin/super_admin)
- ✅ Admin só vê suas solicitações
- ✅ Super Admin vê todas
- ✅ Campos obrigatórios validados
- ✅ Limite de tamanho de arquivo (10MB)
- ✅ Nome único para arquivos (evita sobrescrever)

### Melhorias Futuras

- [ ] Validação de tipos de arquivo pelo MIME type
- [ ] Sanitização de nomes de arquivo
- [ ] Log de ações (auditoria)
- [ ] Notificação por email
- [ ] Sistema de prioridade

---

## 📈 Estatísticas e Relatórios

### Dashboard do Super Admin (futuro)

- Total de solicitações pendentes
- Tempo médio de resolução
- Admins que mais solicitam
- Tipos de problemas mais comuns

---

## 🎯 Exemplos de Uso

### Cenário 1: Dúvida sobre Permissões

**Admin:**
1. Título: "Como configurar permissões personalizadas?"
2. Descrição: "Preciso criar um perfil com acesso limitado..."
3. Status: Pendente

**Super Admin:**
1. Status → Em Análise
2. Resolve o problema
3. Status → Concluído
4. Resolução: "Acesse Admin > Perfis > Criar Novo..."

### Cenário 2: Erro no Sistema

**Admin:**
1. Título: "Erro ao exportar relatório"
2. Descrição: "Ao clicar em exportar Excel..."
3. Anexo: screenshot.png
4. Status: Pendente

**Super Admin:**
1. Baixa screenshot
2. Identifica problema
3. Corrige código
4. Status → Concluído
5. Resolução: "Bug corrigido na linha 245 do controller..."

---

## ✅ Checklist de Implementação

- [x] Criar tabela no banco
- [x] Criar controller
- [x] Criar view
- [x] Adicionar rotas
- [x] Adicionar menu sidebar
- [x] Testar criação de solicitação
- [x] Testar visualização
- [x] Testar resolução
- [x] Testar download de anexos
- [x] Documentação completa

---

**Versão:** 1.0  
**Data:** 17/11/2025  
**Sistema:** SGQ-OTI DJ  
**Módulo:** Suporte Técnico
