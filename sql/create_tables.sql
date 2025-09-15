-- Tabelas para o SGQ PRO
-- Execute este script no banco de dados para criar as tabelas necessárias

-- Tabela de Filiais
CREATE TABLE IF NOT EXISTS filiais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Departamentos
CREATE TABLE IF NOT EXISTS departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Fornecedores
CREATE TABLE IF NOT EXISTS fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    contato VARCHAR(255),
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir dados iniciais para Filiais
INSERT IGNORE INTO filiais (nome) VALUES 
('Jundiaí'),
('Franca'),
('Santos'),
('Caçapava'),
('Uberlândia'),
('Uberaba');

-- Inserir dados iniciais para Departamentos
INSERT IGNORE INTO departamentos (nome) VALUES 
('Financeiro'),
('Faturamento'),
('Logística'),
('Compras'),
('Vendas'),
('RH'),
('TI'),
('Qualidade');

-- Inserir dados iniciais para Fornecedores
INSERT IGNORE INTO fornecedores (nome, contato, email) VALUES 
('Fornecedor A', '(11) 1234-5678', 'contato@fornecedora.com'),
('Fornecedor B', '(11) 8765-4321', 'vendas@fornecedorb.com'),
('Fornecedor C', '(11) 5555-5555', 'comercial@fornecedorc.com');
