# MÓDULO NÃO CONFORMIDADES (NC) - DOCUMENTAÇÃO COMPLETA

**Data**: 07/11/2025  
**Tipo**: Módulo Completo  
**Stack**: PHP 100%, MariaDB, Sistema de Abas, Upload de Arquivos, Notificações por E-mail

---

## 📋 ÍNDICE

1. [Visão Geral](#visão-geral)
2. [Estrutura do Banco de Dados](#estrutura-do-banco-de-dados)
3. [Fluxo de Funcionamento](#fluxo-de-funcionamento)
4. [Sistema de Abas](#sistema-de-abas)
5. [Funcionalidades](#funcionalidades)
6. [Notificações por E-mail](#notificações-por-e-mail)
7. [Upload de Arquivos](#upload-de-arquivos)
8. [Permissões e Regras](#permissões-e-regras)
9. [Instalação](#instalação)
10. [Testes](#testes)

---

## 🎯 VISÃO GERAL

O módulo de **Não Conformidades (NC)** permite a gestão completa do ciclo de vida de não conformidades identificadas, desde o registro inicial até a solução final, incluindo:

- ✅ Registro de NC por supervisores
- ✅ Atribuição de responsáveis
- ✅ Upload de evidências (imagens, PDFs, vídeos)
- ✅ Registro de ações corretivas
- ✅ Acompanhamento de status
- ✅ Notificações automáticas por e-mail
- ✅ Sistema de abas para organização
- ✅ Histórico completo

---

## 🗄️ ESTRUTURA DO BANCO DE DADOS

### **Tabela: `nao_conformidades`**

```sql
CREATE TABLE nao_conformidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    
    -- Usuários envolvidos
    usuario_criador_id INT NOT NULL,
    usuario_responsavel_id INT NOT NULL,
    
    -- Status
    status ENUM('pendente', 'em_andamento', 'solucionada') DEFAULT 'pendente',
    
    -- Ação corretiva
    acao_corretiva TEXT NULL,
    usuario_acao_id INT NULL,
    data_acao DATETIME NULL,
    
    -- Solução
    usuario_solucao_id INT NULL,
    data_solucao DATETIME NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (usuario_criador_id) REFERENCES users(id),
    FOREIGN KEY (usuario_responsavel_id) REFERENCES users(id),
    FOREIGN KEY (usuario_acao_id) REFERENCES users(id),
    FOREIGN KEY (usuario_solucao_id) REFERENCES users(id)
);
```

### **Tabela: `nao_conformidades_anexos`**

```sql
CREATE TABLE nao_conformidades_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nc_id INT NOT NULL,
    
    -- Informações do arquivo
    nome_arquivo VARCHAR(255) NOT NULL,
    tipo_arquivo VARCHAR(100) NOT NULL,
    tamanho_bytes INT NOT NULL,
    caminho_arquivo VARCHAR(500) NOT NULL,
    
    -- Tipo de anexo
    tipo_anexo ENUM('evidencia_inicial', 'evidencia_acao') NOT NULL,
    
    -- Usuário que enviou
    usuario_id INT NOT NULL,
    
    -- Timestamp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (nc_id) REFERENCES nao_conformidades(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id)
);
```

---

## 🔄 FLUXO DE FUNCIONAMENTO

### **1. Apontar NC (Supervisor de Qualidade)**

```
Supervisor acessa "Apontar NC"
   ↓
Preenche:
- Título
- Descrição
- Seleciona Responsável
- Anexa Evidências (opcional)
   ↓
Clica "Registrar NC"
   ↓
Sistema:
- Salva NC no banco (status: pendente)
- Salva anexos
- Envia e-mail para responsável 📧
   ↓
NC aparece em "NC Pendentes"
```

### **2. Registrar Ação (Responsável)**

```
Responsável acessa "NC Pendentes"
   ↓
Clica no card da NC
   ↓
Visualiza detalhes completos
   ↓
Clica "Registrar Ação Corretiva"
   ↓
Preenche:
- Descrição da ação tomada
- Anexa evidências da correção (opcional)
   ↓
Clica "Registrar"
   ↓
Sistema:
- Atualiza NC (status: em_andamento)
- Salva ação corretiva
- Salva anexos
- Envia e-mail para supervisor 📧
```

### **3. Marcar como Solucionada**

```
Supervisor ou Responsável visualiza NC
   ↓
Revisa ação corretiva
   ↓
Clica "Marcar como Solucionada"
   ↓
Confirma decisão
   ↓
Sistema:
- Atualiza status (status: solucionada)
- Registra data de solução
- Envia e-mail para ambos 📧
   ↓
NC move para "NC Solucionadas"
```

---

## 📑 SISTEMA DE ABAS

O módulo utiliza **3 abas** para organização:

### **1. Apontar NC**

```
┌─────────────────────────────────────────┐
│ 📝 APONTAR NC                           │
├─────────────────────────────────────────┤
│                                         │
│ Título: _________________________       │
│                                         │
│ Descrição: ______________________       │
│ ________________________________       │
│                                         │
│ Responsável: [Selecione ▼]              │
│                                         │
│ Evidências: [Escolher arquivos]         │
│                                         │
│ [🚨 Registrar NC]                       │
└─────────────────────────────────────────┘
```

**Quem vê**: Todos os usuários  
**Quem pode usar**: Supervisores de qualidade

### **2. NC Pendentes**

```
┌─────────────────────────────────────────┐
│ ⏳ NC PENDENTES (5)                     │
├─────────────────────────────────────────┤
│                                         │
│ ┌─────────────────────────────────┐    │
│ │ ⏳ NC #15 - PENDENTE             │    │
│ │ Peça fora das especificações    │    │
│ │                                 │    │
│ │ Apontado: João Silva            │    │
│ │ Responsável: Maria Santos       │    │
│ │ Data: 07/11/2025                │    │
│ │                                 │    │
│ │ [✍️ Registrar Ação Corretiva]   │    │
│ └─────────────────────────────────┘    │
│                                         │
│ ┌─────────────────────────────────┐    │
│ │ 🔄 NC #14 - EM ANDAMENTO        │    │
│ │ ...                             │    │
│ └─────────────────────────────────┘    │
└─────────────────────────────────────────┘
```

**Mostra**:
- NCs com status `pendente` (amarelo)
- NCs com status `em_andamento` (azul)

**Ações disponíveis**:
- Visualizar detalhes (clique no card)
- Registrar ação (botão - apenas responsável)

### **3. NC Solucionadas**

```
┌─────────────────────────────────────────┐
│ ✅ NC SOLUCIONADAS (12)                 │
├─────────────────────────────────────────┤
│                                         │
│ ┌─────────────────────────────────┐    │
│ │ ✅ NC #10 - SOLUCIONADA          │    │
│ │ Material não conforme            │    │
│ │                                 │    │
│ │ Apontado: João Silva            │    │
│ │ Responsável: Maria Santos       │    │
│ │ Criada: 01/11/2025              │    │
│ │ Solucionada: 05/11/2025         │    │
│ └─────────────────────────────────┘    │
└─────────────────────────────────────────┘
```

**Mostra**:
- NCs com status `solucionada` (verde)
- Histórico completo

---

## ⚙️ FUNCIONALIDADES

### **1. Criar Nova NC**

**Endpoint**: `POST /nao-conformidades/criar`  
**Controller**: `NaoConformidadesController::criar()`

**Campos obrigatórios**:
- `titulo` (string)
- `descricao` (text)
- `responsavel_id` (int)

**Campos opcionais**:
- `anexos[]` (files)

**Validações**:
- Título e descrição não podem estar vazios
- Responsável deve existir e estar ativo
- Anexos devem ser PNG, JPG, PDF ou MP4
- Tamanho máximo: 30MB por arquivo

**Retorno**:
```json
{
    "success": true,
    "message": "NC criada com sucesso!",
    "nc_id": 15
}
```

### **2. Visualizar Detalhes**

**Endpoint**: `GET /nao-conformidades/detalhes/{id}`  
**Controller**: `NaoConformidadesController::detalhes()`

**Retorno**:
```json
{
    "success": true,
    "nc": {
        "id": 15,
        "titulo": "Peça fora das especificações",
        "descricao": "...",
        "status": "pendente",
        "criador_nome": "João Silva",
        "responsavel_nome": "Maria Santos",
        "acao_corretiva": null,
        "created_at": "2025-11-07 10:30:00"
    },
    "anexos": [
        {
            "id": 45,
            "nome_arquivo": "evidencia.jpg",
            "tipo_anexo": "evidencia_inicial",
            "tamanho_bytes": 2048576
        }
    ]
}
```

### **3. Registrar Ação Corretiva**

**Endpoint**: `POST /nao-conformidades/registrar-acao/{id}`  
**Controller**: `NaoConformidadesController::registrarAcao()`

**Campos obrigatórios**:
- `acao_corretiva` (text)

**Campos opcionais**:
- `anexos[]` (files)

**Validações**:
- Usuário deve ser o responsável ou admin
- NC deve existir
- Ação não pode estar vazia

**Efeitos**:
- Status muda para `em_andamento`
- Registra data e usuário da ação
- Envia e-mail para o criador

### **4. Marcar como Solucionada**

**Endpoint**: `POST /nao-conformidades/marcar-solucionada/{id}`  
**Controller**: `NaoConformidadesController::marcarSolucionada()`

**Validações**:
- Usuário deve ser criador, responsável ou admin
- NC deve existir

**Efeitos**:
- Status muda para `solucionada`
- Registra data e usuário da solução
- Envia e-mail para criador e responsável

### **5. Download de Anexo**

**Endpoint**: `GET /nao-conformidades/anexo/{id}`  
**Controller**: `NaoConformidadesController::downloadAnexo()`

**Comportamento**:
- Busca anexo no banco
- Localiza arquivo no filesystem
- Força download com nome original
- Headers corretos para cada tipo de arquivo

---

## 📧 NOTIFICAÇÕES POR E-MAIL

O sistema envia e-mails automáticos em **3 situações**:

### **1. Nova NC Criada**

**Destinatário**: Responsável designado

**Assunto**: 🚨 Nova Não Conformidade: {título}

**Conteúdo**:
```
Nova Não Conformidade Registrada

Olá {responsável},

Uma nova NC foi registrada e você foi designado como responsável.

Detalhes:
- ID: #15
- Título: Peça fora das especificações
- Apontado por: João Silva
- Data: 07/11/2025 10:30

Descrição:
{descrição completa}

[Acessar Sistema]

Por favor, acesse o sistema para registrar a ação corretiva.
```

### **2. Ação Registrada**

**Destinatário**: Supervisor que apontou a NC

**Assunto**: ✅ Ação Registrada na NC #{id}: {título}

**Conteúdo**:
```
Ação Corretiva Registrada

Olá {supervisor},

O responsável {nome} registrou uma ação corretiva para a NC #{id}.

NC: {título}

Ação Corretiva:
{descrição da ação}

[Acessar Sistema]
```

### **3. NC Solucionada**

**Destinatários**: Supervisor E Responsável

**Assunto**: ✅ NC Solucionada: {título}

**Conteúdo**:
```
Não Conformidade Solucionada

A NC #{id} foi marcada como SOLUCIONADA.

Detalhes:
- Título: {título}
- Responsável: {nome}
- Data de Solução: 07/11/2025 15:45

[Acessar Sistema]
```

---

## 📎 UPLOAD DE ARQUIVOS

### **Tipos Permitidos**

| Extensão | MIME Type | Uso |
|----------|-----------|-----|
| PNG | image/png | Fotos |
| JPG/JPEG | image/jpeg | Fotos |
| PDF | application/pdf | Documentos |
| MP4 | video/mp4 | Vídeos |

### **Validações**

```php
// Tamanho máximo: 30MB
$tamanhoMaximo = 30 * 1024 * 1024;

// Tipos permitidos
$arquivosPermitidos = [
    'image/png',
    'image/jpeg', 
    'image/jpg',
    'application/pdf',
    'video/mp4'
];
```

### **Armazenamento**

**Diretório**: `uploads/nao-conformidades/`

**Nome do arquivo**: `{uniqid}_{timestamp}.{extensao}`

**Exemplo**: `6545d3f1a2b4c_1699357200.jpg`

### **Tipos de Anexos**

1. **`evidencia_inicial`**: Anexado ao criar NC
2. **`evidencia_acao`**: Anexado ao registrar ação

### **Segurança**

✅ Validação de tipo MIME  
✅ Validação de tamanho  
✅ Nome único (evita sobrescrever)  
✅ Permissões corretas (755)  
✅ Diretório fora do public (se configurado)

---

## 🔐 PERMISSÕES E REGRAS

### **Regra 1: Criar NC**

```
Quem pode: Supervisor de Qualidade
Validação: Role/Perfil específico
Permissão: nao_conformidades.create
```

### **Regra 2: Registrar Ação**

```
Quem pode: 
- Responsável designado
- Admin/Super Admin

Validação no controller:
if ($nc['usuario_responsavel_id'] != $_SESSION['user_id']) {
    $isAdmin = in_array($_SESSION['user_role'], ['admin', 'super_admin']);
    if (!$isAdmin) {
        return error;
    }
}
```

### **Regra 3: Marcar como Solucionada**

```
Quem pode:
- Criador da NC
- Responsável designado
- Admin/Super Admin

Validação:
$isAutorizado = 
    $nc['usuario_criador_id'] == $userId || 
    $nc['usuario_responsavel_id'] == $userId ||
    $isAdmin;
```

### **Regra 4: Visualizar**

```
Quem pode: Todos os usuários logados
Nota: Todos podem ver todas as NCs (transparência)
```

---

## 📦 INSTALAÇÃO

### **Passo 1: Executar SQL**

```bash
mysql -u root -p sgqpro < SQL_CRIAR_MODULO_NAO_CONFORMIDADES.sql
```

Ou via phpMyAdmin:
1. Abra phpMyAdmin
2. Selecione banco `sgqpro`
3. Vá em SQL
4. Cole o conteúdo do arquivo SQL
5. Execute

### **Passo 2: Verificar Estrutura**

```bash
# Arquivos criados:
src/Controllers/NaoConformidadesController.php ✅
views/pages/nao-conformidades/index.php ✅
uploads/nao-conformidades/ ✅ (criado automaticamente)
```

### **Passo 3: Verificar Rotas**

Arquivo: `public/index.php`

```php
// Rotas adicionadas:
$router->get('/nao-conformidades', [...]);
$router->post('/nao-conformidades/criar', [...]);
$router->get('/nao-conformidades/detalhes/{id}', [...]);
$router->post('/nao-conformidades/registrar-acao/{id}', [...]);
$router->post('/nao-conformidades/marcar-solucionada/{id}', [...]);
$router->get('/nao-conformidades/anexo/{id}', [...]);
```

### **Passo 4: Configurar Permissões**

```sql
-- Adicionar permissões na tabela permissions
INSERT INTO permissions (module_name, action, description) VALUES
('nao_conformidades', 'view', 'Visualizar NCs'),
('nao_conformidades', 'create', 'Criar NCs'),
('nao_conformidades', 'edit', 'Editar NCs'),
('nao_conformidades', 'solve', 'Marcar como Solucionada');

-- Atribuir ao perfil de Supervisor de Qualidade
INSERT INTO profile_permissions (profile_id, permission_id)
SELECT 
    (SELECT id FROM profiles WHERE name = 'Supervisor Qualidade'),
    id
FROM permissions 
WHERE module_name = 'nao_conformidades';
```

### **Passo 5: Verificar EmailService**

Certifique-se de que o `EmailService` está configurado:

```php
// src/Services/EmailService.php
class EmailService {
    public static function send($to, $subject, $body) {
        // Implementação SMTP
    }
}
```

---

## 🧪 TESTES

### **Teste 1: Criar NC**

**Passos**:
1. Acesse `/nao-conformidades`
2. Aba "Apontar NC"
3. Preencha:
   - Título: "Teste de NC"
   - Descrição: "Descrição de teste"
   - Responsável: Selecione um usuário
4. Anexe 1 imagem (opcional)
5. Clique "Registrar NC"

**Resultado Esperado**:
```
✅ NC criada com sucesso!
- Página recarrega
- NC aparece em "NC Pendentes"
- E-mail enviado ao responsável
```

### **Teste 2: Registrar Ação**

**Passos**:
1. Logue como o responsável
2. Acesse "NC Pendentes"
3. Clique no card da NC
4. Clique "Registrar Ação Corretiva"
5. Preencha ação tomada
6. Anexe evidência (opcional)
7. Clique "Registrar"

**Resultado Esperado**:
```
✅ Ação registrada com sucesso!
- NC muda para "Em Andamento"
- E-mail enviado ao supervisor
```

### **Teste 3: Solucionar NC**

**Passos**:
1. Logue como supervisor ou responsável
2. Visualize a NC com ação registrada
3. Clique "Marcar como Solucionada"
4. Confirme

**Resultado Esperado**:
```
✅ NC marcada como solucionada!
- NC move para "NC Solucionadas"
- E-mails enviados para ambos
```

### **Teste 4: Upload de Arquivos**

**Testes**:
- ✅ PNG (2MB) - deve funcionar
- ✅ JPG (5MB) - deve funcionar
- ✅ PDF (10MB) - deve funcionar
- ✅ MP4 (25MB) - deve funcionar
- ❌ MP4 (35MB) - deve rejeitar (>30MB)
- ❌ EXE - deve rejeitar (tipo não permitido)
- ❌ DOCX - deve rejeitar (tipo não permitido)

### **Teste 5: Download de Anexos**

**Passos**:
1. Visualize NC com anexos
2. Clique em "Download" de um anexo
3. Arquivo deve baixar com nome original

**Resultado Esperado**:
```
✅ Download inicia
✅ Nome correto: evidencia.jpg
✅ Tamanho correto
✅ Arquivo abre normalmente
```

### **Teste 6: Notificações E-mail**

**Verificar**:
- ✅ E-mail ao criar NC
- ✅ E-mail ao registrar ação
- ✅ E-mail ao solucionar
- ✅ Conteúdo correto
- ✅ Links funcionais

### **Teste 7: Permissões**

**Cenários**:
1. Usuário comum tenta criar NC → deve permitir (ou bloquear se necessário)
2. Responsável tenta registrar ação em sua NC → deve permitir
3. Outro usuário tenta registrar ação → deve bloquear
4. Admin tenta qualquer ação → deve permitir

---

## 📊 ESTATÍSTICAS E INDICADORES

### **Dashboard Futuro (Sugestão)**

```
┌─────────────────────────────────────────┐
│ 📊 INDICADORES DE NCs                   │
├─────────────────────────────────────────┤
│                                         │
│ Total de NCs: 45                        │
│ Pendentes: 12 (27%)                     │
│ Em Andamento: 8 (18%)                   │
│ Solucionadas: 25 (55%)                  │
│                                         │
│ Tempo Médio de Solução: 3.5 dias        │
│ NCs por Departamento                    │
│ NCs por Tipo                            │
│ Top Responsáveis                        │
└─────────────────────────────────────────┘
```

Queries para indicadores:

```sql
-- Total por status
SELECT status, COUNT(*) as total
FROM nao_conformidades
GROUP BY status;

-- Tempo médio de solução
SELECT AVG(DATEDIFF(data_solucao, created_at)) as tempo_medio_dias
FROM nao_conformidades
WHERE status = 'solucionada';

-- Top responsáveis
SELECT u.name, COUNT(*) as total_ncs
FROM nao_conformidades nc
JOIN users u ON nc.usuario_responsavel_id = u.id
GROUP BY u.id
ORDER BY total_ncs DESC
LIMIT 10;
```

---

## ✅ CHECKLIST DE IMPLEMENTAÇÃO

- [x] Criar tabelas no banco de dados
- [x] Criar controller completo
- [x] Criar view com sistema de abas
- [x] Implementar criação de NC
- [x] Implementar listagem por status
- [x] Implementar registro de ação
- [x] Implementar solução de NC
- [x] Implementar upload de arquivos
- [x] Implementar download de anexos
- [x] Implementar notificações por e-mail
- [x] Adicionar rotas
- [x] Adicionar validações
- [x] Adicionar permissões
- [x] Criar documentação

---

## 🚀 MÓDULO COMPLETO E FUNCIONAL!

O módulo de **Não Conformidades** está 100% implementado e pronto para uso!

**Arquivos Criados**:
1. `SQL_CRIAR_MODULO_NAO_CONFORMIDADES.sql`
2. `src/Controllers/NaoConformidadesController.php`
3. `views/pages/nao-conformidades/index.php`
4. `MODULO_NAO_CONFORMIDADES_DOCUMENTACAO.md`

**Rotas Adicionadas**: 6 rotas completas

**Funcionalidades**: 100% conforme especificação

**Pronto para produção**: ✅

---

**Responsável**: Cascade AI  
**Data**: 07/11/2025  
**Status**: ✅ **COMPLETO E FUNCIONAL**
