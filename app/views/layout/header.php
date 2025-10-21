<?php
/**
 * Portal de contingência ISCMC Off Grid
 *
 * Este arquivo faz parte do framework MVC Projeto Contingenciamento - FrontEnd.
 *
 * @category Framework
 * @package  Servidor de contingência ISCMC
 * @author   Sergio Figueroa <sergio.figueroa@iscmc.com.br>
 * @license  MIT, Apache
 * @link     http://10.132.16.43/ISCMC
 * @version  1.0.0
 * @since    2025-09-01
 * @maindev  Sergio Figueroa
 */

// Detectar módulo atual baseado na URL
$moduloAtual = 'cpoe'; // Padrão: CPOE - escolha
$moduloAtualTexto = 'CPOE - escolha';
$moduloAtualIcone = 'fas fa-list';
$moduloAtualBg = 'bg-green-600';

// Verificar se estamos no módulo de medicamentos
if (strpos($_SERVER['REQUEST_URI'], '/ISCMC/medicamentos/') !== false) {
    $moduloAtual = 'medicamentos';
    $moduloAtualTexto = 'Soluções e Medicamentos';
    $moduloAtualIcone = 'fas fa-pills';
    $moduloAtualBg = 'bg-info';
} 
// Verificar se estamos no módulo de procedimentos
elseif (strpos($_SERVER['REQUEST_URI'], '/ISCMC/procedimentos/') !== false) {
    $moduloAtual = 'procedimentos';
    $moduloAtualTexto = 'Procedimentos';
    $moduloAtualIcone = 'fas fa-procedures';
    $moduloAtualBg = 'bg-primary';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISCMC - Sistema de Contingencia</title>
    <link rel="icon" type="image/x-icon" href="/ISCMC/assets/images/icone-site.png">

    <!-- Tailwind CSS 
    <script src="https://cdn.tailwindcss.com"></script> -->
    <link href="/ISCMC/dist/output.css" rel="stylesheet">
    <link href="/ISCMC/assets/css/tailwind.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/ISCMC/assets/css/all.min.css">
    
    <!-- Minha própria folha de estilos  -->
    <link href="/ISCMC/assets/css/style.css" rel="stylesheet">
    <script src="/ISCMC/assets/js/chart.js"></script>
    
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-gray-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/ISCMC/?controller=dashboard" class="flex items-center space-x-3 text-white hover:text-gray-200">
                        <img src="/ISCMC/assets/images/logo-ISCMC.png" style="margin-top:10px;">
                        <span class="font-bold text-lg">ISCMC - Sistema de Contingência</span>
                    </a>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-300 hover:text-white focus:outline-none focus:text-white">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <!-- Dropdown de Procedimentos -->
                    <div class="relative group">
                        <button class="text-white <?php echo $moduloAtualBg; ?> px-3 py-2 rounded-md font-medium flex items-center space-x-2 hover:bg-primary-dark transition duration-200">
                            <i class="<?php echo $moduloAtualIcone; ?> "></i>
                            <span><?php echo $moduloAtualTexto; ?></span>
                            <i class="fas fa-chevron-down text-xs ml-1 text-white"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute left-0 mt-2 w-64 bg-white rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 border border-gray-200">
                            <div class="py-2">
                                <?php if ($moduloAtual !== 'medicamentos'): ?>
                                <a href="/ISCMC/medicamentos/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-pills text-blue-500 w-5"></i>
                                    <span>Soluções e Medicamentos</span>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($moduloAtual !== 'procedimentos'): ?>
                                <a href="/ISCMC/procedimentos/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-procedures text-red-500 w-5"></i>
                                    <span>Procedimentos</span>
                                </a>
                                <?php endif; ?>
                                
                                <?php
                                /*
                                <a href="/ISCMC/nutricao/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-apple-alt text-green-500 w-5"></i>
                                    <span>Nutrição</span>
                                </a>
                                <a href="/ISCMC/gasoterapia/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-wind text-cyan-500 w-5"></i>
                                    <span>Gasoterapia</span>
                                </a>
                                <a href="/ISCMC/recomendacoes/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-stethoscope text-purple-500 w-5"></i>
                                    <span>Recomendações</span>
                                </a>
                                <a href="/ISCMC/hemoterapia/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-tint text-red-600 w-5"></i>
                                    <span>Hemoterapia</span>
                                </a>
                                <a href="/ISCMC/dialise/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-droplet text-orange-500 w-5"></i>
                                    <span>Diálise</span>
                                </a>
                                */
                                ?>
                            </div>
                        </div>
                    </div>

                    <a href="/TASYBackup/" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium flex items-center space-x-2">
                        <i class="fas fa-database"></i>
                        <span>Backup</span>
                    </a>
                    
                    <div class="flex items-center space-x-2 text-sm text-gray-300">
                        <i class="fas fa-database"></i>
                        <span>Conectado ao Backup Local</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1">