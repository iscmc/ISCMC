<?php
// backup_details.php
session_start();
require_once 'controllers/AuthController.php';
require_once 'controllers/BackupController.php';
require_once 'controllers/LogController.php';

// Verificar autenticação
$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

$backupController = new BackupController();
$logController = new LogController();

// Obter ID do backup
$backupId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($backupId === 0) {
    header('Location: frontend_dashboard.php');
    exit;
}

// Obter detalhes do backup
$backup = $backupController->getBackupDetails($backupId);
if (!$backup) {
    header('Location: frontend_dashboard.php');
    exit;
}

// Obter logs relacionados
$logs = $logController->getBackupLogs($backupId);
$userInfo = $authController->getCurrentUser();

// Ações do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'restore':
            $result = $backupController->restoreBackup($backupId);
            $message = $result ? 'Restauração iniciada com sucesso!' : 'Erro ao iniciar restauração.';
            break;
        case 'delete':
            $result = $backupController->deleteBackup($backupId);
            if ($result) {
                header('Location: frontend_dashboard.php?deleted=1');
                exit;
            } else {
                $message = 'Erro ao deletar backup.';
            }
            break;
        case 'verify':
            $result = $backupController->verifyBackup($backupId);
            $message = $result ? 'Verificação iniciada com sucesso!' : 'Erro ao verificar backup.';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Backup - Sistema de Backup</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .breadcrumb {
            color: #666;
            font-size: 14px;
        }

        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .detail-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
        }

        .detail-info h2 {
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-badge.success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid #28a745;
        }

        .status-badge.warning {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid #ffc107;
        }

        .status-badge.error {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid #dc3545;
        }

        .status-badge.processing {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 1px solid #667eea;
            animation: pulse 2s infinite;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-item {
            padding: 20px;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .detail-item h4 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-item .value {
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }

        .detail-item .subvalue {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .actions-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .actions-card h3 {
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: 2px solid transparent;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
        }

        .btn-warning {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
            color: #333;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 193, 7, 0.3);
        }

        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #e83e8c);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 53, 69, 0.3);
        }

        .logs-section {
            grid-column: 1 / -1;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .logs-section h3 {
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .log-entry {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .log-entry.info {
            background: rgba(102, 126, 234, 0.05);
            border-left-color: #667eea;
        }

        .log-entry.success {
            background: rgba(40, 167, 69, 0.05);
            border-left-color: #28a745;
        }

        .log-entry.warning {
            background: rgba(255, 193, 7, 0.05);
            border-left-color: #ffc107;
        }

        .log-entry.error {
            background: rgba(220, 53, 69, 0.05);
            border-left-color: #dc3545;
        }

        .log-content {
            flex: 1;
        }

        .log-timestamp {
            color: #666;
            font-size: 12px;
            margin-left: 15px;
            white-space: nowrap;
        }

        .log-message {
            color: #333;
            margin-bottom: 5px;
        }

        .log-details {
            color: #666;
            font-size: 14px;
        }

        .progress-section {
            margin: 30px 0;
            padding: 20px;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 10px;
        }

        .progress-bar {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 25px;
            overflow: hidden;
            height: 20px;
            margin: 10px 0;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 25px;
            transition: width 1s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #155724;
            border-left-color: #28a745;
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.1);
            color: #721c24;
            border-left-color: #dc3545;
        }

        .file-tree {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            max-height: 300px;
            overflow-y: auto;
        }

        .file-tree ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .file-tree li {
            margin: 5px 0;
            padding: 5px;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        .file-tree li:hover {
            background: rgba(102, 126, 234, 0.1);
        }

        .file-tree .folder {
            font-weight: bold;
            color: #667eea;
        }

        .file-tree .file {
            color: #666;
            margin-left: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><i class="fas fa-database"></i> Detalhes do Backup</h1>
                <div class="breadcrumb">
                    <a href="frontend_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                    <span> / Detalhes do Backup #<?php echo $backup['id']; ?></span>
                </div>
            </div>
            <a href="frontend_dashboard.php" class="btn btn-primary" style="width: auto; padding: 10px 20px;">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert <?php echo strpos($message, 'sucesso') !== false ? 'alert-success' : 'alert-error'; ?>">
                <i class="fas <?php echo strpos($message, 'sucesso') !== false ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Detail Card -->
            <div class="detail-card">
                <div class="detail-header">
                    <div class="detail-info">
                        <h2>
                            <i class="fas fa-<?php echo $backup['type'] === 'full' ? 'database' : ($backup['type'] === 'incremental' ? 'plus-circle' : 'compress'); ?>"></i>
                            Backup <?php echo ucfirst($backup['type']); ?>
                        </h2>
                        <p>Criado em <?php echo date('d/m/Y H:i:s', strtotime($backup['created_at'])); ?></p>
                    </div>
                    <span class="status-badge <?php echo $backup['status']; ?>">
                        <?php echo ucfirst($backup['status']); ?>
                    </span>
                </div>

                <?php if ($backup['status'] === 'processing'): ?>
                <div class="progress-section">
                    <h4>Progresso do Backup</h4>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $backup['progress']; ?>%">
                            <?php echo $backup['progress']; ?>%
                        </div>
                    </div>
                    <p>Estimativa restante: <?php echo $backup['estimated_time_remaining']; ?></p>
                </div>
                <?php endif; ?>

                <div class="detail-grid">
                    <div class="detail-item">
                        <h4>Tamanho do Arquivo</h4>
                        <div class="value"><?php echo $backup['size_formatted']; ?></div>
                        <div class="subvalue"><?php echo number_format($backup['size_bytes']); ?> bytes</div>
                    </div>

                    <div class="detail-item">
                        <h4>Localização</h4>
                        <div class="value"><?php echo htmlspecialchars($backup['file_path']); ?></div>
                        <div class="subvalue">Servidor: <?php echo htmlspecialchars($backup['server']); ?></div>
                    </div>

                    <div class="detail-item">
                        <h4>Tipo de Compressão</h4>
                        <div class="value"><?php echo strtoupper($backup['compression']); ?></div>
                        <div class="subvalue">Taxa: <?php echo $backup['compression_ratio']; ?>%</div>
                    </div>

                    <div class="detail-item">
                        <h4>Duração</h4>
                        <div class="value"><?php echo $backup['duration_formatted']; ?></div>
                        <div class="subvalue">
                            <?php if ($backup['end_time']): ?>
                                Finalizado: <?php echo date('H:i:s', strtotime($backup['end_time'])); ?>
                            <?php else: ?>
                                Em andamento...
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <h4>Checksum</h4>
                        <div class="value"><?php echo substr($backup['checksum'], 0, 16); ?>...</div>
                        <div class="subvalue"><?php echo $backup['checksum_algorithm']; ?></div>
                    </div>

                    <div class="detail-item">
                        <h4>Executado por</h4>
                        <div class="value"><?php echo htmlspecialchars($backup['created_by']); ?></div>
                        <div class="subvalue">
                            <?php echo $backup['execution_type'] === 'manual' ? 'Manual' : 'Automático'; ?>
                        </div>
                    </div>
                </div>

                <?php if ($backup['included_tables']): ?>
                <div class="file-tree">
                    <h4><i class="fas fa-table"></i> Tabelas Incluídas</h4>
                    <ul>
                        <?php foreach ($backup['included_tables'] as $table): ?>
                            <li class="file">
                                <i class="fas fa-table"></i> <?php echo htmlspecialchars($table['name']); ?>
                                <span style="color: #999; margin-left: 10px;">(<?php echo number_format($table['rows']); ?> linhas)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <!-- Actions Card -->
            <div class="actions-card">
                <h3><i class="fas fa-tools"></i> Ações</h3>

                <form method="post" style="margin-bottom: 20px;">
                    <input type="hidden" name="action" value="verify">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Verificar Integridade
                    </button>
                </form>

                <?php if ($backup['status'] === 'success'): ?>
                    <a href="download_backup.php?id=<?php echo $backup['id']; ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> Download
                    </a>

                    <button onclick="showRestoreModal()" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Restaurar
                    </button>
                <?php endif; ?>

                <button onclick="showDeleteModal()" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Deletar
                </button>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.1);">
                    <h4 style="margin-bottom: 15px; color: #333;">Informações Técnicas</h4>
                    <p style="font-size: 14px; color: #666; margin-bottom: 10px;">
                        <strong>ID:</strong> <?php echo $backup['id']; ?>
                    </p>
                    <p style="font-size: 14px; color: #666; margin-bottom: 10px;">
                        <strong>Hash:</strong> <?php echo substr($backup['hash'], 0, 20); ?>...
                    </p>
                    <p style="font-size: 14px; color: #666; margin-bottom: 10px;">
                        <strong>Versão:</strong> <?php echo $backup['version']; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Logs Section -->
        <div class="logs-section">
            <h3><i class="fas fa-list-alt"></i> Logs do Backup</h3>
            
            <?php if (empty($logs)): ?>
                <p style="color: #666; text-align: center; padding: 40px;">
                    <i class="fas fa-info-circle"></i> Nenhum log disponível para este backup.
                </p>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <div class="log-entry <?php echo $log['level']; ?>">
                        <div class="log-content">
                            <div class="log-message"><?php echo htmlspecialchars($log['message']); ?></div>
                            <?php if ($log['details']): ?>
                                <div class="log-details"><?php echo htmlspecialchars($log['details']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="log-timestamp">
                            <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Restauração -->
    <div id="restoreModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-undo"></i> Confirmar Restauração</h3>
                <button type="button" class="close-modal" onclick="closeModal('restoreModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 20px; color: #666;">
                    Tem certeza que deseja restaurar este backup? Esta ação irá substituir os dados atuais.
                </p>
                <div style="background: rgba(255, 193, 7, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong style="color: #856404;">⚠️ Atenção:</strong>
                    <p style="color: #856404; margin: 10px 0 0 0;">Esta operação não pode ser desfeita. Recomendamos fazer um backup atual antes de prosseguir.</p>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="restore">
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" onclick="closeModal('restoreModal')" class="btn" style="background: #6c757d; color: white;">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-undo"></i> Confirmar Restauração
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Exclusão -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-trash"></i> Confirmar Exclusão</h3>
                <button type="button" class="close-modal" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 20px; color: #666;">
                    Tem certeza que deseja deletar este backup permanentemente?
                </p>
                <div style="background: rgba(220, 53, 69, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong style="color: #721c24;">🗑️ Atenção:</strong>
                    <p style="color: #721c24; margin: 10px 0 0 0;">Esta ação não pode ser desfeita e o arquivo será removido permanentemente do servidor.</p>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="delete">
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" onclick="closeModal('deleteModal')" class="btn" style="background: #6c757d; color: white;">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Confirmar Exclusão
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Funções dos modais
        function showRestoreModal() {
            document.getElementById('restoreModal').style.display = 'flex';
        }

        function showDeleteModal() {
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Fechar modal clicando fora
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Atualização automática para backups em progresso
        <?php if ($backup['status'] === 'processing'): ?>
        setInterval(() => {
            fetch(`api/backup_progress.php?id=<?php echo $backup['id']; ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.progress !== undefined) {
                        const progressFill = document.querySelector('.progress-fill');
                        if (progressFill) {
                            progressFill.style.width = data.progress + '%';
                            progressFill.textContent = data.progress + '%';
                        }
                    }
                    
                    if (data.status === 'success' || data.status === 'error') {
                        location.reload(); // Recarrega a página quando o backup termina
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar progresso:', error);
                });
        }, 5000); // Atualiza a cada 5 segundos
        <?php endif; ?>

        // Animações de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.detail-card, .actions-card, .logs-section');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Copiar informações técnicas
        document.querySelectorAll('.detail-item .value').forEach(element => {
            element.style.cursor = 'pointer';
            element.addEventListener('click', function() {
                navigator.clipboard.writeText(this.textContent).then(() => {
                    const originalText = this.textContent;
                    this.textContent = 'Copiado!';
                    this.style.color = '#28a745';
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.color = '';
                    }, 1500);
                });
            });
        });
    </script>
</body>
</html>