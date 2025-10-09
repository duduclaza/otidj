# Sistema de Ativação/Desativação de Notificações por Usuário

## 📋 Descrição
Sistema que permite ativar ou desativar o sino de notificações individualmente para cada usuário cadastrado no sistema SGQ-OTI DJ.

## 🎯 Funcionalidade
- **Ativado (padrão)**: Usuário vê o sino de notificações na sidebar e recebe alertas visuais/sonoros
- **Desativado**: O sino não é exibido na interface e o sistema não carrega notificações para esse usuário

## 🗄️ Banco de Dados

### Nova Coluna
```sql
ALTER TABLE users ADD COLUMN notificacoes_ativadas TINYINT(1) NOT NULL DEFAULT 1 
COMMENT '1 = Notificações ativadas, 0 = Notificações desativadas';
```

**Valores:**
- `1` = Notificações ativadas (padrão)
- `0` = Notificações desativadas

## 🚀 Como Usar

### 1. Executar Migration
Execute o arquivo SQL de migração:
```bash
mysql -u [usuario] -p [database] < database/migrations/add_notificacoes_ativadas_column.sql
```

### 2. Cadastrar/Editar Usuário
No painel administrativo (`/admin/users`):

1. Clique em **"Novo Usuário"** ou **"Editar"** em um usuário existente
2. Localize a seção **"🔔 Notificações do Sistema Ativadas"** (card amarelo)
3. Marque ou desmarque o checkbox:
   - ✅ **Marcado** = Usuário verá o sino e receberá notificações
   - ❌ **Desmarcado** = Sino oculto, notificações desativadas
4. Clique em **"Criar Usuário"** ou **"Salvar Alterações"**

### 3. Verificar Funcionamento
Após salvar:
- Faça logout do sistema
- Login com o usuário configurado
- **Se ativado**: Sino aparece ao lado do botão de logout
- **Se desativado**: Sino não aparece (apenas botão de logout)

## 🔧 Implementação Técnica

### Arquivos Modificados

#### 1. **Database Migration**
`database/migrations/add_notificacoes_ativadas_column.sql`
- Adiciona coluna `notificacoes_ativadas` na tabela `users`

#### 2. **AdminController.php**
- `users()`: Inclui `notificacoes_ativadas` no SELECT
- `createUser()`: Captura e salva o campo
- `updateUser()`: Atualiza o campo dinamicamente

#### 3. **AuthController.php**
- `authenticate()`: Salva na sessão: `$_SESSION['notificacoes_ativadas']`

#### 4. **views/admin/users.php**
- Adiciona checkbox "Notificações do Sistema Ativadas"
- JavaScript preenche checkbox ao editar usuário

#### 5. **views/partials/sidebar.php**
- **PHP**: Exibe sino apenas se `$_SESSION['notificacoes_ativadas'] == true`
- **JavaScript**: Verifica existência do elemento antes de inicializar sistema

### Fluxo de Dados
```
1. Admin marca/desmarca checkbox no formulário
   ↓
2. POST para /admin/users/create ou /admin/users/update
   ↓
3. AdminController salva no banco: notificacoes_ativadas = 1 ou 0
   ↓
4. Usuário faz login
   ↓
5. AuthController carrega campo e salva em $_SESSION['notificacoes_ativadas']
   ↓
6. Sidebar verifica sessão e renderiza ou não o sino
   ↓
7. JavaScript verifica elemento e inicializa (ou não) o sistema
```

## 📊 Testes

### Teste 1: Criar Usuário com Notificações Desativadas
```sql
-- Criar usuário de teste
INSERT INTO users (name, email, password, role, status, notificacoes_ativadas)
VALUES ('Teste Sem Notif', 'teste@example.com', '$2y$10$...', 'user', 'active', 0);
```

Resultado esperado: Usuário loga mas não vê o sino.

### Teste 2: Ativar Notificações Existente
```sql
-- Ativar notificações para um usuário
UPDATE users SET notificacoes_ativadas = 1 WHERE email = 'usuario@example.com';
```

Resultado esperado: Após novo login, usuário vê o sino.

### Teste 3: Verificar Usuários sem o Campo
```sql
-- Se coluna não existir, padrão é ativado (backward compatibility)
SELECT id, name, email, 
       COALESCE(notificacoes_ativadas, 1) as notif_status 
FROM users;
```

## 🔒 Segurança
- Apenas administradores podem modificar essa configuração
- Campo validado no backend (não aceita valores diferentes de 0 ou 1)
- Sessão recarregada a cada login (não persiste alterações antigas)

## 🐛 Troubleshooting

### Problema: Coluna não existe
**Erro**: `Unknown column 'notificacoes_ativadas'`

**Solução**:
```sql
-- Verificar se coluna existe
SHOW COLUMNS FROM users LIKE 'notificacoes_ativadas';

-- Se não existir, executar migration
SOURCE database/migrations/add_notificacoes_ativadas_column.sql;
```

### Problema: Sino aparece mesmo desativado
**Causas possíveis**:
1. Sessão antiga (solução: fazer logout/login)
2. Cache do navegador (Ctrl+F5)
3. Valor incorreto no banco (verificar com SELECT)

**Verificação**:
```sql
SELECT id, name, notificacoes_ativadas FROM users WHERE email = 'usuario@example.com';
```

### Problema: JavaScript não funciona
**Verificação no Console**:
```javascript
// Abrir DevTools (F12) e verificar:
console.log('Botão notif:', document.getElementById('notificationBtn'));
console.log('Sessão notif:', <?= json_encode($_SESSION['notificacoes_ativadas'] ?? null) ?>);
```

## 📝 Notas Importantes
1. **Padrão = Ativado**: Usuários sem o campo definido terão notificações ativadas
2. **Backward Compatibility**: Sistema funciona mesmo sem a coluna (usa checagem condicional)
3. **Performance**: JavaScript não inicia se sino não existir (economiza recursos)
4. **UX**: Usuário não tem controle próprio (apenas admin pode configurar)

## 🎨 Interface

### Formulário de Usuário
```
┌─────────────────────────────────────────┐
│ 🔔 Notificações do Sistema Ativadas    │
│ ☑ Quando marcado, o usuário verá o     │
│   sino de notificações no sistema e    │
│   receberá alertas visuais e sonoros.  │
│   Quando desmarcado, o sino não será   │
│   exibido.                              │
└─────────────────────────────────────────┘
```

### Sidebar (Ativado)
```
┌────────────────────────┐
│ [Perfil] 🔔 (2) [Sair] │
└────────────────────────┘
```

### Sidebar (Desativado)
```
┌────────────────────────┐
│ [Perfil] [Sair]        │
└────────────────────────┘
```

## 📅 Data de Implementação
**09/10/2025** - Versão 2.6.2

## 👨‍💻 Autor
Sistema SGQ-OTI DJ

---

**Status**: ✅ Implementado e Testado
