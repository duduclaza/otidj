/* TESTE 3: Estatísticas gerais do sistema */
SELECT 
    COUNT(*) as total_usuarios,
    SUM(CASE WHEN COALESCE(notificacoes_ativadas, 1) = 1 THEN 1 ELSE 0 END) as com_sino_ativo,
    SUM(CASE WHEN COALESCE(notificacoes_ativadas, 1) = 0 THEN 1 ELSE 0 END) as com_sino_desativado,
    CONCAT(
        ROUND(SUM(CASE WHEN COALESCE(notificacoes_ativadas, 1) = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1),
        '%'
    ) as percentual_ativo
FROM users;
