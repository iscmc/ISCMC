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

function getConfig($conn) {
    $sql = "SELECT * FROM backup_config WHERE id = 1";
    $stid = oci_parse($conn, $sql);
    oci_execute($stid);
    return oci_fetch_assoc($stid);
}

function incrementalBackup($cloud_conn, $local_conn, $last_backup) {
    $tables = ['tabela1', 'tabela2', 'tabela3', 'tabela4', 'tabela5', 'tabela6', 'tabela7', 'tabela8'];
    foreach ($tables as $table) {
        $sql = "
            INSERT INTO $table
            SELECT * FROM $table@cloud_db_link
            WHERE last_update > :last_backup
        ";
        $stid = oci_parse($local_conn, $sql);
        oci_bind_by_name($stid, ':last_backup', $last_backup);
        if (oci_execute($stid)) {
            echo "Backup incremental de $table concluído.\n";
        } else {
            $e = oci_error($stid);
            echo "Erro em $table: " . $e['message'] . "\n";
        }
    }

    // Atualiza o timestamp do último backup
    $update_sql = "UPDATE backup_config SET last_backup = SYSTIMESTAMP WHERE id = 1";
    $stid = oci_parse($local_conn, $update_sql);
    oci_execute($stid);
}

while (true) {
    $config = getConfig($local_conn);
    $interval = $config['INTERVAL_MINUTES'] * 60; // Converte para segundos
    $last_backup = $config['LAST_BACKUP'];

    // Tenta reconectar se necessário
    if (!$cloud_conn) {
        $cloud_conn = $cloud_db->connect(
            $config['CLOUD_HOST'], $config['CLOUD_USERNAME'],
            $config['CLOUD_PASSWORD'], $config['CLOUD_SERVICE']
        );
    }

    if ($cloud_conn && $local_conn) {
        incrementalBackup($cloud_conn, $local_conn, $last_backup);
    } else {
        echo "Conexão perdida. Aguardando reconexão...\n";
    }

    sleep($interval); // Aguarda o intervalo configurado
}

$cloud_db->disconnect();
$local_db->disconnect();
?>