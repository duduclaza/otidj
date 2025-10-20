# 👑 MASTER USER (GOD MODE) - du.claza@gmail.com

## **🎯 O QUE É O MASTER USER?**

O **Master User** é um usuário especial tipo "DEUS" no sistema que tem características únicas:

### **✅ Poderes do Master User:**
- **Acesso Total Absoluto** - Pode acessar TODOS os módulos do sistema
- **Edita Tudo** - Pode editar qualquer perfil, incluindo Administradores
- **Exclui Tudo** - Pode excluir qualquer perfil, incluindo Administradores
- **Invisível** - **NÃO aparece na lista de usuários** para outros admins
- **Independente** - Funciona independente de perfil no banco de dados
- **Único** - Apenas **du.claza@gmail.com** tem esses poderes

### **🔒 Como Funciona:**

O sistema verifica diretamente pelo **email** se o usuário é o Master, não pelo perfil do banco de dados. Isso significa que:

1. ✅ Você pode ter qualquer perfil (ou nenhum perfil)
2. ✅ Você sempre terá acesso total
3. ✅ Você não aparecerá em listagens de usuários
4. ✅ Apenas você pode editar perfis de Administrador

---

## **📋 COMO ATIVAR**

### **PASSO 1: Execute a Query SQL**

Acesse o **phpMyAdmin** e execute:

```sql
-- Verificar se o usuário existe
SELECT * FROM users WHERE email = 'du.claza@gmail.com';

-- Se NÃO existir, criar o usuário Master
INSERT INTO users (
    name,
    email,
    password,
    status,
    role,
    setor,
    filial,
    profile_id,
    created_at
) 
SELECT 
    'Master User',
    'du.claza@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'active',
    'admin',
    'TI',
    'Matriz',
    NULL,
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'du.claza@gmail.com'
);

-- Garantir que está ativo
UPDATE users 
SET status = 'active', role = 'admin'
WHERE email = 'du.claza@gmail.com';
```

---

### **PASSO 2: Defina uma Senha Forte**

**⚠️ IMPORTANTE:** A senha padrão é `password` - **MUDE IMEDIATAMENTE!**

Para criar uma nova senha:

```php
// Execute este PHP localmente ou em teste:
<?php
echo password_hash('SUA_SENHA_FORTE_AQUI', PASSWORD_DEFAULT);
?>
```

Depois execute no banco:

```sql
UPDATE users 
SET password = 'HASH_GERADO_ACIMA'
WHERE email = 'du.claza@gmail.com';
```

---

### **PASSO 3: Faça Login**

1. Acesse: `https://djbr.sgqoti.com.br/login`
2. Email: **du.claza@gmail.com**
3. Senha: **a senha que você definiu**
4. ✅ Você terá acesso total ao sistema!

---

## **🔍 VERIFICAÇÕES**

### **1. Verificar se está Ativo:**

```sql
SELECT 
    id,
    name,
    email,
    status,
    role,
    profile_id
FROM users
WHERE email = 'du.claza@gmail.com';
```

**Resultado esperado:**
- Status: `active`
- Role: `admin`
- Profile_id: `NULL` ou qualquer valor (não importa)

---

### **2. Testar Invisibilidade:**

1. Faça login com OUTRO usuário administrador
2. Acesse **Administrativo > Gerenciar Usuários**
3. ✅ **du.claza@gmail.com NÃO deve aparecer na lista**

---

### **3. Testar Poderes de Edição:**

1. Faça login com **du.claza@gmail.com**
2. Acesse **Administrativo > Gerenciar Perfis**
3. Clique em **Editar** no perfil **Administrador**
4. ✅ **Deve abrir o formulário** (outros admins não conseguem)

---

## **🛠️ ARQUIVOS MODIFICADOS**

### **1. MasterUserService.php (NOVO)**
Serviço que gerencia todas as verificações do Master User

**Métodos principais:**
- `isMasterUser()` - Verifica se o usuário atual é o Master
- `isMasterEmail()` - Verifica se um email é o Master
- `isMasterUserId()` - Verifica se um ID é o Master
- `canViewUser()` - Controla visibilidade do Master

