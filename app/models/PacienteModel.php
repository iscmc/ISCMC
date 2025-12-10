<?php
/**
 * Portal de contingência ISCMC Off Grid
 *
 * Este arquivo faz parte do framework MVC Projeto Contingenciamento - FrontEnd.
 *
 * @category Framework
 * @package  Servidor de contingência ISCMC
 * @author   Sergio Figueroa <sergio.figueroa@iscmc.com.br>
 * @license  MIT, Apache
 * @link     http://10.132.16.43/ISCMC
 * @version  1.0.0
 * @since    2025-09-01
 * @maindev  Sergio Figueroa
 */
require_once __DIR__ . '/../config/database.php';

class PacienteModel {
    private $conn;
    
    public function __construct() {
        $this->conn = DatabaseConfig::getConnection();
    }
    
    /**
     * Busca paciente por ID
     */
    public function getPacienteById($cdPessoaFisica) {
        $sql = "SELECT 
                    pf.CD_PESSOA_FISICA,
                    obter_nome_pf(pf.CD_PESSOA_FISICA) as NM_PACIENTE,
                    obter_nome_mae(pf.CD_PESSOA_FISICA) as NM_MAE,
                    to_char(pf.DT_NASCIMENTO, 'dd/mm/yyyy') as DT_NASCIMENTO,
                    Obter_Sexo_PF(pf.CD_PESSOA_FISICA, 'D') IE_SEXO,
                    TRUNC(MONTHS_BETWEEN(SYSDATE, pf.DT_NASCIMENTO)/12) as IDADE,
                    pf.NR_CPF,
                    pf.NR_IDENTIDADE,
                    cp.DS_ENDERECO,
                    cp.NR_ENDERECO,
                    cp.DS_COMPLEMENTO,
                    cp.DS_BAIRRO,
                    cp.CD_CEP,
                    cp.DS_MUNICIPIO,
                    cp.SG_ESTADO,
                    pf.NR_TELEFONE_CELULAR
                FROM PESSOA_FISICA pf
                INNER JOIN COMPL_PESSOA_FISICA cp on pf.CD_PESSOA_FISICA = cp.CD_PESSOA_FISICA
                WHERE pf.CD_PESSOA_FISICA = :cd_pessoa_fisica
                ORDER BY
                cp.CD_CEP DESC NULLS LAST, -- Prioriza registros com CEP não nulo (DESC NULLS LAST)
                cp.DS_COMPLEMENTO          -- Critério de desempate
                FETCH FIRST 1 ROWS ONLY";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':cd_pessoa_fisica', $cdPessoaFisica);
        
        if (!oci_execute($stmt)) {
            throw new Exception("Erro ao buscar paciente");
        }
        
        $row = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        if (!$row) {
            throw new Exception("Paciente não encontrado");
        }
        
        return $this->formatRow($row);
    }
    
    /**
     * Busca procedimentos do paciente
     */
    public function getProcedimentosByPaciente($cdPessoaFisica) {
        $sql = "SELECT 
                    p.NR_SEQUENCIA,
                    p.NR_ATENDIMENTO,
                    c.DS_PROC_EXAME as DS_PROCEDIMENTO,
                    to_char(p.DT_PREV_EXECUCAO, 'dd/mm/yyyy hh24:mi') as DT_PROCEDIMENTO,
                    p.DS_OBSERVACAO,
                    p.NM_USUARIO,
                    to_char(p.DT_ATUALIZACAO, 'dd/mm/yyyy hh24:mi') as DT_ATUALIZACAO,
                    u.DS_USUARIO as NM_USUARIO_COMPLETO
                FROM CPOE_PROCEDIMENTO p
                JOIN PROC_INTERNO c ON p.NR_SEQ_PROC_INTERNO = c.nr_sequencia
                LEFT JOIN USUARIO u ON p.NM_USUARIO = u.NM_USUARIO
                WHERE p.CD_PESSOA_FISICA = :cd_pessoa_fisica
                ORDER BY p.DT_PREV_EXECUCAO DESC, p.DT_ATUALIZACAO DESC";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':cd_pessoa_fisica', $cdPessoaFisica);
        
        if (!oci_execute($stmt)) {
            throw new Exception("Erro ao buscar procedimentos do paciente");
        }
        
        $procedimentos = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $procedimentos[] = $this->formatProcedimentoRow($row);
        }
        
