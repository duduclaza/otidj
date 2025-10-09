/* ===== VERIFICAR SE APROVAÇÃO ESTÁ SENDO REGISTRADA ===== */

/* 1. Verificar se colunas existem */
SELECT 
    'TESTE 1: Verificar colunas' as teste,
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'amostragens_2'
AND COLUMN_NAME IN ('aprovado_por', 'aprovado_em')
ORDER BY COLUMN_NAME;

/* 2. Ver amostragens aprovadas/reprovadas */
SELECT 
    'TESTE 2: Amostragens Aprovadas/Reprovadas' as teste,
    id,
    numero_nf,
    status_final,
    aprovado_por,
    aprovado_em,
    created_at,
    updated_at
FROM amostragens_2
WHERE status_final IN ('Aprovado', 'Aprovado Parcialmente', 'Reprovado')
ORDER BY id DESC
LIMIT 10;

/* 3. Ver última amostragem que você alterou */
SELECT 
    'TESTE 3: Última Amostragem Alterada' as teste,
    id,
    numero_nf,
    status_final,
    aprovado_por,
    aprovado_em,
    updated_at
FROM amostragens_2
ORDER BY updated_at DESC
LIMIT 5;

/* 4. Tentar buscar seu usuário */
/* TROQUE 'SEU_EMAIL@AQUI.COM' pelo seu email real! */
SELECT 
    'TESTE 4: Seu Usuário' as teste,
    id,
    name,
    email,
    role
FROM users
WHERE email = 'SEU_EMAIL@AQUI.COM';

/* 5. Ver se JOIN está funcionando */
SELECT 
    'TESTE 5: JOIN com Usuário Aprovador' as teste,
    a.id,
    a.numero_nf,
    a.status_final,
    a.aprovado_por as aprovador_id,
    aprovador.name as aprovador_nome,
    aprovador.email as aprovador_email,
    a.aprovado_em
FROM amostragens_2 a
LEFT JOIN users aprovador ON a.aprovado_por = aprovador.id
WHERE a.status_final IN ('Aprovado', 'Aprovado Parcialmente', 'Reprovado')
ORDER BY a.id DESC
LIMIT 10;

/* ===== RESULTADO ESPERADO ===== 

TESTE 1: 
✅ Deve mostrar 2 linhas (aprovado_por e aprovado_em)

TESTE 2:
✅ Se aprovado_por = NULL → Campo não está sendo preenchido
✅ Se aprovado_por = número → OK, mas pode ser que nome não apareça

TESTE 3:
✅ Mostra última amostragem que foi alterada
✅ Veja se updated_at mudou após você alterar status

TESTE 4:
✅ Confirma seu ID de usuário
✅ Use este ID para comparar

TESTE 5:
✅ Testa se JOIN está trazendo nome corretamente
✅ Se aprovador_nome = NULL mas aprovador_id tem valor → problema no JOIN

*/
