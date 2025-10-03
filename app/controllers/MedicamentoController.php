<?php
require_once __DIR__ . '/../models/MedicamentoModel.php';

class MedicamentoController {
    private $model;
    private $itemsPerPage = 50;

    public function __construct() {
        $this->model = new MedicamentoModel();
    }

    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $searchType = isset($_POST['type']) ? $_POST['type'] : (isset($_GET['type']) ? $_GET['type'] : 'medicamento');
        $searchTerm = isset($_POST['search']) ? $_POST['search'] : (isset($_GET['search']) ? $_GET['search'] : '');

        $offset = ($page - 1) * $this->itemsPerPage;

        if (!empty($searchTerm)) {
            $result = $this->model->search($searchType, $searchTerm, $offset, $this->itemsPerPage);
        } else {
            $result = $this->model->getAll($offset, $this->itemsPerPage);
        }

        $procedimentos = $result['data'];
        $total = $result['total'];
        $totalPages = ceil($total / $this->itemsPerPage);

        include __DIR__ . '/../views/medicamentos/index.php';
    }

    public function view() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id > 0) {
            $medicamento = $this->model->getById($id);
            
            if ($medicamento) {
                include __DIR__ . '/../views/medicamentos/view.php';
                return;
            }
        }
        
        // Se não encontrou, redireciona para a lista
        header('Location: /ISCMC/medicamentos/');
        exit;
    }

    public function search() {
        $this->index();
    }

    public function export() {
        // Implementar exportação em CSV/Excel
        header('Location: /ISCMC/medicamentos/');
        exit;
    }
}
?>