<?php
// index.php - Ponto de entrada da aplicação

// Configurações básicas
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclui o controller
require_once __DIR__ . '/app/controllers/ProcedimentoController.php';

// Roteamento básico
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

$controller = new ProcedimentoController();

try {
    switch ($action) {
        case 'view':
            if ($id) {
                $controller->view($id);
            } else {
                $controller->index();
            }
            break;
            
        case 'search':
            $controller->search();
            break;
            
        case 'index':
        default:
            $controller->index();
            break;
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger m-4'>Erro: " . $e->getMessage() . "</div>";
}
?>