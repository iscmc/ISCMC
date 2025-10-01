<?php
/**
 * Configuração de banco de dados para consulta ISCMC
 * Usa a mesma conexão do TASYBackup
 */

class DatabaseConfig {
    public static $localDb = [
        'host' => 'localhost',
        'port' => '1521',
        'sid' => 'XE',
        'user' => 'SYSTEM',
        'pass' => 'K@t7y317',
        'charset' => 'AL32UTF8'
    ];
    
    public static function getConnection() {
        $db = self::$localDb;
        $tns = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=".$db['host'].")(Port=".$db['port']."))
                (CONNECT_DATA=(SID=".$db['sid'].")))";
        
        $conn = oci_connect($db['user'], $db['pass'], $tns, $db['charset']);
        if (!$conn) {
            throw new Exception("Falha na conexão com banco local: " . oci_error());
        }
        return $conn;
    }
}
?>