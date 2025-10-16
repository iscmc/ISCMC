<?php
require_once __DIR__ . '/../config/database.php';

class OcupacaoSetorModel {
    private $conn;
    
    public function __construct() {
        $this->conn = DatabaseConfig::getConnection();
    }

    public function getSetorInfo($cdSetor) {
        $sql = "SELECT CD_SETOR_ATENDIMENTO, DS_SETOR_ATENDIMENTO 
                FROM setor_atendimento 
                WHERE CD_SETOR_ATENDIMENTO = :cd_setor";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':cd_setor', $cdSetor);
        
        if (!oci_execute($stmt)) {
            $error = oci_error($stmt);
            throw new Exception("Erro ao buscar informações do setor: " . $error['message']);
        }
        
        $setorInfo = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        return $setorInfo;
    }

    public function getPacientesPorSetor($cdSetor) {
        $sql = "
            WITH todos_blocos AS (
                -- Primeiro bloco
                SELECT 
                    b.cd_estabelecimento,
                    a.nr_atendimento,
                    a.dt_entrada_unidade,
                    a.cd_unidade_basica AS leito,
                    b.dt_entrada,
                    e.nm_pessoa_fisica AS paciente,
                    a.cd_setor_atendimento,
                    e.cd_pessoa_fisica,
                    e.dt_nascimento,
                    (SELECT TO_NUMBER(NVL(e2.nr_prontuario,0))
                       FROM pessoa_fisica e2
                      WHERE e2.cd_pessoa_fisica = b.cd_pessoa_fisica
                        AND e2.cd_estabelecimento = b.cd_estabelecimento
                    ) AS nr_prontuario,
                    e.ie_sexo,
                    (SELECT SUBSTR(obter_crm_medico(d.cd_pessoa_fisica),1,255) FROM dual) AS nr_crm,
                    d.nm_guerra,
                    e.nm_pessoa_fisica AS ds_status_unidade,
                    TRUNC(SYSDATE - b.dt_entrada, 0) AS qt_dia_permanencia,
                    a.dt_inicio_higienizacao,
                    a.dt_higienizacao,
                    b.dt_previsto_alta, 
                    (SELECT SUBSTR(obter_valor_dominio(1267,b.ie_probabilidade_alta),1,40) FROM dual) AS ds_probabilidade,
                    SUBSTR(obter_tempo_p_ult_setor(a.nr_atendimento, 'DT'),1,20) AS dt_entrada_unid
                FROM unidade_atendimento a
                JOIN atendimento_paciente b ON a.nr_atendimento = b.nr_atendimento
                JOIN atend_paciente_unidade u ON u.nr_atendimento = b.nr_atendimento 
                                          AND u.nr_Seq_interno = (
                                              SELECT MAX(x.nr_seq_interno)
                                              FROM atend_paciente_unidade x
                                              WHERE x.nr_atendimento = b.nr_atendimento
                                                AND x.cd_setor_atendimento = a.cd_setor_atendimento
                                                AND x.cd_unidade_basica = a.cd_unidade_basica
                                                AND x.cd_unidade_compl = a.cd_unidade_compl
                                          )
                JOIN pessoa_fisica e ON b.cd_pessoa_fisica = e.cd_pessoa_fisica
                LEFT JOIN medico d ON b.cd_medico_resp = d.cd_pessoa_fisica
                LEFT JOIN valor_dominio g ON g.cd_dominio = 17 AND g.ie_situacao = 'A' AND b.ie_clinica = g.vl_dominio
                WHERE a.ie_situacao = 'A'
                  AND a.ie_status_unidade = 'P'

                UNION ALL

                -- Segundo bloco
                SELECT 
                    NULL AS cd_estabelecimento,
                    a.nr_atendimento,
                    a.dt_entrada_unidade,
                    a.cd_unidade_basica AS leito,
                    SYSDATE AS dt_entrada,
                    '' AS paciente,
                    a.cd_setor_atendimento,
                    (SELECT DECODE(a.ie_status_unidade,'A',SUBSTR(obter_pessoa_atendimento(a.nr_atendimento,'C'),1,60),NULL) FROM dual) AS cd_pessoa_fisica,
                    NULL AS dt_nascimento,
                    0 AS nr_prontuario,
                    '' AS ie_sexo,
                    '' AS nr_crm,
                    '' AS nm_guerra,
                    SUBSTR(b.DS_EXPRESSAO,1,80) AS ds_status_unidade,
                    0 AS qt_dia_permanencia,
                    a.dt_inicio_higienizacao,
                    a.dt_higienizacao,
                    NULL AS dt_previsto_alta, 
                    '' AS ds_probabilidade,
                    (SELECT SUBSTR(obter_tempo_p_ult_setor(a.nr_atendimento,'DT'),1,20) FROM dual) AS dt_entrada_unid
                FROM unidade_atendimento a
                JOIN pessoa_fisica f ON a.cd_paciente_reserva = f.cd_pessoa_fisica
                JOIN valor_dominio_v b ON b.cd_dominio = 82 AND b.vl_dominio = a.ie_status_unidade
                WHERE a.ie_situacao = 'A'
                  AND a.ie_status_unidade IN ('R','H','G','A','E','C','O','D','U')

                UNION ALL

                -- Terceiro bloco
                SELECT 
                    NULL AS cd_estabelecimento,
                    a.nr_atendimento,
                    a.dt_entrada_unidade,
                    a.cd_unidade_basica AS leito,
                    SYSDATE AS dt_entrada,
                    '' AS paciente,
                    a.cd_setor_atendimento,
                    DECODE(a.ie_status_unidade,'A',SUBSTR(obter_pessoa_atendimento(a.nr_atendimento,'C'),1,60),
                                         'O',SUBSTR(obter_pessoa_atendimento(a.nr_atendimento,'C'),1,60),
                                         '') AS cd_pessoa_fisica,
                    NULL AS dt_nascimento,
                    0 AS nr_prontuario,
                    'X' AS ie_sexo,
                    '' AS nr_crm,
                    '' AS nm_guerra,
                    SUBSTR(b.DS_EXPRESSAO,1,80) AS ds_status_unidade,
                    0 AS qt_dia_permanencia,
                    a.dt_inicio_higienizacao,
                    a.dt_higienizacao,
                    NULL AS dt_previsto_alta, 
                    '' AS ds_probabilidade,
                    (SELECT SUBSTR(obter_tempo_p_ult_setor(a.nr_atendimento,'DT'),1,20) FROM dual) AS dt_entrada_unid
                FROM unidade_atendimento a
                JOIN valor_dominio_v b ON b.cd_dominio = 82 AND b.vl_dominio = a.ie_status_unidade
                WHERE a.ie_situacao = 'A'
                  AND a.ie_status_unidade IN ('R','L','H','G','A','O','E','C','D','U')
                  AND a.cd_paciente_reserva IS NULL
            )
            SELECT *
            FROM todos_blocos
            WHERE cd_setor_atendimento = :cd_setor
            ORDER BY leito, paciente
        ";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':cd_setor', $cdSetor);
        
        if (!oci_execute($stmt)) {
            $error = oci_error($stmt);
            throw new Exception("Erro ao buscar pacientes do setor: " . $error['message']);
        }
        
        $pacientes = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $pacientes[] = $this->formatRow($row);
        }
        
