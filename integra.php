<?php

// Configurações do banco de dados Oracle
$db_user = "tasy";
$db_password = "XTXYJWIKZF";
$db_connect_string = "10.250.250.214"; // Substitua pelo seu connect string

// Caminho do arquivo TXT com os números de atendimento
$arquivo_atendimentos = "atendimentos.txt";

// Conectar ao banco de dados Oracle
$conn = oci_connect($db_user, $db_password, $db_connect_string);

if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

// Ler os números de atendimento do arquivo TXT
$atendimentos = file($arquivo_atendimentos, FILE_IGNORE_NEW_LINES);

if ($atendimentos === false) {
    die("Erro ao ler o arquivo de atendimentos.");
}

// Processar cada número de atendimento
foreach ($atendimentos as $atendimento) {
    $atendimento = trim($atendimento); // Remover espaços em branco e quebras de linha

    // Consulta SQL para verificar os valores e atualizar se necessário
    $sql = "BEGIN "
        . "    DECLARE "
        . "        v_cd_setor_atendimento NUMBER; "
        . "        v_cd_unidade_basica NUMBER; "
        . "    BEGIN "
        . "        -- Consultar os valores atuais "
        . "        SELECT b.cd_setor_atendimento, b.cd_unidade_basica "
        . "        INTO v_cd_setor_atendimento, v_cd_unidade_basica "
        . "        FROM ATENDIMENTO_PACIENTE a "
        . "        LEFT JOIN ATEND_PACIENTE_UNIDADE b ON a.nr_atendimento = b.nr_atendimento "
        . "        WHERE a.nr_atendimento = :atendimento "
        . "        AND a.cd_estabelecimento = 12; "
        . " "
        . "        -- Verificar se os valores são nulos e atualizar se necessário "
        . "        IF v_cd_setor_atendimento IS NULL OR v_cd_unidade_basica IS NULL THEN "
        . "            UPDATE ATEND_PACIENTE_UNIDADE "
        . "            SET cd_setor_atendimento = 506, "
        . "                cd_unidade_basica = '01' "
        . "            WHERE nr_atendimento = :atendimento; "
        . "            DBMS_OUTPUT.PUT_LINE('Atendimento ' || :atendimento || ': Valores atualizados.'); "
        . "        ELSE "
        . "            DBMS_OUTPUT.PUT_LINE('Atendimento ' || :atendimento || ': Valores já preenchidos.'); "
        . "        END IF; "
        . "    EXCEPTION "
        . "        WHEN NO_DATA_FOUND THEN "
        . "            DBMS_OUTPUT.PUT_LINE('Atendimento ' || :atendimento || ': Não encontrado.'); "
        . "        WHEN OTHERS THEN "
        . "            DBMS_OUTPUT.PUT_LINE('Erro ao processar o atendimento ' || :atendimento || ': ' || SQLERRM); "
        . "    END; "
        . "END;";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":atendimento", $atendimento);

    if (oci_execute($stmt)) {
        echo "Atendimento $atendimento processado com sucesso.<br>";
    } else {
        $e = oci_error($stmt);
        echo "Erro ao processar o atendimento $atendimento: " . htmlentities($e['message'], ENT_QUOTES) . "<br>";
    }

    oci_free_statement($stmt);
}

// Fechar a conexão com o banco de dados
oci_close($conn);

?>