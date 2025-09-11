<?php
/**
 * Servidor de contingência ISCMC Off frid
 *
 * Este arquivo faz parte do framework MVC Projeto Contingenciamento.
 *
 * @category Framework
 * @package  Servidor de contingência ISCMC
 * @author   Sergio Figueroa <sergio.figueroa@iscmc.com.br>
 * @license  MIT, Apache
 * @link     http://10.132.16.43/ISCMC
 * @version  1.0.0
 * @since    2025-04-01
 * @maindev  Sergio Figueroa
 */

 class Database {
    private $conn;

    public function connect($host, $username, $password, $service_name = null, $sid = null) {
        try {
            // Determina a string de conexão com base em service_name ou sid
            if ($service_name) {
                $connection_string = "$host/$service_name";
            } elseif ($sid) {
                $connection_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=1521))(CONNECT_DATA=(SID=$sid)))";
            } else {
                throw new Exception("Service name ou SID deve ser fornecido.");
            }

            $this->conn = oci_connect($username, $password, $connection_string);
            if (!$this->conn) {
                $e = oci_error();
                throw new Exception("Erro de conexão: " . $e['message']);
            }
            return $this->conn;
        } catch (Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    public function disconnect() {
        if ($this->conn) {
            oci_close($this->conn);
        }
    }
}

// Configuração inicial do backend
$config = [
    'cloud' => [
        'host' => '10.250.250.214',
        'username' => 'ISCMC',
        'password' => 'FFEYXAASY',
        'service_name' => 'dbhomol.tasy'
    ],
    'local' => [
        'host' => 'localhost',
        'username' => 'SYSTEM',
        'password' => 'K@t7y317',
        'sid' => 'xe'
    ]
];

// Conexão com Oracle Cloud
$cloud_db = new Database();
$cloud_conn = $cloud_db->connect(
    $config['cloud']['host'],
    $config['cloud']['username'],
    $config['cloud']['password'],
    $config['cloud']['service_name'],
);

// Conexão com Oracle XE Local
$local_db = new Database();
$local_conn = $local_db->connect(
    $config['local']['host'],
    $config['local']['username'],
    $config['local']['password'],
    null,
    $config['local']['sid']
);
?>