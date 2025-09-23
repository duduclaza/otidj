-- ===== MÓDULO GARANTIAS - ESQUEMA DO BANCO DE DADOS =====

-- Tabela principal de garantias
CREATE TABLE garantias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fornecedor_id INT NOT NULL,
    numero_nf_compras VARCHAR(50),
    numero_nf_remessa_simples VARCHAR(50),
    numero_nf_remessa_devolucao VARCHAR(50),
    numero_serie VARCHAR(100),
    numero_lote VARCHAR(100),
    numero_ticket_os VARCHAR(100),
    origem_garantia ENUM('Amostragem', 'Homologação', 'Em Campo') NOT NULL,
    status ENUM(
        'Em andamento',
        'Aguardando Fornecedor',
        'Aguardando Recebimento',
        'Aguardando Item Chegar ao laboratório',
        'Aguardando Emissão de NF',
        'Aguardando Despache',
        'Aguardando Testes',
        'Finalizado',
        'Garantia Expirada',
        'Garantia não coberta'
    ) DEFAULT 'Em andamento',
    observacao TEXT,
    total_itens INT DEFAULT 0,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_fornecedor (fornecedor_id),
    INDEX idx_status (status),
    INDEX idx_origem (origem_garantia),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
);

-- Tabela de itens da garantia
CREATE TABLE garantias_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    garantia_id INT NOT NULL,
    item VARCHAR(255) NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    defeito TEXT,
    valor_total DECIMAL(10,2) GENERATED ALWAYS AS (quantidade * valor_unitario) STORED,
    ordem INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (garantia_id) REFERENCES garantias(id) ON DELETE CASCADE,
    
    INDEX idx_garantia (garantia_id),
    INDEX idx_ordem (ordem)
);

-- Tabela de anexos das garantias
CREATE TABLE garantias_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    garantia_id INT NOT NULL,
    tipo_anexo ENUM(
        'nf_compras',
        'nf_remessa_simples', 
        'nf_remessa_devolucao',
        'outros'
    ) NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    tipo_arquivo VARCHAR(100) NOT NULL,
    tamanho_arquivo INT NOT NULL,
    conteudo_arquivo MEDIUMBLOB NOT NULL,
    descricao VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (garantia_id) REFERENCES garantias(id) ON DELETE CASCADE,
    
    INDEX idx_garantia (garantia_id),
    INDEX idx_tipo (tipo_anexo)
);

-- Trigger para atualizar totais na tabela garantias
DELIMITER $$

CREATE TRIGGER garantias_update_totals_insert
AFTER INSERT ON garantias_itens
FOR EACH ROW
BEGIN
    UPDATE garantias 
    SET 
        total_itens = (SELECT COUNT(*) FROM garantias_itens WHERE garantia_id = NEW.garantia_id),
        valor_total = (SELECT COALESCE(SUM(valor_total), 0) FROM garantias_itens WHERE garantia_id = NEW.garantia_id)
    WHERE id = NEW.garantia_id;
END$$

CREATE TRIGGER garantias_update_totals_update
AFTER UPDATE ON garantias_itens
FOR EACH ROW
BEGIN
    UPDATE garantias 
    SET 
        total_itens = (SELECT COUNT(*) FROM garantias_itens WHERE garantia_id = NEW.garantia_id),
        valor_total = (SELECT COALESCE(SUM(valor_total), 0) FROM garantias_itens WHERE garantia_id = NEW.garantia_id)
    WHERE id = NEW.garantia_id;
END$$

CREATE TRIGGER garantias_update_totals_delete
AFTER DELETE ON garantias_itens
FOR EACH ROW
BEGIN
    UPDATE garantias 
    SET 
        total_itens = (SELECT COUNT(*) FROM garantias_itens WHERE garantia_id = OLD.garantia_id),
        valor_total = (SELECT COALESCE(SUM(valor_total), 0) FROM garantias_itens WHERE garantia_id = OLD.garantia_id)
    WHERE id = OLD.garantia_id;
END$$

DELIMITER ;

-- Dar permissões completas para o perfil Administrador (se existir sistema de perfis)
-- INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
-- SELECT p.id, 'garantias', 1, 1, 1, 1, 1
-- FROM profiles p 
-- WHERE p.name = 'Administrador'
-- ON DUPLICATE KEY UPDATE 
--     can_view = 1, can_edit = 1, can_delete = 1, can_import = 1, can_export = 1;
