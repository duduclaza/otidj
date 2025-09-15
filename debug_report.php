<?php
// Sistema de Debug Completo - SGQ PRO
require_once __DIR__ . '/bootstrap.php';

class DebugReport {
    private $db;
    private $environment;
    private $startTime;
    
    public function __construct() {
        $this->startTime = microtime(true);
        $this->environment = Environment::getInstance();
        try {
            $this->db = getDB();
        } catch (Exception $e) {
            $this->db = null;
        }
    }
    
    public function generateReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => $this->getEnvironmentInfo(),
            'server' => $this->getServerInfo(),
            'database' => $this->getDatabaseInfo(),
            'performance' => $this->getPerformanceInfo(),
            'security' => $this->getSecurityInfo(),
            'files' => $this->getFileSystemInfo(),
            'errors' => $this->getErrorInfo()
        ];
        
        return $report;
    }
    
    private function getEnvironmentInfo() {
        return [
            'detected_environment' => $this->environment->getEnvironment(),
            'is_production' => $this->environment->isProduction(),
            'is_local' => $this->environment->isLocal(),
            'host' => $_SERVER['HTTP_HOST'] ?? 'N/A',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'config' => $this->environment->getConfig()
        ];
    }
    
    private function getServerInfo() {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => date_default_timezone_get(),
            'extensions' => get_loaded_extensions()
        ];
    }
    
    private function getDatabaseInfo() {
        if (!$this->db) {
            return ['status' => 'ERRO', 'message' => 'Conexão não estabelecida'];
        }
        
        try {
            $info = [
                'status' => 'CONECTADO',
                'connection_test' => 'OK'
            ];
            
            // Informações do servidor MySQL
            $version = $this->db->fetchOne("SELECT VERSION() as version");
            $info['mysql_version'] = $version['version'] ?? 'N/A';
            
            // Tabelas existentes
            $tables = $this->db->fetchAll("SHOW TABLES");
            $info['tables'] = array_map(function($table) {
                return array_values($table)[0];
            }, $tables);
            
            // Contagem de registros por tabela
            $info['records'] = [];
            foreach ($info['tables'] as $table) {
                try {
                    $count = $this->db->fetchOne("SELECT COUNT(*) as total FROM `$table`");
                    $info['records'][$table] = $count['total'];
                } catch (Exception $e) {
                    $info['records'][$table] = 'ERRO: ' . $e->getMessage();
                }
            }
            
            // Status das conexões
            $status = $this->db->fetchAll("SHOW STATUS LIKE 'Connections'");
            $info['connections'] = $status[0]['Value'] ?? 'N/A';
            
            return $info;
            
        } catch (Exception $e) {
            return [
                'status' => 'ERRO',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
    
    private function getPerformanceInfo() {
        $currentTime = microtime(true);
        return [
            'execution_time' => round(($currentTime - $this->startTime) * 1000, 2) . 'ms',
            'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . 'MB',
            'memory_peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
            'included_files_count' => count(get_included_files()),
            'included_files' => get_included_files()
        ];
    }
    
    private function getSecurityInfo() {
        return [
            'https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'session_status' => session_status(),
            'session_id' => session_id(),
            'session_name' => session_name(),
            'headers_sent' => headers_sent(),
            'error_reporting' => error_reporting(),
            'display_errors' => ini_get('display_errors'),
            'log_errors' => ini_get('log_errors')
        ];
    }
    
    private function getFileSystemInfo() {
        $info = [
            'current_directory' => getcwd(),
            'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'N/A'
        ];
        
        // Verifica arquivos importantes
        $importantFiles = [
            'bootstrap.php',
            'composer.json',
            'composer.lock',
            '.htaccess',
            'api/process_form.php',
            'src/Config/Environment.php',
            'src/Database/Connection.php'
        ];
        
        $info['files_status'] = [];
        foreach ($importantFiles as $file) {
            $fullPath = __DIR__ . '/' . $file;
            $info['files_status'][$file] = [
                'exists' => file_exists($fullPath),
                'readable' => file_exists($fullPath) && is_readable($fullPath),
                'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                'modified' => file_exists($fullPath) ? date('Y-m-d H:i:s', filemtime($fullPath)) : 'N/A'
            ];
        }
        
        return $info;
    }
    
    private function getErrorInfo() {
        $errors = [];
        
        // Captura últimos erros do log
        if (function_exists('error_get_last')) {
            $lastError = error_get_last();
            if ($lastError) {
                $errors['last_error'] = $lastError;
            }
        }
        
        // Testa funcionalidades críticas
        $errors['tests'] = [];
        
        // Teste de escrita
        $testFile = __DIR__ . '/test_write.tmp';
        try {
            file_put_contents($testFile, 'test');
            $errors['tests']['file_write'] = 'OK';
            unlink($testFile);
        } catch (Exception $e) {
            $errors['tests']['file_write'] = 'ERRO: ' . $e->getMessage();
        }
        
        // Teste de sessão
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['debug_test'] = time();
            $errors['tests']['session'] = 'OK';
        } catch (Exception $e) {
            $errors['tests']['session'] = 'ERRO: ' . $e->getMessage();
        }
        
        return $errors;
    }
    
    public function renderHTML($report) {
        ob_start();
        ?>
        <div class="debug-report">
            <div class="debug-header">
                <h2>🔍 Relatório de Debug - SGQ PRO</h2>
                <p class="timestamp">Gerado em: <?= $report['timestamp'] ?></p>
                <div class="debug-actions">
                    <button onclick="toggleDebug()" class="btn btn-secondary">Minimizar/Expandir</button>
                    <button onclick="refreshDebug()" class="btn btn-primary">🔄 Atualizar</button>
                    <button onclick="exportDebug()" class="btn btn-success">📄 Exportar</button>
                </div>
            </div>
            
            <div id="debug-content" class="debug-content">
                <!-- Resumo Executivo -->
                <div class="debug-section summary">
                    <h3>📊 Resumo Executivo</h3>
                    <div class="summary-grid">
                        <div class="summary-item <?= $report['database']['status'] === 'CONECTADO' ? 'success' : 'error' ?>">
                            <span class="summary-icon"><?= $report['database']['status'] === 'CONECTADO' ? '✅' : '❌' ?></span>
                            <span class="summary-text">Banco de Dados</span>
                        </div>
                        <div class="summary-item <?= $report['environment']['is_production'] ? 'warning' : 'info' ?>">
                            <span class="summary-icon"><?= $report['environment']['is_production'] ? '🏭' : '🔧' ?></span>
                            <span class="summary-text"><?= $report['environment']['detected_environment'] ?></span>
                        </div>
                        <div class="summary-item <?= $report['security']['https'] ? 'success' : 'warning' ?>">
                            <span class="summary-icon"><?= $report['security']['https'] ? '🔒' : '⚠️' ?></span>
                            <span class="summary-text"><?= $report['security']['https'] ? 'HTTPS Ativo' : 'HTTP Apenas' ?></span>
                        </div>
                        <div class="summary-item info">
                            <span class="summary-icon">⚡</span>
                            <span class="summary-text"><?= $report['performance']['execution_time'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- Ambiente -->
                <div class="debug-section">
                    <h3>🌍 Informações do Ambiente</h3>
                    <div class="debug-grid">
                        <div><strong>Ambiente Detectado:</strong> <?= $report['environment']['detected_environment'] ?></div>
                        <div><strong>É Produção:</strong> <?= $report['environment']['is_production'] ? 'Sim' : 'Não' ?></div>
                        <div><strong>Host:</strong> <?= $report['environment']['host'] ?></div>
                        <div><strong>URL Base:</strong> <?= $report['environment']['config']['app']['url'] ?></div>
                        <div><strong>User Agent:</strong> <?= substr($report['environment']['user_agent'], 0, 50) ?>...</div>
                        <div><strong>Request URI:</strong> <?= $report['environment']['request_uri'] ?></div>
                    </div>
                </div>
                
                <!-- Servidor -->
                <div class="debug-section">
                    <h3>🖥️ Informações do Servidor</h3>
                    <div class="debug-grid">
                        <div><strong>PHP:</strong> <?= $report['server']['php_version'] ?></div>
                        <div><strong>Servidor:</strong> <?= $report['server']['server_software'] ?></div>
                        <div><strong>Memória:</strong> <?= $report['server']['memory_limit'] ?></div>
                        <div><strong>Timezone:</strong> <?= $report['server']['timezone'] ?></div>
                    </div>
                </div>
                
                <!-- Banco de Dados -->
                <div class="debug-section">
                    <h3>🗄️ Banco de Dados</h3>
                    <div class="status-<?= strtolower($report['database']['status']) ?>">
                        <strong>Status:</strong> <?= $report['database']['status'] ?>
                    </div>
                    <?php if ($report['database']['status'] === 'CONECTADO'): ?>
                        <div class="debug-grid">
                            <div><strong>MySQL:</strong> <?= $report['database']['mysql_version'] ?></div>
                            <div><strong>Conexões:</strong> <?= $report['database']['connections'] ?></div>
                        </div>
                        <div class="tables-info">
                            <h4>Tabelas e Registros:</h4>
                            <?php foreach ($report['database']['records'] as $table => $count): ?>
                                <div class="table-row">
                                    <span class="table-name"><?= $table ?></span>
                                    <span class="record-count"><?= $count ?> registros</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="error-message"><?= $report['database']['message'] ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Performance -->
                <div class="debug-section">
                    <h3>⚡ Performance</h3>
                    <div class="debug-grid">
                        <div><strong>Tempo de Execução:</strong> <?= $report['performance']['execution_time'] ?></div>
                        <div><strong>Uso de Memória:</strong> <?= $report['performance']['memory_usage'] ?></div>
                        <div><strong>Pico de Memória:</strong> <?= $report['performance']['memory_peak'] ?></div>
                        <div><strong>Arquivos Incluídos:</strong> <?= $report['performance']['included_files_count'] ?></div>
                    </div>
                </div>
                
                <!-- Segurança -->
                <div class="debug-section">
                    <h3>🔒 Segurança</h3>
                    <div class="debug-grid">
                        <div><strong>HTTPS:</strong> <?= $report['security']['https'] ? 'Ativo' : 'Inativo' ?></div>
                        <div><strong>Sessão:</strong> <?= $report['security']['session_status'] === PHP_SESSION_ACTIVE ? 'Ativa' : 'Inativa' ?></div>
                        <div><strong>Display Errors:</strong> <?= $report['security']['display_errors'] ? 'On' : 'Off' ?></div>
                        <div><strong>Log Errors:</strong> <?= $report['security']['log_errors'] ? 'On' : 'Off' ?></div>
                    </div>
                </div>
                
                <!-- Arquivos -->
                <div class="debug-section">
                    <h3>📁 Sistema de Arquivos</h3>
                    <?php foreach ($report['files']['files_status'] as $file => $status): ?>
                        <div class="file-status">
                            <span class="file-name"><?= $file ?></span>
                            <span class="status-<?= $status['exists'] ? 'ok' : 'error' ?>">
                                <?= $status['exists'] ? '✅' : '❌' ?>
                            </span>
                            <?php if ($status['exists']): ?>
                                <span class="file-info"><?= round($status['size']/1024, 1) ?>KB - <?= $status['modified'] ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Testes -->
                <div class="debug-section">
                    <h3>🧪 Testes do Sistema</h3>
                    <?php foreach ($report['errors']['tests'] as $test => $result): ?>
                        <div class="test-result">
                            <span class="test-name"><?= ucfirst(str_replace('_', ' ', $test)) ?>:</span>
                            <span class="status-<?= strpos($result, 'OK') !== false ? 'ok' : 'error' ?>">
                                <?= $result ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <style>
        .debug-report {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            overflow: hidden;
            border: 1px solid #e3e6f0;
        }
        
        .debug-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .debug-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }
        
        .debug-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        
        .timestamp {
            margin: 12px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
            font-weight: 300;
            position: relative;
            z-index: 1;
        }
        
        .debug-actions {
            display: flex;
            gap: 12px;
            margin-top: 15px;
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .btn-secondary { 
            background: #6c757d; 
            color: white; 
        }
        .btn-primary { 
            background: #007bff; 
            color: white; 
        }
        .btn-success { 
            background: #28a745; 
            color: white; 
        }
        
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .debug-content {
            padding: 25px;
            background: #f8f9fc;
        }
        
        .debug-section {
            margin-bottom: 25px;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: box-shadow 0.3s ease;
        }
        
        .debug-section:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        
        .debug-section h3 {
            background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
            margin: 0;
            padding: 18px 25px;
            border-bottom: 1px solid #e3e6f0;
            font-size: 16px;
            font-weight: 600;
            color: #5a5c69;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .debug-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 15px;
            padding: 25px;
        }
        
        .debug-grid > div {
            padding: 12px 15px;
            background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
            border-radius: 6px;
            font-size: 13px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }
        
        .debug-grid > div:hover {
            background: linear-gradient(135deg, #e3e6f0 0%, #f8f9fc 100%);
            transform: translateY(-1px);
        }
        
        .debug-grid > div strong {
            color: #5a5c69;
            font-weight: 600;
        }
        
        .debug-list {
            padding: 25px;
        }
        
        .debug-list ul {
            margin: 0;
            padding-left: 0;
            list-style: none;
        }
        
        .debug-list li {
            margin: 8px 0;
            padding: 12px 15px;
            background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
            border-radius: 6px;
            border-left: 3px solid #4e73df;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        
        .debug-list li:hover {
            background: linear-gradient(135deg, #e3e6f0 0%, #f8f9fc 100%);
            transform: translateX(5px);
        }
        
        .table-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f3f3;
        }
        
        .table-row:last-child {
            border-bottom: none;
        }
        
        .summary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
            pointer-events: none;
        }
        
        .summary h3 { 
            color: white; 
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            padding: 25px;
            position: relative;
            z-index: 1;
        }
        
        .summary-item {
            background: rgba(255,255,255,0.15);
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }
        
        .summary-item:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .summary-item.success { 
            border-left: 4px solid #28a745;
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.3);
        }
        .summary-item.error { 
            border-left: 4px solid #dc3545;
            box-shadow: 0 0 20px rgba(220, 53, 69, 0.3);
        }
        .summary-item.warning { 
            border-left: 4px solid #ffc107;
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.3);
        }
        .summary-item.info { 
            border-left: 4px solid #17a2b8;
            box-shadow: 0 0 20px rgba(23, 162, 184, 0.3);
        }
        
        .summary-icon { 
            font-size: 24px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        
        .summary-text { 
            font-weight: 600;
            font-size: 14px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        
        /* Animações */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .debug-section {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .debug-section:nth-child(1) { animation-delay: 0.1s; }
        .debug-section:nth-child(2) { animation-delay: 0.2s; }
        .debug-section:nth-child(3) { animation-delay: 0.3s; }
        .debug-section:nth-child(4) { animation-delay: 0.4s; }
        .debug-section:nth-child(5) { animation-delay: 0.5s; }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .debug-report {
                margin: 10px;
                border-radius: 8px;
            }
            
            .debug-grid {
                grid-template-columns: 1fr;
                padding: 15px;
            }
            
            .summary-grid {
                grid-template-columns: 1fr;
                padding: 15px;
            }
            
            .debug-actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .btn {
                width: 100%;
            }
        }
        </style>
        
        <script>
        function toggleDebug() {
            const content = document.getElementById('debug-content');
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
        }
        
        function refreshDebug() {
            window.location.reload();
        }
        
        function exportDebug() {
            const debugData = <?= json_encode($report, JSON_UNESCAPED_UNICODE) ?>;
            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(debugData, null, 2));
            const downloadAnchorNode = document.createElement('a');
            downloadAnchorNode.setAttribute("href", dataStr);
            downloadAnchorNode.setAttribute("download", "sgq_debug_report_" + new Date().toISOString().slice(0,19).replace(/:/g, '-') + ".json");
            document.body.appendChild(downloadAnchorNode);
            downloadAnchorNode.click();
            downloadAnchorNode.remove();
        }
        
        // Auto-refresh a cada 30 segundos se estiver na aba debug
        setInterval(function() {
            if (document.querySelector('.tab-button[data-tab="debug"]').classList.contains('active')) {
                const indicator = document.createElement('div');
                indicator.style.cssText = 'position:fixed;top:10px;right:10px;background:#28a745;color:white;padding:5px 10px;border-radius:3px;z-index:9999;font-size:12px;';
                indicator.textContent = 'Debug atualizado automaticamente';
                document.body.appendChild(indicator);
                setTimeout(() => indicator.remove(), 2000);
                
                // Atualiza apenas o conteúdo do debug via AJAX
                fetch(window.location.href)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newDebugContent = doc.querySelector('#debug .debug-report');
                        if (newDebugContent) {
                            document.querySelector('#debug .debug-report').innerHTML = newDebugContent.innerHTML;
                        }
                    });
            }
        }, 30000);
        </script>
        <?php
        return ob_get_clean();
    }
}
?>
