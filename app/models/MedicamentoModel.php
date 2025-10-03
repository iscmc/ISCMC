<?php
require_once __DIR__ . '/../config/database.php';

class MedicamentoModel {
    private $conn;
    
    public function __construct() {
        $this->conn = DatabaseConfig::getConnection();
    }
    
    public function getAll($offset = 0, $limit = 50) {
        $sql = "SELECT 
                    m.NR_SEQUENCIA,
                    m.NR_ATENDIMENTO,
                    m.CD_MATERIAL,
                    m.DS_OBSERVACAO,
                    m.DT_ATUALIZACAO,
                    m.NM_USUARIO,
                    m.IE_VIA_APLICACAO,
                    m.QT_DOSE,
                    m.CD_UNIDADE_MEDIDA,
                    m.QT_DOSAGEM,
                    m.DS_SOLUCAO,
                    m.DS_HORARIOS,
                    m.DT_INICIO,
                    m.DT_FIM,
                    m.IE_URGENCIA,
                    obter_nome_pf(p.CD_PESSOA_FISICA) as NOME_PACIENTE,
                    u.DS_USUARIO,
                    TO_CHAR(m.DT_ATUALIZACAO, 'DD/MM/YYYY HH24:MI') as DT_ATUALIZACAO_FORMATADA,
                    TO_CHAR(m.DT_INICIO, 'DD/MM/YYYY') as DT_INICIO_FORMATADA,
                    TO_CHAR(m.DT_FIM, 'DD/MM/YYYY') as DT_FIM_FORMATADA,
                    SUBSTR(m.DS_OBSERVACAO, 1, 100) as DS_OBSERVACAO_PREVIEW,
                    mat.DS_MATERIAL as DS_MEDICAMENTO
                FROM CPOE_MATERIAL m
                LEFT JOIN ATENDIMENTO_PACIENTE p ON m.NR_ATENDIMENTO = p.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON m.NM_USUARIO = u.NM_USUARIO
                LEFT JOIN MATERIAL mat ON m.CD_MATERIAL = mat.CD_MATERIAL
                ORDER BY m.DT_ATUALIZACAO DESC";
        
        if ($limit > 0) {
            $sql = "SELECT * FROM ($sql) 
                    OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
        }

        $stmt = oci_parse($this->conn, $sql);
        if ($limit > 0) {
            oci_bind_by_name($stmt, ':offset', $offset);
            oci_bind_by_name($stmt, ':limit', $limit);
        }
        
        if (!oci_execute($stmt)) {
            $error = oci_error($stmt);
            throw new Exception("Erro ao buscar medicamentos: " . $error['message']);
        }
        
        $medicamentos = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $medicamentos[] = $this->formatRow($row);
        }
        
