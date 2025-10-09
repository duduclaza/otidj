/* ===== VERIFICAR CARDS DE AMOSTRAGENS - DIAGNÓSTICO ===== */

/* 1. Ver todos os status únicos e suas quantidades */
SELECT 
    'Status Únicos' as teste,
    status_final,
    COUNT(*) as quantidade
FROM amostragens_2
GROUP BY status_final
ORDER BY quantidade DESC;

/* 2. Verificar totais que deveriam aparecer nos cards */
SELECT 
    'Total' as card_nome,
    COUNT(*) as valor_esperado
FROM amostragens_2

UNION ALL

SELECT 
    'Aprovadas' as card_nome,
    COUNT(*) as valor_esperado
FROM amostragens_2
WHERE status_final IN ('Aprovado', 'Aprovado Parcialmente')

UNION ALL

SELECT 
    'Reprovadas' as card_nome,
    COUNT(*) as valor_esperado
FROM amostragens_2
WHERE status_final = 'Reprovado'

UNION ALL

SELECT 
    'Pendentes' as card_nome,
    COUNT(*) as valor_esperado
FROM amostragens_2
WHERE status_final = 'Pendente'

ORDER BY card_nome;

/* 3. Comparação detalhada */
SELECT 
    'COMPARAÇÃO DETALHADA' as info,
    (SELECT COUNT(*) FROM amostragens_2) as total_banco,
    (SELECT COUNT(*) FROM amostragens_2 WHERE status_final IN ('Aprovado', 'Aprovado Parcialmente')) as aprovadas_banco,
    (SELECT COUNT(*) FROM amostragens_2 WHERE status_final = 'Reprovado') as reprovadas_banco,
    (SELECT COUNT(*) FROM amostragens_2 WHERE status_final = 'Pendente') as pendentes_banco;

/* 4. Ver últimas 10 amostragens com seus status */
SELECT 
    'Últimas Amostragens' as teste,
    id,
    numero_nf,
    status_final,
    created_at
FROM amostragens_2
ORDER BY created_at DESC
LIMIT 10;

/* ===== INSTRUÇÕES =====

Execute cada query acima no phpMyAdmin e compare os resultados com os cards do dashboard.

QUERY 1: Mostra todos os status que existem no banco
QUERY 2: Mostra o que cada card DEVERIA mostrar
QUERY 3: Resumo geral para comparação rápida
QUERY 4: Últimas amostragens para verificar dados recentes

Depois de executar, anote:
- Total esperado: _______
- Aprovadas esperadas: _______
- Reprovadas esperadas: _______
- Pendentes esperadas: _______

E compare com o que aparece nos cards do dashboard!

*/
