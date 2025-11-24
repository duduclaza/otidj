# 🔧 CORREÇÃO: Setor de Compras não visualiza POPs e ITs

## 📋 PROBLEMA IDENTIFICADO

O setor de Compras foi configurado para visualizar POPs e ITs, mas os usuários desse setor não conseguem enxergar os documentos.

### **Causa Raiz**

A comparação entre o campo `setor` do usuário (tabela `users`) e o nome do departamento (tabela `departamentos`) era **case-sensitive** e exata, causando falha quando havia:

- Diferenças de maiúsculas/minúsculas: `"Compras"` vs `"compras"`
- Espaços extras: `"Compras "` vs `"Compras"`
- Variações de nome: `"Compras"` vs `"Setor de Compras"`

---

## ✅ CORREÇÃO IMPLEMENTADA

### **Arquivos Modificados**

**1. `src/Controllers/PopItsController.php`**

**Método `listVisualizacao()` - Linhas 1046-1059:**
```php
// ANTES (comparação exata):
WHERE rd3.registro_id = r.id AND d3.nome = ?

// DEPOIS (comparação flexível):
WHERE rd3.registro_id = r.id 
AND (
    LOWER(TRIM(d3.nome)) = LOWER(TRIM(?))  // Case-insensitive
    OR d3.nome LIKE CONCAT('%', ?, '%')     // Departamento contém setor
    OR ? LIKE CONCAT('%', d3.nome, '%')     // Setor contém departamento
)
```

**Método `getUserDepartmentId()` - Linhas 1347-1355:**
```php
// ANTES:
LEFT JOIN departamentos d ON u.setor = d.nome

// DEPOIS:
LEFT JOIN departamentos d ON (
    LOWER(TRIM(u.setor)) = LOWER(TRIM(d.nome))
    OR d.nome LIKE CONCAT('%', u.setor, '%')
    OR u.setor LIKE CONCAT('%', d.nome, '%')
)
```

**Parâmetros da Query - Linha 1091:**
```php
// ANTES: 2 parâmetros
$stmt->execute([$user_id, $user_setor]);

// DEPOIS: 4 parâmetros (user_id + 3x user_setor para as comparações)
$stmt->execute([$user_id, $user_setor, $user_setor, $user_setor]);
```

---

## 🔍 SCRIPT DE DIAGNÓSTICO

Foi criado um script para verificar a configuração:

**Arquivo:** `verificar_setor_compras.php`

### **Como Executar**

```bash
cd c:\Users\Clayton\Desktop\sgqpro
php verificar_setor_compras.php
```

### **O que o Script Verifica**

1. ✅ Se existe departamento "Compras" cadastrado
2. ✅ Quais usuários têm setor "Compras"
3. ✅ Quais POPs/ITs estão configurados para Compras
4. ✅ Se há diferença de case ou formato nos nomes
5. ✅ Recomendações de correção

---

## 🧪 TESTE DA CORREÇÃO

### **Cenários Resolvidos**

A correção resolve os seguintes casos:

| Setor do Usuário | Nome do Departamento | Antes | Depois |
|------------------|---------------------|-------|--------|
| `"Compras"` | `"Compras"` | ✅ | ✅ |
| `"compras"` | `"Compras"` | ❌ | ✅ |
| `"COMPRAS"` | `"Compras"` | ❌ | ✅ |
| `"Compras "` (espaço) | `"Compras"` | ❌ | ✅ |
| `"Setor de Compras"` | `"Compras"` | ❌ | ✅ |
| `"Compras"` | `"Setor de Compras"` | ❌ | ✅ |

### **Passo a Passo para Testar**

1. **Login com usuário do setor Compras**
   ```
   - Email: (usuário do setor Compras)
   ```

2. **Acessar módulo POPs e ITs**
   ```
   Menu: Gestão da Qualidade → POPs e ITs
   ```

3. **Ir para aba "Visualização"**
   ```
   - Aba 4: Visualização
   ```

