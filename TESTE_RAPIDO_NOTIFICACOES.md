# 🧪 TESTE RÁPIDO - Sistema de Notificações

## ⚡ VALIDAÇÃO EM 5 MINUTOS

### **PASSO 1: Executar Migration** (30 segundos)
```bash
cd c:\Users\Clayton\Desktop\sgqpro
mysql -u u230868210_dusouza -p u230868210_djsgqpro < database/migrations/add_notificacoes_ativadas_column.sql
```

**Resultado esperado**:
```
Migration executada com sucesso!
COLUMN_NAME: notificacoes_ativadas
COLUMN_TYPE: tinyint(1)
COLUMN_DEFAULT: 1
```

---

### **PASSO 2: Testar no Admin** (2 minutos)

#### 2.1 - Criar usuário SEM notificações
1. Acesse: `https://djbr.sgqoti.com.br/admin/users`
2. Clique em "Novo Usuário"
3. Preencha dados básicos:
   - Nome: `Teste Sem Sino`
   - Email: `teste.sem.sino@temp.com`
   - Senha: `teste123`
4. **DESMARQUE** o checkbox "🔔 Notificações do Sistema Ativadas"
5. Clique em "Criar Usuário"

#### 2.2 - Validar
1. Faça logout
2. Login com: `teste.sem.sino@temp.com` / `teste123`
3. **✅ SUCESSO**: Sino NÃO aparece na sidebar
4. **❌ ERRO**: Sino aparece → Verificar migration

---

### **PASSO 3: Testar Alteração no Perfil** (2 minutos)

#### 3.1 - Ativar notificações
1. Acesse: `https://djbr.sgqoti.com.br/profile`
2. Localize "Preferências de Notificações"
3. **MARQUE** o checkbox "🔔 Receber Notificações do Sistema"
4. Aguarde mensagem verde: "Notificações ativadas com sucesso!"
5. Página recarrega automaticamente

#### 3.2 - Validar
1. Após reload, verifique sidebar
2. **✅ SUCESSO**: Sino aparece
3. **❌ ERRO**: Sino não aparece → Verificar rota

#### 3.3 - Desativar novamente
1. Volte em `/profile`
2. **DESMARQUE** o checkbox
3. Aguarde mensagem: "Notificações desativadas..."
4. Página recarrega
5. **✅ SUCESSO**: Sino desaparece

---

### **PASSO 4: Testar Admin Editando Usuário** (1 minuto)

1. Login como admin
2. Acesse: `/admin/users`
3. Edite o usuário `teste.sem.sino@temp.com`
4. **MARQUE** o checkbox de notificações
5. Salvar
6. Faça login com o usuário de teste
7. **✅ SUCESSO**: Sino aparece

---

## 🎯 CHECKLIST FINAL

- [ ] Migration executada sem erros
- [ ] Criar usuário SEM sino funciona
- [ ] Login com usuário SEM sino → sino não aparece
- [ ] Perfil permite ativar notificações
- [ ] Toast verde aparece ao ativar
- [ ] Página recarrega após alteração
- [ ] Sino aparece após ativar
- [ ] Perfil permite desativar
- [ ] Sino desaparece após desativar
- [ ] Admin pode editar preferência de outro usuário

---

## 🔍 VALIDAÇÃO SQL RÁPIDA

```sql
-- Ver usuários e status do sino
SELECT 
    id,
    name, 
    email,
    notificacoes_ativadas,
    CASE 
        WHEN notificacoes_ativadas = 1 THEN '🔔 ATIVO'
        ELSE '🔕 DESATIVADO'
    END as status_sino
FROM users
ORDER BY id DESC
LIMIT 10;
```

**Resultado esperado**:
```
+----+----------------+------------------------+----------------------+-------------+
| id | name           | email                  | notificacoes_ativadas| status_sino |
+----+----------------+------------------------+----------------------+-------------+
| 15 | Teste Sem Sino | teste.sem.sino@...     | 0                    | 🔕 DESATIV  |
| 14 | João Silva     | joao@example.com       | 1                    | 🔔 ATIVO    |
+----+----------------+------------------------+----------------------+-------------+
```

---

## 🐛 TROUBLESHOOTING RÁPIDO

### Erro: "Coluna não existe"
```bash
# Re-executar migration
mysql -u user -p database < database/migrations/add_notificacoes_ativadas_column.sql
```

### Erro: Sino não desaparece
```
1. Fazer logout completo
2. Limpar cookies do navegador
3. Login novamente
4. Verificar console (F12) para erros JS
```

### Erro: Alteração no perfil não funciona
```
1. Abrir console (F12)
2. Verificar erro de rede
3. Testar rota manualmente:
   curl -X POST https://djbr.sgqoti.com.br/api/profile/notifications \
   -d "notificacoes_ativadas=0"
```

---

## 📊 TESTE COMPLETO (OPCIONAL)

Execute todos os 10 testes SQL:
```bash
mysql -u user -p database < database/migrations/TEST_notificacoes_ativadas.sql
```

---

## ✅ RESULTADO ESPERADO

**Se todos os passos funcionaram**:

✅ Migration OK  
✅ Admin controla preferência  
✅ Usuário controla própria preferência  
✅ Sino aparece/desaparece corretamente  
✅ JavaScript não inicia quando desativado  
✅ Sessão atualiza em tempo real  

**🎉 SISTEMA PRONTO PARA PRODUÇÃO!**

---

**Tempo total**: 5-10 minutos  
**Data**: 09/10/2025  
**Versão**: 2.6.2
