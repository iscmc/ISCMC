<?php
require_once 'conexao.php';

$tables = ['tabela1', 'tabela2', 'tabela3', 'tabela4', 'tabela5', 'tabela6', 'tabela7', 'tabela8'];

foreach ($tables as $table) {
    // Limpa a tabela local
    $truncate_sql = "TRUNCATE TABLE $table";
    $stid = oci_parse($local_conn, $truncate_sql);
    oci_execute($stid);

    // Copia todos os dados da nuvem
    $copy_sql = "INSERT INTO $table SELECT * FROM $table@cloud_db_link";
    $stid = oci_parse($local_conn, $copy_sql);
    if (oci_execute($stid)) {
        echo "Tabela $table copiada com sucesso.\n";
    } else {
        $e = oci_error($stid);
        echo "Erro ao copiar $table: " . $e['message'] . "\n";
    }
}

// Atualiza o timestamp do último backup
$update_sql = "UPDATE backup_config SET last_backup = SYSTIMESTAMP WHERE id = 1";
$stid = oci_parse($local_conn, $update_sql);
oci_execute($stid);

$cloud_db->disconnect();
$local_db->disconnect();
?>