<?php
require_once __DIR__ . '/../config/database.php';

class MedicamentoModel {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
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
                    p.NM_PACIENTE as NOME_PACIENTE,
                    u.NM_USUARIO_COMPLETO,
                    TO_CHAR(m.DT_ATUALIZACAO, 'DD/MM/YYYY HH24:MI') as DT_ATUALIZACAO_FORMATADA,
                    TO_CHAR(m.DT_INICIO, 'DD/MM/YYYY') as DT_INICIO_FORMATADA,
                    TO_CHAR(m.DT_FIM, 'DD/MM/YYYY') as DT_FIM_FORMATADA,
                    SUBSTR(m.DS_OBSERVACAO, 1, 100) as DS_OBSERVACAO_PREVIEW,
                    mat.DS_MATERIAL as DS_MEDICAMENTO
                FROM CPOE_MATERIAL m
                LEFT JOIN PACIENTE p ON m.NR_ATENDIMENTO = p.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON m.NM_USUARIO = u.CD_USUARIO
                LEFT JOIN MATERIAL mat ON m.CD_MATERIAL = mat.CD_MATERIAL
                WHERE m.IE_MATERIAL = 'MED'
                ORDER BY m.DT_ATUALIZACAO DESC";

        $countSql = "SELECT COUNT(*) as total 
                     FROM CPOE_MATERIAL m 
                     WHERE m.IE_MATERIAL = 'MED'";

        return $this->executePaginatedQuery($sql, $countSql, [], $offset, $limit);
    }

    public function search($type, $term, $offset = 0, $limit = 50) {
        $term = strtoupper(trim($term));
        $params = [];
        $whereConditions = ["m.IE_MATERIAL = 'MED'"];

        switch ($type) {
            case 'medicamento':
                $whereConditions[] = "UPPER(mat.DS_MATERIAL) LIKE UPPER(:term)";
                $params[':term'] = "%$term%";
                break;
            case 'paciente':
                $whereConditions[] = "m.NR_ATENDIMENTO = :term";
                $params[':term'] = $term;
                break;
            case 'atendimento':
                $whereConditions[] = "m.NR_ATENDIMENTO = :term";
                $params[':term'] = $term;
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
                    p.NM_PACIENTE as NOME_PACIENTE,
                    u.NM_USUARIO_COMPLETO,
                    TO_CHAR(m.DT_ATUALIZACAO, 'DD/MM/YYYY HH24:MI') as DT_ATUALIZACAO_FORMATADA,
                    TO_CHAR(m.DT_INICIO, 'DD/MM/YYYY') as DT_INICIO_FORMATADA,
                    TO_CHAR(m.DT_FIM, 'DD/MM/YYYY') as DT_FIM_FORMATADA,
                    SUBSTR(m.DS_OBSERVACAO, 1, 100) as DS_OBSERVACAO_PREVIEW,
                    mat.DS_MATERIAL as DS_MEDICAMENTO
                FROM CPOE_MATERIAL m
                LEFT JOIN PACIENTE p ON m.NR_ATENDIMENTO = p.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON m.NM_USUARIO = u.CD_USUARIO
                LEFT JOIN MATERIAL mat ON m.CD_MATERIAL = mat.CD_MATERIAL
                WHERE " . implode(' AND ', $whereConditions) . "
                ORDER BY m.DT_ATUALIZACAO DESC";

        $countSql = "SELECT COUNT(*) as total 
                     FROM CPOE_MATERIAL m
                     LEFT JOIN PACIENTE p ON m.NR_ATENDIMENTO = p.NR_ATENDIMENTO
                     LEFT JOIN MATERIAL mat ON m.CD_MATERIAL = mat.CD_MATERIAL
                     WHERE " . implode(' AND ', $whereConditions);

        return $this->executePaginatedQuery($sql, $countSql, $params, $offset, $limit);
    }

    public function getById($id) {
        $sql = "SELECT 
                    m.*,
                    p.NM_PACIENTE as NOME_PACIENTE,
                    u.NM_USUARIO_COMPLETO,
                    TO_CHAR(m.DT_ATUALIZACAO, 'DD/MM/YYYY HH24:MI') as DT_ATUALIZACAO_FORMATADA,
                    TO_CHAR(m.DT_INICIO, 'DD/MM/YYYY HH24:MI') as DT_INICIO_FORMATADA,
                    TO_CHAR(m.DT_FIM, 'DD/MM/YYYY HH24:MI') as DT_FIM_FORMATADA,
                    mat.DS_MATERIAL as DS_MEDICAMENTO,
                    um.DS_UNIDADE_MEDIDA
                FROM CPOE_MATERIAL m
                LEFT JOIN PACIENTE p ON m.NR_ATENDIMENTO = p.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON m.NM_USUARIO = u.CD_USUARIO
                LEFT JOIN MATERIAL mat ON m.CD_MATERIAL = mat.CD_MATERIAL
                LEFT JOIN UNIDADE_MEDIDA um ON m.CD_UNIDADE_MEDIDA = um.CD_UNIDADE_MEDIDA
                WHERE m.NR_SEQUENCIA = :id AND m.IE_MATERIAL = 'MED'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function executePaginatedQuery($sql, $countSql, $params, $offset, $limit) {
        // Count total
        $countStmt = $this->conn->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        // Get data with pagination
        $sql .= " OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    public function __destruct() {
        $this->conn = null;
    }
}
?>