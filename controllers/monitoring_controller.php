<?php

require_once 'models/BackupModel.php';
require_once 'models/SyncModel.php';
require_once 'models/MonitoringModel.php';
require_once 'config/Database.php';

class MonitoringController {
    private $backupModel;
    private $syncModel;
    private $monitoringModel;
    private $db;
    
    public function __construct() {
        $this->db = new Database();
        $this->backupModel = new BackupModel();
        $this->syncModel = new SyncModel();
        $this->monitoringModel = new MonitoringModel();
    }
    
    /**
     * Dashboard principal do sistema de monitoramento
     */
    public function dashboard() {
        try {
            $data = [
                'page_title' => 'Dashboard - Sistema de Backup',
                'system_status' => $this->getSystemStatus(),
                'backup_status' => $this->getBackupStatus(),
                'sync_status' => $this->getSyncStatus(),
                'recent_logs' => $this->monitoringModel->getRecentLogs(20),
                'disk_usage' => $this->getDiskUsage(),
                'performance_metrics' => $this->getPerformanceMetrics()
            ];
            
            $this->render('monitoring/dashboard', $data);
            
        } catch (Exception $e) {
            $this->handleError('Erro ao carregar dashboard', $e);
        }
    }
    
    /**
     * Executa backup manual
     */
    public function executeBackup() {
        header('Content-Type: application/json');
        
        try {
            $backup_type = $_POST['backup_type'] ?? 'full';
            $description = $_POST['description'] ?? 'Backup manual executado pelo usuário';
            
            $result = $this->backupModel->executeBackup($backup_type, $description);
            
            if ($result['success']) {
                $this->monitoringModel->logActivity('BACKUP_MANUAL', 'SUCCESS', 
                    "Backup manual executado com sucesso. Tipo: {$backup_type}");
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Backup executado com sucesso',
                    'backup_id' => $result['backup_id'],
                    'size' => $this->formatBytes($result['size']),
                    'duration' => $result['duration'] . 's'
                ]);
            } else {
                throw new Exception($result['message']);
            }
            
        } catch (Exception $e) {
            $this->monitoringModel->logActivity('BACKUP_MANUAL', 'ERROR', $e->getMessage());
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao executar backup: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Força sincronização entre Oracle e PostgreSQL
     */
    public function forceSync() {
        header('Content-Type: application/json');
        
        try {
            $sync_type = $_POST['sync_type'] ?? 'bidirectional';
            
            $result = $this->syncModel->executeSync($sync_type, true); // true = force
            
            if ($result['success']) {
                $this->monitoringModel->logActivity('SYNC_MANUAL', 'SUCCESS', 
                    "Sincronização manual executada. Tipo: {$sync_type}");
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Sincronização executada com sucesso',
                    'records_synced' => $result['records_synced'],
                    'duration' => $result['duration'] . 's'
                ]);
            } else {
                throw new Exception($result['message']);
            }
            
        } catch (Exception $e) {
            $this->monitoringModel->logActivity('SYNC_MANUAL', 'ERROR', $e->getMessage());
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao executar sincronização: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Ativa modo de contingência
     */
    public function activateContingency() {
        header('Content-Type: application/json');
        
        try {
            $reason = $_POST['reason'] ?? 'Ativação manual pelo usuário';
            
            // Verifica se já está em modo contingência
            if ($this->monitoringModel->isContingencyMode()) {
                throw new Exception('Sistema já está em modo de contingência');
            }
            
            // Ativa o modo contingência
            $result = $this->monitoringModel->activateContingencyMode($reason);
            
            if ($result) {
                $this->monitoringModel->logActivity('CONTINGENCY_ACTIVATE', 'SUCCESS', 
                    "Modo de contingência ativado. Razão: {$reason}");
                
                // Envia notificação
                $this->sendNotification('CONTINGENCY_ACTIVATED', 
                    'Sistema ativado em modo de contingência', $reason);
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Modo de contingência ativado com sucesso'
                ]);
            } else {
                throw new Exception('Falha ao ativar modo de contingência');
            }
            
        } catch (Exception $e) {
            $this->monitoringModel->logActivity('CONTINGENCY_ACTIVATE', 'ERROR', $e->getMessage());
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao ativar contingência: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Desativa modo de contingência
     */
    public function deactivateContingency() {
        header('Content-Type: application/json');
        
        try {
            // Verifica se está em modo contingência
            if (!$this->monitoringModel->isContingencyMode()) {
                throw new Exception('Sistema não está em modo de contingência');
            }
            
            // Executa sincronização antes de desativar
            $sync_result = $this->syncModel->executeSync('bidirectional', true);
            
            if (!$sync_result['success']) {
                throw new Exception('Falha na sincronização pré-desativação: ' . $sync_result['message']);
            }
            
            // Desativa o modo contingência
            $result = $this->monitoringModel->deactivateContingencyMode();
            
            if ($result) {
                $this->monitoringModel->logActivity('CONTINGENCY_DEACTIVATE', 'SUCCESS', 
                    'Modo de contingência desativado e sistemas sincronizados');
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Modo de contingência desativado com sucesso',
                    'records_synced' => $sync_result['records_synced']
                ]);
            } else {
                throw new Exception('Falha ao desativar modo de contingência');
            }
            
        } catch (Exception $e) {
            $this->monitoringModel->logActivity('CONTINGENCY_DEACTIVATE', 'ERROR', $e->getMessage());
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao desativar contingência: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Restaura backup específico
     */
    public function restoreBackup() {
        header('Content-Type: application/json');
        
        try {
            $backup_id = $_POST['backup_id'] ?? null;
            $restore_type = $_POST['restore_type'] ?? 'full';
            
            if (!$backup_id) {
                throw new Exception('ID do backup não informado');
            }
            
            // Confirma se o backup existe e está íntegro
            $backup_info = $this->backupModel->getBackupInfo($backup_id);
            if (!$backup_info) {
                throw new Exception('Backup não encontrado');
            }
            
            if (!$this->backupModel->verifyBackupIntegrity($backup_id)) {
                throw new Exception('Backup corrompido ou inválido');
            }
            
            // Executa a restauração
            $result = $this->backupModel->restoreBackup($backup_id, $restore_type);
            
            if ($result['success']) {
                $this->monitoringModel->logActivity('BACKUP_RESTORE', 'SUCCESS', 
                    "Backup {$backup_id} restaurado com sucesso. Tipo: {$restore_type}");
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Backup restaurado com sucesso',
                    'duration' => $result['duration'] . 's',
                    'records_restored' => $result['records_restored']
                ]);
            } else {
                throw new Exception($result['message']);
            }
            
        } catch (Exception $e) {
            $this->monitoringModel->logActivity('BACKUP_RESTORE', 'ERROR', $e->getMessage());
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao restaurar backup: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Retorna logs do sistema com filtros
     */
    public function getLogs() {
        header('Content-Type: application/json');
        
        try {
            $filters = [
                'start_date' => $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days')),
                'end_date' => $_GET['end_date'] ?? date('Y-m-d'),
                'level' => $_GET['level'] ?? null,
                'category' => $_GET['category'] ?? null,
                'limit' => (int)($_GET['limit'] ?? 100),
                'offset' => (int)($_GET['offset'] ?? 0)
            ];
            
            $logs = $this->monitoringModel->getLogs($filters);
            $total = $this->monitoringModel->getLogsCount($filters);
            
            echo json_encode([
                'status' => 'success',
                'logs' => $logs,
                'total' => $total,
                'has_more' => ($filters['offset'] + $filters['limit']) < $total
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao carregar logs: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Testa conectividade com bancos de dados
     */
    public function testConnections() {
        header('Content-Type: application/json');
        
        try {
            $oracle_status = $this->testOracleConnection();
            $postgres_status = $this->testPostgreSQLConnection();
            
            $overall_status = $oracle_status['connected'] && $postgres_status['connected'] ? 'healthy' : 'warning';
            
            echo json_encode([
                'status' => 'success',
                'overall_status' => $overall_status,
                'oracle' => $oracle_status,
                'postgresql' => $postgres_status,
                'tested_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao testar conexões: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Retorna métricas de performance em tempo real
     */
    public function getPerformanceData() {
        header('Content-Type: application/json');
        
        try {
            $metrics = [
                'timestamp' => time(),
                'cpu_usage' => $this->getCPUUsage(),
                'memory_usage' => $this->getMemoryUsage(),
                'disk_usage' => $this->getDiskUsage(),
                'database_connections' => [
                    'oracle' => $this->getOracleConnectionCount(),
                    'postgresql' => $this->getPostgreSQLConnectionCount()
                ],
                'backup_queue_size' => $this->backupModel->getQueueSize(),
                'sync_queue_size' => $this->syncModel->getQueueSize(),
                'system_load' => sys_getloadavg()
            ];
            
            echo json_encode([
                'status' => 'success',
                'metrics' => $metrics
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao obter métricas: ' . $e->getMessage()
            ]);
        }
    }
    
    // Métodos auxiliares privados
    
    private function getSystemStatus() {
        $oracle_connected = $this->testOracleConnection()['connected'];
        $postgres_connected = $this->testPostgreSQLConnection()['connected'];
        $contingency_mode = $this->monitoringModel->isContingencyMode();
        
        if ($contingency_mode) {
            return ['status' => 'contingency', 'message' => 'Sistema em modo de contingência'];
        }
        
        if ($oracle_connected && $postgres_connected) {
            return ['status' => 'healthy', 'message' => 'Todos os sistemas operacionais'];
        } elseif ($postgres_connected) {
            return ['status' => 'warning', 'message' => 'Oracle indisponível, usando PostgreSQL'];
        } else {
            return ['status' => 'critical', 'message' => 'Falha crítica nos bancos de dados'];
        }
    }
    
    private function getBackupStatus() {
        $last_backup = $this->backupModel->getLastBackup();
        $next_scheduled = $this->backupModel->getNextScheduledBackup();
        
        return [
            'last_backup' => $last_backup,
            'next_scheduled' => $next_scheduled,
            'total_backups' => $this->backupModel->getTotalBackups(),
            'total_size' => $this->backupModel->getTotalBackupSize()
        ];
    }
    
    private function getSyncStatus() {
        return [
            'last_sync' => $this->syncModel->getLastSync(),
            'pending_records' => $this->syncModel->getPendingRecordsCount(),
            'sync_errors' => $this->syncModel->getSyncErrorsCount(),
            'is_running' => $this->syncModel->isSyncRunning()
        ];
    }
    
    private function testOracleConnection() {
        try {
            $pdo = $this->db->getOracleConnection();
            $stmt = $pdo->query("SELECT 1 FROM DUAL");
            $result = $stmt->fetch();
            
            return [
                'connected' => true,
                'response_time' => microtime(true),
                'version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function testPostgreSQLConnection() {
        try {
            $pdo = $this->db->getPostgreSQLConnection();
            $stmt = $pdo->query("SELECT 1");
            $result = $stmt->fetch();
            
            return [
                'connected' => true,
                'response_time' => microtime(true),
                'version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function getDiskUsage() {
        $backup_path = __DIR__ . '/../backups/';
        
        return [
            'total' => disk_total_space($backup_path),
            'free' => disk_free_space($backup_path),
            'used' => disk_total_space($backup_path) - disk_free_space($backup_path),
            'usage_percent' => round((1 - disk_free_space($backup_path) / disk_total_space($backup_path)) * 100, 2)
        ];
    }
    
    private function getMemoryUsage() {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit')
        ];
    }
    
    private function getCPUUsage() {
        // Implementação simplificada para ambiente Linux
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0]; // Load average de 1 minuto
        }
        return null;
    }
    
    private function getOracleConnectionCount() {
        try {
            $pdo = $this->db->getOracleConnection();
            $stmt = $pdo->query("SELECT COUNT(*) as connections FROM v\$session WHERE type = 'USER'");
            return $stmt->fetch(PDO::FETCH_ASSOC)['connections'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getPostgreSQLConnectionCount() {
        try {
            $pdo = $this->db->getPostgreSQLConnection();
            $stmt = $pdo->query("SELECT COUNT(*) as connections FROM pg_stat_activity");
            return $stmt->fetch(PDO::FETCH_ASSOC)['connections'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getPerformanceMetrics() {
        return [
            'uptime' => $this->getSystemUptime(),
            'avg_response_time' => $this->monitoringModel->getAverageResponseTime(),
            'error_rate' => $this->monitoringModel->getErrorRate(),
            'backup_success_rate' => $this->backupModel->getSuccessRate(),
            'sync_success_rate' => $this->syncModel->getSuccessRate()
        ];
    }
    
    private function getSystemUptime() {
        if (file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            return floatval(explode(' ', $uptime)[0]);
        }
        return null;
    }
    
    private function formatBytes($size, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    private function sendNotification($type, $title, $message) {
        // Implementação de sistema de notificações (email, SMS, etc.)
        $this->monitoringModel->createNotification($type, $title, $message);
    }
    
    private function render($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }
    
    private function handleError($message, Exception $e) {
        $this->monitoringModel->logActivity('SYSTEM_ERROR', 'ERROR', $message . ': ' . $e->getMessage());
        
        $data = [
            'page_title' => 'Erro do Sistema',
            'error_message' => $message,
            'error_details' => $e->getMessage()
        ];
        
        http_response_code(500);
        $this->render('error/500', $data);
    }
}

?>