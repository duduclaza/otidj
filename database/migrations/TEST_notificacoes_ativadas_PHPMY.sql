/* ===== SCRIPT DE TESTE: Sistema de Notificações Ativadas/Desativadas ===== */
/* Data: 09/10/2025 */
/* Descrição: Testes para validar funcionamento do sistema */
/* EXECUTAR UM TESTE POR VEZ NO PHPMYADMIN */

/* ===== TESTE 1: Verificar se coluna existe ===== */
SELECT 
    '✅ TESTE 1: Verificar coluna' as teste,
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
AND COLUMN_NAME = 'notificacoes_ativadas';

/* ===== TESTE 2: Verificar valores existentes ===== */
SELECT 
    '✅ TESTE 2: Status atual dos usuários' as teste,
    id,
    name,
    email,
    role,
    status,
    COALESCE(notificacoes_ativadas, 1) as notif_ativadas,
    CASE 
        WHEN COALESCE(notificacoes_ativadas, 1) = 1 THEN '🔔 Sino ATIVO'
        ELSE '🔕 Sino DESATIVADO'
    END as status_sino
FROM users
ORDER BY id;

/* ===== TESTE 3: Contar usuários por status de notificação ===== */
SELECT 
    '✅ TESTE 3: Estatísticas' as teste,
    COUNT(*) as total_usuarios,
    SUM(CASE WHEN COALESCE(notificacoes_ativadas, 1) = 1 THEN 1 ELSE 0 END) as com_sino,
    SUM(CASE WHEN COALESCE(notificacoes_ativadas, 1) = 0 THEN 1 ELSE 0 END) as sem_sino,
    CONCAT(
        ROUND(SUM(CASE WHEN COALESCE(notificacoes_ativadas, 1) = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1),
        '%'
    ) as percentual_ativo
FROM users;

/* ===== TESTE 4: Verificar usuários administradores ===== */
SELECT 
    '✅ TESTE 4: Admins com notificações' as teste,
    id,
    name,
    email,
    role,
    COALESCE(notificacoes_ativadas, 1) as notif_ativadas,
    CASE 
        WHEN COALESCE(notificacoes_ativadas, 1) = 1 THEN '✅ Receberá notificações'
        ELSE '❌ NÃO receberá notificações'
    END as status
FROM users
WHERE role = 'admin'
ORDER BY notificacoes_ativadas DESC, name;

/* ===== TESTE 5: Verificar usuários com aprovações ativadas ===== */
SELECT 
    '✅ TESTE 5: Aprovadores x Notificações' as teste,
    name,
    email,
    COALESCE(pode_aprovar_pops_its, 0) as pops_its,
    COALESCE(pode_aprovar_fluxogramas, 0) as fluxogramas,
    COALESCE(pode_aprovar_amostragens, 0) as amostragens,
    COALESCE(notificacoes_ativadas, 1) as notif_ativas,
    CASE 
        WHEN COALESCE(notificacoes_ativadas, 1) = 0 
             AND (pode_aprovar_pops_its = 1 OR pode_aprovar_fluxogramas = 1 OR pode_aprovar_amostragens = 1)
        THEN '⚠️ ATENÇÃO: Aprovador SEM notificações!'
        ELSE '✅ OK'
    END as alerta
FROM users
WHERE pode_aprovar_pops_its = 1 
   OR pode_aprovar_fluxogramas = 1 
   OR pode_aprovar_amostragens = 1
ORDER BY notificacoes_ativadas, name;

/* ===== TESTE 6: Relatório Final ===== */
SELECT 
    '✅ TESTE 6: Relatório Final' as teste,
    CONCAT('Total de ', COUNT(*), ' usuários cadastrados') as linha1,
    CONCAT('🔔 ', SUM(CASE WHEN COALESCE(notificacoes_ativadas, 1) = 1 THEN 1 ELSE 0 END), ' com sino ativo') as linha2,
    CONCAT('🔕 ', SUM(CASE WHEN COALESCE(notificacoes_ativadas, 1) = 0 THEN 1 ELSE 0 END), ' com sino desativado') as linha3,
    CONCAT('👥 ', SUM(CASE WHEN role = 'admin' AND COALESCE(notificacoes_ativadas, 1) = 1 THEN 1 ELSE 0 END), ' admins com notificações') as linha4
FROM users;

/* ===== RESULTADO ESPERADO ===== 
TESTE 1: Coluna deve existir com tipo TINYINT(1), DEFAULT 1
TESTE 2: Todos os usuários devem aparecer com status do sino
TESTE 3: Estatísticas gerais do sistema
TESTE 4: Admins devem preferencialmente ter sino ativado
TESTE 5: Aprovadores SEM notificações receberão alerta (problema potencial)
TESTE 6: Resumo geral

⚠️ IMPORTANTE:
- Usuários aprovadores DEVEM ter notificações ativadas
- Caso contrário, não receberão emails de pendências
*/