        oci_free_statement($stmt);
        return $procedimentos;
    }
    
    /**
     * Busca materiais utilizados no paciente
     */
    public function getMateriaisByPaciente($cdPessoaFisica) {
        $sql = "SELECT 
                    m.NR_SEQUENCIA,
                    m.CD_MATERIAL,
                    a.DS_MATERIAL,
                    to_char(m.DT_ATUALIZACAO, 'dd/mm/yyyy hh24:mi') as DT_UTILIZACAO,
                    m.QT_DOSE,
                    m.DS_OBSERVACAO,
                    m.NM_USUARIO,
                    to_char(m.DT_ATUALIZACAO_NREC, 'dd/mm/yyyy hh24:mi') as DT_ATUALIZACAO,
                    u.DS_USUARIO as NM_USUARIO_COMPLETO
                FROM CPOE_MATERIAL m
                INNER JOIN MATERIAL a ON m.CD_MATERIAL = a.CD_MATERIAL
                LEFT JOIN USUARIO u ON m.NM_USUARIO = u.NM_USUARIO
                WHERE m.CD_PESSOA_FISICA = :cd_pessoa_fisica
                AND a.IE_TIPO_MATERIAL = '1'
                ORDER BY m.DT_ATUALIZACAO DESC, m.DT_ATUALIZACAO_NREC DESC";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':cd_pessoa_fisica', $cdPessoaFisica);
        
        if (!oci_execute($stmt)) {
            throw new Exception("Erro ao buscar materiais do paciente");
        }
        
        $materiais = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $materiais[] = $this->formatMaterialRow($row);
        }
        
        oci_free_statement($stmt);
        return $materiais;
    }
    
    /**
     * Formata dados do paciente
     */
    private function formatRow($row) {
        return [
            'CD_PACIENTE' => $row['CD_PESSOA_FISICA'],
            'NM_PACIENTE' => $row['NM_PACIENTE'],
            'NM_MAE' => ucwords(strtolower($row['NM_MAE'])),
            //'DT_NASCIMENTO' => $row['DT_NASCIMENTO'] ? date('d/m/Y', strtotime($row['DT_NASCIMENTO'])) : '',
            'DT_NASCIMENTO' => $row['DT_NASCIMENTO'] ? $row['DT_NASCIMENTO'] : '',
            'IDADE' => $row['IDADE'],
            'IE_SEXO' => $row['IE_SEXO'],
            'NR_CPF' => $row['NR_CPF'],
            'NR_IDENTIDADE' => $row['NR_IDENTIDADE'],
            'DS_ENDERECO' => $row['DS_ENDERECO'],
            'DS_BAIRRO' => $row['DS_BAIRRO'],
            'NR_CEP' => $row['CD_CEP'],
            'DS_CIDADE' => $row['DS_MUNICIPIO'],
            'DS_UF' => $row['SG_ESTADO'],
            'NR_CELULAR' => $row['NR_TELEFONE_CELULAR']
        ];
    }
    
    /**
     * Formata dados de procedimento
     */
    private function formatProcedimentoRow($row) {
        return [
            'NR_SEQUENCIA' => $row['NR_SEQUENCIA'],
            'NR_ATENDIMENTO' => $row['NR_ATENDIMENTO'],
            'DS_PROCEDIMENTO' => $row['DS_PROCEDIMENTO'],
            'DT_PROCEDIMENTO' => $row['DT_PROCEDIMENTO'],
            'DS_OBSERVACAO' => $row['DS_OBSERVACAO'],
            'NM_USUARIO' => $row['NM_USUARIO'],
            'NM_USUARIO_COMPLETO' => $row['NM_USUARIO_COMPLETO'],
            'DT_ATUALIZACAO' => $row['DT_ATUALIZACAO']
        ];
    }
    
    /**
     * Formata dados de material
     */
    private function formatMaterialRow($row) {
        return [
            'NR_SEQUENCIA' => $row['NR_SEQUENCIA'],
            'CD_MATERIAL' => $row['CD_MATERIAL'],
            'DS_MATERIAL' => $row['DS_MATERIAL'],
            'DT_UTILIZACAO' => $row['DT_UTILIZACAO'],
            'QT_UTILIZADA' => $row['QT_DOSE'],
            'DS_OBSERVACAO' => $row['DS_OBSERVACAO'],
            'NM_USUARIO' => $row['NM_USUARIO'],
            'NM_USUARIO_COMPLETO' => $row['NM_USUARIO_COMPLETO'],
            'DT_ATUALIZACAO' => $row['DT_ATUALIZACAO']
        ];
    }

    /**
     * Busca medicamentos do paciente
     */
    public function getMedicamentosByPaciente($cdPessoaFisica) {
        $sql = "SELECT 
                    m.NR_SEQUENCIA,
                    m.NR_ATENDIMENTO,
                    mat.DS_MATERIAL as DS_MEDICAMENTO,
                    to_char(m.DT_INICIO, 'dd/mm/yyyy') as DT_INICIO,
                    to_char(m.DT_FIM, 'dd/mm/yyyy') as DT_FIM,
                    m.IE_VIA_APLICACAO,
                    m.QT_DOSE,
                    m.CD_UNIDADE_MEDIDA,
                    m.QT_DOSAGEM,
                    m.DS_SOLUCAO,
                    m.DS_HORARIOS,
                    m.DS_OBSERVACAO,
                    m.NM_USUARIO,
                    to_char(m.DT_ATUALIZACAO, 'dd/mm/yyyy hh24:mi') as DT_ATUALIZACAO,
                    u.DS_USUARIO as NM_USUARIO_COMPLETO,
                    m.IE_URGENCIA
                FROM CPOE_MATERIAL m
                LEFT JOIN ATENDIMENTO_PACIENTE ap ON m.NR_ATENDIMENTO = ap.NR_ATENDIMENTO
                LEFT JOIN USUARIO u ON m.NM_USUARIO = u.NM_USUARIO
                LEFT JOIN MATERIAL mat ON m.CD_MATERIAL = mat.CD_MATERIAL
                WHERE ap.CD_PESSOA_FISICA = :cd_pessoa_fisica
                AND mat.IE_TIPO_MATERIAL NOT IN (1, 4, 5) -- Exclui materiais, serviços e gêneros alimentícios
                ORDER BY m.DT_INICIO DESC, m.DT_ATUALIZACAO DESC";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':cd_pessoa_fisica', $cdPessoaFisica);
        
        if (!oci_execute($stmt)) {
            throw new Exception("Erro ao buscar medicamentos do paciente");
        }
        
        $medicamentos = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $medicamentos[] = $this->formatMedicamentoRow($row);
        }
        
        oci_free_statement($stmt);
        return $medicamentos;
    }

    /**
     * Formata dados de medicamento
     */
    private function formatMedicamentoRow($row) {
        return [
            'NR_SEQUENCIA' => $row['NR_SEQUENCIA'],
            'NR_ATENDIMENTO' => $row['NR_ATENDIMENTO'],
            'DS_MEDICAMENTO' => $row['DS_MEDICAMENTO'],
            'DT_INICIO' => $row['DT_INICIO'],
            'DT_FIM' => $row['DT_FIM'],
            'IE_VIA_APLICACAO' => $row['IE_VIA_APLICACAO'],
            'QT_DOSE' => $row['QT_DOSE'],
            'CD_UNIDADE_MEDIDA' => $row['CD_UNIDADE_MEDIDA'],
            'QT_DOSAGEM' => $row['QT_DOSAGEM'],
            'DS_SOLUCAO' => $row['DS_SOLUCAO'],
            'DS_HORARIOS' => $row['DS_HORARIOS'],
            'DS_OBSERVACAO' => $row['DS_OBSERVACAO'],
            'NM_USUARIO' => $row['NM_USUARIO'],
            'NM_USUARIO_COMPLETO' => $row['NM_USUARIO_COMPLETO'],
            'DT_ATUALIZACAO' => $row['DT_ATUALIZACAO'],
            'IE_URGENCIA' => $row['IE_URGENCIA']
        ];
    }
}
?>