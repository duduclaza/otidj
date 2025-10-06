-- ======================================================================
-- MIGRATION: Adicionar coluna pode_aprovar_fluxogramas na tabela users
-- Sistema SGQ OTI DJ - Módulo Fluxogramas
-- Data: 06/10/2025
-- Versão: 2.5.0
-- ======================================================================

-- Adicionar coluna para marcar usuários que podem aprovar fluxogramas
ALTER TABLE users 
ADD COLUMN pode_aprovar_fluxogramas BOOLEAN DEFAULT FALSE COMMENT 'Se o usuário pode aprovar/reprovar fluxogramas';

-- Criar índice para performance em consultas
CREATE INDEX idx_pode_aprovar_fluxogramas ON users(pode_aprovar_fluxogramas);

-- Marcar admins existentes como aprovadores automaticamente (opcional)
UPDATE users 
SET pode_aprovar_fluxogramas = TRUE 
WHERE role = 'admin';

-- Verificar a alteração
SELECT id, name, email, role, pode_aprovar_fluxogramas 
FROM users 
WHERE pode_aprovar_fluxogramas = TRUE
ORDER BY name;

-- Contar quantos usuários podem aprovar
SELECT 
    COUNT(*) as total_aprovadores,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
    SUM(CASE WHEN role != 'admin' THEN 1 ELSE 0 END) as nao_admins
FROM users 
WHERE pode_aprovar_fluxogramas = TRUE;


-- ======================================================================
-- NOTAS DE IMPLEMENTAÇÃO
-- ======================================================================

/*
OBJETIVO:
- Permitir que administradores marquem usuários específicos como "Aprovadores de Fluxogramas"
- Esses usuários receberão emails quando houver fluxogramas pendentes de aprovação
- Similar ao sistema de POPs e ITs (pode_aprovar_pops_its)

FUNCIONALIDADES:
✅ Nova coluna booleana na tabela users
✅ Índice para consultas rápidas
✅ Admins marcados automaticamente por padrão
✅ Checkbox no cadastro de usuários (admin_usuarios)

INTEGRAÇÃO COM EMAILSERVICE:
- Método: sendFluxogramasPendentesNotification()
- Dispara quando novo fluxograma é criado
- Notifica todos os usuários com pode_aprovar_fluxogramas = TRUE

INTERFACE ADMIN:
- Checkbox "Pode Aprovar Fluxogramas" em Gerenciar Usuários
- Visível apenas para administradores
- Salva junto com outros dados do usuário

SEGURANÇA:
- Apenas admins podem alterar este campo
- Verificação no controller antes de salvar
- Auditoria via logs do sistema
*/
