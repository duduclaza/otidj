<?php
namespace App\Core;

use App\Config\Database;
use PDO;

class Migration
{
    private PDO $db;
    private const CURRENT_VERSION = 1;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function runMigrations(): void
    {
        // Create migrations table if not exists
        $this->createMigrationsTable();
        
        $currentVersion = $this->getCurrentVersion();
        
        if ($currentVersion < self::CURRENT_VERSION) {
            $this->migrate();
            $this->updateVersion(self::CURRENT_VERSION);
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

    private function migrate(): void
    {
        // Create main tables
        $this->createFilialTable();
        $this->createDepartamentosTable();
        $this->createFornecedoresTable();
        $this->createParametrosRetornadosTable();
        
        // Seed default data
        $this->seedDefaults();
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
}
