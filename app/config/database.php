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
 * Configuração de banco de dados para consulta ISCMC
 * Usa a mesma conexão do TASYBackup
 * Padrão Singleton com tratamento de erros
 */

class DatabaseConfig {
    private static $connection = null;
    
    private static $localDb = [
        'host' => 'localhost',
        'port' => '1521',
        'sid' => 'XE',
        'user' => 'SYSTEM',
        'pass' => 'K@t7y317',
        'charset' => 'AL32UTF8'
    ];
    
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                $db = self::$localDb;
                $tns = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=".$db['host'].")(Port=".$db['port']."))
                        (CONNECT_DATA=(SID=".$db['sid'].")))";
                
                self::$connection = oci_connect(
                    $db['user'], 
                    $db['pass'], 
                    $tns, 
                    $db['charset']
                );
                
                if (!self::$connection) {
                    $error = oci_error();
                    throw new Exception("Falha na conexão com banco local: " . $error['message']);
                }
                
            } catch (Exception $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new Exception("Não foi possível conectar ao banco de dados.");
            }
        }
        
        return self::$connection;
    }
    
    // Encerrar a conexão
    public static function closeConnection() {
        if (self::$connection !== null) {
            oci_close(self::$connection);
            self::$connection = null;
        }
    }
    
    /**
     * Método para verificar se a conexão está ativa
     */
    public static function isConnected() {
        if (self::$connection === null) {
            return false;
        }
        
        // Testa a conexão com uma query simples
        $test = oci_parse(self::$connection, "SELECT 1 FROM DUAL");
        return oci_execute($test);
    }
}
?>