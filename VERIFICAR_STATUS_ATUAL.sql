/* ===== VERIFICAR STATUS ATUAL DAS NOTIFICAÇÕES ===== */
/* Execute este arquivo para diagnosticar o problema */

/* 1. Verificar se coluna existe */
SELECT 
    'TESTE 1: Verificar se coluna existe' as teste,
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ Coluna existe'
        ELSE '❌ ERRO: Coluna não existe - Execute a migration!'
    END as resultado
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
AND COLUMN_NAME = 'notificacoes_ativadas';

/* 2. Ver seu status atual */
/* SUBSTITUA 'SEU_EMAIL@AQUI.COM' pelo seu email */
SELECT 
    'TESTE 2: Seu status atual' as teste,
    id,
    name,
    email,
    notificacoes_ativadas as valor_no_banco,
    CASE 
        WHEN notificacoes_ativadas = 1 THEN '🔔 ATIVADO (sino deve aparecer)'
        WHEN notificacoes_ativadas = 0 THEN '🔕 DESATIVADO (sino NÃO deve aparecer)'
        ELSE '❓ VALOR INVÁLIDO'
    END as status_esperado
FROM users
WHERE email = 'SEU_EMAIL@AQUI.COM';
/* ⚠️ IMPORTANTE: Troque SEU_EMAIL@AQUI.COM pelo seu email real! */

/* 3. Ver todos os usuários e seus status */
SELECT 
    'TESTE 3: Status de todos os usuários' as teste,
    id,
    name,
    email,
    notificacoes_ativadas,
    CASE 
        WHEN notificacoes_ativadas = 1 THEN '🔔'
        ELSE '🔕'
    END as sino
FROM users
ORDER BY id;

/* 4. Estatísticas gerais */
SELECT 
    'TESTE 4: Estatísticas' as teste,
    COUNT(*) as total_usuarios,
    SUM(CASE WHEN notificacoes_ativadas = 1 THEN 1 ELSE 0 END) as com_sino,
    SUM(CASE WHEN notificacoes_ativadas = 0 THEN 1 ELSE 0 END) as sem_sino
FROM users;

/* ===== RESULTADOS ESPERADOS ===== 

TESTE 1: 
- Deve mostrar "✅ Coluna existe"
- Se mostrar "❌ ERRO", execute a migration primeiro

TESTE 2:
- Veja o valor_no_banco do SEU usuário
- 0 = Desativado (sino não deve aparecer)
- 1 = Ativado (sino deve aparecer)

TESTE 3:
- Mostra todos os usuários e seus status

TESTE 4:
- Resumo geral do sistema

*/
