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

class EstabelecimentoModel {
    private $conn;
    
    public function __construct() {
        $this->conn = DatabaseConfig::getConnection();
    }
    
    /**
     * Busca todos os estabelecimentos ativos
     */
    public function getEstabelecimentosAtivos() {
        $sql = "SELECT 
                    CD_ESTABELECIMENTO,
                    NM_FANTASIA_ESTAB,
                    NM_SIGLA_ESTAB,
                    IE_SITUACAO,
                    CD_CGC,
                    DT_ATUALIZACAO,
                    NM_USUARIO
                FROM ESTABELECIMENTO 
                WHERE IE_SITUACAO = 'A' 
                ORDER BY CD_ESTABELECIMENTO";
        
        $stmt = oci_parse($this->conn, $sql);
        
        if (!oci_execute($stmt)) {
            $this->handleDatabaseError($stmt, "Erro ao buscar estabelecimentos");
        }
        
        $estabelecimentos = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $estabelecimentos[] = $this->formatRow($row);
        }
        
        oci_free_statement($stmt);
        return $estabelecimentos;
    }
    
    /**
     * Busca estabelecimento por ID
     */
    public function getEstabelecimentoById($id) {
        $sql = "SELECT 
                    CD_ESTABELECIMENTO,
                    NM_FANTASIA_ESTAB,
                    NM_SIGLA_ESTAB,
                    IE_SITUACAO,
                    CD_CGC,
                    DT_ATUALIZACAO,
                    NM_USUARIO,
                    CD_INSCRICAO_ESTADUAL,
                    CD_INSCRICAO_MUNICIPAL,
                    DS_ARQ_LOGO,
                    IE_CONVENIADO_SUS,
                    CD_ANS,
                    SG_ESTADO
                FROM ESTABELECIMENTO 
                WHERE CD_ESTABELECIMENTO = :id";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_bind_by_name($stmt, ':id', $id);
        
        if (!oci_execute($stmt)) {
            $this->handleDatabaseError($stmt, "Erro ao buscar estabelecimento");
        }
        
        $row = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        if (!$row) {
            throw new Exception("Estabelecimento não encontrado com ID: " . $id);
        }
        
        return $this->formatRow($row);
    }
    
  
    /**
     * Conta total de estabelecimentos ativos
     */
    public function getTotalEstabelecimentosAtivos() {
        $sql = "SELECT COUNT(*) as total 
                FROM ESTABELECIMENTO 
                WHERE IE_SITUACAO = 'A'";
        
        $stmt = oci_parse($this->conn, $sql);
        oci_execute($stmt);
        $result = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);
        
        return $result['TOTAL'] ?? 0;
    }
    
    /**
     * Formata os dados para exibição
     */
    private function formatRow($row) {
        // Garante que todas as chaves necessárias existam
        $defaults = [
            'NM_FANTASIA_ESTAB' => 'N/D',
            'NM_SIGLA_ESTAB' => '',
            'IE_SITUACAO' => 'N/D',
            'CD_CGC' => '',
            'DT_ATUALIZACAO' => null,
            'NM_USUARIO' => 'N/D'
        ];
        
        $row = array_merge($defaults, $row);
        
        // Formata CNPJ se existir
        if (isset($row['CD_CGC']) && strlen($row['CD_CGC']) == 14) {
            $cnpj = $row['CD_CGC'];
            $row['CD_CGC_FORMATADO'] = substr($cnpj, 0, 2) . '.' . 
                                      substr($cnpj, 2, 3) . '.' . 
                                      substr($cnpj, 5, 3) . '/' . 
                                      substr($cnpj, 8, 4) . '-' . 
                                      substr($cnpj, 12, 2);
        } else {
            $row['CD_CGC_FORMATADO'] = $row['CD_CGC'];
        }
        
        // Status formatado
        if (isset($row['IE_SITUACAO'])) {
            $row['DS_SITUACAO'] = $row['IE_SITUACAO'] == 'A' ? 'Ativo' : 'Inativo';
        }
        
        return $row;
    }
    
 

    // Captura erros do Oracle e formata numa mensagem amigável
    private function handleDatabaseError($stmt, $message) {
        $error = oci_error($stmt);
        throw new Exception("$message: " . ($error['message'] ?? 'Erro desconhecido'));
    }
}
?>