/* ===================================================================
   MIGRATION: Habilitar permissões de importação para Cadastro de Peças
   
   Data: 14/10/2025
   Descrição: Atualiza as permissões do módulo cadastro_pecas para permitir
              importação de dados via planilha Excel
   =================================================================== */

-- Atualizar permissões existentes para incluir import/export
UPDATE profile_permissions 
SET can_import = 1, 
    can_export = 1, 
    updated_at = NOW()
WHERE module = 'cadastro_pecas';

-- Verificar se as permissões foram atualizadas
SELECT 
    p.name as Perfil,
    pp.module as Módulo,
    pp.can_view as Visualizar,
    pp.can_edit as Editar,
    pp.can_delete as Excluir,
    pp.can_import as Importar,
    pp.can_export as Exportar
FROM profile_permissions pp
JOIN profiles p ON pp.profile_id = p.id
WHERE pp.module = 'cadastro_pecas'
ORDER BY p.name;
