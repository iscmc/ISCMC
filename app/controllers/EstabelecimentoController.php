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

require_once __DIR__ . '/../helpers/SessionHelper.php';
require_once __DIR__ . '/../models/EstabelecimentoModel.php';

class EstabelecimentoController {
    
    private $estabelecimentoModel;
    
    public function __construct() {
        $this->estabelecimentoModel = new EstabelecimentoModel();
    }
    
    public function changeEstabelecimento() {
        // DEBUG: Verificar o que está chegando
        error_log("POST data: " . print_r($_POST, true));
        
        if ($_POST && isset($_POST['cd_estabelecimento'])) {
            $cd_estabelecimento = (int)$_POST['cd_estabelecimento'];
            $nm_fantasia_estab = $_POST['nm_fantasia_estab'] ?? 'Estabelecimento';
            $nm_sigla_estab = $_POST['nm_sigla_estab'] ?? '';
            
            // DEBUG
            error_log("Mudando estabelecimento para: CD=$cd_estabelecimento, NOME=$nm_fantasia_estab, SIGLA=$nm_sigla_estab");
            
            SessionHelper::setEstabelecimento($cd_estabelecimento, $nm_fantasia_estab, $nm_sigla_estab);
            
            // DEBUG: Verificar sessão após mudança
            error_log("Sessão após mudança: " . print_r($_SESSION, true));
            
            // Redirecionar de volta para a página anterior
            $referer = $_SERVER['HTTP_REFERER'] ?? '/ISCMC/?controller=dashboard';
            header('Location: ' . $referer);
            exit;
        } else {
            // DEBUG: Se não chegou POST
            error_log("Nenhum POST recebido ou cd_estabelecimento não definido");
        }
    }
    
    public function getEstabelecimentosAtivos() {
        return $this->estabelecimentoModel->getEstabelecimentosAtivos();
    }
}