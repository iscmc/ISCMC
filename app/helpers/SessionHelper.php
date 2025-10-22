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
 * 
 * helpers/SessionHelper.php
 */

class SessionHelper {
    
    public static function startSession() {
        if (session_status() == PHP_SESSION_NONE) {            
            session_start();
        }
    }
    
    public static function getCurrentEstabelecimento() {
        self::startSession();
        return isset($_SESSION['cd_estabelecimento']) ? $_SESSION['cd_estabelecimento'] : 89;
    }
    
    public static function getEstabelecimentoName() {
        self::startSession();
        return isset($_SESSION['nm_fantasia_estab']) ? $_SESSION['nm_fantasia_estab'] : 'Estabelecimento Matriz';
    }
    
    public static function getEstabelecimentoSigla() {
        self::startSession();
        return isset($_SESSION['nm_sigla_estab']) ? $_SESSION['nm_sigla_estab'] : '';
    }
    
    public static function setEstabelecimento($cd_estabelecimento, $nm_fantasia_estab, $nm_sigla_estab = '') {
        self::startSession();
        $_SESSION['cd_estabelecimento'] = $cd_estabelecimento;
        $_SESSION['nm_fantasia_estab'] = $nm_fantasia_estab;
        $_SESSION['nm_sigla_estab'] = $nm_sigla_estab;
    }
}