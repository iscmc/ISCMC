    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <h6>Irmandade da Santa Casa de Misericórdia de Curitiba</h6>
                    <p class="mb-0 small">
                        Sistema de Contingência - Consulta de Procedimentos<br>
                        <span class="text-muted">Versão 1.0</span>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="mb-2">
                        <span class="badge bg-success">
                            <i class="fas fa-database me-1"></i>
                            Conectado ao Backup Local
                        </span>
                    </div>
                    <p class="small text-muted mb-0">
                        &copy; <?php echo date('Y'); ?> ISCMC - Todos os direitos reservados<br>
                        Desenvolvido para contingência do TASY EMR pela equipe de TI Sistemas
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/ISCMC/assets/js/script.js"></script>
    
    <script>
        // Funções JavaScript para melhorar a experiência do usuário
        document.addEventListener('DOMContentLoaded', function() {
            // Adiciona loading state aos botões de submit
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processando...';
                        submitBtn.disabled = true;
                    }
                });
            });

            // Auto-hide alerts após 5 segundos
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            if (alert.parentNode) {
                                alert.parentNode.removeChild(alert);
                            }
                        }, 300);
                    }
                }, 5000);
            });

            // Tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Função para confirmar ações
        function confirmAction(message) {
            return confirm(message || 'Tem certeza que deseja realizar esta ação?');
        }

        // Função para mostrar loading
        function showLoading() {
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            `;
            overlay.innerHTML = `
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            `;
            document.body.appendChild(overlay);
            return overlay;
        }

        // Função para esconder loading
        function hideLoading(overlay) {
            if (overlay && overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }
    </script>
</body>
</html>