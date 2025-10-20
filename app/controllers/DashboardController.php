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
require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardController {
    private $model;
    
    public function __construct() {
        $this->model = new DashboardModel();
    }
    
    public function index() {
        try {
            $dadosOcupacao = $this->model->buscarOcupacaoHospitalar();
            $totais = $this->model->calcularTotaisOcupacao($dadosOcupacao);
            
            require_once __DIR__ . '/../views/layout/header.php';
            require_once __DIR__ . '/../views/dashboard.php';
            require_once __DIR__ . '/../views/layout/footer.php';
            
        } catch (Exception $e) {
            $this->showError("Erro ao carregar dashboard: " . $e->getMessage());
        }
    }
    
    private function showError($message) {
        echo "<div class='alert alert-danger'>$message</div>";
    }
}
?>