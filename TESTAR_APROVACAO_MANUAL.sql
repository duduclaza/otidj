/* ===== TESTAR APROVAÇÃO MANUALMENTE ===== */

/* 1. VER SEU ID DE USUÁRIO */
/* TROQUE 'SEU_EMAIL@AQUI.COM' pelo seu email! */
SELECT 
    'SEU USUÁRIO' as info,
    id,
    name,
    email
FROM users
WHERE email = 'SEU_EMAIL@AQUI.COM';
-- Anote o ID que aparecer

/* 2. VER ÚLTIMAS AMOSTRAGENS */
SELECT 
    'ÚLTIMAS AMOSTRAGENS' as info,
    id,
    numero_nf,
    status_final,
    aprovado_por,
    aprovado_em
FROM amostragens_2
ORDER BY id DESC
LIMIT 5;
-- Escolha um ID de amostragem para testar

/* 3. TESTAR UPDATE MANUAL */
-- IMPORTANTE: TROQUE OS VALORES ABAIXO!
-- :amostragem_id = ID da amostragem que você quer testar
-- :seu_user_id = Seu ID de usuário que apareceu no passo 1

-- DESCOMENTE E EXECUTE AS LINHAS ABAIXO:
/*
UPDATE amostragens_2 SET 
    status_final = 'Aprovado',
    aprovado_por = 1,              -- TROQUE pelo SEU ID
    aprovado_em = NOW(),
    updated_at = NOW()
WHERE id = 1;                      -- TROQUE pelo ID da amostragem
*/

/* 4. VERIFICAR SE FUNCIONOU */
SELECT 
    'VERIFICAÇÃO' as info,
    a.id,
    a.numero_nf,
    a.status_final,
    a.aprovado_por,
    aprovador.name as aprovador_nome,
    a.aprovado_em
FROM amostragens_2 a
LEFT JOIN users aprovador ON a.aprovado_por = aprovador.id
WHERE a.id = 1                     -- TROQUE pelo ID que você usou
ORDER BY a.id DESC;

/* ===== DIAGNÓSTICO ===== 

✅ SE APARECEU SEU NOME:
- Migration OK
- JOIN OK
- Problema está no código PHP ou cache

❌ SE NÃO APARECEU SEU NOME:
- Pode ser que colunas não existam
- Execute: DESCRIBE amostragens_2;

❌ SE DEU ERRO:
- Colunas não foram criadas
- Execute a migration primeiro

*/

/* ===== SE COLUNAS NÃO EXISTEM, EXECUTE ISTO: ===== */
/*
ALTER TABLE amostragens_2 
ADD COLUMN aprovado_por INT(11) NULL DEFAULT NULL 
  COMMENT 'ID do usuário que aprovou/reprovou' AFTER status_final,
ADD COLUMN aprovado_em DATETIME NULL DEFAULT NULL 
  COMMENT 'Data e hora da aprovação/reprovação' AFTER aprovado_por;

-- Se der erro de constraint já existir, ignore
ALTER TABLE amostragens_2 
ADD CONSTRAINT fk_amostragens_2_aprovado_por 
  FOREIGN KEY (aprovado_por) REFERENCES users(id) ON DELETE SET NULL;

CREATE INDEX idx_amostragens_2_aprovado_por ON amostragens_2(aprovado_por);
*/
