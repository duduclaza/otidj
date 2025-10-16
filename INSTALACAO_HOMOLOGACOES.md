# 🚀 INSTALAÇÃO RÁPIDA - MÓDULO HOMOLOGAÇÕES

## **EM 5 MINUTOS**

### **📋 Passo 1: Executar SQL (1 min)**

**Via phpMyAdmin:**
1. Acesse phpMyAdmin do Hostinger
2. Selecione o banco `u526527818_sgqoti_db`
3. Clique em **SQL**
4. Cole o conteúdo de `database/homologacoes_kanban.sql`
5. Clique em **Executar**

**Via Terminal:**
```bash
mysql -u u526527818_sgqoti_user -p u526527818_sgqoti_db < database/homologacoes_kanban.sql
```

---

### **👥 Passo 2: Configurar Departamentos (2 min)**

**Opção A - Via SQL (Rápido):**
```sql
-- Adicionar coluna department (se não existir)
ALTER TABLE users ADD COLUMN department VARCHAR(100) DEFAULT NULL AFTER email;

-- Configurar usuários de Compras
UPDATE users 
SET department = 'Compras' 
WHERE email IN (
    'usuario1@empresa.com',
    'usuario2@empresa.com'
);

-- Configurar usuários de Logística
UPDATE users 
SET department = 'Logistica' 
WHERE email IN (
    'usuario3@empresa.com',
    'usuario4@empresa.com'
);
```

**Opção B - Via Interface Web:**
1. Acesse **Administrativo > Gerenciar Usuários**
2. Edite cada usuário
3. Adicione campo "Departamento" no formulário (se necessário)
4. Salve

---

### **🔐 Passo 3: Configurar Permissões (1 min)**

1. Acesse: `https://djbr.sgqoti.com.br/admin/profiles`
2. Para cada perfil que deve ter acesso:
   - Clique em **"Editar"**
   - Encontre **"homologacoes"**
   - Marque: ✅ **Visualizar**, ✅ **Editar**, ✅ **Excluir**
   - Clique em **"Salvar Permissões"**

**Perfis recomendados:**
- ✅ **Administrador** - Todas as permissões
- ✅ **Compras** - Visualizar + Editar (criar homologações)
- ✅ **Qualidade** - Visualizar + Editar (mover cartões)
- ⚠️ **Outros** - Apenas Visualizar (se necessário)

---

### **✅ Passo 4: Verificar Instalação (1 min)**

**Acesse:**
```
https://djbr.sgqoti.com.br/verificar_homologacoes.php
```

Este script verifica:
- ✅ Tabelas criadas
- ✅ Coluna department configurada
- ✅ Permissões aplicadas
- ✅ Arquivos do sistema
- ✅ Usuários que podem criar homologações

**⚠️ DELETE este arquivo após verificar!**

---

### **🎯 Passo 5: Testar (30 segundos)**

1. Faça login como usuário de **Compras** ou **Admin**
2. Acesse: `https://djbr.sgqoti.com.br/homologacoes`
3. Verifique se o formulário aparece no topo
4. Crie uma homologação de teste
5. Veja o cartão aparecer na coluna **"Pendente Recebimento"**

---

## 🎉 **PRONTO!**

O módulo está instalado e funcionando.

---

## 📊 **QUICK REFERENCE**

### **URLs:**
- **Kanban:** `/homologacoes`
- **API Criar:** `POST /homologacoes/store`
- **API Mover:** `POST /homologacoes/update-status`
- **API Detalhes:** `GET /homologacoes/{id}/details`

### **Quem Pode Criar:**
- ✅ Super Admin
- ✅ Administradores
- ✅ Departamento: **Compras**
- ✅ Departamento: **Administrativo**

### **Quem Recebe Notificações:**
- ✉️ Responsáveis selecionados
- ✉️ Departamento **Logística** (se marcado)

---

## 🐛 **TROUBLESHOOTING**

### **Formulário não aparece?**
→ Verifique se usuário tem `department = 'Compras'` ou é Admin

### **Erro ao criar homologação?**
→ Execute `verificar_homologacoes.php` para diagnóstico

### **Notificações não chegam?**
→ Verifique configuração SMTP no `.env`

### **Menu não aparece no sidebar?**
→ Verifique permissões do perfil para módulo `homologacoes`

---

## 📞 **SUPORTE**

Se precisar de ajuda:
1. Execute `verificar_homologacoes.php`
2. Consulte `MODULO_HOMOLOGACOES_README.md`
3. Verifique logs do sistema

---

**🎯 Instalação completa em ~5 minutos**  
**📅 Criado: 16/10/2025**  
**🚀 Pronto para produção**
