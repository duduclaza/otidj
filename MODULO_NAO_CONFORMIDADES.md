# ⚠️ Módulo de Não Conformidades (NC)

**Data:** 17/11/2025  
**Tipo:** Módulo Core  
**Status:** ✅ Implementado

---

## 🎯 Objetivo

Sistema completo de gestão de Não Conformidades onde:
- **Admins e Super Admins** apontam NCs e designam responsáveis
- **Supervisores/Responsáveis** registram ações corretivas
- **Todos** acompanham o status e resolução
- **Super Admin** tem acesso total a tudo

---

## 📋 Fluxo do Sistema

```
1. ADMIN APONTA NC
   ├─ Título e descrição
   ├─ Designa responsável (supervisor)
   ├─ Upload de evidências
   └─ Email automático para responsável

2. RESPONSÁVEL REGISTRA AÇÃO
   ├─ Descreve ação corretiva
   ├─ Upload de evidências da ação
   ├─ NC muda para "Em Andamento"
   └─ Email automático para criador

3. SOLUÇÃO E FECHAMENTO
   ├─ Criador ou responsável marca como solucionada
   ├─ NC muda para "Solucionada"
   └─ Email para ambos
```

---

## 🔐 Controle de Permissões

### **1. Admins e Super Admins**
- ✅ Criar novas NCs
- ✅ Ver todas as NCs
- ✅ Registrar ações em qualquer NC
- ✅ Marcar qualquer NC como solucionada
- ✅ Acesso total

### **2. Supervisores/Responsáveis**
- ✅ Ver NCs onde são responsáveis
- ✅ Registrar ação corretiva nas suas NCs
- ✅ Marcar suas NCs como solucionadas

### **3. Criador da NC**
- ✅ Ver suas próprias NCs
- ✅ Marcar como solucionada (após ação registrada)
- ✅ Receber notificações

---

## 📊 Interface do Módulo

### **Sistema de 3 Abas**

**1. Pendentes** (🔴)
- NCs aguardando ação corretiva
- Mostram quem apontou e quem é o responsável

**2. Em Andamento** (🟡)
- NCs com ação corretiva registrada
- Aguardando validação e fechamento

**3. Solucionadas** (🟢)
- NCs completamente resolvidas
- Histórico completo disponível

---

## 📁 Estrutura de Arquivos

### **Backend**
```
src/Controllers/NaoConformidadesController.php
├─ index() - Página principal
├─ criar() - Criar nova NC
├─ detalhes($id) - Ver detalhes
├─ registrarAcao($id) - Registrar ação corretiva
├─ marcarSolucionada($id) - Marcar como resolvida
└─ downloadAnexo($id) - Download de evidências
```

### **Database**
```
database/nao_conformidades.sql
├─ Tabela: nao_conformidades
├─ Tabela: nao_conformidades_anexos
├─ View: vw_nao_conformidades_completo
├─ Triggers: validação de status
└─ Queries úteis para relatórios
```

### **Frontend**
```
views/pages/nao-conformidades/
├─ index.php (página principal)
├─ partials/lista_ncs.php (listagem)
├─ partials/modais.php (modais)
└─ partials/scripts.php (JavaScript)
```

---

## 🗄️ Estrutura do Banco de Dados

### **Tabela: nao_conformidades**
```sql
- id (PK)
- titulo
- descricao
- usuario_criador_id (quem apontou)
- usuario_responsavel_id (quem vai corrigir)
- usuario_acao_id (quem registrou ação)
- usuario_solucao_id (quem marcou como resolvida)
- acao_corretiva
- status (pendente | em_andamento | solucionada)
- data_acao
- data_solucao
- created_at
- updated_at
```

### **Tabela: nao_conformidades_anexos**
```sql
- id (PK)
- nc_id (FK)
- nome_arquivo
- tipo_arquivo
- tamanho_bytes
- caminho_arquivo
- tipo_anexo (evidencia_inicial | evidencia_acao | outro)
- usuario_id
- created_at
```

---

## 📧 Sistema de Notificações

### **1. Nova NC Criada**
- **Destinatário:** Responsável designado
- **Conteúdo:** Detalhes da NC, descrição, quem apontou
- **Ação:** Link para acessar o sistema

### **2. Ação Registrada**
- **Destinatário:** Criador da NC
- **Conteúdo:** Ação corretiva descrita, responsável
- **Ação:** Link para validar

### **3. NC Solucionada**
- **Destinatários:** Criador E Responsável
- **Conteúdo:** Confirmação de solução
- **Ação:** Link para histórico

---

## 🔒 Validações e Segurança

### **Backend**
- ✅ Verificação de autenticação em todos os métodos
- ✅ Validação de permissão por role (admin/super_admin)
- ✅ Validação de propriedade (criador ou responsável)
- ✅ Super Admin sempre tem acesso total
- ✅ Upload limitado: 30MB por arquivo
- ✅ Tipos permitidos: PNG, JPG, PDF, MP4
- ✅ Transações SQL para integridade
- ✅ Triggers para validar transições de status

