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

// index.php - Ponto de entrada da aplicação

// Configurações básicas
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =================================================================
// NOVA FUNCIONALIDADE: Verificação de acesso ao front-end
// =================================================================

// Carrega helper de configurações
require_once __DIR__ . '/app/helpers/ConfigHelper.php';

// Verifica se o front-end está ativo
try {
    if (!ConfigHelper::isFrontendActive()) {
        // Front-end bloqueado - mostra página "Coming Soon"
        require_once __DIR__ . '/app/views/coming-soon.php';
        exit; // Termina execução aqui
    }
} catch (Exception $e) {
    // Em caso de erro, registra mas permite o acesso (fail-open)
    error_log("Erro na verificação de acesso: " . $e->getMessage());
    // Continua com a execução normal
}

// =================================================================
// CÓDIGO EXISTENTE
// =================================================================

// Roteamento básico - Suporta tanto 'controller' quanto 'page' para compatibilidade
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Determina o controller baseado em 'controller' ou 'page'
if (isset($_GET['controller'])) {
    $controller_name = $_GET['controller'];
} elseif (isset($_GET['page'])) {
    // Mapeia 'page' para 'controller' para compatibilidade
    $page_to_controller = [
        'dashboard' => 'dashboard',
        'ocupacao_setor' => 'ocupacao_setor'
    ];
    $controller_name = $page_to_controller[$_GET['page']] ?? 'dashboard';
} else {
    $controller_name = 'dashboard'; // Padrão
}

// Se acessar a raiz sem parâmetros, redireciona para dashboard
if (empty($_GET) && $_SERVER['REQUEST_URI'] === '/ISCMC/') {
    header('Location: /ISCMC/?controller=dashboard');
    exit;
}

try {
    // Define qual controller usar
    switch ($controller_name) {
        case 'dashboard':
            require_once __DIR__ . '/app/controllers/DashboardController.php';
            $controller = new DashboardController();
            break;

        case 'estabelecimento': // ADICIONE ESTE CASO
            require_once __DIR__ . '/app/controllers/EstabelecimentoController.php';
            $controller = new EstabelecimentoController();
            break;

        case 'medicamentos':
            if (file_exists(__DIR__ . '/app/controllers/MedicamentoController.php')) {
                require_once __DIR__ . '/app/controllers/MedicamentoController.php';
                $controller = new MedicamentoController();
            } else {
                require_once __DIR__ . '/app/views/medicamentos/index.php';
                exit;
            }
            break;
            
        case 'procedimento':
            require_once __DIR__ . '/app/controllers/ProcedimentoController.php';
            $controller = new ProcedimentoController();
            break;

        case 'ocupacao_setor':
            require_once __DIR__ . '/app/controllers/OcupacaoSetorController.php';
            $controller = new OcupacaoSetorController();
            break;

        case 'pacientes':
            require_once __DIR__ . '/app/controllers/PacienteController.php';
            $controller = new PacienteController();
            break;
            
        default:
            // Se não encontrou, vai para dashboard
            header('Location: index.php?controller=dashboard');
            exit;
    }

    // Executa a ação
    if (method_exists($controller, $action)) {
        $controller->$action($id);
    } else {
        $controller->index();
    }
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger m-4'>Erro: " . $e->getMessage() . "</div>";
}

// Fecha conexão ao final da execução (boa prática)
register_shutdown_function(function() {
    if (class_exists('DatabaseConfig')) {
        DatabaseConfig::closeConnection();
    }
});
?>