        oci_free_statement($stmt);
        return $pacientes;
    }

    /**
     * Formata os dados para exibição
     */
    private function formatRow($row) {
        // Garante que todas as chaves necessárias existam
        $defaults = [
            'PACIENTE' => 'N/D',
            'LEITO' => 'N/D',
            'NR_PRONTUARIO' => '0',
            'NM_GUERRA' => 'N/D',
            'DS_STATUS_UNIDADE' => 'N/D',
            'QT_DIA_PERMANENCIA' => '0',
            'DT_ENTRADA_UNIDADE' => null,
            'DT_NASCIMENTO' => null,
            'IE_SEXO' => 'N/D'
        ];
        
        $row = array_merge($defaults, $row);
        
        // Formata datas
        foreach ($row as $key => $value) {
            if (preg_match('/^DT_/', $key) && $value) {
                $row[$key . '_FORMATADA'] = $this->formatDate($value);
            }
        }
        
        return $row;
    }
    
    /**
     * Formata data Oracle para formato brasileiro
     */
    private function formatDate($oracleDate) {
        if (empty($oracleDate) || $oracleDate == 'N/A') {
            return 'N/A';
        }
        
        try {
            // Caso 1: Já está no formato brasileiro
            if (is_string($oracleDate) && preg_match('/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/', $oracleDate)) {
                return $oracleDate;
            }
            
            // Caso 2: Formato brasileiro sem hora
            if (is_string($oracleDate) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $oracleDate)) {
                return $oracleDate;
            }
            
            // Caso 3: Formato Oracle DD-MON-YY
            if (is_string($oracleDate) && preg_match('/^(\d{2})-([A-Z]{3})-(\d{2})$/', $oracleDate, $matches)) {
                $mesesOracle = [
                    'JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04',
                    'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AUG' => '08',
                    'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12'
                ];
                
                $dia = $matches[1];
                $mes = $mesesOracle[strtoupper($matches[2])] ?? '01';
                $ano = $matches[3];
                $anoCompleto = ($ano < 50) ? "20$ano" : "19$ano";
                
                return "$dia/$mes/$anoCompleto";
            }
            
            // Caso 4: Formato Oracle DD-MON-YY com hora
            if (is_string($oracleDate) && preg_match('/^(\d{2})-([A-Z]{3})-(\d{2}) (\d{2}:\d{2}:\d{2}(\.\d+)?)$/', $oracleDate, $matches)) {
                $mesesOracle = [
                    'JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04',
                    'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AUG' => '08',
                    'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12'
                ];
                
                $dia = $matches[1];
                $mes = $mesesOracle[strtoupper($matches[2])] ?? '01';
                $ano = $matches[3];
                $hora = substr($matches[4], 0, 5);
                $anoCompleto = ($ano < 50) ? "20$ano" : "19$ano";
                
                return "$dia/$mes/$anoCompleto $hora";
            }
            
            // Caso 5: Tenta conversão genérica
            $timestamp = strtotime($oracleDate);
            if ($timestamp !== false) {
                return date('d/m/Y H:i', $timestamp);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao formatar data '$oracleDate': " . $e->getMessage());
        }
        
        return substr($oracleDate, 0, 50);
    }
}
?>