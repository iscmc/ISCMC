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
 * @version  1.0.0
 * @since    2025-04-01
 * @maindev  Sergio Figueroa
 */


// api/dashboard_data.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../controllers/AuthController.php';
require_once '../controllers/BackupController.php';
require_once '../controllers/MonitoringController.php';
require_once '../config/Database.php';

try {
    // Verificar autenticação
    $authController = new AuthController();
    if (!$authController->isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autorizado']);
        exit;
    }

    $backupController = new BackupController();
    $monitoringController = new MonitoringController();

    // Coletar dados atualizados
    $data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'backup_status' => $backupController->getCurrentBackupStatus(),
        'backup_progress' => $backupController->getBackupProgress(),
        'system_health' => $monitoringController->getSystemHealth(),
        'connection_status' => $monitoringController->getConnectionStatus(),
        'disk_usage' => $monitoringController->getDiskUsage(),
        'memory_usage' => $monitoringController->getMemoryUsage(),
        'cpu_usage' => $monitoringController->getCpuUsage(),
        'active_processes' => $monitoringController->getActiveProcesses(),
        'recent_logs' => $monitoringController->getRecentLogs(5),
        'chart_data' => $backupController->getChartData(),
        'alerts' => $monitoringController->getActiveAlerts(),
        'performance_metrics' => $monitoringController->getPerformanceMetrics()
    ];

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro interno do servidor',
        'message' => $e->getMessage()
    ]);
}
?>