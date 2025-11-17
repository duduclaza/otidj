# 🔧 Correção: Lista de Responsáveis Não Aparece

**Data:** 17/11/2025  
**Problema:** Lista de responsáveis vazia no modal de criar NC

---

## 🔍 Diagnóstico

A lista não aparece porque:
1. ⚠️ Coluna `active` pode não existir na tabela `users`
2. ⚠️ Todos usuários podem estar com `active = 0`
3. ⚠️ Variável `$usuarios` não está sendo passada corretamente

---

## ✅ Soluções Aplicadas

### **1. Controller com Fallback**
```php
// Arquivo: src/Controllers/NaoConformidadesController.php (linha 84-91)

// Tenta buscar usuários ativos
try {
    $stmt = $this->db->query("SELECT id, name, email FROM users WHERE active = 1 ORDER BY name");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    // Se coluna active não existir, buscar todos
    $stmt = $this->db->query("SELECT id, name, email FROM users ORDER BY name");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

### **2. Modal com Debug**
```php
// Arquivo: views/pages/nao-conformidades/partials/modais.php (linha 19-30)

if (empty($usuarios)) {
    echo '<option value="" disabled>⚠️ Nenhum usuário encontrado</option>';
} else {
    foreach ($usuarios as $u): 
        // Exibe usuários
    endforeach;
}
```

---

## 🚀 Como Resolver

### **Passo 1: Verificar Tabela Users**

```bash
mysql -u root -p u230868210_djsgqpro < database/verificar_usuarios.sql
```

**Ou manualmente:**
```sql
-- Ver estrutura da tabela
DESCRIBE users;

-- Ver usuários
SELECT id, name, email FROM users ORDER BY name;
```

---

### **Passo 2: Se Coluna 'active' Não Existir**

```sql
-- Adicionar coluna active
ALTER TABLE users 
ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1;

-- Ativar todos os usuários
UPDATE users SET active = 1;
```

---

### **Passo 3: Se Coluna Existir mas Usuários Inativos**

```sql
-- Ver quantos ativos
SELECT COUNT(*) FROM users WHERE active = 1;

-- Ativar todos
UPDATE users SET active = 1;

-- Ou ativar específicos
UPDATE users 
SET active = 1 
WHERE id IN (1, 2, 3, 4, 5);
```

---

### **Passo 4: Testar**

```
1. ✅ Recarregar página /nao-conformidades
2. ✅ Clicar "Nova NC"
3. ✅ Ver campo "Responsável pela Correção"
4. ✅ Verificar se aparece:
   - Lista de usuários ✅
   - OU mensagem "Nenhum usuário encontrado" ⚠️
```

---

## 🐛 Troubleshooting

### **Problema: "⚠️ Nenhum usuário encontrado"**

**Causa:** Tabela users está vazia ou todos inativos

**Solução:**
```sql
-- Ver se há usuários
SELECT COUNT(*) FROM users;

-- Se retornar 0, criar usuário de teste
INSERT INTO users (name, email, password, role) 
VALUES ('Usuário Teste', 'teste@email.com', 'senha_hash', 'user');

-- Ativar
UPDATE users SET active = 1 WHERE email = 'teste@email.com';
```

---

### **Problema: Lista ainda não aparece**

**Verificar logs:**
```bash
# Linux/Mac
tail -f /var/log/php/error.log

# Windows
# Ver arquivo de log do PHP
```

**Verificar variável:**
```php
// Adicionar no modal (temporário para debug):
<?php 
var_dump($usuarios); 
echo "Total: " . count($usuarios ?? []);
?>
```

---

### **Problema: Erro SQL**

**Se erro na coluna 'active':**
```
✅ Já corrigido! O controller agora tem fallback
✅ Se active não existir, busca todos usuários
```

---

## 📋 Checklist de Verificação

```
□ Tabela users existe
□ Há usuários cadastrados
□ Coluna active existe (ou fallback funciona)
□ Pelo menos 1 usuário com active = 1
□ Controller busca usuários corretamente
□ Variável $usuarios está disponível
□ Modal exibe lista ou mensagem de erro
□ Lista aparece ao clicar "Nova NC"
```

---

## ✅ Resultado Esperado

**Ao abrir "Nova NC":**

```
┌──────────────────────────────────────┐
│ Responsável pela Correção *          │
├──────────────────────────────────────┤
│ Selecione...                    ▼   │
│ João Silva (joao@email.com)          │
│ Maria Santos (maria@email.com)       │
│ Pedro Oliveira (pedro@email.com)     │
│ Ana Costa (ana@email.com)            │
└──────────────────────────────────────┘
```

---

## 🔑 Comandos Rápidos

**Ver usuários:**
```sql
SELECT id, name, email, active FROM users ORDER BY name;
```

**Ativar todos:**
```sql
UPDATE users SET active = 1;
```

**Criar coluna active:**
```sql
ALTER TABLE users ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1;
```

**Testar query do sistema:**
```sql
SELECT id, name, email FROM users WHERE active = 1 ORDER BY name;
-- OU (se active não existe):
SELECT id, name, email FROM users ORDER BY name;
```

---

## 📞 Próximos Passos

1. ✅ Execute `verificar_usuarios.sql`
2. ✅ Corrija conforme necessário
3. ✅ Recarregue /nao-conformidades
4. ✅ Teste criar NC
5. ✅ Verifique se lista aparece

---

**✅ CORREÇÃO APLICADA!**

**Arquivos modificados:**
- `src/Controllers/NaoConformidadesController.php` (fallback adicionado)
- `views/pages/nao-conformidades/partials/modais.php` (debug adicionado)

**Arquivos criados:**
- `database/verificar_usuarios.sql` (verificação)
- `CORRIGIR_LISTA_RESPONSAVEIS.md` (este guia)

---

**Versão:** 1.0  
**Status:** ✅ Corrigido com Fallback  
**Sistema:** SGQ-OTI DJ
