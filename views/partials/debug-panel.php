<!-- Debug Panel - Always Visible -->
<div id="debug-panel" class="fixed bottom-4 right-4 z-50">
    <!-- Toggle Button -->
    <button id="debug-toggle" class="bg-red-600 hover:bg-red-700 text-white p-3 rounded-full shadow-lg transition-all duration-300">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    </button>
    
    <!-- Debug Window -->
    <div id="debug-window" class="hidden absolute bottom-16 right-0 w-96 max-h-96 bg-gray-900 text-white rounded-lg shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-red-600 p-3 flex justify-between items-center">
            <h3 class="font-bold text-sm">🐛 Debug Logger</h3>
            <div class="flex space-x-2">
                <button id="debug-clear" class="text-xs bg-red-700 hover:bg-red-800 px-2 py-1 rounded" title="Limpar Logs">
                    🗑️
                </button>
                <button id="debug-test" class="text-xs bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded" title="Teste">
                    🧪
                </button>
                <button id="debug-download" class="text-xs bg-green-600 hover:bg-green-700 px-2 py-1 rounded" title="Download Relatório">
                    💾
                </button>
                <button id="debug-close" class="text-xs hover:bg-red-700 px-2 py-1 rounded" title="Fechar">
                    ✕
                </button>
            </div>
        </div>
        
        <!-- Stats -->
        <div id="debug-stats" class="bg-gray-800 p-2 text-xs border-b border-gray-700">
            <div class="grid grid-cols-3 gap-2">
                <div>Logs: <span id="log-count" class="text-yellow-400">0</span></div>
                <div>Erros: <span id="error-count" class="text-red-400">0</span></div>
                <div>Memória: <span id="memory-usage" class="text-blue-400">0</span></div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-gray-800 p-2 border-b border-gray-700">
            <div class="flex space-x-1 text-xs">
                <button class="debug-filter active" data-type="all">Todos</button>
                <button class="debug-filter" data-type="ERROR">Erros</button>
                <button class="debug-filter" data-type="WARNING">Avisos</button>
                <button class="debug-filter" data-type="INFO">Info</button>
                <button class="debug-filter" data-type="SQL">SQL</button>
            </div>
        </div>
        
        <!-- Logs Container -->
        <div id="debug-logs" class="overflow-y-auto max-h-48 p-2 text-xs">
            <div class="text-gray-400 text-center py-4">Carregando logs...</div>
        </div>
    </div>
</div>

<style>
.debug-filter {
    @apply px-2 py-1 rounded bg-gray-700 hover:bg-gray-600 transition-colors;
}
.debug-filter.active {
    @apply bg-blue-600 hover:bg-blue-700;
}
.debug-log-entry {
    @apply mb-2 p-2 rounded border-l-2;
}
.debug-log-ERROR {
    @apply bg-red-900 border-red-500;
}
.debug-log-WARNING {
    @apply bg-yellow-900 border-yellow-500;
}
.debug-log-INFO {
    @apply bg-blue-900 border-blue-500;
}
.debug-log-DEBUG {
    @apply bg-gray-800 border-gray-500;
}
.debug-log-SQL {
    @apply bg-purple-900 border-purple-500;
}
.debug-log-PHP_ERROR {
    @apply bg-red-900 border-red-400;
}
.debug-log-EXCEPTION {
    @apply bg-red-800 border-red-300;
}
</style>

<script>
class DebugPanel {
    constructor() {
        this.isOpen = false;
        this.logs = [];
        this.lastTimestamp = 0;
        this.currentFilter = 'all';
        this.pollInterval = null;
        
        this.initElements();
        this.bindEvents();
        this.startPolling();
    }
    
    initElements() {
        this.toggle = document.getElementById('debug-toggle');
        this.window = document.getElementById('debug-window');
        this.logsContainer = document.getElementById('debug-logs');
        this.logCount = document.getElementById('log-count');
        this.errorCount = document.getElementById('error-count');
        this.memoryUsage = document.getElementById('memory-usage');
    }
    
    bindEvents() {
        // Toggle panel
        this.toggle.addEventListener('click', () => this.togglePanel());
        
        // Close panel
        document.getElementById('debug-close').addEventListener('click', () => this.closePanel());
        
        // Clear logs
        document.getElementById('debug-clear').addEventListener('click', () => this.clearLogs());
        
        // Test logs
        document.getElementById('debug-test').addEventListener('click', () => this.testLogs());
        
        // Download report
        document.getElementById('debug-download').addEventListener('click', () => this.downloadReport());
        
        // Filters
        document.querySelectorAll('.debug-filter').forEach(btn => {
            btn.addEventListener('click', (e) => this.setFilter(e.target.dataset.type));
        });
    }
    
