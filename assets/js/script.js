// DOM Elements
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.sidebar');
const mainContent = document.querySelector('.main-content');

// Sidebar Toggle Functionality
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('open');
        
        // Add overlay for mobile
        if (window.innerWidth <= 1024) {
            toggleOverlay();
        }
    });
}

// Create and toggle overlay for mobile
function toggleOverlay() {
    let overlay = document.querySelector('.sidebar-overlay');
    
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        `;
        document.body.appendChild(overlay);
        
        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            toggleOverlay();
        });
    }
    
    if (sidebar.classList.contains('open')) {
        overlay.style.opacity = '1';
        overlay.style.visibility = 'visible';
    } else {
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
    }
}

// Close sidebar on window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 1024) {
        sidebar.classList.remove('open');
        const overlay = document.querySelector('.sidebar-overlay');
        if (overlay) {
            overlay.style.opacity = '0';
            overlay.style.visibility = 'hidden';
        }
    }
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Loading states for buttons
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="loading"></span> Carregando...';
    button.disabled = true;
    
    return function hideLoading() {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

// Form validation helper
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        const value = field.value ? field.value.trim() : '';
        
        // Verifica diferentes tipos de campo
        if (field.type === 'checkbox' || field.type === 'radio') {
            if (!field.checked) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        } else if (field.tagName.toLowerCase() === 'select') {
            if (!value || value === '' || value === '0') {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        } else {
            // Campos de texto, email, etc.
            if (!value || value.length < 1) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        }
    });
    
    return isValid;
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-left: 4px solid ${getNotificationColor(type)};
        z-index: 9999;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 400px;
    `;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto hide after 5 seconds
    const autoHide = setTimeout(() => {
        hideNotification(notification);
    }, 5000);
    
    // Close button functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        clearTimeout(autoHide);
        hideNotification(notification);
    });
    
    function hideNotification(notification) {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function getNotificationColor(type) {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    return colors[type] || '#3b82f6';
}

// Table sorting functionality
function initTableSorting() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('th[data-sortable]');
        
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.innerHTML += ' <i class="fas fa-sort sort-icon"></i>';
            
            header.addEventListener('click', () => {
                sortTable(table, header);
            });
        });
    });
}

function sortTable(table, header) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const headerIndex = Array.from(header.parentNode.children).indexOf(header);
    const isAscending = !header.classList.contains('sort-asc');
    
    // Remove sort classes from all headers
    header.parentNode.querySelectorAll('th').forEach(h => {
        h.classList.remove('sort-asc', 'sort-desc');
        const icon = h.querySelector('.sort-icon');
        if (icon) icon.className = 'fas fa-sort sort-icon';
    });
    
    // Add sort class to current header
    header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
    const icon = header.querySelector('.sort-icon');
    if (icon) {
        icon.className = `fas fa-sort-${isAscending ? 'up' : 'down'} sort-icon`;
    }
    
    // Sort rows
    rows.sort((a, b) => {
        const aValue = a.children[headerIndex].textContent.trim();
        const bValue = b.children[headerIndex].textContent.trim();
        
        // Try to parse as numbers
        const aNum = parseFloat(aValue);
        const bNum = parseFloat(bValue);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }
        
        // String comparison
        return isAscending ? 
            aValue.localeCompare(bValue) : 
            bValue.localeCompare(aValue);
    });
    
    // Reorder DOM
    rows.forEach(row => tbody.appendChild(row));
}

// Search functionality
function initSearch() {
    const searchInputs = document.querySelectorAll('[data-search]');
    
    searchInputs.forEach(input => {
        const targetSelector = input.getAttribute('data-search');
        const target = document.querySelector(targetSelector);
        
        if (target) {
            input.addEventListener('input', (e) => {
                filterContent(target, e.target.value);
            });
        }
    });
}

function filterContent(container, searchTerm) {
    const items = container.querySelectorAll('[data-searchable]');
    const term = searchTerm.toLowerCase();
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        const matches = text.includes(term);
        
        item.style.display = matches ? '' : 'none';
    });
}

