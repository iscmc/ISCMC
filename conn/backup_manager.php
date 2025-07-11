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

require_once 'DatabaseConfig.php';

/**
 * Classe principal para gerenciamento de backup incremental
 */
class BackupManager {
    private $config;
    private $isRunning = false;
    private $retryCount = 0;
    
    public function __construct() {
        $this->config = new DatabaseConfig();
    }
    
    /**
     * Executa backup completo inicial
     */
    public function runFullBackup() {
        echo "Iniciando backup completo...\n";
        
        $tabelas = $this->config->getBackupTables();
        
        foreach ($tabelas as $tabela) {
            $tabela = trim($tabela);
            echo "Processando tabela: $tabela\n";
            
            try {
                $this->config->logOperation($tabela, 'FULL', 'PENDING');
                
                $count = $this->copyFullTable($tabela);
                
                $this->config->logOperation($tabela, 'FULL', 'SUCCESS', $count);
                $this->config->updateSyncControl($tabela, null, date('Y-m-d H:i:s'));
                
                echo "Tabela $tabela: $count registros copiados\n";
                
            } catch (Exception $e) {
                $this->config->logOperation($tabela, 'FULL', 'ERROR', 0, $e->getMessage());
                echo "Erro ao processar tabela $tabela: " . $e->getMessage() . "\n";
            }
        }
        
        echo "Backup completo finalizado.\n";
    }
    
    /**
     * Executa backup incremental
     */
    public function runIncrementalBackup() {
        if (!$this->config->testCloudConnection()) {
            echo "Conexão com cloud indisponível. Entrando em stand-by...\n";
            $this->handleConnectionError();
            return;
        }
        
        $this->retryCount = 0;
        $tabelas = $this->config->getBackupTables();
        
        foreach ($tabelas as $tabela) {
            $tabela = trim($tabela);
            
            try {
                $this->config->logOperation($tabela, 'INCREMENTAL', 'PENDING');
                
                $count = $this->copyIncrementalTable($tabela);
                
                if ($count > 0) {
                    $this->config->logOperation($tabela, 'INCREMENTAL', 'SUCCESS', $count);
                    echo "Tabela $tabela: $count novos registros copiados\n";
                } else {
                    $this->config->logOperation($tabela, 'INCREMENTAL', 'SUCCESS', 0);
                }
                
            } catch (Exception $e) {
                $this->config->logOperation($tabela, 'INCREMENTAL', 'ERROR', 0, $e->getMessage());
                echo "Erro ao processar tabela $tabela: " . $e->getMessage() . "\n";
            }
        }
    }
    
    /**
     * Copia tabela completa
     */
    private function copyFullTable($tabela) {
        $cloudConn = $this->config->getCloudConnection();
        $localConn = $this->config->getLocalConnection();
        
        // Limpa tabela local
        $localConn->exec("DELETE FROM $tabela");
        
        // Obtém estrutura da tabela
        $columns = $this->getTableColumns($tabela);
        
        // Copia dados
        $stmt = $cloudConn->prepare("SELECT * FROM $tabela");
        $stmt->execute();
        
        $count = 0;
        $batchSize = 1000;
        $batch = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batch[] = $row;
            
            if (count($batch) >= $batchSize) {
                $this->insertBatch($localConn, $tabela, $columns, $batch);
                $count += count($batch);
                $batch = [];
            }
        }
        
        // Insere último batch
        if (!empty($batch)) {
            $this->insertBatch($localConn, $tabela, $columns, $batch);
            $count += count($batch);
        }
        
