<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISCMC - Consulta de Procedimentos</title>
    <link rel="icon" type="image/x-icon" href="assets/images/icone-site.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="/ISCMC/assets/css/style.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FA0F0F',
                        'primary-dark': '#CC0C0C',
                        secondary: '#6B7280',
                        success: '#0d9488',
                        danger: '#EF4444',
                        warning: '#F59E0B',
                        info: '#0EA5E9',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-gray-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/ISCMC/" class="flex items-center space-x-3 text-white hover:text-gray-200">
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
                    <a href="/ISCMC/" class="text-white bg-primary px-3 py-2 rounded-md font-medium flex items-center space-x-2">
                        <i class="fas fa-procedures"></i>
                        <span>Procedimentos</span>
                    </a>
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