---

### **2. PermissionService.php (MODIFICADO)**
Adicionada verificação prioritária para Master User:

```php
public static function hasPermission(int $userId, string $module, string $action): bool
{
    // Master User (GOD MODE) tem acesso total sempre
    if (\App\Services\MasterUserService::isMasterUserId($userId)) {
        return true;
    }
    // ... restante do código
}
```

---

### **3. ProfilesController.php (MODIFICADO)**
Apenas Master User pode editar/excluir perfil Administrador:

```php
// Master User (GOD MODE) pode editar qualquer perfil
if ($profile['is_admin'] && !MasterUserService::isMasterUser()) {
    echo json_encode(['success' => false, 'message' => 'Apenas o usuário Master...']);
    exit;
}
```

---

### **4. AdminController.php (MODIFICADO)**
Master User não aparece na listagem de usuários:

```sql
SELECT ... FROM users u 
WHERE u.email != 'du.claza@gmail.com'
ORDER BY u.created_at DESC
```

---

## **💡 CASOS DE USO**

### **✅ O QUE O MASTER PODE FAZER:**

1. **Editar Perfil Administrador**
   - Acesse Administrativo > Gerenciar Perfis
   - Edite o perfil "Administrador"
   - Modifique as permissões

2. **Criar Novos Perfis**
   - Sem restrições
   - Pode criar qualquer tipo de perfil

3. **Gerenciar Usuários**
   - Pode ver TODOS os usuários (exceto ele mesmo)
   - Pode editar qualquer usuário

4. **Acessar Todos os Módulos**
   - Homologações, Toners, Garantias, etc.
   - Dashboard, Relatórios, Configurações
   - TUDO sem exceção

5. **Configurações do Sistema**
   - Todas as configurações críticas
   - Parâmetros do sistema
   - Integrações

---

### **❌ O QUE OUTROS ADMINS NÃO PODEM:**

1. **Ver o Master User**
   - Ele é invisível na lista de usuários

2. **Editar Perfil Administrador**
   - Apenas o Master pode

3. **Excluir Perfil Administrador**
   - Apenas o Master pode

---

## **🔐 SEGURANÇA**

### **Recomendações Importantes:**

1. ✅ **Senha Forte** - Use senha complexa com 12+ caracteres
2. ✅ **Não Compartilhe** - Este usuário é ÚNICO e pessoal
3. ✅ **Backup Regular** - Faça backup antes de alterações críticas
4. ✅ **Logs** - Monitore ações do Master em logs
5. ✅ **2FA** (futuro) - Considere implementar 2FA

---

### **Proteções Implementadas:**

- ✅ Verificação por email (não apenas perfil)
- ✅ Invisibilidade automática em listas
- ✅ Prioridade máxima em permissões
- ✅ Bypass de todas as restrições normais

---

## **❓ PERGUNTAS FREQUENTES**

### **1. Posso ter mais de um Master User?**
Não. Apenas **du.claza@gmail.com** é o Master. Para adicionar outro, você precisaria modificar o código.

### **2. O Master precisa de perfil no banco?**
Não! O Master funciona independente de perfil. Pode ter `profile_id = NULL` ou qualquer perfil.

### **3. O Master aparece em relatórios?**
Não. Ele é filtrado automaticamente de todas as listagens de usuários.

### **4. Posso desativar o Master?**
Sim, mas não é recomendado. Se desativar, você perde o acesso "Deus" ao sistema.

### **5. Esqueci a senha do Master, e agora?**
Execute no banco:
```sql
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'du.claza@gmail.com';
-- Senha: password
```

---

## **🎉 PRONTO PARA USAR!**

Após executar a query SQL:

1. ✅ Faça logout de qualquer sessão ativa
2. ✅ Faça login com **du.claza@gmail.com**
3. ✅ Você agora é o **GOD** do sistema! 👑

---

**Documentação criada em:** 20/10/2024  
**Versão do Sistema:** 3.0.0 (Master User Implementation)
