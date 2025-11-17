# 📋 Guia de Implementação - Motivo de Recusa

## 🎯 Objetivo
Adicionar suporte para armazenar o motivo quando uma melhoria for recusada no sistema de Melhoria Contínua 2.0.

## 📦 Arquivos Criados

### 1. `update_melhoria_continua_motivo_recusa.sql`
- **Propósito**: Atualizar tabela existente
- **Uso**: Executar se a tabela já existe
- **Ação**: Adiciona/modifica coluna `observacao`

### 2. `estrutura_melhoria_continua_2.sql`
- **Propósito**: Estrutura completa da tabela
- **Uso**: Referência ou criar tabela do zero
- **Ação**: CREATE TABLE completo

### 3. `teste_motivo_recusa.sql`
- **Propósito**: Testar funcionalidade
- **Uso**: Validar implementação
- **Ação**: Testes automatizados

## 🚀 Como Executar

### Opção 1: Tabela JÁ EXISTE (Mais Comum)

1. Abra o **phpMyAdmin** ou cliente MySQL
2. Selecione o banco de dados do SGQ
3. Execute o arquivo: `update_melhoria_continua_motivo_recusa.sql`

```sql
-- Copia e cola no phpMyAdmin:
ALTER TABLE `melhoria_continua_2` 
MODIFY COLUMN `observacao` TEXT NULL 
COMMENT 'Observações gerais. Quando status=Recusada, armazena o motivo com prefixo RECUSADA:';
```

### Opção 2: Tabela NÃO EXISTE (Raro)

1. Execute o arquivo: `estrutura_melhoria_continua_2.sql`
2. Isso criará a tabela completa com todos os campos

### Opção 3: TESTAR Implementação

1. Execute: `teste_motivo_recusa.sql`
2. Verifique os resultados
3. Valide que motivo está sendo salvo corretamente

## 📊 Estrutura do Campo

### Campo: `observacao`

| Propriedade | Valor |
|-------------|-------|
| **Tipo** | TEXT |
| **Nullable** | SIM (NULL) |
| **Tamanho** | Até 65.535 caracteres |
| **Propósito** | Armazena observações gerais e motivo de recusa |

### Formato quando Recusada

**No banco:**
```
observacao = "RECUSADA: Não está alinhado com objetivos estratégicos"
```

**Na exibição:**
```php
// Remove o prefixo para mostrar apenas o motivo
REPLACE(observacao, 'RECUSADA: ', '')
// Resultado: "Não está alinhado com objetivos estratégicos"
```

## 🔍 Queries Úteis

### Ver todas as melhorias recusadas
```sql
SELECT 
    id,
    titulo,
    status,
    REPLACE(observacao, 'RECUSADA: ', '') as motivo_recusa,
    created_at,
    updated_at
FROM melhoria_continua_2
WHERE status = 'Recusada'
ORDER BY updated_at DESC;
```

### Verificar se campo existe
```sql
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'melhoria_continua_2'
  AND COLUMN_NAME = 'observacao';
```

### Atualizar manualmente uma recusa
```sql
UPDATE melhoria_continua_2 
SET 
    status = 'Recusada',
    observacao = 'RECUSADA: Seu motivo aqui',
    updated_at = NOW()
WHERE id = 123;
```

## ✅ Checklist de Implementação

- [ ] 1. Executar SQL de atualização da tabela
- [ ] 2. Verificar que coluna `observacao` existe
- [ ] 3. Testar recusa via interface (Admin)
- [ ] 4. Verificar que motivo aparece no grid
- [ ] 5. Confirmar que email é enviado
- [ ] 6. Validar que motivo está no email
- [ ] 7. Testar com diferentes usuários

## 🔐 Permissões Necessárias

**Apenas Administradores** podem:
- Alterar status para "Recusada"
- Informar motivo da recusa
- Ver modal de recusa

**Todos os usuários** podem:
- Ver motivo da recusa no grid
- Receber email com motivo (se for criador ou responsável)

## 📧 Sistema de Email

### Quando status muda para "Recusada":

**Destinatários:**
- ✅ Criador da melhoria
- ✅ Todos os responsáveis
- ✅ Remove duplicatas automaticamente

**Conteúdo inclui:**
- Status atualizado
- Motivo da recusa
- Dados completos da melhoria
- Link para visualização

## 🐛 Troubleshooting

### Problema: Coluna não existe
**Solução:** Execute `update_melhoria_continua_motivo_recusa.sql`

### Problema: Motivo não salva
**Solução:** Verifique logs do PHP e tipo do campo (deve ser TEXT)

### Problema: Email não envia
**Solução:** Verifique configurações SMTP e que usuários têm email cadastrado

### Problema: Motivo não aparece no grid
**Solução:** Limpe cache do navegador e verifique que status = 'Recusada'

## 📝 Formato dos Dados

### JSON (quando houver anexos)
```json
{
  "anexos": [...],
  "observacao": "RECUSADA: Motivo da recusa aqui"
}
```

### PHP (processamento)
```php
if ($status === 'Recusada' && !empty($motivoRecusa)) {
    $observacao = 'RECUSADA: ' . trim($motivoRecusa);
}
```

### SQL (armazenamento)
```sql
UPDATE melhoria_continua_2 
SET observacao = 'RECUSADA: [motivo]'
WHERE id = ?
```

## 🎨 Interface

### Modal de Recusa
- Abre automaticamente ao selecionar "Recusada"
- Campo obrigatório
- Validação frontend e backend
- Feedback visual

### Grid
- Exibe motivo abaixo do status badge
- Cor vermelha (#dc2626)
- Ícone de X (❌)
- Quebra de linha automática

## 🔄 Fluxo Completo

1. Admin seleciona status "Recusada"
2. Modal abre pedindo motivo
3. Admin digita e confirma
4. Sistema valida (obrigatório)
5. Salva no banco: `observacao = "RECUSADA: [motivo]"`
6. Envia email para envolvidos
7. Atualiza grid exibindo motivo
8. Cria registro no log

## 📊 Estatísticas

### Query de estatísticas
```sql
SELECT 
    status,
    COUNT(*) as total,
    COUNT(CASE WHEN observacao LIKE 'RECUSADA:%' THEN 1 END) as com_motivo
FROM melhoria_continua_2
GROUP BY status;
```

---

## 💡 Dicas

1. **Sempre** use o prefixo "RECUSADA: " ao salvar
2. **Sempre** remova o prefixo ao exibir
3. **Valide** que motivo não está vazio
4. **Envie** email para todos envolvidos
5. **Registre** no log para auditoria

---

**Versão:** 1.0  
**Data:** 17/11/2025  
**Autor:** Sistema SGQ-OTI DJ  
**Módulo:** Melhoria Contínua 2.0
