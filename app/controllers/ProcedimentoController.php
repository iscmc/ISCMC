<?php
require_once __DIR__ . '/../models/ProcedimentoModel.php';

class ProcedimentoController {
    private $model;
    
    public function __construct() {
        $this->model = new ProcedimentoModel();
    }
    
    public function index() {
        try {
            // Paginação
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = 50;
            $offset = ($page - 1) * $limit;
            
            // Busca
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            if (!empty($search)) {
                // Implementar busca se necessário
                $procedimentos = $this->model->getAllProcedimentos($limit, $offset);
            } else {
                $procedimentos = $this->model->getAllProcedimentos($limit, $offset);
            }
            
            $total = $this->model->getTotalProcedimentos();
            $totalPages = ceil($total / $limit);
            
            require_once __DIR__ . '/../views/procedimentos/index.php';
            
        } catch (Exception $e) {
            $this->showError("Erro ao carregar procedimentos: " . $e->getMessage());
        }
    }
    
    public function view($id) {
        try {
            $procedimento = $this->model->getProcedimentoById($id);
            
            if (!$procedimento) {
                $this->showError("Procedimento não encontrado");
                return;
            }
            
            // Busca outros procedimentos do mesmo paciente
            $procedimentosPaciente = [];
            if (isset($procedimento['CD_PACIENTE'])) {
                $procedimentosPaciente = $this->model->getProcedimentosByPaciente($procedimento['CD_PACIENTE']);
            }
            
            require_once __DIR__ . '/../views/procedimentos/view.php';
            
        } catch (Exception $e) {
            $this->showError("Erro ao carregar procedimento: " . $e->getMessage());
        }
    }
    
    public function search() {
        try {
            $search = isset($_POST['search']) ? trim($_POST['search']) : '';
            $type = isset($_POST['type']) ? $_POST['type'] : 'procedimento';
            
            $results = [];
            
            if (!empty($search)) {
                switch ($type) {
                    case 'paciente':
                        $results = $this->model->getProcedimentosByPaciente($search);
                        break;
                    case 'atendimento':
                        $results = $this->model->getProcedimentosByAtendimento($search);
                        break;
                    default:
                        // Busca geral - implementar se necessário
                        $results = $this->model->getAllProcedimentos(100, 0);
                        break;
                }
            }
            
            require_once __DIR__ . '/../views/procedimentos/search.php';
            
        } catch (Exception $e) {
            $this->showError("Erro na busca: " . $e->getMessage());
        }
    }
    
    private function showError($message) {
        echo "<div class='alert alert-danger'>$message</div>";
    }
}
?>