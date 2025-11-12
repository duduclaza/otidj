-- ============================================
-- SCRIPT: REVERTER Permissões do Módulo NPS
-- Versão: 2.7.1 ROLLBACK
-- Data: 2024
-- Descrição: Remove as permissões NPS se causaram problema
-- ============================================

-- Simplesmente deletar todas as permissões do módulo NPS
DELETE FROM profile_permissions WHERE module = 'nps';

-- Verificar se foi removido
SELECT COUNT(*) as 'Permissoes NPS Restantes (deve ser 0)' 
FROM profile_permissions 
WHERE module = 'nps';

-- Mensagem de confirmação
SELECT 'Permissões NPS removidas com sucesso! Site deve voltar ao normal.' AS Status;
