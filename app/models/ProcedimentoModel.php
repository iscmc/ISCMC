<?php
require_once __DIR__ . '/../config/database.php';

class ProcedimentoModel {
    private $conn;
    
    public function __construct() {
        $this->conn = DatabaseConfig::getConnection();
    }
    
    /**
     * Busca todos os procedimentos com paginação
     */
    public function getAllProcedimentos($limit = 50, $offset = 0) {
        $sql = "SELECT * FROM (
            SELECT 
                p.NR_SEQUENCIA,
                p.NR_ATENDIMENTO,
                p.CD_PESSOA_FISICA,
                obter_nome_pf(p.CD_PESSOA_FISICA) as NOME_PACIENTE,
                c.DS_PROC_EXAME,
                to_char(p.DT_PREV_EXECUCAO, 'dd/mm/yyyy hh24:mi') as DT_PREV_EXECUCAO,
                p.DS_OBSERVACAO,
                p.NM_USUARIO,
                p.DT_ATUALIZACAO,
                b.NM_PESSOA_FISICA as NM_USUARIO_COMPLETO,
                u.DS_USUARIO,
                ROW_NUMBER() OVER (ORDER BY p.DT_ATUALIZACAO DESC, p.NR_SEQUENCIA DESC) as rn
            FROM CPOE_PROCEDIMENTO p
            INNER JOIN PESSOA_FISICA b ON p.CD_MEDICO_EXEC = b.CD_PESSOA_FISICA
            INNER JOIN PROC_INTERNO c ON p.NR_SEQ_PROC_INTERNO = c.nr_sequencia
            LEFT JOIN USUARIO u ON p.NM_USUARIO = u.NM_USUARIO
            WHERE p.NR_SEQUENCIA IS NOT NULL
        ) 
        WHERE rn BETWEEN :start_row AND :end_row";
        
