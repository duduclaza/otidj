<?php

namespace App\Core;

class DebugLogger
{
    private static $instance = null;
    private $logs = [];
    private $logFile;
    private $sessionId;
    
    private function __construct()
    {
        try {
            $this->sessionId = session_id() ?: uniqid();
            $this->logFile = __DIR__ . '/../../storage/logs/debug_' . date('Y-m-d') . '.log';
            
            // Garantir que o diretório existe
            $logDir = dirname($this->logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            
            // Só configurar handlers se conseguiu criar o diretório
            if (is_dir($logDir) && is_writable($logDir)) {
                $this->setupErrorHandlers();
                
                // Log de início da sessão
                $this->log('SESSION_START', 'Nova sessão iniciada', [
                    'session_id' => $this->sessionId,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            // Se falhar, continuar sem debug
            error_log('DebugLogger constructor failed: ' . $e->getMessage());
        }
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function setupErrorHandlers(): void
    {
        // Handler para erros PHP
        set_error_handler([$this, 'handleError']);
        
        // Handler para exceções não capturadas
        set_exception_handler([$this, 'handleException']);
        
        // Handler para erros fatais
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    public function handleError($severity, $message, $file, $line): bool
    {
        $this->log('PHP_ERROR', $message, [
            'severity' => $this->getSeverityName($severity),
            'file' => $file,
            'line' => $line,
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ]);
        
        return false; // Permite que o handler padrão também execute
    }
    
    public function handleException($exception): void
    {
        $this->log('EXCEPTION', $exception->getMessage(), [
            'class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
    
    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $this->log('FATAL_ERROR', $error['message'], [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $this->getSeverityName($error['type'])
            ]);
        }
    }
    
    public function log(string $type, string $message, array $context = []): void
    {
        $logEntry = [
            'timestamp' => microtime(true),
            'datetime' => date('Y-m-d H:i:s.u'),
            'session_id' => $this->sessionId,
            'type' => $type,
            'message' => $message,
            'context' => $context,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI'
        ];
        
        // Adicionar à memória
        $this->logs[] = $logEntry;
        
        // Manter apenas os últimos 100 logs na memória
        if (count($this->logs) > 100) {
            array_shift($this->logs);
        }
        
        // Salvar no arquivo
        $this->writeToFile($logEntry);
    }
    
    private function writeToFile(array $logEntry): void
    {
        try {
            if (!$this->logFile || !is_writable(dirname($this->logFile))) {
                return; // Não consegue escrever, ignora
            }
            
            $line = sprintf(
                "[%s] %s: %s | %s | Memory: %s\n",
                $logEntry['datetime'],
                $logEntry['type'],
                $logEntry['message'],
                $logEntry['url'],
                $this->formatBytes($logEntry['memory_usage'])
            );
            
            @file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
            
            // Se há contexto, salvar detalhes
            if (!empty($logEntry['context'])) {
                $contextLine = "    Context: " . json_encode($logEntry['context'], JSON_PRETTY_PRINT) . "\n";
                @file_put_contents($this->logFile, $contextLine, FILE_APPEND | LOCK_EX);
            }
        } catch (\Exception $e) {
            // Se falhar ao escrever, ignora silenciosamente
        }
    }
    
    public function getLogs(): array
    {
        return $this->logs;
    }
    
    public function getLogsSince(float $timestamp): array
    {
        return array_filter($this->logs, function($log) use ($timestamp) {
            return $log['timestamp'] > $timestamp;
        });
    }
    
    public function generateReport(): array
    {
        $stats = $this->getStats();
        
        return [
            'session_id' => $this->sessionId,
            'generated_at' => date('Y-m-d H:i:s'),
            'stats' => $stats,
            'logs' => $this->logs,
            'system_info' => $this->getSystemInfo()
        ];
    }
    
    private function getStats(): array
    {
        $types = array_count_values(array_column($this->logs, 'type'));
        $memoryUsage = array_column($this->logs, 'memory_usage');
        
        return [
            'total_logs' => count($this->logs),
            'log_types' => $types,
            'memory_peak' => max($memoryUsage ?: [0]),
            'memory_current' => memory_get_usage(true),
            'execution_time' => microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))
        ];
    }
    
    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'error_reporting' => error_reporting(),
            'display_errors' => ini_get('display_errors'),
            'log_errors' => ini_get('log_errors')
        ];
    }
    
    private function getSeverityName(int $severity): string
    {
        $severities = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED'
        ];
        
        return $severities[$severity] ?? 'UNKNOWN';
    }
    
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    // Métodos de conveniência
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }
    
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }
    
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }
    
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }
    
    public function sql(string $query, array $params = [], float $executionTime = 0): void
    {
        $this->log('SQL', $query, [
            'params' => $params,
            'execution_time' => $executionTime . 'ms'
        ]);
    }
}
