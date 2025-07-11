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
 * Serviço de Backup Tasy EMR
 * Este script deve ser executado como serviço Windows ou daemon
 */

// Configuração de ambiente
ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');
error_reporting(E_ALL);

// Inclui classes necessárias
require_once __DIR__ . '/DatabaseConfig.php';
require_once __DIR__ . '/BackupManager.php';

/**
 * Classe do Serviço de Backup
 */
class TasyBackupService {
    private $backupManager;
    private $pidFile;
    private $logFile;
    private $isRunning = false;
    
    public function __construct() {
        $this->pidFile = __DIR__ . '/backup_service.pid';
        $this->logFile = __DIR__ . '/logs/backup_service.log';
        
        // Cria diretório de logs se não existir
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $this->backupManager = new BackupManager();
    }
    
    /**
     * Inicia o serviço
     */
    public function start() {
        // Verifica se já está rodando
        if ($this->isAlreadyRunning()) {
            $this->log("Serviço já está em execução.");
            return false;
        }
        
        // Registra PID
        file_put_contents($this->pidFile, getmypid());
        
        // Registra handlers de sinal
        $this->registerSignalHandlers();
        
        $this->log("Serviço de backup iniciado - PID: " . getmypid());
        $this->isRunning = true;
        
        // Loop principal do serviço
        $this->runService();
        
        return true;
    }
    
    /**
     * Para o serviço
     */
    public function stop() {
        $this->isRunning = false;
        
        if (file_exists($this->pidFile)) {
            unlink($this->pidFile);
        }
        
        $this->log("Serviço de backup interrompido.");
    }
    
    /**
     * Verifica se o serviço já está rodando
     */
    private function isAlreadyRunning() {
        if (!file_exists($this->pidFile)) {
            return false;
        }
        
        $pid = file_get_contents($this->pidFile);
        
        // Verifica se o processo ainda existe (Windows)
        if (PHP_OS_FAMILY === 'Windows') {
            exec("tasklist /FI \"PID eq $pid\" 2>NUL", $output);
            return count($output) > 1;
        } else {
            // Unix/Linux
            return posix_kill($pid, 0);
        }
    }
    
    /**
     * Registra handlers de sinal
     */
    private function registerSignalHandlers() {
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
        }
    }
    
    /**
     * Manipula sinais do sistema
     */
    public function handleSignal($signal) {
        switch ($signal) {
            case SIGTERM:
            case SIGINT:
                $this->log("Recebido sinal de interrupção. Parando serviço...");
                $this->stop();
                exit(0);
                break;
        }
    }
    
    /**
     * Loop principal do serviço
     */
    private function runService() {
        $config = new DatabaseConfig();
        
        while ($this->isRunning) {
            try {
                // Processa sinais pendentes
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }
                
                $startTime = time();
                $this->log("Iniciando ciclo de backup incremental...");
                
                // Executa backup incremental
                $this->backupManager->runIncrementalBackup();
                
                $endTime = time();
                $elapsed = $endTime - $startTime;
                
                $this->log("Ciclo de backup concluído em {$elapsed}s");
                
                // Aguarda próximo ciclo
                $interval = (int)$config->getConfig('BACKUP_INTERVAL');
                $sleepTime = max(0, $interval - $elapsed);
                
                if ($sleepTime > 0) {
                    $this->log("Aguardando {$sleepTime}s para próximo ciclo...");
                    sleep($sleepTime);
                }
                
            } catch (Exception $e) {
                $this->log("Erro no ciclo de backup: " . $e->getMessage());
                
                // Aguarda antes de tentar novamente
                $retryInterval = (int)$config->getConfig('RETRY_INTERVAL');
                sleep($retryInterval);
            }
        }
    }
    
    /**
     * Registra log
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        echo $logMessage;
    }
    
    /**
     * Obtém status do serviço
     */
    public function getStatus() {
        return [
            'is_running' => $this->isRunning,
            'pid' => $this->isAlreadyRunning() ? file_get_contents($this->pidFile) : null,
            'log_file' => $this->logFile,
            'last_log_entries' => $this->getLastLogEntries(10)
        ];
    }
    
    /**
     * Obtém últimas entradas do log
     */
    private function getLastLogEntries($lines = 10) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $content = file_get_contents($this->logFile);
        $allLines = explode(PHP_EOL, $content);
        
        return array_slice(array_filter($allLines), -$lines);
    }
}

// Execução do serviço
if (php_sapi_name() === 'cli') {
    $service = new TasyBackupService();
    
    $command = $argv[1] ?? 'start';
    
    switch ($command) {
        case 'start':
            $service->start();
            break;
            
        case 'stop':
            $service->stop();
            break;
            
        case 'status':
            $status = $service->getStatus();
            echo "Status: " . ($status['is_running'] ? 'Rodando' : 'Parado') . PHP_EOL;
            if ($status['pid']) {
                echo "PID: " . $status['pid'] . PHP_EOL;
            }
            break;
            
        case 'install':
            // Executa backup completo inicial
            echo "Executando backup completo inicial...\n";
            $backupManager = new BackupManager();
            $backupManager->runFullBackup();
            echo "Backup inicial concluído.\n";
            break;
            
        default:
            echo "Uso: php backup_service.php [start|stop|status|install]\n";
            break;
    }
} else {
    echo "Este script deve ser executado via linha de comando.\n";
}
?>