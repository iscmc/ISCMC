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
 * @version  1.2.0
 * @since    2025-07-01
 * @maindev  Sergio Figueroa
 */

/**
 * Classe para gerenciamento de configurações e conexões do sistema de backup
 */
class DatabaseConfig {
    private $localConnection;
    private $cloudConnection;
    private $config = [];
    
    public function __construct() {
        $this->loadLocalConnection();
        $this->loadConfigurations();
    }
    
    /**
     * Carrega conexão local Oracle XE
     */
    private function loadLocalConnection() {
        try {
            $dsn = "oci:dbname=localhost:1521/XE";
            $this->localConnection = new PDO($dsn, 'backup_user', 'senha_local');
            $this->localConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Erro ao conectar ao banco local: " . $e->getMessage());
        }
    }
    
    /**
     * Carrega configurações do banco de dados
     */
    private function loadConfigurations() {
        try {
            $stmt = $this->localConnection->query("SELECT parametro, valor FROM config_backup");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->config[$row['PARAMETRO']] = $row['VALOR'];
            }
        } catch (PDOException $e) {
            throw new Exception("Erro ao carregar configurações: " . $e->getMessage());
        }
    }
    
    /**
     * Obtém conexão com Oracle Cloud
     */
    public function getCloudConnection() {
        if (!$this->cloudConnection) {
            try {
                $dsn = sprintf("oci:dbname=%s:%s/%s", 
                    $this->config['CLOUD_HOST'], 
                    $this->config['CLOUD_PORT'], 
                    $this->config['CLOUD_SERVICE']
                );
                
                $this->cloudConnection = new PDO($dsn, 
                    $this->config['CLOUD_USER'], 
                    $this->config['CLOUD_PASSWORD']
                );
                
                $this->cloudConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new Exception("Erro ao conectar ao banco cloud: " . $e->getMessage());
            }
        }
        return $this->cloudConnection;
    }
    
    /**
     * Obtém conexão local
     */
    public function getLocalConnection() {
        return $this->localConnection;
    }
    
    /**
     * Obtém configuração específica
     */
    public function getConfig($key) {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }
    
    /**
     * Atualiza configuração
     */
    public function updateConfig($key, $value) {
        try {
            $stmt = $this->localConnection->prepare("UPDATE config_backup SET valor = :valor, data_atualizacao = SYSDATE WHERE parametro = :parametro");
            $stmt->bindParam(':valor', $value);
            $stmt->bindParam(':parametro', $key);
            $stmt->execute();
            
            $this->config[$key] = $value;
            return true;
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar configuração: " . $e->getMessage());
        }
    }
    
    /**
     * Testa conexão com o banco cloud
     */
    public function testCloudConnection() {
        try {
            $conn = $this->getCloudConnection();
            $stmt = $conn->query("SELECT 1 FROM DUAL");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtém lista de tabelas para backup
     */
    public function getBackupTables() {
        $tabelas = $this->getConfig('TABELAS_BACKUP');
        return explode(',', $tabelas);
    }
    
    /**
     * Registra log de operação
     */
    public function logOperation($tabela, $tipo, $status, $registros = 0, $erro = null, $ultimoId = null) {
        try {
            $stmt = $this->localConnection->prepare("
                INSERT INTO log_backup (id, tabela_origem, tipo_operacao, registros_copiados, data_inicio, data_fim, status, erro_mensagem, ultimo_id_processado) 
                VALUES (seq_log_backup.NEXTVAL, :tabela, :tipo, :registros, SYSDATE, SYSDATE, :status, :erro, :ultimo_id)
            ");
            
            $stmt->bindParam(':tabela', $tabela);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':registros', $registros);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':erro', $erro);
            $stmt->bindParam(':ultimo_id', $ultimoId);
            
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }
    
    /**
     * Obtém último controle de sincronização
     */
    public function getLastSync($tabela) {
        try {
            $stmt = $this->localConnection->prepare("SELECT ultimo_timestamp, ultimo_id FROM controle_sync WHERE tabela_nome = :tabela");
            $stmt->bindParam(':tabela', $tabela);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao obter último sync: " . $e->getMessage());
        }
    }
    
    /**
     * Atualiza controle de sincronização
     */
    public function updateSyncControl($tabela, $ultimoId = null, $ultimoTimestamp = null) {
        try {
            $sql = "UPDATE controle_sync SET data_atualizacao = SYSDATE";
            $params = [];
            
            if ($ultimoId !== null) {
                $sql .= ", ultimo_id = :ultimo_id";
                $params[':ultimo_id'] = $ultimoId;
            }
            
            if ($ultimoTimestamp !== null) {
                $sql .= ", ultimo_timestamp = :ultimo_timestamp";
                $params[':ultimo_timestamp'] = $ultimoTimestamp;
            }
            
            $sql .= " WHERE tabela_nome = :tabela";
            $params[':tabela'] = $tabela;
            
            $stmt = $this->localConnection->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar controle sync: " . $e->getMessage());
        }
    }
    
    /**
     * Fecha conexões
     */
    public function closeConnections() {
        $this->localConnection = null;
        $this->cloudConnection = null;
    }
    
    /**
     * Destrutor
     */
    public function __destruct() {
        $this->closeConnections();
    }
}
?>