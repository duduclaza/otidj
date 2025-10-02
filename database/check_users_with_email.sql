-- Verificar todos os usuários e seus emails
SELECT 
    id,
    name,
    email,
    role,
    status
FROM users
ORDER BY id;

-- Verificar quantos usuários têm email
SELECT 
    COUNT(*) as total_usuarios,
    SUM(CASE WHEN email IS NOT NULL AND email != '' THEN 1 ELSE 0 END) as usuarios_com_email,
    SUM(CASE WHEN email IS NULL OR email = '' THEN 1 ELSE 0 END) as usuarios_sem_email
FROM users;

-- Verificar se há melhorias com responsáveis
SELECT 
    m.id,
    m.titulo,
    m.responsaveis,
    GROUP_CONCAT(u.name SEPARATOR ', ') as nomes_responsaveis,
    GROUP_CONCAT(u.email SEPARATOR ', ') as emails_responsaveis
FROM melhoria_continua_2 m
LEFT JOIN users u ON FIND_IN_SET(u.id, m.responsaveis)
WHERE m.responsaveis IS NOT NULL AND m.responsaveis != ''
GROUP BY m.id
LIMIT 10;