4. **Verificar se aparecem os POPs configurados para Compras**
   ```
   ✅ Devem aparecer POPs com "Departamentos: Compras"
   ✅ Devem aparecer POPs públicos
   ✅ Devem aparecer POPs criados pelo próprio usuário
   ```

---

## 🛠️ TROUBLESHOOTING

### **Se ainda não funcionar após a correção:**

#### **1. Verificar Logs**

Logs estão em: `storage/logs/`

Procurar por linhas como:
```
NOVA LÓGICA - Usuário 123 -> Setor: 'Compras'
VISUALIZAÇÃO - Total registros: 0
```

#### **2. Verificar no Banco de Dados**

**Checar nome do departamento:**
```sql
SELECT id, nome FROM departamentos WHERE nome LIKE '%compra%';
```

**Checar setor do usuário:**
```sql
SELECT id, name, setor FROM users WHERE setor LIKE '%compra%';
```

**Checar POPs configurados para Compras:**
```sql
SELECT r.id, t.titulo, d.nome as departamento
FROM pops_its_registros r
INNER JOIN pops_its_titulos t ON r.titulo_id = t.id
INNER JOIN pops_its_registros_departamentos rd ON r.id = rd.registro_id
INNER JOIN departamentos d ON rd.departamento_id = d.id
WHERE d.nome LIKE '%compra%' AND r.status = 'APROVADO';
```

#### **3. Possíveis Causas Restantes**

| Problema | Solução |
|----------|---------|
| Usuário sem setor configurado | Editar usuário e definir setor "Compras" |
| Departamento "Compras" não existe | Criar em Registros → Departamentos |
| POP não foi configurado para Compras | Ao criar POP, selecionar departamento "Compras" |
| Cache do navegador | Limpar cache (Ctrl + Shift + Delete) |
| Sessão antiga | Fazer logout e login novamente |

---

## 📊 LOGS DE DEBUG

Os logs ajudam a diagnosticar problemas:

```php
error_log("NOVA LÓGICA - Usuário $user_id -> Setor: '$user_setor'");
error_log("VISUALIZAÇÃO - Total registros: " . count($registros));
error_log("REGISTRO: {$titulo} - Público: {$publico} - Departamentos: {$departamentos}");
```

**Localização:** `logs/pops_its_debug.log` ou logs do servidor

---

## 🎯 COMPORTAMENTO ESPERADO

### **Para Usuários do Setor Compras:**

✅ **Veem:**
- POPs/ITs públicos (todos)
- POPs/ITs criados por eles
- POPs/ITs configurados para o departamento "Compras"

❌ **NÃO Veem:**
- POPs/ITs restritos a outros departamentos
- POPs/ITs ainda não aprovados (exceto os próprios em "Meus Registros")

### **Para Administradores:**

✅ **Veem:**
- TODOS os POPs/ITs aprovados
- Independente do departamento

---

## 📝 CHECKLIST DE VERIFICAÇÃO

Antes de reportar problema, verificar:

- [ ] Usuário tem setor "Compras" configurado na tabela `users`
- [ ] Existe departamento "Compras" na tabela `departamentos`
- [ ] POP/IT foi configurado com visibilidade "Departamentos Selecionados"
- [ ] Departamento "Compras" foi selecionado ao criar o POP/IT
- [ ] POP/IT está com status "APROVADO"
- [ ] Usuário tem permissão `pops_its_visualizacao` no seu perfil
- [ ] Cache do navegador foi limpo
- [ ] Usuário fez logout e login novamente

---

## 🔄 RESUMO DA SOLUÇÃO

A correção tornou a comparação entre setor do usuário e nome do departamento **case-insensitive** e **flexível**, resolvendo problemas de:

1. ✅ Diferenças de maiúsculas/minúsculas
2. ✅ Espaços extras no início/fim
3. ✅ Variações de nome (ex: "Compras" contido em "Setor de Compras")

A mudança foi aplicada em:
- Query de listagem de POPs (aba Visualização)
- Query de visualização individual de arquivo
- Query de busca de departamento por setor

**Status:** ✅ **CORREÇÃO APLICADA E PRONTA PARA TESTE**
