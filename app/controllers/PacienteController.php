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
require_once __DIR__ . '/../models/PacienteModel.php';

class PacienteController {
    private $model;
    
    public function __construct() {
        $this->model = new PacienteModel();
    }
    
    /**
     * Exibe detalhes do paciente
     */
    public function view($id = null) {
        // Se o ID veio como parâmetro do método
        $cdPaciente = $id ?: ($_GET['id'] ?? null);
        
        if (!$cdPaciente) {
            $this->showError("ID do paciente não especificado");
            return;
        }
        
        try {
            // Busca dados do paciente
            $paciente = $this->model->getPacienteById($cdPaciente);
            
            // Busca procedimentos do paciente
            $procedimentos = $this->model->getProcedimentosByPaciente($cdPaciente);
            
            // Busca materiais do paciente
            $materiais = $this->model->getMateriaisByPaciente($cdPaciente);

            // Busca medicamentos do paciente
            $medicamentos = $this->model->getMedicamentosByPaciente($cdPaciente);
            
            require_once __DIR__ . '/../views/pacientes/view.php';
            
        } catch (Exception $e) {
            $this->showError("Erro ao carregar dados do paciente: " . $e->getMessage());
        }
    }
    
    /**
     * Action index - redireciona ou mostra lista
     */
    public function index() {
        // Pode implementar uma lista de pacientes aqui se necessário
        $this->showError("Ação não implementada. Use a view para ver detalhes de um paciente específico.");
    }
    
    private function showError($message) {
        echo "<div class='alert alert-danger'>$message</div>";
    }
}
?>