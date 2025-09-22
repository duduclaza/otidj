<?php
namespace App\Core;

use App\Config\Database;
use PDO;

class Migration
{
    private PDO $db;
    private const CURRENT_VERSION = 20;

    public function __construct()
    {
        try {
            $this->db = Database::getInstance();
        } catch (\PDOException $e) {
            // Skip migrations if connection limit exceeded
            if (strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
                throw new \Exception('Database connection limit exceeded');
            }

    private function migration20(): void
    {
        // Tabela principal de garantias
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS garantias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fornecedor_id INT NOT NULL,
                origem ENUM('Amostragem','Homologação','Em Campo') NOT NULL,
                status ENUM('Em andamento','Aguardando Fornecedor','Aguardando Recebimento','Aguardando Item Chegar ao laboratório','Aguardando Emissão de NF','Aguardando Despache','Aguardando Testes','Finalizado','Garantia Expirada','Garantia não coberta') NOT NULL DEFAULT 'Em andamento',
                observacoes TEXT NULL,
                nf_compra_numero VARCHAR(50) NULL,
                nf_compra_blob MEDIUMBLOB NULL,
                nf_compra_nome VARCHAR(255) NULL,
                nf_compra_tipo VARCHAR(100) NULL,
                nf_compra_tamanho INT NULL,
                nf_remessa_simples_numero VARCHAR(50) NULL,
                nf_remessa_simples_blob MEDIUMBLOB NULL,
                nf_remessa_simples_nome VARCHAR(255) NULL,
                nf_remessa_simples_tipo VARCHAR(100) NULL,
                nf_remessa_simples_tamanho INT NULL,
                nf_devolucao_numero VARCHAR(50) NULL,
                nf_devolucao_blob MEDIUMBLOB NULL,
                nf_devolucao_nome VARCHAR(255) NULL,
                nf_devolucao_tipo VARCHAR(100) NULL,
                nf_devolucao_tamanho INT NULL,
                numero_serie VARCHAR(100) NULL,
                numero_lote VARCHAR(100) NULL,
                numero_ticket_os VARCHAR(100) NULL,
                created_by INT NOT NULL,
                updated_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE RESTRICT,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_fornecedor (fornecedor_id),
                INDEX idx_status (status),
                INDEX idx_origem (origem)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registros de garantias'
        ");

        // Itens da garantia
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS garantias_itens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                garantia_id INT NOT NULL,
                item_descricao VARCHAR(255) NOT NULL,
                quantidade INT NOT NULL,
                valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                defeito VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (garantia_id) REFERENCES garantias(id) ON DELETE CASCADE,
                INDEX idx_garantia (garantia_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Itens vinculados à garantia'
        ");

        // Anexos diversos (até 5 por solicitação via aplicação)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS garantias_anexos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                garantia_id INT NOT NULL,
                tipo ENUM('outro','evidencia') NOT NULL DEFAULT 'outro',
                arquivo_blob MEDIUMBLOB NOT NULL,
                arquivo_nome VARCHAR(255) NOT NULL,
                arquivo_tipo VARCHAR(100) NOT NULL,
                arquivo_tamanho INT NOT NULL,
                uploaded_by INT NOT NULL,
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (garantia_id) REFERENCES garantias(id) ON DELETE CASCADE,
                FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_garantia (garantia_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Anexos adicionais da garantia'
        ");

        // Permissões padrão
        $this->updateGarantiasPermissions();
    }

    private function updateGarantiasPermissions(): void
    {
        $module = 'garantias';

        // Administrador: total
        $admin = $this->getProfileIdByName('Administrador');
        if ($admin) {
            $stmt = $this->db->prepare("INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, 1, 1, 1, 1) ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=1, can_import=1, can_export=1");
            $stmt->execute([$admin, $module]);
        }

        // Supervisor: CRUD e export
        $supervisor = $this->getProfileIdByName('Supervisor');
        if ($supervisor) {
            $stmt = $this->db->prepare("INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, 1, 1, 0, 1) ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=1, can_import=0, can_export=1");
            $stmt->execute([$supervisor, $module]);
        }

        // Analista de Qualidade: criar/editar/visualizar e exportar
        $analista = $this->getProfileIdByName('Analista de Qualidade');
        if ($analista) {
            $stmt = $this->db->prepare("INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, 1, 1, 0, 1) ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=1, can_import=0, can_export=1");
            $stmt->execute([$analista, $module]);
        }

        // Usuário Comum: visualizar e criar/editar próprios (controlado no controller)
        $user = $this->getProfileIdByName('Usuário Comum');
        if ($user) {
            $stmt = $this->db->prepare("INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, 1, 0, 0, 0) ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=0, can_import=0, can_export=0");
            $stmt->execute([$user, $module]);
        }
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
            if ($currentVersion < 8) {
                // Version 8: Create profiles system
                $this->migration8();
                $this->updateVersion(8);
            }
            if ($currentVersion < 9) {
                // Version 9: Create solicitacoes_melhorias system
                $this->migration9();
                $this->updateVersion(9);
            }
            if ($currentVersion < 10) {
                // Version 10: Recreate amostragens system with MEDIUMBLOB
                $this->migration10();
                $this->updateVersion(10);
            }
            if ($currentVersion < 11) {
                // Version 11: Create notifications system
                $this->migration11();
                $this->updateVersion(11);
            }
            if ($currentVersion < 12) {
                // Version 12: Create FMEA system
                $this->migration12();
                $this->updateVersion(12);
            }
            if ($currentVersion < 13) {
                // Version 13: Update Melhoria Continua system
                $this->migration13();
                $this->updateVersion(13);
            }
            if ($currentVersion < 14) {
                // Version 14: Create user invitations table
                $this->migration14();
                $this->updateVersion(14);
            }
            if ($currentVersion < 15) {
                // Version 15: Update profile permissions for current modules
                $this->migration15();
                $this->updateVersion(15);
            }
            if ($currentVersion < 16) {
                // Version 16: Create POPs and ITs system tables
                $this->migration16();
                $this->updateVersion(16);
            }
            if ($currentVersion < 17) {
                // Version 17: Create 5W2H system tables
                $this->migration17();
                $this->updateVersion(17);
            }
            if ($currentVersion < 18) {
                // Version 18: Create Controle de Descartes system tables
                $this->migration18();
                $this->updateVersion(18);
            }
            if ($currentVersion < 19) {
                // Version 19: Create Auditorias system tables
                $this->migration19();
                $this->updateVersion(19);
            }
            if ($currentVersion < 20) {
                // Version 20: Create Garantias system tables
                $this->migration20();
                $this->updateVersion(20);
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

    private function migration8(): void
    {
        // Create profiles table
        $this->db->exec('CREATE TABLE IF NOT EXISTS profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT NULL,
            is_default BOOLEAN DEFAULT FALSE,
            is_admin BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_default (is_default),
            INDEX idx_admin (is_admin)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        // Create profile_permissions table
        $this->db->exec('CREATE TABLE IF NOT EXISTS profile_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            profile_id INT NOT NULL,
            module VARCHAR(100) NOT NULL,
            can_view BOOLEAN DEFAULT FALSE,
            can_edit BOOLEAN DEFAULT FALSE,
            can_delete BOOLEAN DEFAULT FALSE,
            can_import BOOLEAN DEFAULT FALSE,
            can_export BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
            UNIQUE KEY unique_profile_module (profile_id, module),
            INDEX idx_profile_id (profile_id),
            INDEX idx_module (module)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        // Add profile_id column to users table
        $this->db->exec('ALTER TABLE users 
            ADD COLUMN IF NOT EXISTS profile_id INT NULL AFTER role,
            ADD INDEX idx_profile_id (profile_id)');

        // Create default profiles
        $this->createDefaultProfiles();
        
        // Migrate existing users to use profiles
        $this->migrateUsersToProfiles();
    }

    private function createDefaultProfiles(): void
    {
        // Create Administrator profile
        $stmt = $this->db->prepare("INSERT IGNORE INTO profiles (name, description, is_admin, is_default) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrador', 'Perfil com acesso total ao sistema', true, false]);
        $adminProfileId = $this->db->lastInsertId() ?: $this->getProfileIdByName('Administrador');

        // Create default user profile
        $stmt->execute(['Usuário Comum', 'Perfil padrão para usuários comuns', false, true]);
        $userProfileId = $this->db->lastInsertId() ?: $this->getProfileIdByName('Usuário Comum');

        // Create Supervisor profile
        $stmt->execute(['Supervisor', 'Perfil para supervisores com permissões intermediárias', false, false]);
        $supervisorProfileId = $this->db->lastInsertId() ?: $this->getProfileIdByName('Supervisor');

        // Create Operador de Toners profile
        $stmt->execute(['Operador de Toners', 'Perfil para operadores que trabalham especificamente com toners', false, false]);
        $operadorProfileId = $this->db->lastInsertId() ?: $this->getProfileIdByName('Operador de Toners');

        // Create Analista de Qualidade profile
        $stmt->execute(['Analista de Qualidade', 'Perfil para analistas responsáveis por controle de qualidade', false, false]);
        $analistaProfileId = $this->db->lastInsertId() ?: $this->getProfileIdByName('Analista de Qualidade');

        // Define modules (todos os módulos do sistema)
        $modules = [
            'dashboard' => 'Dashboard',
            'toners_cadastro' => 'Cadastro de Toners',
            'toners_retornados' => 'Registro de Retornados',
            'homologacoes' => 'Homologações',
            'amostragens' => 'Amostragens',
            'garantias' => 'Garantias',
            'controle_descartes' => 'Controle de Descartes',
            'femea' => 'FEMEA',
            'pops_its' => 'POPs e ITs',
            'fluxogramas' => 'Fluxogramas',
            'melhoria_continua' => 'Melhoria Contínua',
            'controle_rc' => 'Controle de RC',
            'registros_filiais' => 'Filiais',
            'registros_departamentos' => 'Departamentos',
            'registros_fornecedores' => 'Fornecedores',
            'registros_parametros' => 'Parâmetros de Retornados',
            'configuracoes_gerais' => 'Configurações Gerais',
            'admin_usuarios' => 'Gerenciar Usuários',
            'admin_perfis' => 'Gerenciar Perfis',
            'admin_convites' => 'Solicitações de Acesso',
            'admin_painel' => 'Painel Administrativo',
            'profile' => 'Perfil do Usuário',
            'email_config' => 'Configurações de Email'
        ];

        // Administrator permissions (full access)
        foreach ($modules as $module => $name) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, 1, 1, 1, 1)");
            $stmt->execute([$adminProfileId, $module]);
        }

        // Common user permissions (view only for basic modules)
        $userModules = [
            'dashboard', 'toners_cadastro', 'toners_retornados', 'amostragens', 
            'homologacoes', 'garantias', 'profile'
        ];
        foreach ($userModules as $module) {
            $canEdit = ($module === 'profile') ? 1 : 0; // Usuários podem editar próprio perfil
            $stmt = $this->db->prepare("INSERT IGNORE INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, ?, 0, 0, 0)");
            $stmt->execute([$userProfileId, $module, $canEdit]);
        }

        // Supervisor permissions (view and edit for operational modules)
        $supervisorModules = [
            'dashboard', 'toners_cadastro', 'toners_retornados', 'amostragens', 
            'homologacoes', 'garantias', 'controle_descartes', 'femea', 
            'pops_its', 'fluxogramas', 'melhoria_continua', 'registros_filiais', 
            'registros_departamentos', 'registros_fornecedores', 'profile'
        ];
        foreach ($supervisorModules as $module) {
            $canEdit = in_array($module, [
                'toners_cadastro', 'toners_retornados', 'amostragens', 
                'registros_filiais', 'registros_departamentos', 'registros_fornecedores', 'profile'
            ]) ? 1 : 0;
            $canDelete = in_array($module, ['amostragens', 'toners_retornados']) ? 1 : 0;
            $canExport = 1; // Supervisores podem exportar dados
            $stmt = $this->db->prepare("INSERT IGNORE INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, ?, ?, 0, ?)");
            $stmt->execute([$supervisorProfileId, $module, $canEdit, $canDelete, $canExport]);
        }

        // Operador de Toners permissions (foco em toners e operações relacionadas)
        $operadorModules = [
            'dashboard', 'toners_cadastro', 'toners_retornados', 'amostragens', 
            'controle_descartes', 'registros_parametros', 'profile'
        ];
        foreach ($operadorModules as $module) {
            $canEdit = in_array($module, ['toners_cadastro', 'toners_retornados', 'amostragens', 'profile']) ? 1 : 0;
            $canDelete = in_array($module, ['toners_retornados', 'amostragens']) ? 1 : 0;
            $canImport = in_array($module, ['toners_cadastro', 'toners_retornados']) ? 1 : 0;
            $canExport = in_array($module, ['toners_cadastro', 'toners_retornados', 'amostragens']) ? 1 : 0;
            $stmt = $this->db->prepare("INSERT IGNORE INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, ?, ?, ?, ?)");
            $stmt->execute([$operadorProfileId, $module, $canEdit, $canDelete, $canImport, $canExport]);
        }

        // Analista de Qualidade permissions (foco em qualidade e análises)
        $analistaModules = [
            'dashboard', 'homologacoes', 'amostragens', 'garantias', 'femea', 
            'pops_its', 'fluxogramas', 'melhoria_continua', 'controle_rc', 
            'toners_cadastro', 'toners_retornados', 'profile'
        ];
        foreach ($analistaModules as $module) {
            $canEdit = in_array($module, [
                'homologacoes', 'amostragens', 'femea', 'pops_its', 'fluxogramas', 
                'melhoria_continua', 'controle_rc', 'profile'
            ]) ? 1 : 0;
            $canDelete = in_array($module, ['amostragens', 'homologacoes']) ? 1 : 0;
            $canExport = 1; // Analistas podem exportar dados para análise
            $stmt = $this->db->prepare("INSERT IGNORE INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, ?, ?, 0, ?)");
            $stmt->execute([$analistaProfileId, $module, $canEdit, $canDelete, $canExport]);
        }
    }

    private function getProfileIdByName(string $name): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM profiles WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetchColumn() ?: null;
    }

    private function migrateUsersToProfiles(): void
    {
        // Get profile IDs
        $adminProfileId = $this->getProfileIdByName('Administrador');
        $userProfileId = $this->getProfileIdByName('Usuário Comum');

        // Update existing users to use profiles
        $stmt = $this->db->prepare("UPDATE users SET profile_id = ? WHERE role = 'admin' AND profile_id IS NULL");
        $stmt->execute([$adminProfileId]);

        $stmt = $this->db->prepare("UPDATE users SET profile_id = ? WHERE role = 'user' AND profile_id IS NULL");
        $stmt->execute([$userProfileId]);
    }

    private function migration9(): void
    {
        // Create solicitacoes_melhorias table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS solicitacoes_melhorias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                usuario_nome VARCHAR(255) NOT NULL,
                data_solicitacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                setor VARCHAR(255) NOT NULL,
                processo TEXT NOT NULL,
                descricao_melhoria TEXT NOT NULL,
                status ENUM('pendente', 'em_analise', 'aprovado', 'rejeitado', 'implementado') NOT NULL DEFAULT 'pendente',
                observacoes TEXT,
                resultado_esperado TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");

        // Create solicitacoes_melhorias_responsaveis table (many-to-many)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS solicitacoes_melhorias_responsaveis (
                id INT AUTO_INCREMENT PRIMARY KEY,
                solicitacao_id INT NOT NULL,
                usuario_id INT NOT NULL,
                usuario_nome VARCHAR(255) NOT NULL,
                usuario_email VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_melhorias(id) ON DELETE CASCADE,
                FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_solicitacao_responsavel (solicitacao_id, usuario_id)
            )
        ");

        // Create solicitacoes_melhorias_anexos table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS solicitacoes_melhorias_anexos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                solicitacao_id INT NOT NULL,
                nome_arquivo VARCHAR(255) NOT NULL,
                nome_original VARCHAR(255) NOT NULL,
                tipo_arquivo VARCHAR(100) NOT NULL,
                tamanho_arquivo INT NOT NULL,
                caminho_arquivo VARCHAR(500) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_melhorias(id) ON DELETE CASCADE
            )
        ");

        // Add solicitacao_melhorias module to existing profiles
        $this->addModuleToProfiles();
    }

    private function addModuleToProfiles(): void
    {
        // Get all profiles
        $stmt = $this->db->prepare("SELECT id, name FROM profiles");
        $stmt->execute();
        $profiles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($profiles as $profile) {
            // Add solicitacao_melhorias permissions based on profile type
            $canEdit = 0;
            $canDelete = 0;
            $canView = 1;

            if ($profile['name'] === 'Administrador') {
                $canEdit = 1;
                $canDelete = 1;
            } elseif (in_array($profile['name'], ['Supervisor', 'Analista de Qualidade'])) {
                $canEdit = 1;
            }

            $stmt = $this->db->prepare("
                INSERT IGNORE INTO profile_permissions 
                (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, 'solicitacao_melhorias', ?, ?, ?, 0, 1)
            ");
            $stmt->execute([$profile['id'], $canView, $canEdit, $canDelete]);
        }
    }

    private function migration10(): void
    {
        // Drop existing amostragens table if exists (fresh start)
        $this->db->exec('DROP TABLE IF EXISTS amostragens_evidencias');
        $this->db->exec('DROP TABLE IF EXISTS amostragens');

        // Create new amostragens table with MEDIUMBLOB support
        $this->db->exec('CREATE TABLE amostragens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero_nf VARCHAR(100) NOT NULL,
            status ENUM("pendente", "aprovado", "reprovado") NOT NULL DEFAULT "pendente",
            observacao TEXT NULL,
            
            -- PDF storage in MEDIUMBLOB
            arquivo_nf_blob MEDIUMBLOB NULL,
            arquivo_nf_name VARCHAR(255) NULL,
            arquivo_nf_type VARCHAR(100) NULL,
            arquivo_nf_size INT NULL,
            
            -- Fallback filesystem path (compatibility)
            arquivo_nf VARCHAR(255) NULL,
            
            -- Responsaveis as JSON
            responsaveis JSON NULL,
            
            -- Legacy evidencias field (for filesystem fallback)
            evidencias JSON NULL,
            
            -- Fotos as JSON (for filesystem fallback)
            fotos JSON NULL,
            
            data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_numero_nf (numero_nf),
            INDEX idx_status (status),
            INDEX idx_data_registro (data_registro)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        // Create evidencias table with MEDIUMBLOB for images
        $this->db->exec('CREATE TABLE amostragens_evidencias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            amostragem_id INT NOT NULL,
            image MEDIUMBLOB NOT NULL,
            name VARCHAR(255) NULL,
            type VARCHAR(100) NULL,
            size INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (amostragem_id) REFERENCES amostragens(id) ON DELETE CASCADE,
            INDEX idx_amostragem_id (amostragem_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    private function migration11(): void
    {
        // Criar tabela de notificações
        $this->db->exec('CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) NOT NULL DEFAULT "info",
            related_type VARCHAR(50) NULL,
            related_id INT NULL,
            read_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            INDEX idx_user_id (user_id),
            INDEX idx_read_at (read_at),
            INDEX idx_created_at (created_at),
            INDEX idx_related (related_type, related_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    private function migration12(): void
    {
        // Criar tabela FMEA
        $this->db->exec('CREATE TABLE IF NOT EXISTS fmea (
            id INT AUTO_INCREMENT PRIMARY KEY,
            modo_falha TEXT NOT NULL,
            efeito_falha TEXT NOT NULL,
            severidade INT NOT NULL CHECK (severidade >= 0 AND severidade <= 10),
            ocorrencia INT NOT NULL CHECK (ocorrencia >= 0 AND ocorrencia <= 10),
            deteccao INT NOT NULL CHECK (deteccao >= 0 AND deteccao <= 10),
            rpn INT GENERATED ALWAYS AS (severidade * ocorrencia * deteccao) STORED,
            risco VARCHAR(50) GENERATED ALWAYS AS (
                CASE 
                    WHEN (severidade * ocorrencia * deteccao) < 40 THEN "Não Crítico"
                    WHEN (severidade * ocorrencia * deteccao) < 100 THEN "Risco Moderado"
                    WHEN (severidade * ocorrencia * deteccao) < 200 THEN "Risco Alto"
                    ELSE "Risco Crítico"
                END
            ) STORED,
            acao_sugerida TEXT NOT NULL,
            data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_rpn (rpn),
            INDEX idx_risco (risco),
            INDEX idx_data_registro (data_registro),
            INDEX idx_created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    private function migration13(): void
    {
        // Recriar tabela de melhorias contínuas com nova estrutura
        $this->db->exec('DROP TABLE IF EXISTS melhorias_continuas_anexos');
        $this->db->exec('DROP TABLE IF EXISTS melhorias_continuas_responsaveis');
        $this->db->exec('DROP TABLE IF EXISTS melhorias_continuas');
        
        // Criar tabela principal de melhorias contínuas
        $this->db->exec('CREATE TABLE melhorias_continuas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            departamento_id INT NOT NULL,
            processo VARCHAR(255) NOT NULL,
            descricao_melhoria TEXT NOT NULL,
            status ENUM("pendente", "em_andamento", "concluido", "cancelado") DEFAULT "pendente",
            pontuacao INT NULL,
            observacao TEXT NULL,
            resultado TEXT NULL,
            created_by INT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_status (status),
            INDEX idx_data_registro (data_registro),
            INDEX idx_departamento (departamento_id),
            INDEX idx_created_by (created_by),
            FOREIGN KEY (departamento_id) REFERENCES departamentos(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        
        // Criar tabela de responsáveis (many-to-many)
        $this->db->exec('CREATE TABLE melhorias_continuas_responsaveis (
            id INT AUTO_INCREMENT PRIMARY KEY,
            melhoria_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            UNIQUE KEY unique_melhoria_user (melhoria_id, user_id),
            FOREIGN KEY (melhoria_id) REFERENCES melhorias_continuas(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        
        // Criar tabela de anexos (MEDIUMBLOB)
        $this->db->exec('CREATE TABLE melhorias_continuas_anexos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            melhoria_id INT NOT NULL,
            arquivo MEDIUMBLOB NOT NULL,
            nome_arquivo VARCHAR(255) NOT NULL,
            tipo_arquivo VARCHAR(100) NOT NULL,
            tamanho_arquivo INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            INDEX idx_melhoria_id (melhoria_id),
            FOREIGN KEY (melhoria_id) REFERENCES melhorias_continuas(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    private function migration14(): void
    {
        // Criar tabela de solicitações de acesso
        $this->db->exec('CREATE TABLE IF NOT EXISTS user_invitations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            setor VARCHAR(255) NULL,
            filial VARCHAR(255) NULL,
            message TEXT NULL,
            status ENUM("pending", "approved", "rejected") DEFAULT "pending",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            approved_by INT NULL,
            approved_at TIMESTAMP NULL,
            
            INDEX idx_email (email),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    private function migration15(): void
    {
        // Limpar permissões antigas do módulo solicitacao_melhorias que não existe mais
        $this->db->exec("DELETE FROM profile_permissions WHERE module = 'solicitacao_melhorias'");
        
        // Adicionar permissão de melhoria_continua para perfis que não têm
        $profiles = ['Supervisor', 'Analista de Qualidade'];
        
        foreach ($profiles as $profileName) {
            $profileId = $this->getProfileIdByName($profileName);
            if ($profileId) {
                // Verificar se já existe a permissão
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM profile_permissions WHERE profile_id = ? AND module = 'melhoria_continua'");
                $stmt->execute([$profileId]);
                
                if ($stmt->fetchColumn() == 0) {
                    // Adicionar permissão de melhoria_continua
                    $canEdit = ($profileName === 'Analista de Qualidade') ? 1 : 0;
                    $stmt = $this->db->prepare("INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, 'melhoria_continua', 1, ?, 0, 0, 1)");
                    $stmt->execute([$profileId, $canEdit]);
                }
            }
        }
        
        // Atualizar permissões do usuário comum para incluir melhoria_continua (apenas visualização)
        $userProfileId = $this->getProfileIdByName('Usuário Comum');
        if ($userProfileId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM profile_permissions WHERE profile_id = ? AND module = 'melhoria_continua'");
            $stmt->execute([$userProfileId]);
            
            if ($stmt->fetchColumn() == 0) {
                $stmt = $this->db->prepare("INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, 'melhoria_continua', 1, 1, 0, 0, 0)");
                $stmt->execute([$userProfileId]);
            }
        }
    }

    private function migration16(): void
    {
        // Criar tabela de títulos de POPs e ITs
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS pops_its_titulos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                titulo VARCHAR(255) NOT NULL,
                departamento_id INT NOT NULL,
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_departamento (departamento_id),
                INDEX idx_created_by (created_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Criar tabela de registros de POPs e ITs
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS pops_its_registros (
                id INT AUTO_INCREMENT PRIMARY KEY,
                titulo_id INT NOT NULL,
                versao VARCHAR(10) NOT NULL,
                arquivo_blob MEDIUMBLOB NOT NULL,
                arquivo_name VARCHAR(255) NOT NULL,
                arquivo_type VARCHAR(100) NOT NULL,
                arquivo_size INT NOT NULL,
                visibilidade ENUM('publico', 'departamentos') NOT NULL DEFAULT 'departamentos',
                status ENUM('pendente', 'aprovado', 'reprovado') NOT NULL DEFAULT 'pendente',
                observacao_reprovacao TEXT NULL,
                created_by INT NOT NULL,
                approved_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                approved_at TIMESTAMP NULL,
                FOREIGN KEY (titulo_id) REFERENCES pops_its_titulos(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_titulo (titulo_id),
                INDEX idx_status (status),
                INDEX idx_created_by (created_by),
                INDEX idx_visibilidade (visibilidade)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Criar tabela de departamentos permitidos para visualização
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS pops_its_departamentos_permitidos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                registro_id INT NOT NULL,
                departamento_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (registro_id) REFERENCES pops_its_registros(id) ON DELETE CASCADE,
                FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE,
                UNIQUE KEY unique_registro_departamento (registro_id, departamento_id),
                INDEX idx_registro (registro_id),
                INDEX idx_departamento (departamento_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Atualizar permissões para incluir POPs e ITs com permissões granulares
        $this->updatePopItsPermissions();
    }

    private function updatePopItsPermissions(): void
    {
        // Remover permissões antigas se existirem
        $this->db->exec("DELETE FROM profile_permissions WHERE module LIKE 'pops_its%'");

        // Definir permissões específicas para cada aba
        $popItsModules = [
            'pops_its_cadastro_titulos',    // Aba 1: Cadastro de Títulos
            'pops_its_meus_registros',      // Aba 2: Meus Registros  
            'pops_its_pendente_aprovacao',  // Aba 3: Pendente Aprovação
            'pops_its_visualizacao'         // Aba 4: Visualização (todos podem ver)
        ];

        // Administrador: acesso total a todas as abas
        $adminProfileId = $this->getProfileIdByName('Administrador');
        if ($adminProfileId) {
            foreach ($popItsModules as $module) {
                $stmt = $this->db->prepare("
                    INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                    VALUES (?, ?, 1, 1, 1, 1, 1)
                ");
                $stmt->execute([$adminProfileId, $module]);
            }
        }

        // Analista de Qualidade: pode cadastrar títulos, ver seus registros, aprovar/reprovar, visualizar
        $analistaProfileId = $this->getProfileIdByName('Analista de Qualidade');
        if ($analistaProfileId) {
            $analistaPermissions = [
                'pops_its_cadastro_titulos' => ['view' => 1, 'edit' => 1, 'delete' => 0],
                'pops_its_meus_registros' => ['view' => 1, 'edit' => 1, 'delete' => 1],
                'pops_its_pendente_aprovacao' => ['view' => 1, 'edit' => 1, 'delete' => 0],
                'pops_its_visualizacao' => ['view' => 1, 'edit' => 0, 'delete' => 0]
            ];
            
            foreach ($analistaPermissions as $module => $perms) {
                $stmt = $this->db->prepare("
                    INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                    VALUES (?, ?, ?, ?, ?, 0, 1)
                ");
                $stmt->execute([$analistaProfileId, $module, $perms['view'], $perms['edit'], $perms['delete']]);
            }
        }

        // Supervisor: pode cadastrar títulos, ver seus registros, visualizar
        $supervisorProfileId = $this->getProfileIdByName('Supervisor');
        if ($supervisorProfileId) {
            $supervisorPermissions = [
                'pops_its_cadastro_titulos' => ['view' => 1, 'edit' => 1, 'delete' => 0],
                'pops_its_meus_registros' => ['view' => 1, 'edit' => 1, 'delete' => 1],
                'pops_its_pendente_aprovacao' => ['view' => 0, 'edit' => 0, 'delete' => 0],
                'pops_its_visualizacao' => ['view' => 1, 'edit' => 0, 'delete' => 0]
            ];
            
            foreach ($supervisorPermissions as $module => $perms) {
                $stmt = $this->db->prepare("
                    INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                    VALUES (?, ?, ?, ?, ?, 0, 1)
                ");
                $stmt->execute([$supervisorProfileId, $module, $perms['view'], $perms['edit'], $perms['delete']]);
            }
        }

        // Usuário Comum: apenas visualização
        $userProfileId = $this->getProfileIdByName('Usuário Comum');
        if ($userProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, 'pops_its_visualizacao', 1, 0, 0, 0, 0)
            ");
            $stmt->execute([$userProfileId]);
        }
    }

    private function migration17(): void
    {
        // Criar tabela de planos 5W2H
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS planos_5w2h (
                id INT AUTO_INCREMENT PRIMARY KEY,
                titulo VARCHAR(255) NOT NULL,
                what TEXT NOT NULL COMMENT 'O que será feito',
                why TEXT NOT NULL COMMENT 'Por que será feito',
                where_local TEXT COMMENT 'Onde será feito',
                when_inicio DATE COMMENT 'Data de início',
                when_fim DATE COMMENT 'Data de término',
                who_id INT COMMENT 'Responsável principal',
                how TEXT COMMENT 'Como será feito',
                how_much DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Quanto custará',
                status ENUM('Aberto','Em andamento','Concluído','Cancelado') DEFAULT 'Aberto',
                setor_id INT COMMENT 'Setor responsável',
                observacoes TEXT NULL,
                anexos TEXT NULL COMMENT 'URLs ou nomes de arquivos',
                created_by INT NOT NULL COMMENT 'Usuário que criou',
                updated_by INT NULL COMMENT 'Último usuário que atualizou',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (who_id) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (setor_id) REFERENCES departamentos(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_status (status),
                INDEX idx_who_id (who_id),
                INDEX idx_setor_id (setor_id),
                INDEX idx_created_by (created_by),
                INDEX idx_dates (when_inicio, when_fim)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Planos 5W2H - Metodologia de planejamento'
        ");

        // Criar tabela de histórico de alterações dos planos 5W2H
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS planos_5w2h_historico (
                id INT AUTO_INCREMENT PRIMARY KEY,
                plano_id INT NOT NULL,
                campo_alterado VARCHAR(100) NOT NULL,
                valor_anterior TEXT,
                valor_novo TEXT,
                alterado_por INT NOT NULL,
                alterado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (plano_id) REFERENCES planos_5w2h(id) ON DELETE CASCADE,
                FOREIGN KEY (alterado_por) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_plano_id (plano_id),
                INDEX idx_alterado_por (alterado_por),
                INDEX idx_alterado_em (alterado_em)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Histórico de alterações dos planos 5W2H'
        ");

        // Criar tabela de anexos dos planos 5W2H
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS planos_5w2h_anexos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                plano_id INT NOT NULL,
                nome_arquivo VARCHAR(255) NOT NULL,
                nome_original VARCHAR(255) NOT NULL,
                tipo_arquivo VARCHAR(100) NOT NULL,
                tamanho_arquivo INT NOT NULL,
                caminho_arquivo VARCHAR(500) NOT NULL,
                uploaded_by INT NOT NULL,
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (plano_id) REFERENCES planos_5w2h(id) ON DELETE CASCADE,
                FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_plano_id (plano_id),
                INDEX idx_uploaded_by (uploaded_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Anexos dos planos 5W2H'
        ");

        // Atualizar permissões para incluir módulo 5W2H
        $this->update5W2HPermissions();
    }

    private function update5W2HPermissions(): void
    {
        // Módulo 5W2H
        $module = '5w2h_planos';

        // Administrador: acesso total
        $adminProfileId = $this->getProfileIdByName('Administrador');
        if ($adminProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 1, 1, 1, 1)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=1, can_import=1, can_export=1
            ");
            $stmt->execute([$adminProfileId, $module]);
        }

        // Supervisor: pode criar, editar e visualizar planos do seu setor
        $supervisorProfileId = $this->getProfileIdByName('Supervisor');
        if ($supervisorProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 1, 1, 0, 1)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=1, can_import=0, can_export=1
            ");
            $stmt->execute([$supervisorProfileId, $module]);
        }

        // Analista de Qualidade: pode criar e gerenciar planos
        $analistaProfileId = $this->getProfileIdByName('Analista de Qualidade');
        if ($analistaProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 1, 1, 0, 1)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=1, can_import=0, can_export=1
            ");
            $stmt->execute([$analistaProfileId, $module]);
        }

        // Usuário Comum: pode criar e editar apenas seus próprios planos
        $userProfileId = $this->getProfileIdByName('Usuário Comum');
        if ($userProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 1, 0, 0, 0)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=0, can_import=0, can_export=0
            ");
            $stmt->execute([$userProfileId, $module]);
        }
    }

    private function migration18(): void
    {
        // Criar tabela de controle de descartes
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS controle_descartes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                numero_serie VARCHAR(100) NOT NULL,
                filial_id INT NOT NULL,
                codigo_produto VARCHAR(100) NOT NULL,
                descricao_produto TEXT NOT NULL,
                data_descarte DATE NOT NULL,
                numero_os VARCHAR(50) NULL COMMENT 'Número da OS para busca',
                anexo_os_blob MEDIUMBLOB NULL COMMENT 'Arquivo da OS assinada',
                anexo_os_nome VARCHAR(255) NULL COMMENT 'Nome original do arquivo',
                anexo_os_tipo VARCHAR(100) NULL COMMENT 'Tipo MIME do arquivo',
                anexo_os_tamanho INT NULL COMMENT 'Tamanho do arquivo em bytes',
                responsavel_tecnico VARCHAR(200) NOT NULL COMMENT 'Nome do técnico responsável',
                observacoes TEXT NULL,
                created_by INT NOT NULL COMMENT 'Usuário que criou o registro',
                updated_by INT NULL COMMENT 'Último usuário que atualizou',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE RESTRICT,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_numero_serie (numero_serie),
                INDEX idx_numero_os (numero_os),
                INDEX idx_filial_id (filial_id),
                INDEX idx_data_descarte (data_descarte),
                INDEX idx_created_by (created_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Controle de Descartes de Equipamentos'
        ");

        // Atualizar permissões para incluir módulo Controle de Descartes
        $this->updateControleDescartesPermissions();
    }

    private function updateControleDescartesPermissions(): void
    {
        // Módulo Controle de Descartes
        $module = 'controle_descartes';

        // Administrador: acesso total
        $adminProfileId = $this->getProfileIdByName('Administrador');
        if ($adminProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 1, 1, 1, 1)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=1, can_import=1, can_export=1
            ");
            $stmt->execute([$adminProfileId, $module]);
        }

        // Supervisor: pode criar, editar e visualizar descartes
        $supervisorProfileId = $this->getProfileIdByName('Supervisor');
        if ($supervisorProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 1, 1, 0, 1)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=1, can_import=0, can_export=1
            ");
            $stmt->execute([$supervisorProfileId, $module]);
        }

        // Analista de Qualidade: pode visualizar e criar relatórios
        $analistaProfileId = $this->getProfileIdByName('Analista de Qualidade');
        if ($analistaProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 1, 0, 0, 1)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=0, can_import=0, can_export=1
            ");
            $stmt->execute([$analistaProfileId, $module]);
        }

        // Operador de Toners: pode criar e editar descartes (foco operacional)
        $operadorProfileId = $this->getProfileIdByName('Operador de Toners');
        if ($operadorProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 1, 0, 0, 0)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=0, can_import=0, can_export=0
            ");
            $stmt->execute([$operadorProfileId, $module]);
        }

        // Usuário Comum: apenas visualização
        $userProfileId = $this->getProfileIdByName('Usuário Comum');
        if ($userProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 0, 0, 0, 0)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=0, can_delete=0, can_import=0, can_export=0
            ");
            $stmt->execute([$userProfileId, $module]);
        }
    }

    private function migration19(): void
    {
        // Criar tabela de auditorias
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS auditorias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filial_id INT NOT NULL,
                data_auditoria_inicio DATE NOT NULL COMMENT 'Data de início da auditoria',
                data_auditoria_fim DATE NOT NULL COMMENT 'Data de fim da auditoria',
                anexo_auditoria_blob MEDIUMBLOB NULL COMMENT 'Arquivo PDF/DOC da auditoria',
                anexo_auditoria_nome VARCHAR(255) NULL COMMENT 'Nome original do arquivo',
                anexo_auditoria_tipo VARCHAR(100) NULL COMMENT 'Tipo MIME do arquivo',
                anexo_auditoria_tamanho INT NULL COMMENT 'Tamanho do arquivo em bytes',
                observacoes TEXT NULL,
                created_by INT NOT NULL COMMENT 'Usuário que criou o registro',
                updated_by INT NULL COMMENT 'Último usuário que atualizou',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE RESTRICT,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_filial_id (filial_id),
                INDEX idx_data_auditoria (data_auditoria_inicio, data_auditoria_fim),
                INDEX idx_created_by (created_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Auditorias por Filial'
        ");

        // Atualizar permissões para incluir módulo Auditorias
        $this->updateAuditoriasPermissions();
    }

    private function updateAuditoriasPermissions(): void
    {
        // Módulo Auditorias
        $module = 'auditorias';

        // Administrador: acesso total
        $adminProfileId = $this->getProfileIdByName('Administrador');
        if ($adminProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 1, 1, 1, 1)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=1, can_import=1, can_export=1
            ");
            $stmt->execute([$adminProfileId, $module]);
        }

        // Supervisor: pode criar, editar e visualizar auditorias
        $supervisorProfileId = $this->getProfileIdByName('Supervisor');
        if ($supervisorProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 1, 1, 0, 1)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=1, can_import=0, can_export=1
            ");
            $stmt->execute([$supervisorProfileId, $module]);
        }

        // Analista de Qualidade: pode criar, editar e visualizar auditorias (foco em qualidade)
        $analistaProfileId = $this->getProfileIdByName('Analista de Qualidade');
        if ($analistaProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 1, 1, 0, 1)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=1, can_delete=1, can_import=0, can_export=1
            ");
            $stmt->execute([$analistaProfileId, $module]);
        }

        // Operador de Toners: pode visualizar auditorias
        $operadorProfileId = $this->getProfileIdByName('Operador de Toners');
        if ($operadorProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 0, 0, 0, 0)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=0, can_delete=0, can_import=0, can_export=0
            ");
            $stmt->execute([$operadorProfileId, $module]);
        }

        // Usuário Comum: apenas visualização
        $userProfileId = $this->getProfileIdByName('Usuário Comum');
        if ($userProfileId) {
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, 1, 0, 0, 0, 0)
                ON DUPLICATE KEY UPDATE can_view=1, can_edit=0, can_delete=0, can_import=0, can_export=0
            ");
            $stmt->execute([$userProfileId, $module]);
        }
    }
}