// Initialize all functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initTableSorting();
    initSearch();
    initTabs();
    initSidebar();
    
    // Add AJAX form submission
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Sempre previne submit padrão
            
            // Debug: log form data
            console.log('Form submission attempt:', form.id || 'unnamed form');
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: "${value}"`);
            }
            
            if (!validateForm(form)) {
                console.log('Form validation failed');
                showNotification('Por favor, preencha todos os campos obrigatórios.', 'error');
                
                // Highlight first invalid field
                const firstError = form.querySelector('.error');
                if (firstError) {
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }
            
            console.log('Form validation passed - submitting via AJAX');
            const submitBtn = form.querySelector('button[type="submit"]');
            const hideLoading = submitBtn ? showLoading(submitBtn) : null;
            
            // Determina a ação baseada no ID do form
            let action = '';
            switch(form.id) {
                case 'form-filiais':
                    action = 'add_filial';
                    break;
                case 'form-departamentos':
                    action = 'add_departamento';
                    break;
                case 'form-fornecedores':
                    action = 'add_fornecedor';
                    break;
                default:
                    console.error('Form ID não reconhecido:', form.id);
                    if (hideLoading) hideLoading();
                    return;
            }
            
            // Adiciona a ação ao FormData
            formData.append('action', action);
            
            // Envia via AJAX
            fetch('api/process_form.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    form.reset(); // Limpa o formulário
                    
                    // Atualiza a lista correspondente
                    updateList(form.id, data.data);
                } else {
                    showNotification(data.message || 'Erro ao processar solicitação', 'error');
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                showNotification('Erro de conexão. Tente novamente.', 'error');
            })
            .finally(() => {
                if (hideLoading) hideLoading();
            });
        });
    });
});

// Cache-busting para requisições AJAX
function getCacheBuster() {
    return Date.now() + '_' + Math.floor(Math.random() * 10000);
}

// Função para fazer requisições AJAX sem cache
function fetchWithoutCache(url, options = {}) {
    const separator = url.includes('?') ? '&' : '?';
    const urlWithCacheBuster = `${url}${separator}_cb=${getCacheBuster()}`;
    
    // Adiciona headers para prevenir cache
    const defaultOptions = {
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0',
            ...options.headers
        },
        ...options
    };
    
    return fetch(urlWithCacheBuster, defaultOptions);
}

// Função para recarregar dados dinamicamente
function refreshData(callback) {
    // Força recarregamento de dados sem cache
    const timestamp = getCacheBuster();
    
    if (typeof callback === 'function') {
        callback(timestamp);
    }
    
    // Dispara evento customizado para componentes que precisam se atualizar
    document.dispatchEvent(new CustomEvent('dataRefresh', { 
        detail: { timestamp } 
    }));
}

// Auto-refresh de dados a cada 30 segundos (opcional)
function enableAutoRefresh(intervalSeconds = 30) {
    setInterval(() => {
        refreshData();
    }, intervalSeconds * 1000);
}

// Intercepta todas as requisições fetch para adicionar cache-busting
const originalFetch = window.fetch;
window.fetch = function(url, options = {}) {
    // Se não for uma URL externa, adiciona cache-busting
    if (typeof url === 'string' && !url.startsWith('http') && !url.includes('_cb=')) {
        return fetchWithoutCache(url, options);
    }
    return originalFetch(url, options);
};

// Função para atualizar listas após inserção
function updateList(formId, data) {
    let listId = '';
    switch(formId) {
        case 'form-filiais':
            listId = 'lista-filiais';
            break;
        case 'form-departamentos':
            listId = 'lista-departamentos';
            break;
        case 'form-fornecedores':
            listId = 'lista-fornecedores';
            break;
    }
    
    if (listId && data) {
        const list = document.getElementById(listId);
        if (list) {
            const newItem = document.createElement('div');
            newItem.className = 'bg-gray-50 border border-gray-200 rounded-md p-3 hover:bg-gray-100 transition duration-200';
            newItem.textContent = data.nome || data.nome_filial || data.nome_departamento;
            list.appendChild(newItem);
        }
    }
}

// Tab functionality
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'text-primary', 'border-primary', 'bg-blue-50');
                btn.classList.add('text-gray-500', 'border-transparent');
            });
            
            tabContents.forEach(content => {
                content.classList.remove('active');
                content.style.display = 'none';
            });
            
            // Add active class to clicked button
            this.classList.add('active', 'text-primary', 'border-primary', 'bg-blue-50');
            this.classList.remove('text-gray-500', 'border-transparent');
            
            // Show target content
            const targetContent = document.getElementById(targetTab);
            if (targetContent) {
                targetContent.classList.add('active');
                targetContent.style.display = 'block';
            }
        });
    });
}

// Sidebar functionality
function initSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(e.target) && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                }
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                sidebar.classList.remove('open');
            }
        });
    }
}

// Export functions for global use
window.SGQ = {
    showNotification,
    showLoading,
    validateForm,
    sortTable,
    filterContent,
    fetchWithoutCache,
    refreshData,
    enableAutoRefresh,
    getCacheBuster,
    updateList,
    initTabs,
    initSidebar
};
