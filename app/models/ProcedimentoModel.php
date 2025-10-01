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
        $sql = "SELECT 
                    p.NR_SEQUENCIA,
                    p.NR_ATENDIMENTO,
                    p.CD_PACIENTE,
                    p.DS_PROCEDIMENTO,
                    p.DT_PROCEDIMENTO,
                    p.DS_OBSERVACAO,
                    p.NM_USUARIO,
                    p.DT_ATUALIZACAO,
                    a.NM_PACIENTE,
                    u.NM_USUARIO_COMPLETO
                FROM CPOE_PROCEDIMENTO p
                LEFT JOIN ATENDIMENTO a ON p.NR_ATENDIMENTO = a.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON p.NM_USUARIO = u.NM_USUARIO
                WHERE p.NR_SEQUENCIA IS NOT NULL
                ORDER BY p.DT_ATUALIZACAO DESC, p.NR_SEQUENCIA DESC";
        
        if ($limit > 0) {
            $sql = "SELECT * FROM ($sql) WHERE ROWNUM <= :limit OFFSET :offset";
        }
        
        $stmt = oci_parse($this->conn, $sql);
        if ($limit > 0) {
            oci_bind_by_name($stmt, ':limit', $limit);
            oci_bind_by_name($stmt, ':offset', $offset);
        }
        
        if (!oci_execute($stmt)) {
            throw new Exception("Erro ao buscar procedimentos: " . oci_error($stmt));
        }
        
        $procedimentos = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $procedimentos[] = $this->formatRow($row);
        }
        
        oci_free_statement($stmt);
        return $procedimentos;
    }
    
    /**
     * Busca procedimento por ID
     */
    public function getProcedimentoById($id) {
        $sql = "SELECT 
                    p.*,
                    a.NM_PACIENTE,
                    a.DT_NASCIMENTO,
                    a.TP_SEXO,
                    u.NM_USUARIO_COMPLETO,
                    u.DS_EMAIL,
                    loc.DS_LOCAL_ATENDIMENTO,
                    esp.DS_ESPECIALIDADE
                FROM CPOE_PROCEDIMENTO p
                LEFT JOIN ATENDIMENTO a ON p.NR_ATENDIMENTO = a.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON p.NM_USUARIO = u.NM_USUARIO
                LEFT JOIN LOCAL_ATENDIMENTO loc ON a.CD_LOCAL_ATENDIMENTO = loc.CD_LOCAL_ATENDIMENTO
                LEFT JOIN ESPECIALIDADE esp ON a.CD_ESPECIALIDADE = esp.CD_ESPECIALIDADE
                WHERE p.NR_SEQUENCIA = :id";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':id', $id);
        
        if (!oci_execute($stmt)) {
            throw new Exception("Erro ao buscar procedimento: " . oci_error($stmt));
        }
        
        $row = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        return $row ? $this->formatRow($row) : null;
    }
    
    /**
     * Busca procedimentos por paciente
     */
    public function getProcedimentosByPaciente($pacienteId) {
        $sql = "SELECT 
                    p.NR_SEQUENCIA,
                    p.DS_PROCEDIMENTO,
                    p.DT_PROCEDIMENTO,
                    p.DS_OBSERVACAO,
                    p.NM_USUARIO,
                    p.DT_ATUALIZACAO,
                    u.NM_USUARIO_COMPLETO
                FROM CPOE_PROCEDIMENTO p
                LEFT JOIN USUARIO u ON p.NM_USUARIO = u.NM_USUARIO
                WHERE p.CD_PACIENTE = :paciente_id
                ORDER BY p.DT_PROCEDIMENTO DESC";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':paciente_id', $pacienteId);
        
        if (!oci_execute($stmt)) {
            throw new Exception("Erro ao buscar procedimentos do paciente: " . oci_error($stmt));
        }
        
        $procedimentos = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $procedimentos[] = $this->formatRow($row);
        }
        
        oci_free_statement($stmt);
        return $procedimentos;
    }
    
    /**
     * Busca procedimentos por atendimento
     */
    public function getProcedimentosByAtendimento($atendimentoId) {
        $sql = "SELECT 
                    p.NR_SEQUENCIA,
                    p.DS_PROCEDIMENTO,
                    p.DT_PROCEDIMENTO,
                    p.DS_OBSERVACAO,
                    p.NM_USUARIO,
                    p.DT_ATUALIZACAO,
                    u.NM_USUARIO_COMPLETO,
                    loc.DS_LOCAL_ATENDIMENTO
                FROM CPOE_PROCEDIMENTO p
                LEFT JOIN USUARIO u ON p.NM_USUARIO = u.NM_USUARIO
                LEFT JOIN ATENDIMENTO a ON p.NR_ATENDIMENTO = a.NR_ATENDIMENTO
                LEFT JOIN LOCAL_ATENDIMENTO loc ON a.CD_LOCAL_ATENDIMENTO = loc.CD_LOCAL_ATENDIMENTO
                WHERE p.NR_ATENDIMENTO = :atendimento_id
                ORDER BY p.DT_PROCEDIMENTO DESC";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':atendimento_id', $atendimentoId);
        
        if (!oci_execute($stmt)) {
            throw new Exception("Erro ao buscar procedimentos do atendimento: " . oci_error($stmt));
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
        
        return $row;
    }
    
    /**
     * Formata data para exibição
     */
    private function formatDate($oracleDate) {
        if (empty($oracleDate)) return '';
        
        try {
            $timestamp = strtotime($oracleDate);
            if ($timestamp !== false) {
                return date('d/m/Y H:i', $timestamp);
            }
        } catch (Exception $e) {
            error_log("Erro ao formatar data: " . $e->getMessage());
        }
        
        return $oracleDate;
    }
    
    /**
     * Conta total de procedimentos
     */
    public function getTotalProcedimentos() {
        $sql = "SELECT COUNT(*) as total FROM CPOE_PROCEDIMENTO WHERE NR_SEQUENCIA IS NOT NULL";
        $stmt = oci_parse($this->conn, $sql);
        oci_execute($stmt);
        $result = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        return $result['TOTAL'] ?? 0;
    }
    
    public function __destruct() {
        if ($this->conn) {
            oci_close($this->conn);
        }
    }
}
?>