        oci_free_statement($stmt);
        return $medicamentos;
    }

    public function search($searchType, $searchTerm, $offset = 0, $limit = 50) {
        $searchTerm = strtoupper(trim($searchTerm));
        $whereConditions = [];
        $params = [];

        switch ($searchType) {
            case 'medicamento':
                $whereConditions[] = "UPPER(mat.DS_MATERIAL) LIKE UPPER('%' || :search_term || '%')";
                $params[':search_term'] = $searchTerm;
                break;
                
            case 'paciente':
                $whereConditions[] = "UPPER(obter_nome_pf(p.CD_PESSOA_FISICA)) LIKE UPPER('%' || :search_term || '%')";
                $params[':search_term'] = $searchTerm;
                break;
                
            case 'atendimento':
                $whereConditions[] = "m.NR_ATENDIMENTO = :search_term";
                $params[':search_term'] = $searchTerm;
                break;
                
            case 'usuario':
                $whereConditions[] = "UPPER(u.DS_USUARIO) LIKE UPPER('%' || :search_term || '%')";
                $params[':search_term'] = $searchTerm;
                break;
        }

        $sql = "SELECT 
                    m.NR_SEQUENCIA,
                    m.NR_ATENDIMENTO,
                    m.CD_MATERIAL,
                    m.DS_OBSERVACAO,
                    m.DT_ATUALIZACAO,
                    m.NM_USUARIO,
                    m.IE_VIA_APLICACAO,
                    m.QT_DOSE,
                    m.CD_UNIDADE_MEDIDA,
                    m.QT_DOSAGEM,
                    m.DS_SOLUCAO,
                    m.DS_HORARIOS,
                    m.DT_INICIO,
                    m.DT_FIM,
                    m.IE_URGENCIA,
                    obter_nome_pf(p.CD_PESSOA_FISICA) as NOME_PACIENTE,
                    u.DS_USUARIO,
                    TO_CHAR(m.DT_ATUALIZACAO, 'DD/MM/YYYY HH24:MI') as DT_ATUALIZACAO_FORMATADA,
                    TO_CHAR(m.DT_INICIO, 'DD/MM/YYYY') as DT_INICIO_FORMATADA,
                    TO_CHAR(m.DT_FIM, 'DD/MM/YYYY') as DT_FIM_FORMATADA,
                    SUBSTR(m.DS_OBSERVACAO, 1, 100) as DS_OBSERVACAO_PREVIEW,
                    mat.DS_MATERIAL as DS_MEDICAMENTO
                FROM CPOE_MATERIAL m
                LEFT JOIN ATENDIMENTO_PACIENTE p ON m.NR_ATENDIMENTO = p.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON m.NM_USUARIO = u.NM_USUARIO
                LEFT JOIN MATERIAL mat ON m.CD_MATERIAL = mat.CD_MATERIAL";

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }

        $sql .= " ORDER BY m.DT_ATUALIZACAO DESC";

        if ($limit > 0) {
            $sql = "SELECT * FROM ($sql) 
                    OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
        }

        $stmt = oci_parse($this->conn, $sql);
        
        // Bind dos parâmetros
        foreach ($params as $key => $value) {
            oci_bind_by_name($stmt, $key, $value);
        }
        
        if ($limit > 0) {
            oci_bind_by_name($stmt, ':offset', $offset);
            oci_bind_by_name($stmt, ':limit', $limit);
        }
        
        if (!oci_execute($stmt)) {
            $error = oci_error($stmt);
            throw new Exception("Erro ao buscar medicamentos: " . $error['message']);
        }
        
        $medicamentos = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $medicamentos[] = $this->formatRow($row);
        }
        
        oci_free_statement($stmt);
        return $medicamentos;
    }

    /**
     * Conta total de resultados da busca
     */
    public function getSearchCount($searchType, $searchTerm) {
        $searchTerm = strtoupper(trim($searchTerm));
        $whereConditions = [];
        $params = [];

        switch ($searchType) {
            case 'medicamento':
                $whereConditions[] = "UPPER(mat.DS_MATERIAL) LIKE UPPER('%' || :search_term || '%')";
                $params[':search_term'] = $searchTerm;
                break;
                
            case 'paciente':
                $whereConditions[] = "UPPER(obter_nome_pf(p.CD_PESSOA_FISICA)) LIKE UPPER('%' || :search_term || '%')";
                $params[':search_term'] = $searchTerm;
                break;
                
            case 'atendimento':
                $whereConditions[] = "m.NR_ATENDIMENTO = :search_term";
                $params[':search_term'] = $searchTerm;
                break;
                
            case 'usuario':
                $whereConditions[] = "UPPER(u.DS_USUARIO) LIKE UPPER('%' || :search_term || '%')";
                $params[':search_term'] = $searchTerm;
                break;
        }

        $sql = "SELECT COUNT(*) as total 
                FROM CPOE_MATERIAL m
                LEFT JOIN ATENDIMENTO_PACIENTE p ON m.NR_ATENDIMENTO = p.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON m.NM_USUARIO = u.NM_USUARIO
                LEFT JOIN MATERIAL mat ON m.CD_MATERIAL = mat.CD_MATERIAL";

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }

        $stmt = oci_parse($this->conn, $sql);
        
        foreach ($params as $key => $value) {
            oci_bind_by_name($stmt, $key, $value);
        }
        
        oci_execute($stmt);
        $result = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        return $result['TOTAL'] ?? 0;
    }

    public function getById($id) {
        $sql = "SELECT 
                    m.*,
                    obter_nome_pf(p.CD_PESSOA_FISICA) as NOME_PACIENTE,
                    u.DS_USUARIO,
                    TO_CHAR(m.DT_ATUALIZACAO, 'DD/MM/YYYY HH24:MI') as DT_ATUALIZACAO_FORMATADA,
                    TO_CHAR(m.DT_INICIO, 'DD/MM/YYYY HH24:MI') as DT_INICIO_FORMATADA,
                    TO_CHAR(m.DT_FIM, 'DD/MM/YYYY HH24:MI') as DT_FIM_FORMATADA,
                    mat.DS_MATERIAL as DS_MEDICAMENTO
                FROM CPOE_MATERIAL m
                LEFT JOIN ATENDIMENTO_PACIENTE p ON m.NR_ATENDIMENTO = p.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON m.NM_USUARIO = u.NM_USUARIO
                LEFT JOIN MATERIAL mat ON m.CD_MATERIAL = mat.CD_MATERIAL
                WHERE m.NR_SEQUENCIA = :id";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':id', $id);
        
        if (!oci_execute($stmt)) {
            $error = oci_error($stmt);
            throw new Exception("Erro ao buscar medicamento: " . $error['message']);
        }
        
        $row = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        if (!$row) {
            throw new Exception("Medicamento não encontrado com ID: " . $id);
        }
        
        return $this->formatRow($row);
    }

    /**
     * Formata os dados para exibição
     */
    private function formatRow($row) {
        $defaults = [
            'DS_MEDICAMENTO' => 'N/A',
            'NOME_PACIENTE' => 'N/A',
            'DS_USUARIO' => 'N/A',
            'DS_OBSERVACAO' => '',
            'IE_VIA_APLICACAO' => 'N/A',
            'QT_DOSE' => 0,
            'QT_DOSAGEM' => 0,
            'DS_SOLUCAO' => '',
            'DS_HORARIOS' => '',
            'IE_URGENCIA' => 'N'
        ];
        
        $row = array_merge($defaults, $row);
        
        // Formata datas
        foreach ($row as $key => $value) {
            if (preg_match('/^DT_/', $key) && $value && !preg_match('/_FORMATADA$/', $key)) {
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
    
    private function formatDate($oracleDate) {
        if (empty($oracleDate) || $oracleDate == 'N/A') {
            return 'N/A';
        }
        
        try {
            if (is_string($oracleDate) && preg_match('/^\d{2}\/\d{2}\/\d{4}/', $oracleDate)) {
                return $oracleDate;
            }
            
            $timestamp = strtotime($oracleDate);
            if ($timestamp !== false) {
                return date('d/m/Y H:i', $timestamp);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao formatar data '$oracleDate': " . $e->getMessage());
        }
        
        return substr($oracleDate, 0, 50);
    }
    
    public function getTotalMedicamentos() {
        $sql = "SELECT COUNT(*) as total FROM CPOE_MATERIAL m";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_execute($stmt);
        $result = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        return $result['TOTAL'] ?? 0;
    }
}
?>