<?php

namespace SgqPro\Database;

use SgqPro\Config\Environment;
use PDO;
use PDOException;

class Connection
{
    private static $instance = null;
    private $pdo;
    private $environment;

    private function __construct()
    {
        $this->environment = Environment::getInstance();
        $this->connect();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect()
    {
        try {
            $dbConfig = $this->environment->getConfig('db');
            
            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['database'],
                $dbConfig['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            // Configurações adicionais de segurança para produção
            if ($this->environment->isProduction()) {
                $options[PDO::ATTR_PERSISTENT] = false;
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }

            $this->pdo = new PDO(
                $dsn,
                $dbConfig['username'],
                $dbConfig['password'],
                $options
            );

            // Log da conexão (apenas em desenvolvimento)
            if (!$this->environment->isProduction()) {
                error_log("SGQ PRO: Conectado ao banco de dados no ambiente: " . $this->environment->getEnvironment());
            }

        } catch (PDOException $e) {
            $errorMsg = "Erro na conexão com o banco de dados";
            
            // Mostra detalhes do erro apenas em desenvolvimento
            if (!$this->environment->isProduction()) {
                $errorMsg .= ": " . $e->getMessage();
            }
            
            error_log("SGQ PRO Database Error: " . $e->getMessage());
            throw new \Exception($errorMsg);
        }
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("SGQ PRO Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new \Exception("Erro na execução da consulta");
        }
    }

    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function insert($table, $data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->query($sql, $data);
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = [])
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $params = array_merge($data, $whereParams);
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }

    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollback()
    {
        return $this->pdo->rollback();
    }

    // Previne clonagem
    private function __clone() {}

    // Previne deserialização
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