### **Frontend**
- ✅ Botões exibidos conforme permissão
- ✅ Validação de formulários
- ✅ Confirmação antes de ações críticas
- ✅ Feedback visual (alerts)

---

## 🚀 Como Usar

### **Para Admins/Super Admins:**

**1. Criar Nova NC**
```
1. Clicar em "Nova NC"
2. Preencher título e descrição
3. Selecionar responsável (supervisor)
4. Anexar evidências (opcional)
5. Criar
```

**2. Acompanhar NCs**
```
- Ver abas: Pendentes, Em Andamento, Solucionadas
- Clicar em "Ver Detalhes" em qualquer NC
- Verificar ações registradas
- Marcar como solucionada (se aplicável)
```

### **Para Supervisores/Responsáveis:**

**1. Ver NCs Designadas**
```
- Acessar módulo
- Ver NCs na aba "Pendentes"
- Clicar em "Ver Detalhes"
```

**2. Registrar Ação Corretiva**
```
1. Clicar em "Registrar Ação"
2. Descrever ação tomada/planejada
3. Anexar evidências (opcional)
4. Salvar
```

**3. Marcar como Solucionada**
```
- Após ação implementada
- Clicar em "Marcar como Solucionada"
- Confirmar
```

---

## 📈 Recursos Adicionais

### **Anexos e Evidências**
- Suporta múltiplos arquivos por NC
- Fotos, PDFs, vídeos
- Download individual de cada anexo
- Identificação de tipo (inicial ou ação)

### **Histórico Completo**
- Quem criou e quando
- Quem registrou ação e quando
- Quem solucionou e quando
- Timeline completa de cada NC

### **Filtros e Organização**
- Separação por status automática
- Contador em cada aba
- Cards visuais por NC
- Badges coloridos por status

---

## 🔧 Configuração

### **1. Executar SQL**
```bash
# Criar tabelas e estrutura
mysql -u root -p sgqpro < database/nao_conformidades.sql
```

### **2. Verificar Módulo**
```sql
-- Verificar se módulo existe
SELECT * FROM modules WHERE key = 'nao_conformidades';

-- Se não existir, executar:
INSERT INTO modules (`key`, name, description) 
VALUES ('nao_conformidades', 'Não Conformidades', 'Gestão de NCs com apontamento e resolução');
```

### **3. Verificar Permissões**
```php
// No controller já está implementado:
$isAdmin = in_array($_SESSION['user_role'], ['admin', 'super_admin']);
$isSuperAdmin = \App\Services\PermissionService::isSuperAdmin($userId);
```

---

## 📊 Relatórios Úteis (SQL)

### **NCs Pendentes por Responsável**
```sql
SELECT 
    ur.name as responsavel,
    COUNT(*) as total_pendentes
FROM nao_conformidades nc
JOIN users ur ON nc.usuario_responsavel_id = ur.id
WHERE nc.status = 'pendente'
GROUP BY ur.id
ORDER BY total_pendentes DESC;
```

### **Tempo Médio de Resolução**
```sql
SELECT 
    AVG(DATEDIFF(data_solucao, created_at)) as media_dias
FROM nao_conformidades
WHERE status = 'solucionada';
```

### **NCs por Mês**
```sql
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as mes,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'solucionada' THEN 1 ELSE 0 END) as resolvidas
FROM nao_conformidades
GROUP BY mes
ORDER BY mes DESC;
```

---

## 🧪 Testar o Módulo

### **1. Como Admin**
```
1. Login como admin ou super_admin
2. Acessar /nao-conformidades
3. Ver botão "Nova NC" visível
4. Criar NC de teste
5. Verificar email enviado para responsável
6. Ver NC aparecer na aba "Pendentes"
```

### **2. Como Responsável**
```
1. Login como o responsável designado
2. Acessar /nao-conformidades
3. Ver NC na aba "Pendentes"
4. Clicar "Ver Detalhes"
5. Clicar "Registrar Ação"
6. Preencher e salvar
7. Ver NC mover para "Em Andamento"
```

### **3. Marcar como Solucionada**
```
1. Abrir detalhes da NC "Em Andamento"
2. Clicar "Marcar como Solucionada"
3. Confirmar
4. Ver NC mover para "Solucionadas"
5. Verificar emails enviados
```

---

## ✅ Resultado Final

**Módulo Completo Implementado:**
- ✅ Backend completo com todas as funções
- ✅ Frontend com interface intuitiva
- ✅ Sistema de permissões robusto
- ✅ Super Admin com acesso total
- ✅ Notificações por email
- ✅ Upload de evidências
- ✅ Sistema de 3 abas
- ✅ Histórico completo
- ✅ Validações e segurança
- ✅ Triggers e constraints no banco
- ✅ Views SQL para relatórios

**Pronto para uso em produção!** 🚀

---

**Versão:** 1.0  
**Status:** ✅ Completo  
**Sistema:** SGQ-OTI DJ
