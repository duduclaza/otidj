<?php
// Script para atualizar o banco de dados diretamente
require __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

use PDO;
use PDOException;

try {
    // Conectar ao banco
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $dbname = $_ENV['DB_NAME'] ?? 'sgqpro';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'",
    ]);

    echo "✅ Conectado ao banco de dados com sucesso!\n";

    // Criar tabela de migrações
    $pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        version INT NOT NULL DEFAULT 0,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    echo "✅ Tabela migrations criada/verificada\n";

    // Verificar versão atual
    $stmt = $pdo->query('SELECT MAX(version) as version FROM migrations');
    $result = $stmt->fetch();
    $currentVersion = (int)($result['version'] ?? 0);
    echo "📊 Versão atual do banco: {$currentVersion}\n";

    // Migração 1: Tabelas básicas
    if ($currentVersion < 1) {
        echo "🔄 Executando migração 1...\n";
        
        // Filiais
        $pdo->exec('CREATE TABLE IF NOT EXISTS filiais (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(150) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        
        // Departamentos
        $pdo->exec('CREATE TABLE IF NOT EXISTS departamentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(150) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        
        // Fornecedores
        $pdo->exec('CREATE TABLE IF NOT EXISTS fornecedores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(200) NOT NULL,
            contato VARCHAR(200) NULL,
            rma VARCHAR(200) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (nome)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        
        // Parâmetros Retornados
        $pdo->exec('CREATE TABLE IF NOT EXISTS parametros_retornados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(200) NOT NULL,
            faixa_min INT NOT NULL,
            faixa_max INT NULL,
            orientacao TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        // Seed dados padrão
        seedDefaults($pdo);
        
        $pdo->prepare('INSERT INTO migrations (version) VALUES (1)')->execute();
        echo "✅ Migração 1 concluída\n";
    }

    // Migração 2: Fix fornecedores
    if ($currentVersion < 2) {
        echo "🔄 Executando migração 2...\n";
        
        try {
            $pdo->exec('ALTER TABLE fornecedores 
                MODIFY COLUMN contato VARCHAR(200) NULL,
                MODIFY COLUMN rma VARCHAR(200) NULL');
        } catch (PDOException $e) {
            // Ignorar se já estiver correto
        }
        
        $pdo->prepare('INSERT INTO migrations (version) VALUES (2)')->execute();
        echo "✅ Migração 2 concluída\n";
    }

    // Migração 3: Tabela toners
    if ($currentVersion < 3) {
        echo "🔄 Executando migração 3...\n";
        
        $pdo->exec('CREATE TABLE IF NOT EXISTS toners (
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
        
        $pdo->prepare('INSERT INTO migrations (version) VALUES (3)')->execute();
        echo "✅ Migração 3 concluída\n";
    }

    // Migração 4: Tabela retornados
    if ($currentVersion < 4) {
        echo "🔄 Executando migração 4...\n";
        
        $pdo->exec('CREATE TABLE IF NOT EXISTS retornados (
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
        
        $pdo->prepare('INSERT INTO migrations (version) VALUES (4)')->execute();
        echo "✅ Migração 4 concluída\n";
    }

    echo "\n🎉 Todas as migrações foram executadas com sucesso!\n";
    echo "📊 Versão final do banco: 4\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
        echo "⚠️ Limite de conexões por hora excedido. Tente novamente mais tarde.\n";
    } else {
        echo "❌ Erro: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

function seedDefaults($pdo) {
    // Filiais
    $filiais = ['Jundiai','Franca','Santos','Caçapava','Uberlândia','Uberaba'];
    $stmt = $pdo->query('SELECT COUNT(*) AS c FROM filiais');
    if ((int)$stmt->fetchColumn() === 0) {
        $ins = $pdo->prepare('INSERT IGNORE INTO filiais (nome) VALUES (:n)');
        foreach ($filiais as $n) { 
            $ins->execute([':n' => $n]); 
        }
        echo "✅ Filiais inseridas\n";
    }

    // Departamentos
    $departamentos = [
        'Financeiro','Faturamento','Logística','Compras','Área Técnica','Área Técnica ADM','Comercial',
        'Implantação','Implantação ADM','Qualidade','RH','Licitações','Gerencia','Limpeza','Atendimento',
        'Controladoria','Monitoramento'
    ];
    $stmt = $pdo->query('SELECT COUNT(*) FROM departamentos');
    if ((int)$stmt->fetchColumn() === 0) {
        $ins = $pdo->prepare('INSERT IGNORE INTO departamentos (nome) VALUES (:n)');
        foreach ($departamentos as $n) { 
            $ins->execute([':n' => $n]); 
        }
        echo "✅ Departamentos inseridos\n";
    }

    // Parâmetros de Retornados
    $stmt = $pdo->query('SELECT COUNT(*) FROM parametros_retornados');
    if ((int)$stmt->fetchColumn() === 0) {
        $ins = $pdo->prepare('INSERT INTO parametros_retornados (nome, faixa_min, faixa_max, orientacao) VALUES (:nome, :min, :max, :ori)');
        
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
        
        echo "✅ Parâmetros de retornados inseridos\n";
    }
}
