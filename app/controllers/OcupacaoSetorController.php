<?php
require_once __DIR__ . '/../models/OcupacaoSetorModel.php';

class OcupacaoSetorController {
    private $model;

    public function __construct() {
        $this->model = new OcupacaoSetorModel();
    }

    public function index() {
        // Verificar se o parâmetro setor foi passado
        // Pode vir como 'setor' (query string) ou como parte da URL
        if (isset($_GET['setor'])) {
            $setor = $_GET['setor'];
        } else {
            // Tentar obter da query string se vier pela rota
            parse_str($_SERVER['QUERY_STRING'], $queryParams);
            $setor = isset($queryParams['setor']) ? $queryParams['setor'] : null;
        }

        if (!$setor) {
            header('Location: index.php?page=dashboard');
            exit;
        }

        try {
            // Buscar informações do setor
            $setorInfo = $this->model->getSetorInfo($setor);

            if (!$setorInfo) {
                throw new Exception("Setor não encontrado");
            }

            // Buscar pacientes do setor
            $pacientes = $this->model->getPacientesPorSetor($setor);

            require_once 'app/views/layout/header.php'; // Incluir header
            require 'app/views/ocupacao_setor.php';// Carregar a view
            require_once 'app/views/layout/footer.php'; // Incluir footer

        } catch (Exception $e) {
            // Em caso de erro, redirecionar para dashboard com mensagem
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?page=dashboard');
            exit;
        }
    }
}
?>