    togglePanel() {
        this.isOpen = !this.isOpen;
        this.window.classList.toggle('hidden', !this.isOpen);
        
        if (this.isOpen) {
            this.loadLogs();
        }
    }
    
    closePanel() {
        this.isOpen = false;
        this.window.classList.add('hidden');
    }
    
    startPolling() {
        this.pollInterval = setInterval(() => {
            if (this.isOpen) {
                this.loadLogs();
            }
        }, 2000); // Poll every 2 seconds
    }
    
    async loadLogs() {
        try {
            const response = await fetch(`/debug/logs?since=${this.lastTimestamp}`);
            const data = await response.json();
            
            if (data.success && data.logs.length > 0) {
                this.logs.push(...data.logs);
                this.lastTimestamp = data.timestamp;
                
                // Keep only last 50 logs
                if (this.logs.length > 50) {
                    this.logs = this.logs.slice(-50);
                }
                
                this.updateDisplay();
            }
        } catch (error) {
            console.error('Erro ao carregar logs:', error);
        }
    }
    
    updateDisplay() {
        this.updateStats();
        this.renderLogs();
    }
    
    updateStats() {
        const errorCount = this.logs.filter(log => 
            log.type.includes('ERROR') || log.type === 'EXCEPTION'
        ).length;
        
        const latestLog = this.logs[this.logs.length - 1];
        const memory = latestLog ? this.formatBytes(latestLog.memory_usage) : '0';
        
        this.logCount.textContent = this.logs.length;
        this.errorCount.textContent = errorCount;
        this.memoryUsage.textContent = memory;
    }
    
    renderLogs() {
        const filteredLogs = this.currentFilter === 'all' 
            ? this.logs 
            : this.logs.filter(log => log.type === this.currentFilter);
        
        if (filteredLogs.length === 0) {
            this.logsContainer.innerHTML = '<div class="text-gray-400 text-center py-4">Nenhum log encontrado</div>';
            return;
        }
        
        const html = filteredLogs.slice(-20).reverse().map(log => this.renderLogEntry(log)).join('');
        this.logsContainer.innerHTML = html;
        
        // Auto scroll to top (newest logs)
        this.logsContainer.scrollTop = 0;
    }
    
    renderLogEntry(log) {
        const time = new Date(log.datetime).toLocaleTimeString();
        const typeClass = `debug-log-${log.type}`;
        
        let contextHtml = '';
        if (log.context && Object.keys(log.context).length > 0) {
            contextHtml = `<div class="mt-1 text-gray-300 text-xs opacity-75">${JSON.stringify(log.context, null, 2)}</div>`;
        }
        
        return `
            <div class="debug-log-entry ${typeClass}">
                <div class="flex justify-between items-start">
                    <span class="font-bold text-xs">${log.type}</span>
                    <span class="text-xs opacity-75">${time}</span>
                </div>
                <div class="mt-1">${this.escapeHtml(log.message)}</div>
                ${contextHtml}
            </div>
        `;
    }
    
    setFilter(type) {
        this.currentFilter = type;
        
        // Update active filter button
        document.querySelectorAll('.debug-filter').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.type === type);
        });
        
        this.renderLogs();
    }
    
    async clearLogs() {
        try {
            const response = await fetch('/debug/clear', { method: 'POST' });
            const data = await response.json();
            
            if (data.success) {
                this.logs = [];
                this.lastTimestamp = 0;
                this.updateDisplay();
                this.showNotification('Logs limpos com sucesso', 'success');
            }
        } catch (error) {
            this.showNotification('Erro ao limpar logs', 'error');
        }
    }
    
    async testLogs() {
        try {
            const response = await fetch('/debug/test', { method: 'POST' });
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Logs de teste gerados', 'success');
                setTimeout(() => this.loadLogs(), 500);
            }
        } catch (error) {
            this.showNotification('Erro no teste', 'error');
        }
    }
    
    downloadReport() {
        window.open('/debug/report', '_blank');
        this.showNotification('Relatório sendo gerado...', 'info');
    }
    
    showNotification(message, type = 'info') {
        // Simple notification - you can enhance this
        const colors = {
            success: 'bg-green-600',
            error: 'bg-red-600',
            info: 'bg-blue-600'
        };
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-4 py-2 rounded shadow-lg z-50`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    formatBytes(bytes) {
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unitIndex = 0;
        
        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }
        
        return `${size.toFixed(1)}${units[unitIndex]}`;
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize debug panel when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.debugPanel = new DebugPanel();
});
</script>
