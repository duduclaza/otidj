/* TESTE 2: Ver status de notificações de todos os usuários */
SELECT 
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
