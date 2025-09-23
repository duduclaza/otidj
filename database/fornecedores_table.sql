-- Criar tabela fornecedores se não existir
CREATE TABLE IF NOT EXISTS fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18),
    email VARCHAR(255),
    telefone VARCHAR(20),
    endereco TEXT,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nome (nome),
    INDEX idx_ativo (ativo)
);

-- Inserir alguns fornecedores de exemplo se a tabela estiver vazia
INSERT INTO fornecedores (nome, cnpj, email, telefone, ativo) 
SELECT * FROM (
    SELECT 'HP Inc.' as nome, '12.345.678/0001-90' as cnpj, 'contato@hp.com' as email, '(11) 1234-5678' as telefone, 1 as ativo
    UNION ALL
    SELECT 'Canon do Brasil', '23.456.789/0001-01', 'vendas@canon.com.br', '(11) 2345-6789', 1
    UNION ALL
    SELECT 'Epson Brasil', '34.567.890/0001-12', 'suporte@epson.com.br', '(11) 3456-7890', 1
    UNION ALL
    SELECT 'Brother International', '45.678.901/0001-23', 'comercial@brother.com.br', '(11) 4567-8901', 1
    UNION ALL
    SELECT 'Lexmark Brasil', '56.789.012/0001-34', 'atendimento@lexmark.com.br', '(11) 5678-9012', 1
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM fornecedores LIMIT 1);
