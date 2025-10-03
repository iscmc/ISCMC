    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-auto">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div class="text-center md:text-left">
                    <h6 class="font-bold text-lg mb-2">Irmandade da Santa Casa de Misericórdia de Curitiba</h6>
                    <p class="text-gray-300 text-sm">
                        Sistema de Contingência - Consulta de Procedimentos<br>
                        <span class="text-gray-400">Versão 1.0</span>
                    </p>
                </div>
                <div class="text-center md:text-right">
                    <div class="mb-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fas fa-database mr-1"></i>
                            Conectado ao Backup Local
                        </span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        &copy; <?php echo date('Y'); ?> ISCMC - Todos os direitos reservados<br>
                        Desenvolvido para contingência do TASY EMR pela equipe de TI Sistemas
                    </p>
                </div>
            </div>
        </div>
    </footer>

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
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processando...';
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
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
        });

        // Função para confirmar ações
        function confirmAction(message) {
            return confirm(message || 'Tem certeza que deseja realizar esta ação?');
        }

        // Função para mostrar loading
        function showLoading() {
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50';
            overlay.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-xl flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                    <span class="text-gray-700">Carregando...</span>
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