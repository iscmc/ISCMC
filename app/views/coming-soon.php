<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal ISCMC - Em Manutenção</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="p--4">
    <div class="max-w-4xl w-full mx-auto">
        <div class="glass rounded-2xl shadow-2xl overflow-hidden">
            <div class="md:flex">
                <!-- Imagem/Ilustração -->
                <div class="md:w-2/5 bg-gradient-to-br from-blue-600 to-purple-700 p-8 md:p-12 flex items-center justify-center">
                    <div class="text-center text-white">
                        <div class="pulse mb-6">
                            <i class="fas fa-tools text-8xl opacity-80"></i>
                        </div>
                        <h2 class="text-2xl font-bold mb-2">Portal ISCMC</h2>
                        <p class="opacity-90">Sistema de Contingenciamento</p>
                    </div>
                </div>
                
                <!-- Conteúdo -->
                <div class="md:w-3/5 p-8 md:p-12 bg-white">
                    <div class="mb-8">
                        <div class="inline-flex items-center px-4 py-2 rounded-full bg-yellow-100 text-yellow-800 text-sm font-semibold mb-6">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            EM MANUTENÇÃO
                        </div>
                        
                        <h1 class="text-4xl font-bold text-gray-800 mb-4">Serviço Temporariamente Pausado</h1>
                        
                        <p class="text-gray-600 text-lg mb-6">
                            O portal encontra-se em manutenção programada para melhorias e atualizações do sistema.
                        </p>
                        
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-blue-700">
                                        <strong>Previsão de retorno:</strong> Em breve<br>
                                        <strong>Motivo:</strong> Atualizações de sistema e manutenção preventiva
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-cogs text-purple-600 text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">O que está acontecendo?</h3>
                                <p class="text-gray-600">
                                    Estamos realizando atualizações críticas no sistema para melhorar a performance, segurança e adicionar novas funcionalidades.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-clock text-purple-600 text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">Quanto tempo vai levar?</h3>
                                <p class="text-gray-600">
                                    Nossa equipe técnica trabalha para concluir as atualizações o mais rápido possível. Agradecemos sua paciência.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-headset text-purple-600 text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">Precisa de ajuda?</h3>
                                <p class="text-gray-600">
                                    Para emergências, entre em contato com a equipe de TI:<br>
                                    <strong class="text-purple-700">ti@iscmc.com.br</strong> ou 
                                    <strong class="text-purple-700">(11) 99999-9999</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-10 pt-8 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-between items-center">
                            <div class="mb-4 sm:mb-0">
                                <img src="https://via.placeholder.com/150x50/667eea/ffffff?text=ISCMC" alt="ISCMC Logo" class="h-10">
                            </div>
                            <div class="text-sm text-gray-500">
                                <p>© <?php echo date('Y'); ?> ISCMC - Instituto de Saúde. Todos os direitos reservados.</p>
                                <p class="mt-1">Versão do Sistema: 1.0.0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contador (opcional) -->
        <div class="text-center mt-8 text-white text-sm">
            <p class="opacity-80">
                <i class="fas fa-sync-alt mr-2 animate-spin"></i>
                Sistema verifica automaticamente a cada 30 segundos
            </p>
            <button onclick="location.reload()" class="mt-4 px-6 py-2 bg-white text-purple-700 rounded-lg font-semibold hover:bg-gray-100 transition duration-200">
                <i class="fas fa-redo mr-2"></i> Tentar Novamente
            </button>
        </div>
    </div>
    
    <!-- Auto-refresh após 30 segundos -->
    <script>
        setTimeout(function() {
            location.reload();
        }, 30000); // 30 segundos
    </script>
</body>
</html>