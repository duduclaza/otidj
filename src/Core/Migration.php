<?php
namespace App\Core;

use App\Config\Database;
use PDO;

class Migration
{
    private PDO $db;
    private const CURRENT_VERSION = 7;

    public function __construct()
    {
        try {
            $this->db = Database::getInstance();
        } catch (\PDOException $e) {
            // Skip migrations if connection limit exceeded
            if (strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
                throw new \Exception('Database connection limit exceeded');
            }
            throw $e;
        }
    }

    public function runMigrations(): void
    {
        try {
            $this->createMigrationsTable();
            $currentVersion = $this->getCurrentVersion();

            if ($currentVersion < 1) {
                // Version 1: Create initial tables and seed defaults
                $this->createFilialTable();
                $this->createDepartamentosTable();
                $this->createFornecedoresTable();
                $this->createParametrosRetornadosTable();
                $this->seedDefaults();
                $this->updateVersion(1);
            }
            if ($currentVersion < 2) {
                // Version 2: Fix fornecedores table
                $this->fixFornecedoresTable();
                $this->updateVersion(2);
            }
            if ($currentVersion < 3) {
                // Version 3: Create toners table
                $this->createTonersTable();
                $this->updateVersion(3);
            }
            if ($currentVersion < 4) {
                // Version 4: Create retornados table
                $this->createRetornadosTable();
                $this->migration6();
                $this->updateVersion(4);
            }
            if ($currentVersion < 5) {
                // Version 5: Add observacao column to retornados table
                $this->addObservacaoColumn();
                $this->updateVersion(5);
            }
            if ($currentVersion < 6) {
                // Version 6: Create amostragens table
                $this->migration6();
                $this->updateVersion(6);
            }
            if ($currentVersion < 7) {
                // Version 7: Create authentication tables
                $this->migration7();
                $this->updateVersion(7);
            }
        } catch (\PDOException $e) {
            // Skip migrations if connection limit exceeded
            if (strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
                return;
            }
            throw $e;
        }
    }

    private function createMigrationsTable(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            version INT NOT NULL DEFAULT 0,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    }

    private function getCurrentVersion(): int
    {
        try {
            $stmt = $this->db->query('SELECT MAX(version) as version FROM migrations');
            $result = $stmt->fetch();
            return (int)($result['version'] ?? 0);
        } catch (\PDOException $e) {
            return 0;
        }
    }

    private function updateVersion(int $version): void
    {
        $stmt = $this->db->prepare('INSERT INTO migrations (version) VALUES (:version)');
        $stmt->execute([':version' => $version]);
    }

