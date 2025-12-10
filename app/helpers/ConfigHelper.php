<?php
/**
 * Portal de contingência ISCMC Off Grid
 *
 * Helper para gerenciamento de configurações do sistema
 *
 * @category Framework
 * @package  Servidor de contingência ISCMC
 * @author   Sergio Figueroa <sergio.figueroa@iscmc.com.br>
 * @license  MIT, Apache
 * @link     http://10.132.16.43/ISCMC
 * @version  1.0.0
 * @since    2025-12-18
 * @maindev  Sergio Figueroa
 */
require_once __DIR__ . '/../config/database.php';

class ConfigHelper {
    private static $conn = null;
    
    /**
     * Verifica se o front-end está ativo
     */
    public static function isFrontendActive() {
        try {
            $conn = DatabaseConfig::getConnection();
            
            $sql = "SELECT VALOR FROM CONFIG WHERE CHAVE = 'FRONTEND_ACTIVE'";
            $stmt = oci_parse($conn, $sql);
            
            if (!oci_execute($stmt)) {
                // Se houver erro, assume que está ativo (fail-open)
                return true;
            }
            
            $row = oci_fetch_assoc($stmt);
            oci_free_statement($stmt);
            
            if (!$row || !isset($row['VALOR'])) {
                // Se não encontrar a configuração, permite acesso
                return true;
            }
            
            // TRUE = Bloqueado, FALSE/NULL = Liberado
            return strtoupper($row['VALOR']) !== 'TRUE';
            
        } catch (Exception $e) {
            error_log("Erro ao verificar configuração do front-end: " . $e->getMessage());
            // Em caso de erro, permite acesso (fail-open)
            return true;
        }
    }
    
    /**
     * Obtém o valor de uma configuração específica
     */
    public static function getConfig($chave) {
        try {
            $conn = DatabaseConfig::getConnection();
            
            $sql = "SELECT VALOR, DESCRICAO FROM CONFIG WHERE CHAVE = :chave";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ':chave', $chave);
            
            if (!oci_execute($stmt)) {
                throw new Exception("Erro ao buscar configuração");
            }
            
            $row = oci_fetch_assoc($stmt);
            oci_free_statement($stmt);
            
            return $row;
            
        } catch (Exception $e) {
            error_log("Erro ao obter configuração '$chave': " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Atualiza uma configuração
     */
    public static function setConfig($chave, $valor, $descricao = null, $usuario = 'SYSTEM') {
        try {
            $conn = DatabaseConfig::getConnection();
            
            // Verifica se a configuração já existe
            $sql_check = "SELECT COUNT(*) as count FROM CONFIG WHERE CHAVE = :chave";
            $stmt_check = oci_parse($conn, $sql_check);
            oci_bind_by_name($stmt_check, ':chave', $chave);
            oci_execute($stmt_check);
            $check = oci_fetch_assoc($stmt_check);
            oci_free_statement($stmt_check);
            
            if ($check['COUNT'] > 0) {
                // Atualiza existente
                $sql = "UPDATE CONFIG 
                       SET VALOR = :valor, 
                           DESCRICAO = :descricao,
                           DT_ATUALIZACAO = CURRENT_TIMESTAMP,
                           NM_USUARIO = :usuario
                       WHERE CHAVE = :chave";
            } else {
                // Insere nova
                $sql = "INSERT INTO CONFIG (CHAVE, VALOR, DESCRICAO, NM_USUARIO) 
                       VALUES (:chave, :valor, :descricao, :usuario)";
            }
            
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ':chave', $chave);
            oci_bind_by_name($stmt, ':valor', $valor);
            oci_bind_by_name($stmt, ':descricao', $descricao);
            oci_bind_by_name($stmt, ':usuario', $usuario);
            
            if (!oci_execute($stmt)) {
                throw new Exception("Erro ao salvar configuração");
            }
            
            oci_free_statement($stmt);
            oci_commit($conn);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao definir configuração '$chave': " . $e->getMessage());
            return false;
        }
    }
}
?>