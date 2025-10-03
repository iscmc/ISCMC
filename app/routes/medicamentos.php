<?php
require_once __DIR__ . '/../controllers/MedicamentoController.php';

$controller = new MedicamentoController();
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch ($action) {
    case 'view':
        $controller->view();
        break;
    case 'search':
        $controller->search();
        break;
    case 'export':
        $controller->export();
        break;
    case 'index':
    default:
        $controller->index();
        break;
}
?>