        return $count;
    }
    
    /**
     * Copia registros incrementais
     */
    private function copyIncrementalTable($tabela) {
        $cloudConn = $this->config->getCloudConnection();
        $localConn = $this->config->getLocalConnection();
        
        // Obtém último controle de sincronização
        $lastSync = $this->config->getLastSync($tabela);
        $lastTimestamp = $lastSync['ULTIMO_TIMESTAMP'];
        $lastId = $lastSync['ULTIMO_ID'];
        
        // Obtém estrutura da tabela
        $columns = $this->getTableColumns($tabela);
        
        // Identifica coluna de timestamp/id para controle
        $timestampColumn = $this->identifyTimestampColumn($tabela);
        $idColumn = $this->identifyIdColumn($tabela);
        
        $whereClause = '';
        $params = [];
        
        if ($timestampColumn && $lastTimestamp) {
            $whereClause = "WHERE $timestampColumn > :last_timestamp";
            $params[':last_timestamp'] = $lastTimestamp;
        } elseif ($idColumn && $lastId) {
            $whereClause = "WHERE $idColumn > :last_id";
            $params[':last_id'] = $lastId;
        }
        
        if (empty($whereClause)) {
            return 0; // Não há controle incremental possível
        }
        
        // Busca novos registros
        $sql = "SELECT * FROM $tabela $whereClause ORDER BY " . ($timestampColumn ?: $idColumn);
        $stmt = $cloudConn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        $count = 0;
        $batchSize = 1000;
        $batch = [];
        $lastProcessedId = $lastId;
        $lastProcessedTimestamp = $lastTimestamp;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batch[] = $row;
            
            // Atualiza controles
            if ($idColumn && isset($row[strtoupper($idColumn)])) {
                $lastProcessedId = $row[strtoupper($idColumn)];
            }
            if ($timestampColumn && isset($row[strtoupper($timestampColumn)])) {
                $lastProcessedTimestamp = $row[strtoupper($timestampColumn)];
            }
            
            if (count($batch) >= $batchSize) {
                $this->insertBatch($localConn, $tabela, $columns, $batch);
                $count += count($batch);
                $batch = [];
                
                // Atualiza controle a cada batch
                $this->config->updateSyncControl($tabela, $lastProcessedId, $lastProcessedTimestamp);
            }
        }
        
        // Insere último batch
        if (!empty($batch)) {
            $this->insertBatch($localConn, $tabela, $columns, $batch);
            $count += count($batch);
        }
        
        // Atualiza controle final
        if ($count > 0) {
            $this->config->updateSyncControl($tabela, $lastProcessedId, $lastProcessedTimestamp);
        }
        
        return $count;
    }
    
    /**
     * Insere batch de registros
     */
    private function insertBatch($connection, $tabela, $columns, $batch) {
        $columnNames = implode(', ', $columns);
        $placeholders = ':' . implode(', :', $columns);
        
        $sql = "INSERT INTO $tabela ($columnNames) VALUES ($placeholders)";
        $stmt = $connection->prepare($sql);
        
        foreach ($batch as $row) {
            $params = [];
            foreach ($columns as $column) {
                $params[':' . $column] = $row[strtoupper($column)] ?? null;
            }
            
            $stmt->execute($params);
        }
    }
    
    /**
     * Obtém colunas da tabela
     */
    private function getTableColumns($tabela) {
        $localConn = $this->config->getLocalConnection();
        
        $stmt = $localConn->prepare("
            SELECT column_name 
            FROM user_tab_columns 
            WHERE table_name = :tabela 
            ORDER BY column_id
        ");
        
        $stmt->bindParam(':tabela', strtoupper($tabela));
        $stmt->execute();
        
        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = strtolower($row['COLUMN_NAME']);
        }
        
        return $columns;
    }
    
    /**
     * Identifica coluna de timestamp
     */
    private function identifyTimestampColumn($tabela) {
        $possibleColumns = ['data_criacao', 'data_atualizacao', 'created_at', 'updated_at', 'timestamp'];
        $columns = $this->getTableColumns($tabela);
        
        foreach ($possibleColumns as $possible) {
            if (in_array($possible, $columns)) {
                return $possible;
            }
        }
        
        return null;
    }
    
    /**
     * Identifica coluna de ID
     */
    private function identifyIdColumn($tabela) {
        $possibleColumns = ['id', $tabela . '_id', 'codigo', 'pk_id'];
        $columns = $this->getTableColumns($tabela);
        
        foreach ($possibleColumns as $possible) {
            if (in_array($possible, $columns)) {
                return $possible;
            }
        }
        
        return null;
    }
    
    /**
     * Gerencia erro de conexão
     */
    private function handleConnectionError() {
        $maxRetries = (int)$this->config->getConfig('MAX_RETRIES');
        $retryInterval = (int)$this->config->getConfig('RETRY_INTERVAL');
        
        if ($this->retryCount < $maxRetries) {
            $this->retryCount++;
            echo "Tentativa de reconexão {$this->retryCount}/$maxRetries em {$retryInterval}s...\n";
            sleep($retryInterval);
            
            if ($this->config->testCloudConnection()) {
                echo "Conexão restaurada!\n";
                $this->retryCount = 0;
                return;
            }
            
            $this->handleConnectionError();
        } else {
            echo "Máximo de tentativas excedido. Entrando em stand-by...\n";
            $this->retryCount = 0;
        }
    }
    
    /**
     * Inicia serviço de backup contínuo
     */
    public function startBackupService() {
        $this->isRunning = true;
        $interval = (int)$this->config->getConfig('BACKUP_INTERVAL');
        
        echo "Serviço de backup iniciado. Intervalo: {$interval}s\n";
        
        while ($this->isRunning) {
            $startTime = time();
            
            echo "[" . date('Y-m-d H:i:s') . "] Iniciando backup incremental...\n";
            $this->runIncrementalBackup();
            
            $endTime = time();
            $elapsed = $endTime - $startTime;
            $sleepTime = max(0, $interval - $elapsed);
            
            echo "[" . date('Y-m-d H:i:s') . "] Backup concluído em {$elapsed}s. Aguardando {$sleepTime}s...\n";
            
            if ($sleepTime > 0) {
                sleep($sleepTime);
            }
        }
    }
    
    /**
     * Para o serviço de backup
     */
    public function stopBackupService() {
        $this->isRunning = false;
        echo "Serviço de backup interrompido.\n";
    }
    
    /**
     * Obtém status do serviço
     */
    public function getServiceStatus() {
        return [
            'is_running' => $this->isRunning,
            'cloud_connection' => $this->config->testCloudConnection(),
            'last_backup' => $this->getLastBackupTime(),
            'retry_count' => $this->retryCount
        ];
    }
    
    /**
     * Obtém horário do último backup
     */
    private function getLastBackupTime() {
        $localConn = $this->config->getLocalConnection();
        
        $stmt = $localConn->query("
            SELECT MAX(data_fim) as ultimo_backup 
            FROM log_backup 
            WHERE status = 'SUCCESS'
        ");
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['ULTIMO_BACKUP'];
    }
}