    private function createFilialTable(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS filiais (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(150) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    }

    private function createDepartamentosTable(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS departamentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(150) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    }

    private function createFornecedoresTable(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS fornecedores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(200) NOT NULL,
            contato VARCHAR(200) NULL,
            rma VARCHAR(200) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (nome)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    }

    private function fixFornecedoresTable(): void
    {
        // Check if table exists and has correct structure
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM fornecedores LIKE 'created_at'");
            if (!$stmt->fetch()) {
                // Add missing columns if they don't exist
                $this->db->exec('ALTER TABLE fornecedores 
                    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
            }
        } catch (\PDOException $e) {
            // Table might not exist, create it
            $this->createFornecedoresTable();
        }

        // Ensure contato and rma can be NULL
        try {
            $this->db->exec('ALTER TABLE fornecedores 
                MODIFY COLUMN contato VARCHAR(200) NULL,
                MODIFY COLUMN rma VARCHAR(200) NULL');
        } catch (\PDOException $e) {
            // Ignore if columns already allow NULL
        }
    }

    private function createParametrosRetornadosTable(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS parametros_retornados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(200) NOT NULL,
            faixa_min INT NOT NULL,
            faixa_max INT NULL,
            orientacao TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    }

    private function seedDefaults(): void
    {
        // Filiais
        $filiais = ['Jundiai','Franca','Santos','Caçapava','Uberlândia','Uberaba'];
        $stmt = $this->db->query('SELECT COUNT(*) AS c FROM filiais');
        if ((int)$stmt->fetchColumn() === 0) {
            $ins = $this->db->prepare('INSERT IGNORE INTO filiais (nome) VALUES (:n)');
            foreach ($filiais as $n) { 
                $ins->execute([':n' => $n]); 
            }
        }

        // Departamentos
        $departamentos = [
            'Financeiro','Faturamento','Logística','Compras','Área Técnica','Área Técnica ADM','Comercial',
            'Implantação','Implantação ADM','Qualidade','RH','Licitações','Gerencia','Limpeza','Atendimento',
            'Controladoria','Monitoramento'
        ];
        $stmt = $this->db->query('SELECT COUNT(*) FROM departamentos');
        if ((int)$stmt->fetchColumn() === 0) {
            $ins = $this->db->prepare('INSERT IGNORE INTO departamentos (nome) VALUES (:n)');
            foreach ($departamentos as $n) { 
                $ins->execute([':n' => $n]); 
            }
        }

        // Parâmetros de Retornados
        $stmt = $this->db->query('SELECT COUNT(*) FROM parametros_retornados');
        if ((int)$stmt->fetchColumn() === 0) {
            $ins = $this->db->prepare('INSERT INTO parametros_retornados (nome, faixa_min, faixa_max, orientacao) VALUES (:nome, :min, :max, :ori)');
            $ins->execute([
                ':nome' => 'Destino Descarte',
                ':min' => 0,
                ':max' => 5,
                ':ori' => 'Se a % for <= 5%: Descarte o Toner.'
            ]);
            $ins->execute([
                ':nome' => 'Uso Interno',
                ':min' => 6,
                ':max' => 39,
                ':ori' => 'Se a % for >= 6% e <= 39%: Teste o Toner; se a qualidade estiver boa, utilize internamente para testes; se estiver ruim, descarte.'
            ]);
            $ins->execute([
                ':nome' => 'Estoque Semi Novo',
                ':min' => 40,
                ':max' => 89,
                ':ori' => 'Se a % for >= 40% e <= 89%: Teste o Toner; se a qualidade estiver boa, envie para o estoque como seminovo e marque a % na caixa; se estiver ruim, solicite garantia.'
            ]);
            $ins->execute([
                ':nome' => 'Estoque Novo',
                ':min' => 90,
                ':max' => null,
                ':ori' => 'Se a % for >= 90%: Teste o Toner; se a qualidade estiver boa, envie para o estoque como novo e marque na caixa; se estiver ruim, solicite garantia.'
            ]);
        }
    }

    private function createTonersTable(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS toners (
            id INT AUTO_INCREMENT PRIMARY KEY,
            modelo VARCHAR(200) NOT NULL,
            peso_cheio DECIMAL(8,2) NOT NULL COMMENT "Peso em gramas",
            peso_vazio DECIMAL(8,2) NOT NULL COMMENT "Peso em gramas", 
            gramatura DECIMAL(8,2) GENERATED ALWAYS AS (peso_cheio - peso_vazio) STORED COMMENT "Calculado automaticamente",
            capacidade_folhas INT NOT NULL COMMENT "Quantidade de folhas",
            preco_toner DECIMAL(10,2) NOT NULL COMMENT "Preço em reais",
            gramatura_por_folha DECIMAL(10,4) GENERATED ALWAYS AS (gramatura / capacidade_folhas) STORED COMMENT "Calculado automaticamente",
            custo_por_folha DECIMAL(10,4) GENERATED ALWAYS AS (preco_toner / capacidade_folhas) STORED COMMENT "Calculado automaticamente",
            cor ENUM("Yellow", "Magenta", "Cyan", "Black") NOT NULL,
            tipo ENUM("Original", "Compativel", "Remanufaturado") NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (modelo),
            INDEX (cor),
            INDEX (tipo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    }

    private function createRetornadosTable(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS retornados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            modelo VARCHAR(200) NOT NULL,
            modelo_cadastrado BOOLEAN DEFAULT TRUE,
            usuario VARCHAR(100) NOT NULL,
            filial VARCHAR(150) NOT NULL,
            codigo_cliente VARCHAR(50) NOT NULL,
            modo ENUM("peso", "chip") NOT NULL,
            peso_retornado DECIMAL(8,2) NULL,
            percentual_chip DECIMAL(5,2) NULL,
            gramatura_existente DECIMAL(8,2) NULL,
            percentual_restante DECIMAL(5,2) NULL,
            destino ENUM("descarte", "estoque", "uso_interno", "garantia") NOT NULL,
            valor_calculado DECIMAL(10,2) DEFAULT 0.00,
            data_registro DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_modelo (modelo),
            INDEX idx_filial (filial),
            INDEX idx_destino (destino),
            INDEX idx_data_registro (data_registro)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    }

    private function addObservacaoColumn(): void
    {
        try {
            // Check if column already exists
            $stmt = $this->db->query("SHOW COLUMNS FROM retornados LIKE 'observacao'");
            if (!$stmt->fetch()) {
                $this->db->exec('ALTER TABLE retornados ADD COLUMN observacao TEXT NULL AFTER valor_calculado');
            }
        } catch (\PDOException $e) {
            // Column might already exist or table doesn't exist
        }
    }

    private function migration6(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS amostragens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero_nf VARCHAR(100) NOT NULL,
            status ENUM("aprovado", "reprovado") NOT NULL,
            observacao TEXT NULL,
            arquivo_nf VARCHAR(255) NULL,
            evidencias JSON NULL,
            data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_numero_nf (numero_nf),
            INDEX idx_status (status),
            INDEX idx_data_registro (data_registro)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        
        // Update amostragens table to include responsaveis and fotos fields
        $this->db->exec('ALTER TABLE amostragens 
            ADD COLUMN IF NOT EXISTS responsaveis JSON NULL AFTER observacao,
            ADD COLUMN IF NOT EXISTS fotos JSON NULL AFTER responsaveis,
            MODIFY COLUMN status ENUM("pendente", "aprovado", "reprovado") DEFAULT "pendente"');
    }

    private function migration7(): void
    {
        // Create users table
        $this->db->exec('CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            setor VARCHAR(100) NULL,
            filial VARCHAR(150) NULL,
            role ENUM("admin", "user") DEFAULT "user",
            status ENUM("active", "pending", "rejected", "suspended") DEFAULT "pending",
            email_verified_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_status (status),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        
        // Add profile_photo column to users table
        $this->db->exec('ALTER TABLE users 
            ADD COLUMN IF NOT EXISTS profile_photo LONGBLOB NULL AFTER email,
            ADD COLUMN IF NOT EXISTS profile_photo_type VARCHAR(50) NULL AFTER profile_photo');

        // Create user_permissions table
        $this->db->exec('CREATE TABLE IF NOT EXISTS user_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            module VARCHAR(100) NOT NULL,
            can_view BOOLEAN DEFAULT FALSE,
            can_edit BOOLEAN DEFAULT FALSE,
            can_delete BOOLEAN DEFAULT FALSE,
            can_import BOOLEAN DEFAULT FALSE,
            can_export BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_module (user_id, module),
            INDEX idx_user_id (user_id),
            INDEX idx_module (module)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        // Create user_invitations table
        $this->db->exec('CREATE TABLE IF NOT EXISTS user_invitations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            setor VARCHAR(100) NULL,
            filial VARCHAR(150) NULL,
            message TEXT NULL,
            status ENUM("pending", "approved", "rejected") DEFAULT "pending",
            approved_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_email (email),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        // Create default admin user
        $adminEmail = "djsgqoti@sgqoti.com.br";
        $adminPassword = password_hash("Pandora@1989", PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$adminEmail]);
        
        if ($stmt->fetchColumn() == 0) {
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, status, email_verified_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute(["Administrador", $adminEmail, $adminPassword, "admin", "active"]);
            
            $adminId = $this->db->lastInsertId();
            
            // Grant all permissions to admin for all modules
            $modules = ["toners", "amostragens", "retornados", "registros", "configuracoes"];
            foreach ($modules as $module) {
                $stmt = $this->db->prepare("INSERT INTO user_permissions (user_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, 1, 1, 1, 1)");
                $stmt->execute([$adminId, $module]);
            }
        }
    }
}
