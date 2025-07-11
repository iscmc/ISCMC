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

// A função testDatabaseLink não precisa de alterações, ela testa o link no local_conn
function testDatabaseLink($local_conn, $db_link_name) {
    $stid = null; // Inicializa para garantir que a variável exista
    try {
        $sql = "SELECT 1 FROM DUAL@" . $db_link_name . ";";
        $stid = oci_parse($local_conn, $sql); // Usa a conexão local para testar o DB Link
        if (!$stid) { // Verifica se oci_parse falhou
            $e = oci_error($local_conn); // Erro na conexão, não no statement ainda
            throw new Exception("Erro ao preparar a declaração SQL para testar Database Link: " . $e['message']);
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
// Esta função agora pega o DDL DIRETAMENTE da conexão $cloud_conn
function createTableIfNotExists($local_conn, $table, $cloud_conn, $remote_schema) {
    $stid = null; // Inicializa stid para o finally
    try {
        // Obtém a estrutura da tabela do banco remoto USANDO A CONEXÃO DIRETA DO CLOUD
        $sql = "SELECT DBMS_METADATA.GET_DDL('TABLE', :table_name, :schema_name) AS DDL FROM DUAL";
        $stid = oci_parse($cloud_conn, $sql); // Usa $cloud_conn para a query DDL
        if (!$stid) {
            $e = oci_error($cloud_conn);
            throw new Exception("Erro ao preparar a declaração SQL para obter DDL: " . $e['message']);
        }
        oci_bind_by_name($stid, ':table_name', $table);
        oci_bind_by_name($stid, ':schema_name', $remote_schema);

        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Erro ao obter DDL para $table no schema $remote_schema: " . $e['message']);
        }

        $row = oci_fetch_assoc($stid);
        $ddl_clob = $row['DDL']; // DBMS_METADATA.GET_DDL retorna um CLOB.

        $ddl = ''; // Inicializa a variável para garantir que seja uma string

        // **CORREÇÃO: Unificação da leitura do CLOB**
        // A maneira mais robusta é verificar se a variável $ddl_clob é um objeto OCILob
        // e, em caso afirmativo, usar o método read() para obter seu conteúdo como string.
        if ($ddl_clob instanceof OCILob) {
            $ddl = $ddl_clob->read($ddl_clob->size());
            $ddl_clob->free(); // Libera o LOB após a leitura
        } else {
            // Se, por algum motivo (ex: DDL muito pequeno), não for um OCILob, assume que já é string.
            // Embora raro para DBMS_METADATA, é bom ter um fallback.
            $ddl = (string) $ddl_clob;
        }

        if (empty($ddl)) {
            throw new Exception("DDL vazio ou não obtido para a tabela $table no schema $remote_schema");
        }

        // Substitui tablespaces e outros elementos incompatíveis
        $ddl = preg_replace('/TABLESPACE "\w+"/', '', $ddl);
        $ddl = preg_replace('/STORAGE\s*\([^\)]*\)/', '', $ddl);
        $ddl = preg_replace('/SEGMENT CREATION (IMMEDIATE|DEFERRED)/', '', $ddl);
        // Remove o schema do DDL para evitar conflitos no banco local
        // Isso é crucial para que a tabela seja criada no schema do usuário conectado localmente
        $ddl = str_replace("\"{$remote_schema}\".", "", $ddl);
        $ddl = str_replace("{$remote_schema}.", "", $ddl); // Para casos sem aspas ou mistos

        // Log do DDL para depuração
        file_put_contents('ddl_log.txt', "DDL para $table:\n$ddl\n\n", FILE_APPEND);

        // Executa o DDL no banco local
        $stid_local = oci_parse($local_conn, $ddl);
        if (!$stid_local) { // Verifica se oci_parse para DDL local falhou
             $e = oci_error($local_conn);
             throw new Exception("Erro ao preparar DDL para criação da tabela $table no local: " . $e['message']);
        }

        if (!oci_execute($stid_local, OCI_NO_AUTO_COMMIT)) {
            $e = oci_error($stid_local);
            throw new Exception("Erro ao executar DDL para $table: " . $e['message']);
        }
        oci_commit($local_conn);
        echo "Tabela $table criada com sucesso no banco local.<br>";

        // Verifica se a tabela foi realmente criada
        $check_sql = "SELECT COUNT(*) AS CNT FROM user_tables WHERE table_name = :table_name";
        $check_stid = oci_parse($local_conn, $check_sql);
        oci_bind_by_name($check_stid, ':table_name', $table);
        oci_execute($check_stid);
        $row = oci_fetch_assoc($check_stid);
        if ($row['CNT'] == 0) {
            throw new Exception("Tabela $table não foi criada no banco local, embora o DDL tenha sido executado.");
        }
        oci_free_statement($check_stid); // Liberar statement de verificação
        oci_free_statement($stid_local); // Liberar statement de execução do DDL

    } catch (Exception $e) {
        echo "Erro ao criar tabela $table: " . $e->getMessage() . "<br>";
        file_put_contents('error_log.txt', "Erro ao criar $table: " . $e->getMessage() . "\n", FILE_APPEND);
        throw $e; // Re-lança a exceção para que o loop principal possa lidar
    } finally {
        if ($stid) {
            oci_free_statement($stid); // Libera o statement usado para obter o DDL do cloud
        }
    }
}

// Verifica se as conexões estão ativas
if (!$cloud_conn || !$local_conn) {
    echo "Erro: Não foi possível conectar a um dos bancos.<br>";
    file_put_contents('error_log.txt', "Erro de conexão - Cloud: " . ($cloud_conn ? "OK" : "Falhou") . ", Local: " . ($local_conn ? "OK" : "Falhou") . "\n", FILE_APPEND);
    exit;
}

// Testa o Database Link do lado local (para garantir que a cópia de dados funcionará)
if (!testDatabaseLink($local_conn, 'CLOUD_DB_LINK')) {
    echo "Erro fatal: Database Link 'CLOUD_DB_LINK' não está funcionando. Não é possível continuar a cópia de dados.<br>";
    exit;
}

foreach ($tables as $table) {
    echo "Processando tabela: $table<br>";
    // Verifica se a tabela existe no banco local
    $check_sql = "SELECT COUNT(*) AS CNT FROM user_tables WHERE table_name = :table_name";
    $stid = oci_parse($local_conn, $check_sql);
    oci_bind_by_name($stid, ':table_name', $table);
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);
    $table_exists = $row['CNT'] > 0;
    oci_free_statement($stid); // Liberar statement de verificação

    if (!$table_exists) {
        echo "Tabela '$table' não existe no banco local. Tentando criar...<br>";
        try {
            createTableIfNotExists($local_conn, $table, $cloud_conn, $remote_schema);
        } catch (Exception $e) {
            echo "Falha crítica ao criar a tabela '$table'. Pulando para a próxima.<br>";
            continue; // Continua com a próxima tabela em caso de erro na criação
        }
    } else {
        echo "Tabela '$table' já existe no banco local. Prosseguindo para truncar e copiar dados.<br>";
    }

    // Limpa a tabela local
    echo "Truncando tabela '$table' no banco local...<br>";
    $truncate_sql = "TRUNCATE TABLE $table";
    $stid = oci_parse($local_conn, $truncate_sql);
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        echo "Erro ao truncar $table: " . $e['message'] . "<br>";
        file_put_contents('error_log.txt', "Erro ao truncar $table: " . $e['message'] . "\n", FILE_APPEND);
        oci_free_statement($stid);
        continue;
    }
    oci_free_statement($stid);
    echo "Tabela '$table' truncada com sucesso.<br>";

    // Copia todos os dados da nuvem, especificando o schema remoto
    echo "Copiando dados da tabela '$table' do banco remoto para o local...<br>";
    $copy_sql = "INSERT /*+ APPEND */ INTO $table SELECT * FROM $remote_schema.$table@cloud_db_link";
    $stid = oci_parse($local_conn, $copy_sql);
    if (!$stid) {
        $e = oci_error($local_conn);
        echo "Erro ao preparar a declaração SQL para copiar $table: " . $e['message'] . "<br>";
        file_put_contents('error_log.txt', "Erro ao preparar a declaração SQL para copiar $table: " . $e['message'] . "\n", FILE_APPEND);
        continue;
    }

    if (oci_execute($stid, OCI_NO_AUTO_COMMIT)) {
        oci_commit($local_conn);
        echo "Dados da tabela '$table' copiados com sucesso.<br>";
    } else {
        $e = oci_error($stid);
        echo "Erro ao copiar $table: " . $e['message'] . "<br>";
        file_put_contents('error_log.txt', "Erro ao copiar $table: " . $e['message'] . "\n", FILE_APPEND);
    }
    oci_free_statement($stid); // Liberar statement de cópia de dados
}

// Atualiza o timestamp do último backup
echo "Atualizando timestamp do último backup...<br>";
$update_sql = "UPDATE backup_config SET last_backup = SYSTIMESTAMP WHERE id = 1";
$stid = oci_parse($local_conn, $update_sql);
if (oci_execute($stid)) {
    oci_commit($local_conn);
    echo "Timestamp de backup atualizado com sucesso.<br>";
} else {
    $e = oci_error($stid);
    echo "Erro ao atualizar timestamp de backup: " . $e['message'] . "<br>";
    file_put_contents('error_log.txt', "Erro ao atualizar timestamp de backup: " . $e['message'] . "\n", FILE_APPEND);
}
oci_free_statement($stid);

$cloud_db->disconnect();
$local_db->disconnect();
echo "Backup e cópia de dados concluídos.<br>";

?>