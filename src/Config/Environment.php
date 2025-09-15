<?php

namespace SgqPro\Config;

class Environment
{
    private static $instance = null;
    private $environment;
    private $config;

    private function __construct()
    {
        $this->detectEnvironment();
        $this->loadConfig();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function detectEnvironment()
    {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        
        // Detecta se está no ambiente de produção oficial (Hostinger)
        if (strpos($host, 'djbr.sgqoti.com.br') !== false) {
            $this->environment = 'production';
        }
        // Detecta se está no localhost (apenas para desenvolvimento local)
        elseif (strpos($host, 'localhost') !== false || 
                strpos($host, '127.0.0.1') !== false || 
                strpos($host, '::1') !== false) {
            $this->environment = 'local';
        }
        // Detecta ambientes de desenvolvimento online
        elseif (strpos($host, 'github.io') !== false ||
                strpos($host, 'netlify.app') !== false ||
                strpos($host, 'vercel.app') !== false ||
                strpos($host, 'herokuapp.com') !== false ||
                strpos($host, 'surge.sh') !== false) {
            $this->environment = 'development';
        }
        // Fallback: se não reconhecer o host, assume produção (para Hostinger)
        else {
            $this->environment = 'production';
        }
    }

    private function loadConfig()
    {
        switch ($this->environment) {
            case 'production':
                $this->config = [
                    'db' => [
                        'host' => 'srv1890.hstgr.io',
                        'port' => 3306,
                        'database' => 'u230868210_djsgqpro',
                        'username' => 'u230868210_dusouza',
                        'password' => 'Pandora@1989',
                        'charset' => 'utf8mb4'
                    ],
                    'app' => [
                        'debug' => false,
                        'url' => 'https://djbr.sgqoti.com.br',
                        'secure' => true
                    ],
                    'session' => [
                        'lifetime' => 120,
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]
                ];
                break;

            case 'local':
                $this->config = [
                    'db' => [
                        'host' => 'srv1890.hstgr.io',
                        'port' => 3306,
                        'database' => 'u230868210_djsgqpro',
                        'username' => 'u230868210_dusouza',
                        'password' => 'Pandora@1989',
                        'charset' => 'utf8mb4'
                    ],
                    'app' => [
                        'debug' => true,
                        'url' => 'https://djbr.sgqoti.com.br',
                        'secure' => true
                    ],
                    'session' => [
                        'lifetime' => 1440,
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                ];
                break;

            default:
                // Fallback para desenvolvimento (usa config de produção mas com debug ativo)
                $this->config = [
                    'db' => [
                        'host' => 'srv1890.hstgr.io',
                        'port' => 3306,
                        'database' => 'u230868210_djsgqpro',
                        'username' => 'u230868210_dusouza',
                        'password' => 'Pandora@1989',
                        'charset' => 'utf8mb4'
                    ],
                    'app' => [
                        'debug' => true,
                        'url' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
                        'secure' => true
                    ],
                    'session' => [
                        'lifetime' => 1440,
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                ];
        }
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->config;
        }

        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function isProduction()
    {
        return $this->environment === 'production';
    }

    public function isLocal()
    {
        return $this->environment === 'local';
    }

    public function isDevelopment()
    {
        return $this->environment === 'development';
    }
}
