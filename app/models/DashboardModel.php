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

class DashboardModel {
    private $conn;
    
    public function __construct() {
        $this->conn = DatabaseConfig::getConnection();
    }
    
    public function buscarOcupacaoHospitalar() {
        try {
            $sql = "
                select 
                    cd_setor_atendimento,
                    ds_setor_atendimento,
                    nr_unidades_setor qtd_total,
                    nr_unidades_ocupadas qtd_ocupadas,
                    nr_unidades_livres qtd_livres,
                    nr_unid_aguard_higien qtd_aguardando_higienizacao,
                    nr_unidades_higienizacao qtd_higienizacao,
                    qt_pac_isolado qtd_isolado,
                    nr_unidades_reservadas,
                    round(dividir((nr_unidades_ocupadas*100), (nr_unidades_setor-nr_unidades_interditadas-nr_unid_temp_ocup)),2) percentual_ocupacao
                from ocup_ocupacao_setores_v2
                where cd_estabelecimento_base = ".SessionHelper::getCurrentEstabelecimento()."
                and cd_classif_setor in (3, 4)
                and ie_situacao = 'A'
                and ie_ocup_hospitalar <> 'N'
                order by cd_classif_setor, ds_setor_atendimento";
            $stmt = oci_parse($this->conn, $sql);
            
            if (!oci_execute($stmt)) {
                $this->handleDatabaseError($stmt, "Erro ao buscar ocupação hospitalar");
            }
            
            $resultados = [];
            while ($row = oci_fetch_assoc($stmt)) {
                $resultados[] = $this->formatRow($row);
            }
            
            oci_free_statement($stmt);
            return $resultados;
            
        } catch (Exception $e) {
            error_log("DashboardModel buscarOcupacaoHospitalar error: " . $e->getMessage());
            throw new Exception("Erro ao carregar dados de ocupação hospitalar");
        }
    }
    
    public function calcularTotaisOcupacao($dados) {
        $totais = [
            'ocupados' => 0,
            'livres' => 0,
            'higienizacao' => 0,
            'aguardando_higienizacao' => 0,
            'isolados' => 0,
            'reservados' => 0,
            'total' => 0
        ];
        
        foreach ($dados as $setor) {
            $totais['ocupados'] += $setor['QTD_OCUPADAS'] ?? 0;
            $totais['livres'] += $setor['QTD_LIVRES'] ?? 0;
            $totais['higienizacao'] += $setor['QTD_HIGIENIZACAO'] ?? 0;
            $totais['aguardando_higienizacao'] += $setor['QTD_AGUARDANDO_HIGIENIZACAO'] ?? 0;
            $totais['isolados'] += $setor['QTD_ISOLADO'] ?? 0;
            $totais['reservados'] += $setor['NR_UNIDADES_RESERVADAS'] ?? 0;
            $totais['total'] += $setor['QTD_TOTAL'] ?? 0;
        }
        
        return $totais;
    }
    
    /**
     * Formata os dados para exibição
     */
    private function formatRow($row) {
        $defaults = [
            'CD_SETOR_ATENDIMENTO' => 'N/A',
            'DS_SETOR_ATENDIMENTO' => 'Setor não identificado',
            'QTD_TOTAL' => 0,
            'QTD_OCUPADAS' => 0,
            'QTD_LIVRES' => 0,
            'QTD_AGUARDANDO_HIGIENIZACAO' => 0,
            'QTD_HIGIENIZACAO' => 0,
            'QTD_ISOLADO' => 0,
            'NR_UNIDADES_RESERVADAS' => 0,
            'PERCENTUAL_OCUPACAO' => 0
        ];
        
        return array_merge($defaults, $row);
    }

    private function handleDatabaseError($stmt, $message) {
        $error = oci_error($stmt);
        throw new Exception("$message: " . ($error['message'] ?? 'Erro desconhecido'));
    }
}
?>