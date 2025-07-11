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

$tables = [
    'USUARIO' => 'NM_USUARIO', // Tabela => Chave primária
    'CPOE_DIETA' => 'NR_SEQUENCIA',
    'CPOE_MATERIAL' => 'NR_SEQUENCIA',
    'CPOE_PROCEDIMENTO' => 'NR_SEQUENCIA',
    'CPOE_GASOTERAPIA' => 'NR_SEQUENCIA',
    'CPOE_RECOMENDACAO' => 'NR_SEQUENCIA',
    'CPOE_HEMOTERAPIA' => 'NR_SEQUENCIA',
    'CPOE_DIALISE' => 'NR_SEQUENCIA',
    'CPOE_INTERVENCAO' => 'NR_SEQUENCIA',
    'CPOE_ANATOMIA_PATOLOGICA' => 'NR_SEQUENCIA'
];

function getConfig($conn) {
    $sql = "SELECT * FROM backup_config WHERE id = 1";
    $stid = oci_parse($conn, $sql);
    oci_execute($stid);
    return oci_fetch_assoc($stid);
}

function incrementalBackup($cloud_conn, $local_conn) {
    global $tables;
    foreach ($tables as $table => $primary_key) {
        // Obtém o maior valor da chave primária no banco local
        $max_sql = "SELECT MAX($primary_key) AS max_key FROM $table";
        $stid = oci_parse($local_conn, $max_sql);
        oci_execute($stid);
        $row = oci_fetch_assoc($stid);
        $max_key = $row['MAX_KEY'] ?? null;

        // Copia apenas registros novos (com chave primária maior que o máximo local)
        if ($max_key !== null) {
            $sql = "
                INSERT /*+ APPEND */ INTO $table
                SELECT * FROM $table@cloud_db_link
                WHERE $primary_key > :max_key
            ";
            $stid = oci_parse($local_conn, $sql);
            oci_bind_by_name($stid, ':max_key', $max_key);
        } else {
            // Se a tabela local estiver vazia, copia tudo
            $sql = "
                INSERT /*+ APPEND */ INTO $table
                SELECT * FROM $table@cloud_db_link
            ";
            $stid = oci_parse($local_conn, $sql);
        }

        if (oci_execute($stid, OCI_NO_AUTO_COMMIT)) {
            oci_commit($local_conn);
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

    // Tenta reconectar se necessário
    if (!$cloud_conn) {
        $cloud_conn = $cloud_db->connect(
            $config['CLOUD_HOST'], $config['CLOUD_USERNAME'],
            $config['CLOUD_PASSWORD'], $config['CLOUD_SERVICE']
        );
    }

    if ($cloud_conn && $local_conn) {
        try {
            incrementalBackup($cloud_conn, $local_conn);
        } catch (Exception $e) {
            echo "Erro no backup incremental: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Conexão perdida. Aguardando reconexão...\n";
    }

    sleep($interval); // Aguarda o intervalo configurado
}

$cloud_db->disconnect();
$local_db->disconnect();
?>