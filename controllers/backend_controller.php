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
 * Backend de Controle do Sistema de Backup
 * API REST para gerenciamento das configurações e monitoramento
 */

// Headers para API REST
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Trata requisições OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Inclui classes necessárias
require_once __DIR__ . '/DatabaseConfig.php';
require_once __DIR__ . '/BackupManager.php';

/**
 * Classe Controller da API
 */
class BackupController {
    private $config;
    private $backupManager;
    
    public function __construct() {
        $this->config = new DatabaseConfig();
        $this->backupManager = new BackupManager();
    }
    
    /**
     * Processa requisições da API
     */
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));
        
        try {
            switch ($method) {
                case 'GET':
                    return $this->handleGet($pathParts);
                    
                case 'POST':
                    return $this->handlePost($pathParts);
                    
                case 'PUT':
                    return $this->handlePut($pathParts);
                    
                case 'DELETE':
                    return $this->handleDelete($pathParts);
                    
                default:
                    return $this->jsonResponse(['error' => 'Método não suportado'], 405);
            }
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Manipula requisições GET
     */
    private function handleGet($pathParts) {
        $endpoint = $pathParts[0] ?? '';
        
        switch ($endpoint) {
            case 'config':
                return $this->getConfigurations();
                
            case 'status':
                return $this->getSystemStatus();
                
            case 'logs':
                $limit = $_GET['limit'] ?? 50;
                return $this->getLogs($limit);
                
            case 'tables':
                return $this->getTablesStatus();
                
            case 'dashboard':
                return $this->getDashboardData();
                
            default:
                return $this->jsonResponse(['error' => 'Endpoint não encontrado'], 404);
        }
    }
    
    /**
     * Manipula requisições POST
     */
    private function handlePost($pathParts) {
        $endpoint = $pathParts[0] ?? '';
        $data = json_decode(file_get_contents('php://input'), true);
        
        switch ($endpoint) {
            case 'backup':
                $type = $data['type'] ?? 'incremental';
                return $this->executeBackup($type);
                
            case 'service':
                $action = $data['action'] ?? '';
                return $this->controlService($action);
                
            case 'test-connection':
                return $this->testConnections();
                
            default:
                return $this->jsonResponse(['error' => 'Endpoint não encontrado'], 404);
        }
    }
    
    /**
     * Manipula requisições PUT
     */
    private function handlePut($pathParts) {
        $endpoint = $pathParts[0] ?? '';
        $data = json_decode(file_get_contents('php://input'), true);
        
        switch ($endpoint) {
            case 'config':
                return $this->updateConfiguration($data);
                
            default:
                return $this->jsonResponse(['error' => 'Endpoint não encontrado'], 404);
        }
    }
    
    /**
     * Manipula requisições DELETE
     */
    private function handleDelete($pathParts) {
        $endpoint = $pathParts[0] ?? '';
        
        switch ($endpoint) {
            case 'logs':
                return $this->clearLogs();
                
            default:
                return $this->jsonResponse(['error' => 'Endpoint não encontrado'], 404);
        }
    }
    
    /**
     * Obtém configurações do sistema
     */
    private function getConfigurations() {
        $conn = $this->config->getLocalConnection();
        
        $stmt = $conn->query("SELECT parametro, valor, descricao FROM config_backup ORDER BY parametro");
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->jsonResponse(['configurations' => $configs]);
    }
    
    /**
     * Obtém status do sistema
     */
    private function getSystemStatus() {
        $cloudConnection = $this->config->testCloudConnection();
        
        // Verifica se o serviço está rodando
        $pidFile = __DIR__ . '/backup_service.pid';
        $serviceRunning = false;
        
        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            if (PHP_OS_FAMILY === 'Windows') {
                exec("tasklist /FI \"PID eq $pid\" 2>NUL", $output);
                $serviceRunning = count($output) > 1;
            } else {
                $serviceRunning = posix_kill($pid, 0);
            }
        }
        
        // Último backup
        $conn = $this->config->getLocalConnection();
        $stmt = $conn->query("SELECT MAX(data_fim) as ultimo_backup FROM log_backup WHERE status = 'SUCCESS'");
        $lastBackup = $stmt->fetch(PDO::FETCH_ASSOC)['ULTIMO_BACKUP'];
        
        return $this->jsonResponse([
            'cloud_connection' => $cloudConnection,
            'service_running' => $serviceRunning,
            'last_backup' => $lastBackup,
            'backup_interval' => $this->config->getConfig('BACKUP_INTERVAL'),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Obtém logs do sistema
     */
    private function getLogs($limit) {
        $conn = $this->config->getLocalConnection();
        
        $stmt = $conn->prepare("
            SELECT * FROM (
                SELECT tabela_origem, tipo_operacao, registros_copiados, 
                       data_inicio, data_fim, status, erro_mensagem
                FROM log_backup 
                ORDER BY data_inicio DESC
            ) WHERE ROWNUM <= :limit
        ");
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->jsonResponse(['logs' => $logs]);
    }
    
    /**
     * Obtém status das tabelas
     */
    private function getTablesStatus() {
        $conn = $this->config->getLocalConnection();
        
        $stmt = $conn->query("
            SELECT cs.tabela_nome, cs.ultimo_timestamp, cs.ultimo_id,
                   COUNT(lb.id) as total_backups,
                   MAX(lb.data_fim) as ultimo_backup_sucesso
            FROM controle_sync cs
            LEFT JOIN log_backup lb ON cs.tabela_nome = lb.tabela_origem 
                AND lb.status = 'SUCCESS'
            GROUP BY cs.tabela_nome, cs.ultimo_timestamp, cs.ultimo_id
            ORDER BY cs.tabela_nome
        ");
        
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->jsonResponse(['tables' => $tables]);
    }
    
    /**
     * Obtém dados do dashboard
     */
    private function getDashboardData() {
        $conn = $this->config->getLocalConnection();
        
        // Estatísticas gerais
        $stmt = $conn->query("
            SELECT 
                COUNT(*) as total_backups,
                SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as sucessos,
                SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as erros,
                SUM(registros_copiados) as total_registros
            FROM log_backup 
            WHERE data_inicio >= SYSDATE - 7
        ");
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Backups por dia (últimos 7 dias)
        $stmt = $conn->query("
            SELECT TO_CHAR(data_inicio, 'DD/MM') as dia,
                   COUNT(*) as total,
                   SUM(registros_copiados) as registros
            FROM log_backup 
            WHERE data_inicio >= SYSDATE - 7
            GROUP BY TO_CHAR(data_inicio, 'DD/MM')
            ORDER BY MIN(data_inicio)
        ");
        
        $dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->jsonResponse([
            'general_stats' => $stats,
            'daily_stats' => $dailyStats,
            'system_status' => $this->getSystemStatus()['data']
        ]);
    }
    
    /**
     * Executa backup manual
     */
    private function executeBackup($type) {
        try {
            if ($type === 'full') {
                $this->backupManager->runFullBackup();
                $message = 'Backup completo executado com sucesso';
            } else {
                $this->backupManager->runIncrementalBackup();
                $message = 'Backup incremental executado com sucesso';
            }
            
            return $this->jsonResponse(['message' => $message]);
            
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Controla o serviço de backup
     */
    private function controlService($action) {
        $pidFile = __DIR__ . '/backup_service.pid';
        
        switch ($action) {
            case 'start':
                if (file_exists($pidFile)) {
                    return $this->jsonResponse(['error' => 'Serviço já está rodando'], 400);
                }
                
                if (PHP_OS_FAMILY === 'Windows') {
                    $cmd = 'start /B php ' . __DIR__ . '/backup_service.php start';
                } else {
                    $cmd = 'php ' . __DIR__ . '/backup_service.php start > /dev/null 2>&1 &';
                }
                
                exec($cmd);
                sleep(2); // Aguarda inicialização
                
                return $this->jsonResponse(['message' => 'Serviço iniciado']);
                
            case 'stop':
                if (!file_exists($pidFile)) {
                    return $this->jsonResponse(['error' => 'Serviço não está rodando'], 400);
                }
                
                $pid = file_get_contents($pidFile);
                
                if (PHP_OS_FAMILY === 'Windows') {
                    exec("taskkill /F /PID $pid");
                } else {
                    posix_kill($pid, SIGTERM);
                }
                
                if (file_exists($pidFile)) {
                    unlink($pidFile);
                }
                
                return $this->jsonResponse(['message' => 'Serviço parado']);
                
            case 'restart':
                $this->controlService('stop');
                sleep(2);
                return $this->controlService('start');
                
            default:
                return $this->jsonResponse(['error' => 'Ação não suportada'], 400);
        }
    }
    
    /**
     * Testa conexões
     */
    private function testConnections() {
        $cloudConnection = $this->config->testCloudConnection();
        
        $localConnection = true;
        try {
            $this->config->getLocalConnection();
        } catch (Exception $e) {
            $localConnection = false;
        }
        
        return $this->jsonResponse([
            'cloud_connection' => $cloudConnection,
            'local_connection' => $localConnection,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Atualiza configuração
     */
    private function updateConfiguration($data) {
        foreach ($data as $key => $value) {
            $this->config->updateConfig($key, $value);
        }
        
        return $this->jsonResponse(['message' => 'Configurações atualizadas']);
    }
    
    /**
     * Limpa logs
     */
    private function clearLogs() {
        $conn = $this->config->getLocalConnection();
        
        $stmt = $conn->prepare("DELETE FROM log_backup WHERE data_inicio < SYSDATE - 30");
        $stmt->execute();
        
        $deletedRows = $stmt->rowCount();
        
        return $this->jsonResponse(['message' => "$deletedRows logs removidos"]);
    }
    
    /**
     * Resposta JSON padronizada
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        
        $response = [
            'success' => $statusCode < 400,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Inicializa o controller
$controller = new BackupController();
$controller->handleRequest();
?>