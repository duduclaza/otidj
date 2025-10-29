# 🚀 Instalação Rápida - Módulo Controle de RC

## ✅ Passo a Passo

### 1️⃣ Executar Migration Principal

**Via PhpMyAdmin:**
1. Acesse PhpMyAdmin
2. Selecione o banco `u230868210_djsgqpro`
3. Clique em "SQL"
4. Cole o conteúdo de `create_controle_rc_tables.sql`
5. Clique em "Executar"

**Via MySQL CLI:**
```bash
mysql -u u230868210_djsgqpro -p u230868210_djsgqpro < database/migrations/create_controle_rc_tables.sql
```

---

### 2️⃣ Verificar Criação das Tabelas

Execute no SQL:
```sql
SHOW TABLES LIKE 'controle_rc%';
```

**Resultado esperado:**
```
controle_rc
controle_rc_evidencias
```

---

### 3️⃣ Configurar Permissões

**Via Admin Interface:**
1. Login como Admin
2. Administrativo → Gerenciar Perfis
3. Editar perfil desejado
4. Marcar permissões para módulo `controle_rc`:
   - ✅ View (visualizar)
   - ✅ Edit (criar/editar)
   - ✅ Delete (excluir)
   - ✅ Export (exportar)

**Via SQL (opcional):**
```sql
-- Exemplo: Dar todas as permissões para perfil ID 1 (Administrador)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_export)
VALUES (1, 'controle_rc', 1, 1, 1, 1);
```

---

### 4️⃣ Testar Acesso

1. Logout e login novamente
2. Acesse: **Gestão da Qualidade → Controle de RC**
3. Ou diretamente: `https://djbr.sgqoti.com.br/controle-de-rc`

---

## 🔧 Troubleshooting

### ❌ Erro: "Não pode criar a tabela (erro 150)"

**Causa:** Foreign keys tentando referenciar tabelas que não existem ou com tipos incompatíveis.

**Solução:** A migration foi corrigida SEM foreign keys. Execute novamente.

Se ainda persistir:
```sql
-- 1. Dropar tabelas se existirem
DROP TABLE IF EXISTS `controle_rc_evidencias`;
DROP TABLE IF EXISTS `controle_rc`;

-- 2. Executar migration novamente
```

---

### ❌ Erro: "Tabela já existe"

**Solução:**
```sql
-- Verificar estrutura atual
DESCRIBE controle_rc;

-- Se estrutura estiver correta, apenas continue
-- Se estrutura estiver errada, dropar e recriar
```

---

### ❌ Menu não aparece no Sidebar

**Possíveis causas:**

1. **Permissão não configurada**
   - Verificar: Admin → Gerenciar Perfis → Editar perfil
   - Módulo `controle_rc` deve ter `can_view = 1`

2. **Cache de sessão**
   - Fazer logout completo
   - Limpar cookies do navegador
   - Fazer login novamente

3. **Verificar mapeamento no Middleware**
   ```sql
   -- Verificar se rota está mapeada
   -- Arquivo: src/Middleware/PermissionMiddleware.php
   -- Linha 87-94
   ```

---

### ❌ Erro HTTP 500 ao acessar

**Causa:** Erro no código PHP (controller ou view)

**Solução:**

**1. Execute o script de verificação:**
```
https://djbr.sgqoti.com.br/verificar_controle_rc.php
```

**2. Verifique logs de erro PHP:**
- Hostinger: Painel → Advanced → Error Log
- Local: `tail -f /var/log/php_errors.log`

**3. Problemas comuns:**
- Controller não encontrado
- View não encontrada
- Erro de sintaxe PHP
- Tabela do banco não existe

---

### ❌ Erro 403 ao acessar

**Solução:**
```sql
-- Verificar se usuário tem permissão
SELECT pp.*, p.name 
FROM profile_permissions pp
JOIN profiles p ON pp.profile_id = p.id
WHERE pp.module = 'controle_rc';

-- Se não houver resultado, adicionar permissão
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_export)
VALUES (1, 'controle_rc', 1, 1, 1, 1);
```

---

### ❌ Upload de evidências falha

**Verificar configurações PHP:**
```ini
; No php.ini ou .htaccess
upload_max_filesize = 10M
post_max_size = 20M
max_file_uploads = 10
```

**Verificar no PhpMyAdmin:**
```sql
-- Tamanho MEDIUMBLOB suporta até 16MB
SHOW CREATE TABLE controle_rc_evidencias;
```

---

## 📊 Verificação Final

Execute este checklist:

```sql
-- ✅ 1. Tabelas criadas
SELECT COUNT(*) as tabelas FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'u230868210_djsgqpro' 
AND TABLE_NAME LIKE 'controle_rc%';
-- Deve retornar: 2

-- ✅ 2. Estrutura da tabela principal
DESCRIBE controle_rc;
-- Deve ter 13 colunas

-- ✅ 3. Estrutura da tabela de evidências
DESCRIBE controle_rc_evidencias;
-- Deve ter 7 colunas

-- ✅ 4. Índices criados
SHOW INDEX FROM controle_rc;
-- Deve ter 6 índices

-- ✅ 5. Permissões configuradas
SELECT * FROM profile_permissions WHERE module = 'controle_rc';
-- Deve ter pelo menos 1 linha
```

---

## 🎯 Próximos Passos

Após instalação bem-sucedida:

1. **Criar primeiro registro de teste**
2. **Testar upload de evidências**
3. **Testar busca inteligente**
4. **Testar impressão de relatório**
5. **Testar exportação múltipla**

---

## 📞 Suporte

**Problemas persistentes?**

1. Verificar logs do PHP (error.log)
2. Verificar logs do MySQL
3. Verificar console do navegador (F12)
4. Testar em modo anônimo do navegador

**Arquivos importantes:**
- Controller: `src/Controllers/ControleRcController.php`
- View: `views/pages/controle-rc/index.php`
- Migration: `database/migrations/create_controle_rc_tables.sql`
- Rotas: `public/index.php` (linhas 188-197)
- Middleware: `src/Middleware/PermissionMiddleware.php` (linhas 87-94)

---

## ✅ Status Final

Após executar todos os passos, você deve ter:

- ✅ 2 tabelas criadas no banco
- ✅ Permissões configuradas
- ✅ Menu visível no sidebar
- ✅ Acesso ao módulo funcionando
- ✅ CRUD completo operacional
- ✅ Sistema de evidências funcionando
- ✅ Busca inteligente ativa
- ✅ Impressão e exportação disponíveis

**Sistema pronto para uso em produção! 🎉**
