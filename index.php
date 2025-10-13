<?php
// index.php - Ponto de entrada da aplicação

// Configurações básicas
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Roteamento básico
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;
$controller_name = $_GET['controller'] ?? 'dashboard'; // Alterado para dashboard como padrão

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
            
        default:
            // Se não encontrou, vai para dashboard
            require_once __DIR__ . '/app/controllers/DashboardController.php';
            $controller = new DashboardController();
            break;
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
?>