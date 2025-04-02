<?php
class Database {
    private $conn;

    public function connect($host, $username, $password, $service_name) {
        try {
            $this->conn = oci_connect($username, $password, "$host/$service_name");
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
        'host' => 'oracle-cloud-host',
        'username' => 'cloud_user',
        'password' => 'cloud_pass',
        'service_name' => 'tasy_emr_service'
    ],
    'local' => [
        'host' => 'localhost',
        'username' 'local_user',
        'password' => 'local_pass',
        'service_name' => 'xe'
    ]
];

// Conexão com Oracle Cloud
$cloud_db = new Database();
$cloud_conn = $cloud_db->connect(
    $config['cloud']['host'],
    $config['cloud']['username'],
    $config['cloud']['password'],
    $config['cloud']['service_name']
);

// Conexão com Oracle XE Local
$local_db = new Database();
$local_conn = $local_db->connect(
    $config['local']['host'],
    $config['local']['username'],
    $config['local']['password'],
    $config['local']['service_name']
);
?>