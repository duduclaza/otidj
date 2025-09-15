<?php

// Bootstrap do SGQ PRO
// Este arquivo inicializa o sistema e detecta automaticamente o ambiente

// Carrega o autoloader do Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Fallback para quando o Composer não estiver instalado
    spl_autoload_register(function ($class) {
        $prefix = 'SgqPro\\';
        $base_dir = __DIR__ . '/src/';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    });
}

use SgqPro\Config\Environment;
use SgqPro\Database\Connection;

// Inicializa o sistema de detecção de ambiente
$environment = Environment::getInstance();

// Configura as sessões baseado no ambiente
$sessionConfig = $environment->getConfig('session');
ini_set('session.cookie_lifetime', $sessionConfig['lifetime'] * 60);
ini_set('session.cookie_secure', $sessionConfig['secure'] ? '1' : '0');
ini_set('session.cookie_httponly', $sessionConfig['httponly'] ? '1' : '0');
ini_set('session.cookie_samesite', $sessionConfig['samesite']);

// Configura o tratamento de erros baseado no ambiente
if ($environment->isProduction()) {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
}

// Configura o timezone
date_default_timezone_set('America/Sao_Paulo');

// Headers para prevenir cache do navegador
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

// Headers adicionais para garantir dados sempre atualizados
if (!headers_sent()) {
    header('X-Accel-Expires: 0');
    header('Surrogate-Control: no-store');
    header('Vary: *');
}

// Função global para obter a conexão com o banco
function getDB() {
    return Connection::getInstance();
}

// Função global para obter configurações
function config($key = null) {
    return Environment::getInstance()->getConfig($key);
}

// Função global para verificar o ambiente
function isProduction() {
    return Environment::getInstance()->isProduction();
}

function isLocal() {
    return Environment::getInstance()->isLocal();
}

// Função para debug (só funciona em desenvolvimento)
function debug($data, $die = false) {
    if (!isProduction()) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($die) die();
    }
}

// Função para gerar versão de assets (cache-busting)
function assetVersion($file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        return $file . '?v=' . filemtime($filePath);
    }
    return $file . '?v=' . time();
}

// Função para gerar timestamp único para dados dinâmicos
function getCacheBuster() {
    return time() . '_' . mt_rand(1000, 9999);
}

// Inicia a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log do ambiente atual (apenas em desenvolvimento)
if (!isProduction()) {
    error_log("SGQ PRO: Sistema inicializado no ambiente: " . $environment->getEnvironment());
}
