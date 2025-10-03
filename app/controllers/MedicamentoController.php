<?php
require_once __DIR__ . '/../models/MedicamentoModel.php';

class MedicamentoController {
    private $model;
    
    public function __construct() {
        $this->model = new MedicamentoModel();
    }
    
    public function index() {
        try {
            // Paginação
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = 50;
            $offset = ($page - 1) * $limit;
            
            // Busca
            $searchType = $_GET['type'] ?? 'medicamento';
            $searchTerm = $_GET['search'] ?? '';
            
            if (!empty($searchTerm)) {
                $medicamentos = $this->model->search($searchType, $searchTerm, $offset, $limit);
            } else {
                $medicamentos = $this->model->getAll($offset, $limit);
            }
            
            $total = $this->model->getTotalMedicamentos();
            $totalPages = ceil($total / $limit);
            
            require_once __DIR__ . '/../views/medicamentos/index.php';
            
        } catch (Exception $e) {
            $this->showError("Erro ao carregar medicamentos: " . $e->getMessage());
        }
    }
    
    public function view($id) {
        try {
            $medicamento = $this->model->getById($id);
            
            if (!$medicamento) {
                $this->showError("Medicamento não encontrado");
                return;
            }
            
            require_once __DIR__ . '/../views/medicamentos/view.php';
            
        } catch (Exception $e) {
            $this->showError("Erro ao carregar medicamento: " . $e->getMessage());
        }
    }
    
    public function search() {
        $this->index();
    }
    
    private function showError($message) {
        echo "<div class='alert alert-danger'>$message</div>";
    }
}
?>