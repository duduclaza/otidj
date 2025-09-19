<?php

namespace App\Controllers;

use App\Core\DebugLogger;

class DebugController
{
    /**
     * Get current debug logs via AJAX
     */
    public function getLogs()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $logger = DebugLogger::getInstance();
            $since = $_GET['since'] ?? 0;
            
            if ($since > 0) {
                $logs = $logger->getLogsSince((float)$since);
            } else {
                $logs = $logger->getLogs();
            }
            
            echo json_encode([
                'success' => true,
                'logs' => $logs,
                'timestamp' => microtime(true)
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Generate and download debug report
     */
    public function generateReport()
    {
        try {
            $logger = DebugLogger::getInstance();
            $report = $logger->generateReport();
            
            $filename = 'debug_report_' . date('Y-m-d_H-i-s') . '.json';
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            
            echo json_encode($report, JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Clear debug logs
     */
    public function clearLogs()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Limpar arquivo de log do dia
            $logFile = __DIR__ . '/../../storage/logs/debug_' . date('Y-m-d') . '.log';
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Logs limpos com sucesso'
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao limpar logs: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Test debug logging
     */
    public function test()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $logger = DebugLogger::getInstance();
            
            // Gerar alguns logs de teste
            $logger->info('Teste de log INFO', ['test' => true]);
            $logger->warning('Teste de log WARNING', ['level' => 'warning']);
            $logger->error('Teste de log ERROR', ['simulated' => true]);
            $logger->debug('Teste de log DEBUG', ['debug_data' => ['a' => 1, 'b' => 2]]);
            
            // Simular erro PHP
            trigger_error('Erro de teste simulado', E_USER_WARNING);
            
            echo json_encode([
                'success' => true,
                'message' => 'Logs de teste gerados com sucesso'
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro no teste: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}
