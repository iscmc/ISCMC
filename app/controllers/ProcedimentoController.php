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
            
            if (!empty($search)) { // Implementar busca se necessário
                $procedimentos = $this->model->getAllProcedimentos($limit, $offset);
            } else {
                $procedimentos = $this->model->getAllProcedimentos($limit, $offset);
            }
            
            $total = $this->model->getTotalProcedimentos();
            $totalPages = ceil($total / $limit);
            
            require_once __DIR__ . '/../views/procedimentos/index.php';
            
        } catch (Exception $e) {
            $this->showError("Erro ao carregar procedimentos: " . $e->getMessage());
        } finally {
            // Fecha conexão para liberar recursos
            DatabaseConfig::closeConnection();
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
    
    /**
     * Busca procedimentos
     */
    public function search() {
        try {
            $searchType = $_POST['type'] ?? 'procedimento';
            $searchTerm = $_POST['search'] ?? '';
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $perPage = 50;
            $offset = ($page - 1) * $perPage;

            $model = new ProcedimentoModel();
            
            // Busca os procedimentos baseado no tipo
            switch ($searchType) {
                case 'atendimento':
                    $procedimentos = $model->getProcedimentosByAtendimento($searchTerm);
                    $total = count($procedimentos); // Para busca específica, não temos paginação
                    break;
                    
                case 'paciente':
                    $procedimentos = $model->getProcedimentosByPaciente($searchTerm);
                    $total = count($procedimentos);
                    break;
                    
                case 'procedimento':
                default:
                    $procedimentos = $model->searchProcedimentos($searchTerm, $limit = $perPage, $offset = $offset);
                    $total = $model->getSearchCount($searchTerm);
                    break;
            }

            $totalPages = ceil($total / $perPage);

            // Carrega a view de resultados
            require_once __DIR__ . '/../views/procedimentos/search.php';
            
        } catch (Exception $e) {
            $this->handleError($e->getMessage());
        }
    }
    
    private function showError($message) {
        echo "<div class='alert alert-danger'>$message</div>";
    }
}
?>