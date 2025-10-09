-- ============================================
-- CORRECAO: Notificacoes POPs/ITs
-- Execute estas queries uma por uma
-- ============================================

-- PASSO 1: Criar coluna se nao existir
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS pode_aprovar_pops_its TINYINT(1) DEFAULT 0 
COMMENT 'Admin recebe emails de POPs/ITs pendentes';

-- PASSO 2: Ativar para TODOS os admins
UPDATE users 
SET pode_aprovar_pops_its = 1 
WHERE role = 'admin';

-- PASSO 3: Verificar se funcionou (copie resultado)
SELECT 
    id,
    name,
    email,
    role,
    pode_aprovar_pops_its as 'Ativado',
    CASE 
        WHEN pode_aprovar_pops_its = 1 THEN 'SIM - Recebera emails'
        ELSE 'NAO - Nao recebera'
    END as 'Status'
FROM users
WHERE role = 'admin'
ORDER BY name;