        $start_row = $offset + 1;
        $end_row = $offset + $limit;
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':start_row', $start_row);
        oci_bind_by_name($stmt, ':end_row', $end_row);
        
        if (!oci_execute($stmt)) {
            $this->handleDatabaseError($stmt, "Erro ao buscar procedimentos");
        }
        
        $procedimentos = [];
        while ($row = oci_fetch_assoc($stmt)) {
            // Remove a coluna rn do resultado
            unset($row['RN']);
            $procedimentos[] = $this->formatRow($row);
        }
        
        oci_free_statement($stmt);
        return $procedimentos;
    }
    
    /**
     * Busca procedimento por ID - VERSÃO CORRIGIDA
     */
    public function getProcedimentoById($id) {
        $sql = "SELECT 
                    p.NR_SEQUENCIA,
                    p.NR_ATENDIMENTO,
                    p.CD_PESSOA_FISICA,
                    UPPER(obter_nome_pf(p.CD_PESSOA_FISICA)) as NM_PACIENTE,
                    c.DS_PROC_EXAME as DS_PROCEDIMENTO,
                    p.DT_PREV_EXECUCAO as DT_PROCEDIMENTO,
                    p.DS_OBSERVACAO,
                    p.NM_USUARIO,
                    p.DT_ATUALIZACAO,
                    b.NM_PESSOA_FISICA,
                    u.DS_USUARIO as NM_USUARIO_COMPLETO,
                    pf.DT_NASCIMENTO,
                    Obter_Sexo_PF(pf.CD_PESSOA_FISICA, 'D') IE_SEXO,
                    obter_nome_mae(p.CD_PESSOA_FISICA) as NM_MAE_PACIENTE
                FROM CPOE_PROCEDIMENTO p
                LEFT JOIN ATENDIMENTO_PACIENTE a ON p.NR_ATENDIMENTO = a.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON p.NM_USUARIO = u.NM_USUARIO
                JOIN PESSOA_FISICA b ON p.CD_PESSOA_FISICA = b.CD_PESSOA_FISICA
                JOIN PESSOA_FISICA pf ON p.CD_PESSOA_FISICA = pf.CD_PESSOA_FISICA
                JOIN PROC_INTERNO c ON p.NR_SEQ_PROC_INTERNO = c.nr_sequencia
                WHERE p.NR_SEQUENCIA = :id";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':id', $id);
        
        if (!oci_execute($stmt)) {
            $this->handleDatabaseError($stmt, "Erro ao buscar ocupação hospitalar");
        }
        
        $row = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        if (!$row) {
            throw new Exception("Procedimento não encontrado com ID: " . $id);
        }
        
        return $this->formatRow($row);
    }
    
    /**
    * Busca procedimentos por paciente - VERSÃO CORRIGIDA
    */
    public function getProcedimentosByPaciente($pacienteId) {
        $sql = "SELECT 
                    p.NR_SEQUENCIA,
                    c.DS_PROC_EXAME as DS_PROCEDIMENTO,
                    p.DT_PREV_EXECUCAO as DT_PROCEDIMENTO,
                    p.DS_OBSERVACAO,
                    p.NM_USUARIO,
                    p.DT_ATUALIZACAO,
                    u.DS_USUARIO as NM_USUARIO_COMPLETO,
                    b.NM_PESSOA_FISICA as NM_PACIENTE
                FROM CPOE_PROCEDIMENTO p
                LEFT JOIN USUARIO u ON p.NM_USUARIO = u.NM_USUARIO
                JOIN PESSOA_FISICA b ON p.CD_PESSOA_FISICA = b.CD_PESSOA_FISICA
                JOIN PROC_INTERNO c ON p.NR_SEQ_PROC_INTERNO = c.nr_sequencia
                WHERE p.CD_PESSOA_FISICA = :paciente_id
                ORDER BY p.DT_PREV_EXECUCAO DESC";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':paciente_id', $pacienteId);
        
        if (!oci_execute($stmt)) {
            $this->handleDatabaseError($stmt, "Erro ao buscar ocupação hospitalar");
        }
        
        $procedimentos = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $procedimentos[] = $this->formatRow($row);
        }
        
        oci_free_statement($stmt);
        return $procedimentos;
    }
    
    /**
     * Busca procedimentos por atendimento - VERSÃO CORRIGIDA
     */
    public function getProcedimentosByAtendimento($atendimentoId) {
        $sql = "SELECT 
                    p.NR_SEQUENCIA,
                    c.DS_PROC_EXAME as DS_PROCEDIMENTO,
                    p.DT_PREV_EXECUCAO as DT_PROCEDIMENTO,
                    p.DS_OBSERVACAO,
                    p.NM_USUARIO,
                    p.DT_ATUALIZACAO,
                    u.DS_USUARIO as NM_USUARIO_COMPLETO
                FROM CPOE_PROCEDIMENTO p
                LEFT JOIN USUARIO u ON p.NM_USUARIO = u.NM_USUARIO
                LEFT JOIN ATENDIMENTO_PACIENTE a ON p.NR_ATENDIMENTO = a.NR_ATENDIMENTO
                JOIN PROC_INTERNO c ON p.NR_SEQ_PROC_INTERNO = c.nr_sequencia
                WHERE p.NR_ATENDIMENTO = :atendimento_id
                ORDER BY p.DT_PREV_EXECUCAO DESC";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':atendimento_id', $atendimentoId);
        
        if (!oci_execute($stmt)) {
            $this->handleDatabaseError($stmt, "Erro ao buscar ocupação hospitalar");
        }
        
        $procedimentos = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $procedimentos[] = $this->formatRow($row);
        }
        
        oci_free_statement($stmt);
        return $procedimentos;
    }
    
    /**
     * Formata os dados para exibição
     */
    private function formatRow($row) {
        // Garante que todas as chaves necessárias existam
        $defaults = [
            'DS_PROCEDIMENTO' => 'N/D',
            'NM_PACIENTE' => 'N/D',
            'NM_USUARIO_COMPLETO' => 'N/D',
            'DS_OBSERVACAO' => '',
            'DT_NASCIMENTO' => null,
            'TP_SEXO' => 'N/D',
            'DS_LOCAL_ATENDIMENTO' => 'N/D',
            'DS_ESPECIALIDADE' => 'N/D'
        ];
        
        $row = array_merge($defaults, $row);
        
        // Formata datas
        foreach ($row as $key => $value) {
            if (preg_match('/^DT_/', $key) && $value) {
                $row[$key . '_FORMATADA'] = $this->formatDate($value);
            }
        }
        
        // Limita o tamanho da observação para preview
        if (isset($row['DS_OBSERVACAO']) && strlen($row['DS_OBSERVACAO']) > 100) {
            $row['DS_OBSERVACAO_PREVIEW'] = substr($row['DS_OBSERVACAO'], 0, 100) . '...';
        } else {
            $row['DS_OBSERVACAO_PREVIEW'] = $row['DS_OBSERVACAO'] ?? '';
        }
        
        // Define código do paciente
        if (isset($row['CD_PESSOA_FISICA'])) {
            $row['CD_PACIENTE'] = $row['CD_PESSOA_FISICA'];
        }
        
        return $row;
    }
    
    /**
     * Formata data Oracle para formato brasileiro de forma elegante
     */
    private function formatDate($oracleDate) {
        if (empty($oracleDate) || $oracleDate == 'N/A') {
            return 'N/A';
        }
        
        try {
            // Debug: ver o tipo e valor recebido
            error_log("Tentando formatar data: " . gettype($oracleDate) . " - " . $oracleDate);
            
            // Caso 1: Já está no formato brasileiro
            if (is_string($oracleDate) && preg_match('/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/', $oracleDate)) {
                return $oracleDate;
            }
            
            // Caso 2: Formato brasileiro sem hora
            if (is_string($oracleDate) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $oracleDate)) {
                return $oracleDate;
            }
            
            // Caso 3: Formato Oracle DD-MON-YY (ex: 17-APR-81)
            if (is_string($oracleDate) && preg_match('/^(\d{2})-([A-Z]{3})-(\d{2})$/', $oracleDate, $matches)) {
                $mesesOracle = [
                    'JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04',
                    'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AUG' => '08',
                    'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12'
                ];
                
                $dia = $matches[1];
                $mes = $mesesOracle[strtoupper($matches[2])] ?? '01';
                $ano = $matches[3];
                
                // Converte ano de 2 para 4 dígitos
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
                $hora = substr($matches[4], 0, 5); // Pega apenas HH:MM
                
                $anoCompleto = ($ano < 50) ? "20$ano" : "19$ano";
                
                return "$dia/$mes/$anoCompleto $hora";
            }
            
            // Caso 5: Objeto DateTime do Oracle
            if (is_object($oracleDate)) {
                // Tenta métodos comuns de objetos de data Oracle
                if (method_exists($oracleDate, 'format')) {
                    return $oracleDate->format('d/m/Y H:i');
                }
                
                if (method_exists($oracleDate, 'toString')) {
                    $dateString = $oracleDate->toString();
                    return $this->formatDate($dateString); // Recursão
                }
            }
            
            // Caso 6: Tenta conversão genérica como fallback
            $timestamp = strtotime($oracleDate);
            if ($timestamp !== false) {
                return date('d/m/Y H:i', $timestamp);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao formatar data '$oracleDate': " . $e->getMessage());
        }
        
        // Último recurso: retorna limpo (remove horas se for muito longo)
        $cleanDate = substr($oracleDate, 0, 50);
        return $cleanDate;
    }
    
    /**
     * Conta total de procedimentos
     */
    public function getTotalProcedimentos() {
        $sql = "SELECT COUNT(*) as total 
                FROM CPOE_PROCEDIMENTO p
                JOIN PESSOA_FISICA b ON p.CD_PESSOA_FISICA = b.CD_PESSOA_FISICA
                WHERE p.NR_SEQUENCIA IS NOT NULL";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_execute($stmt);
        $result = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        return $result['TOTAL'] ?? 0;
    }

    /**
     * Busca procedimentos por termo geral
     */
    public function searchProcedimentos($searchTerm, $limit = 50, $offset = 0) {
        $sql = "SELECT 
                    p.NR_SEQUENCIA,
                    p.NR_ATENDIMENTO,
                    p.CD_PESSOA_FISICA,
                    obter_nome_pf(p.CD_PESSOA_FISICA) NOME_PACIENTE,
                    c.DS_PROC_EXAME,
                    to_char(p.DT_PREV_EXECUCAO, 'dd/mm/yyyy hh24:mm') DT_PREV_EXECUCAO,
                    p.DS_OBSERVACAO,
                    p.NM_USUARIO,
                    p.DT_ATUALIZACAO,
                    b.NM_PESSOA_FISICA as NM_USUARIO_COMPLETO,
                    u.DS_USUARIO
                FROM CPOE_PROCEDIMENTO p
                LEFT JOIN ATENDIMENTO_PACIENTE a ON p.NR_ATENDIMENTO = a.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON p.NM_USUARIO = u.NM_USUARIO
                JOIN PESSOA_FISICA b ON p.CD_PESSOA_FISICA = b.CD_PESSOA_FISICA
                JOIN PROC_INTERNO c ON p.NR_SEQ_PROC_INTERNO = c.nr_sequencia
                WHERE p.NR_SEQUENCIA IS NOT NULL
                AND (UPPER(c.DS_PROC_EXAME) LIKE UPPER('%' || :search_term || '%')
                    OR UPPER(p.DS_OBSERVACAO) LIKE UPPER('%' || :search_term || '%')
                    OR UPPER(p.NM_USUARIO) LIKE UPPER('%' || :search_term || '%'))
                ORDER BY p.DT_ATUALIZACAO DESC, p.NR_SEQUENCIA DESC";
        
        if ($limit > 0) {
            $sql = "SELECT * FROM ($sql) 
                    OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
        }

        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':search_term', $searchTerm);
        
        if ($limit > 0) {
            oci_bind_by_name($stmt, ':offset', $offset);
            oci_bind_by_name($stmt, ':limit', $limit);
        }
        
        if (!oci_execute($stmt)) {
            $this->handleDatabaseError($stmt, "Erro ao buscar ocupação hospitalar");
        }
        
        $procedimentos = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $procedimentos[] = $this->formatRow($row);
        }
        
        oci_free_statement($stmt);
        return $procedimentos;
    }

    /**
     * Conta total de resultados da busca
     */
    public function getSearchCount($searchTerm) {
        $sql = "SELECT COUNT(*) as total 
                FROM CPOE_PROCEDIMENTO p
                JOIN PESSOA_FISICA b ON p.CD_PESSOA_FISICA = b.CD_PESSOA_FISICA
                JOIN PROC_INTERNO c ON p.NR_SEQ_PROC_INTERNO = c.nr_sequencia
                WHERE p.NR_SEQUENCIA IS NOT NULL
                AND (UPPER(c.DS_PROC_EXAME) LIKE UPPER('%' || :search_term || '%')
                    OR UPPER(p.DS_OBSERVACAO) LIKE UPPER('%' || :search_term || '%')
                    OR UPPER(p.NM_USUARIO) LIKE UPPER('%' || :search_term || '%'))";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':search_term', $searchTerm);
        oci_execute($stmt);
        $result = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        return $result['TOTAL'] ?? 0;
    }

    // Captura erros do Oracle e formata numa mensagem amivável
    private function handleDatabaseError($stmt, $message) {
        $error = oci_error($stmt);
        throw new Exception("$message: " . ($error['message'] ?? 'Erro desconhecido'));
    }
}

?>