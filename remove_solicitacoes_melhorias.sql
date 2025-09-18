-- ========================================
-- SCRIPT PARA REMOVER MÓDULO SOLICITAÇÃO DE MELHORIAS
-- Execute essas queries no seu banco de dados
-- ========================================

-- 1. REMOVER PERMISSÕES DO MÓDULO
DELETE FROM profile_permissions WHERE module = 'solicitacao_melhorias';

-- 2. REMOVER TABELAS (CUIDADO: Isso apagará todos os dados!)
-- Descomente as linhas abaixo apenas se você tem certeza

-- DROP TABLE IF EXISTS solicitacoes_melhorias_anexos;
-- DROP TABLE IF EXISTS solicitacoes_melhorias_responsaveis;
-- DROP TABLE IF EXISTS solicitacoes_melhorias;

-- ========================================
-- VERIFICAÇÕES (execute para confirmar)
-- ========================================

-- Verificar se as permissões foram removidas
SELECT COUNT(*) as permissoes_restantes FROM profile_permissions WHERE module = 'solicitacao_melhorias';

-- Verificar se as tabelas ainda existem
SHOW TABLES LIKE '%solicitacoes_melhorias%';

-- ========================================
-- LIMPEZA COMPLETA (opcional)
-- ========================================

-- Se você quiser remover completamente o diretório de uploads também:
-- No sistema de arquivos, remova: storage/uploads/melhorias/

-- ========================================
-- RESULTADO ESPERADO
-- ========================================
-- ✅ Permissões removidas: 0 registros encontrados
-- ✅ Tabelas removidas: Nenhuma tabela encontrada
-- ✅ Módulo completamente removido do sistema
