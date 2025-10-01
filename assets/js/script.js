/**
 * ISCMC Portal - Xintra Style Interactions
 */

class ISCMCApp {
    constructor() {
        this.init();
    }

    init() {
        this.initTableInteractions();
        this.initFormEnhancements();
        //this.initSearch();
        this.initToolbar();
        this.initUIComponents();
    }

    initTableInteractions() {
        // Seleção de linhas da tabela
        document.addEventListener('click', (e) => {
            const row = e.target.closest('tbody tr');
            if (row && !e.target.closest('a, button')) {
                this.toggleRowSelection(row);
            }
        });

        // Ordenação de colunas
        const sortableHeaders = document.querySelectorAll('th[data-sortable]');
        sortableHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                this.sortTable(header);
            });
        });
    }

    initToolbar() {
        const searchInput = document.querySelector('.search-input');
        const searchButton = document.querySelector('.toolbar .btn-primary');
        const filterSelect = document.querySelector('.filter-select');
        
        if (searchInput && searchButton) {
            searchButton.addEventListener('click', () => {
                this.performSearch(searchInput.value, filterSelect.value);
            });
            
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch(searchInput.value, filterSelect.value);
                }
            });
        }
    }

    toggleRowSelection(row) {
        row.classList.toggle('selected');
    }

    sortTable(header) {
        const table = header.closest('table');
        const columnIndex = Array.from(header.parentNode.children).indexOf(header);
        const isAscending = header.classList.contains('sort-asc');
        
        // Remove classes de ordenação de todos os headers
        table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        
        // Adiciona classe ao header atual
        header.classList.toggle('sort-asc', !isAscending);
        header.classList.toggle('sort-desc', isAscending);
        
        // Implementar lógica de ordenação aqui
        console.log(`Ordenando coluna ${columnIndex} - ${isAscending ? 'desc' : 'asc'}`);
    }

    initFormEnhancements() {
        // Foco em inputs
        const inputs = document.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', () => {
                input.parentElement.classList.remove('focused');
            });
        });

        // Validação em tempo real
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                this.validateField(input);
            });
        });
    }

    validateField(field) {
        // Implementar validações específicas
        if (field.validity) {
            if (field.validity.valid) {
                field.classList.remove('error');
            } else {
                field.classList.add('error');
            }
        }
    }

    initSearch() {
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.performSearch(e.target.value);
            }, 300));
        }
    }
    performSearch(term, filterType) {
        console.log(`Buscando: "${term}" no filtro: ${filterType}`);
        // Implementar lógica de busca específica
        this.showLoading();
        
        setTimeout(() => {
            this.hideLoading();
            // Atualizar tabela com resultados
        }, 500);
    }
    
    updateSearchResults(count) {
        let counter = document.getElementById('search-results-count');
        if (!counter) {
            counter = document.createElement('div');
            counter.id = 'search-results-count';
            counter.className = 'text-sm text-muted mt-2';
            document.querySelector('.search-box').appendChild(counter);
        }
        counter.textContent = `${count} resultados encontrados`;
    }

    initUIComponents() {
        // Tooltips
        this.initTooltips();
        
        // Loading states
        this.initLoadingStates();
    }

    initTooltips() {
        const elements = document.querySelectorAll('[title]');
        elements.forEach(el => {
            el.addEventListener('mouseenter', this.showTooltip);
            el.addEventListener('mouseleave', this.hideTooltip);
        });
    }

    showTooltip(e) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = e.target.title;
        tooltip.style.cssText = `
            position: absolute;
            background: #1f2937;
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            z-index: 50;
            white-space: nowrap;
            pointer-events: none;
        `;
        
        document.body.appendChild(tooltip);
        
        const rect = e.target.getBoundingClientRect();
        tooltip.style.left = rect.left + 'px';
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
        
        e.target._tooltip = tooltip;
    }

    hideTooltip(e) {
        if (e.target._tooltip) {
            e.target._tooltip.remove();
            e.target._tooltip = null;
        }
    }

    initLoadingStates() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <span class="spinner"></span>
                        Processando...
                    `;
                    
                    // Restaurar após 5 segundos (fallback)
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }, 5000);
                }
            });
        });
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Exportação
    exportData(format = 'csv') {
        this.showLoading();
        
        // Simular exportação
        setTimeout(() => {
            const data = "data:text/csv;charset=utf-8,";
            const encodedUri = encodeURI(data);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `procedimentos_${new Date().toISOString().split('T')[0]}.${format}`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            this.hideLoading();
            this.showNotification('Dados exportados com sucesso', 'success');
        }, 1000);
    }

    showLoading() {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        `;
        overlay.innerHTML = '<div class="spinner" style="width: 2rem; height: 2rem;"></div>';
        document.body.appendChild(overlay);
    }

    hideLoading() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) overlay.remove();
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} fixed top-4 right-4 z-50 max-w-sm`;
        notification.style.cssText = `
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 50;
            max-width: 24rem;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    window.ISCMC_APP = new ISCMCApp();
});

// Utilitários globais
function formatDate(dateString) {
    if (!dateString) return '-';
    const options = { 
        year: 'numeric', 
        month: '2-digit', 
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('pt-BR', options);
}

function truncateText(text, length = 100) {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
}

function confirmAction(message = 'Tem certeza que deseja realizar esta ação?') {
    return confirm(message);
}