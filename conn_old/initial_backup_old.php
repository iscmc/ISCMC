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
    'USUARIO', 'CPOE_DIETA', 'CPOE_MATERIAL', 'CPOE_PROCEDIMENTO',
    'CPOE_GASOTERAPIA', 'CPOE_RECOMENDACAO', 'CPOE_HEMOTERAPIA',
    'CPOE_DIALISE', 'CPOE_INTERVENCAO', 'CPOE_ANATOMIA_PATOLOGICA'
];

// Schema do banco remoto onde as tabelas estão localizadas
$remote_schema = 'TASY';

// Função para verificar o Database Link
/*function testDatabaseLink($cloud_conn) {
    try {
        $sql = "SELECT * FROM DUAL@cloud_db_link;";
        $stid = oci_parse($cloud_conn, $sql);
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Database Link inválido: " . $e['message']);
        }
        echo "Database Link cloud_db_link está funcionando.\n";
        return true;
    } catch (Exception $e) {
        file_put_contents('error_log.txt', "Erro ao testar Database Link: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}*/
function testDatabaseLink($cloud_conn, $db_link_name) {
    $stid = null; // Inicializa para garantir que a variável exista
    try {
        $sql = "SELECT 1 FROM DUAL@" . $db_link_name . ";";
        $stid = oci_parse($cloud_conn, $sql);
        if (!$stid) { // Verifica se oci_parse falhou
            $e = oci_error($cloud_conn); // Erro na conexão, não no statement ainda
            throw new Exception("Erro ao preparar a declaração SQL: " . $e['message']);
        }
        oci_execute($stid);
        echo "Database Link " . $db_link_name . " está funcionando.<br>";
        return true;
    } catch (Exception $e) {
        file_put_contents('error_log.txt', "Erro ao testar Database Link '" . $db_link_name . "': " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    } finally {
        if ($stid) {
            oci_free_statement($stid); // Libera o statement
        }
    }
}

// Função para criar tabela no Oracle XE (se necessário)
function createTableIfNotExists($local_conn, $table, $cloud_conn, $remote_schema) {
    try {
        // Verifica se o Database Link está funcionando
        if (!testDatabaseLink($cloud_conn, 'CLOUD_DB_LINK')) {
            throw new Exception("Não é possível criar tabela $table: Database Link não está funcionando.");
        }

        // Obtém a estrutura da tabela do banco remoto, especificando o schema
        $sql = "SELECT DBMS_METADATA.GET_DDL('TABLE', :table_name, :schema_name) AS DDL FROM DUAL@cloud_db_link";
        $stid = oci_parse($cloud_conn, $sql);
        oci_bind_by_name($stid, ':table_name', $table);
        oci_bind_by_name($stid, ':schema_name', $remote_schema);
        echo $sql."-".$table."-".$remote_schema;
        exit();
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Erro ao obter DDL para $table no schema $remote_schema: " . $e['message']);
        }
        $row = oci_fetch_assoc($stid);
        $ddl = $row['DDL'];

        if (empty($ddl)) {
            throw new Exception("DDL vazio para a tabela $table no schema $remote_schema");
        }

        // Substitui tablespaces e outros elementos incompatíveis
        $ddl = preg_replace('/TABLESPACE "\w+"/', '', $ddl);
        $ddl = preg_replace('/STORAGE\s*\([^\)]*\)/', '', $ddl);
        $ddl = preg_replace('/SEGMENT CREATION (IMMEDIATE|DEFERRED)/', '', $ddl);
        // Remove o schema do DDL para evitar conflitos no banco local
        $ddl = str_replace("$remote_schema.", "", $ddl);

        // Log do DDL para depuração
        file_put_contents('ddl_log.txt', "DDL para $table:<br>$ddl<br><br>", FILE_APPEND);

        // Executa o DDL no banco local
        $stid = oci_parse($local_conn, $ddl);
        if (!oci_execute($stid, OCI_NO_AUTO_COMMIT)) {
            $e = oci_error($stid);
            throw new Exception("Erro ao executar DDL para $table: " . $e['message']);
        }
        oci_commit($local_conn);
        echo "Tabela $table criada com sucesso.<br>";

        // Verifica se a tabela foi realmente criada
        $check_sql = "SELECT COUNT(*) AS CNT FROM user_tables WHERE table_name = :table_name";
        $check_stid = oci_parse($local_conn, $check_sql);
        oci_bind_by_name($check_stid, ':table_name', $table);
        oci_execute($check_stid);
        $row = oci_fetch_assoc($check_stid);
        if ($row['CNT'] == 0) {
            throw new Exception("Tabela $table não foi criada, embora o DDL tenha sido executado.");
        }
    } catch (Exception $e) {
        echo "Erro ao criar tabela $table: " . $e->getMessage() . "<br>";
        file_put_contents('error_log.txt', "Erro ao criar $table: " . $e->getMessage() . "<br>", FILE_APPEND);
        throw $e;
    }
}

// Verifica se as conexões estão ativas
if (!$cloud_conn || !$local_conn) {
    echo "Erro: Não foi possível conectar a um dos bancos.<br>";
    file_put_contents('error_log.txt', "Erro de conexão - Cloud: " . ($cloud_conn ? "OK" : "Falhou") . ", Local: " . ($local_conn ? "OK" : "Falhou") . "<br>", FILE_APPEND);
    exit;
}

foreach ($tables as $table) {
    // Verifica se a tabela existe no banco local
    $check_sql = "SELECT COUNT(*) AS CNT FROM user_tables WHERE table_name = :table_name";
    $stid = oci_parse($local_conn, $check_sql);
    oci_bind_by_name($stid, ':table_name', $table);
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);
    $table_exists = $row['CNT'] > 0;

    if (!$table_exists) {
        echo "Criando tabela $table no banco local...<br>";
        try {
            createTableIfNotExists($local_conn, $table, $cloud_conn, $remote_schema);
        } catch (Exception $e) {
            continue; // Continua com a próxima tabela em caso de erro
        }
    }

    // Limpa a tabela local
    $truncate_sql = "TRUNCATE TABLE $table";
    $stid = oci_parse($local_conn, $truncate_sql);
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        echo "Erro ao truncar $table: " . $e['message'] . "<br>";
        file_put_contents('error_log.txt', "Erro ao truncar $table: " . $e['message'] . "<br>", FILE_APPEND);
        continue;
    }

    // Copia todos os dados da nuvem, especificando o schema remoto
    $copy_sql = "INSERT /*+ APPEND */ INTO $table SELECT * FROM $remote_schema.$table@cloud_db_link"; //atenção! primeiro precisa criar o Database Link no Oracle XE local apontando para o Oracle Cloud
    $stid = oci_parse($local_conn, $copy_sql);
    if (oci_execute($stid, OCI_NO_AUTO_COMMIT)) {
        oci_commit($local_conn);
        echo "Tabela $table copiada com sucesso.<br>";
    } else {
        $e = oci_error($stid);
        echo "Erro ao copiar $table: " . $e['message'] . "<br>";
        file_put_contents('error_log.txt', "Erro ao copiar $table: " . $e['message'] . "<br>", FILE_APPEND);
    }
}

// Atualiza o timestamp do último backup
$update_sql = "UPDATE backup_config SET last_backup = SYSTIMESTAMP WHERE id = 1";
$stid = oci_parse($local_conn, $update_sql);
oci_execute($stid);

$cloud_db->disconnect();
$local_db->disconnect();
?>