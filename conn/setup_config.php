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

require_once 'conexao.php';

$sql = "
    CREATE TABLE backup_config (
        id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
        cloud_host VARCHAR2(100),
        cloud_username VARCHAR2(50),
        cloud_password VARCHAR2(50),
        cloud_service VARCHAR2(50),
        local_host VARCHAR2(100),
        local_username VARCHAR2(50),
        local_password VARCHAR2(50),
        local_service VARCHAR2(50),
        interval_minutes NUMBER DEFAULT 15,
        last_backup TIMESTAMP
    )
";

$stid = oci_parse($local_conn, $sql);
if (oci_execute($stid)) {
    echo "Tabela de configuração criada com sucesso.";
} else {
    $e = oci_error($stid);
    echo "Erro ao criar tabela: " . $e['message'];
}

// Inserir configuração inicial
$insert_sql = "
    INSERT INTO backup_config (
        cloud_host, cloud_username, cloud_password, cloud_service,
        local_host, local_username, local_password, local_service
    ) VALUES (:cloud_host, :cloud_username, :cloud_password, :cloud_service,
              :local_host, :local_username, :local_password, :local_service)
";

$stid = oci_parse($local_conn, $insert_sql);
oci_bind_by_name($stid, ':cloud_host', $config['cloud']['host']);
oci_bind_by_name($stid, ':cloud_username', $config['cloud']['username']);
oci_bind_by_name($stid, ':cloud_password', $config['cloud']['password']);
oci_bind_by_name($stid, ':cloud_service', $config['cloud']['service_name']);
oci_bind_by_name($stid, ':local_host', $config['local']['host']);
oci_bind_by_name($stid, ':local_username', $config['local']['username']);
oci_bind_by_name($stid, ':local_password', $config['local']['password']);
oci_bind_by_name($stid, ':local_service', $config['local']['service_name']);

if (oci_execute($stid)) {
    echo "Configuração inicial salva.";
} else {
    $e = oci_error($stid);
    echo "Erro ao salvar configuração: " . $e['message'];
}

$local_db->disconnect();
?>