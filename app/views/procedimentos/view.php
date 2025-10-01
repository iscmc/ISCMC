<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Card do Procedimento -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-file-medical me-2"></i>
                            Procedimento #<?php echo htmlspecialchars($procedimento['NR_SEQUENCIA']); ?>
                        </h4>
                        <a href="?" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informações do Procedimento</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Descrição:</th>
                                    <td><?php echo htmlspecialchars($procedimento['DS_PROCEDIMENTO'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Data do Procedimento:</th>
                                    <td><?php echo $procedimento['DT_PROCEDIMENTO_FORMATADA'] ?? 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Usuário Responsável:</th>
                                    <td><?php echo htmlspecialchars($procedimento['NM_USUARIO_COMPLETO'] ?? $procedimento['NM_USUARIO'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Última Atualização:</th>
                                    <td><?php echo $procedimento['DT_ATUALIZACAO_FORMATADA'] ?? 'N/A'; ?></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Informações do Paciente</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Código Paciente:</th>
                                    <td><?php echo htmlspecialchars($procedimento['CD_PACIENTE'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Nome:</th>
                                    <td><?php echo htmlspecialchars($procedimento['NM_PACIENTE'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Nascimento:</th>
                                    <td><?php echo !empty($procedimento['DT_NASCIMENTO']) ? $this->formatDate($procedimento['DT_NASCIMENTO']) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Sexo:</th>
                                    <td><?php echo htmlspecialchars($procedimento['TP_SEXO'] ?? 'N/A'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Observações -->
                    <?php if (!empty($procedimento['DS_OBSERVACAO'])): ?>
                    <div class="mt-4">
                        <h5>Observações</h5>
                        <div class="alert alert-info">
                            <?php echo nl2br(htmlspecialchars($procedimento['DS_OBSERVACAO'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Informações Adicionais -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Informações do Atendimento</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Número Atendimento:</th>
                                    <td><?php echo htmlspecialchars($procedimento['NR_ATENDIMENTO'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Local:</th>
                                    <td><?php echo htmlspecialchars($procedimento['DS_LOCAL_ATENDIMENTO'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Especialidade:</th>
                                    <td><?php echo htmlspecialchars($procedimento['DS_ESPECIALIDADE'] ?? 'N/A'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Outros Procedimentos do Paciente -->
            <?php if (!empty($procedimentosPaciente)): ?>
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Outros Procedimentos do Paciente
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($procedimentosPaciente as $proc): ?>
                            <?php if ($proc['NR_SEQUENCIA'] != $procedimento['NR_SEQUENCIA']): ?>
                            <a href="?action=view&id=<?php echo $proc['NR_SEQUENCIA']; ?>" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($proc['DS_PROCEDIMENTO'] ?? 'Procedimento'); ?></h6>
                                    <small><?php echo $proc['DT_PROCEDIMENTO_FORMATADA'] ?? ''; ?></small>
                                </div>
                                <p class="mb-1 small text-muted">
                                    <?php echo htmlspecialchars($proc['DS_OBSERVACAO_PREVIEW'] ?? ''); ?>
                                </p>
                                <small>Por: <?php echo htmlspecialchars($proc['NM_USUARIO_COMPLETO'] ?? $proc['NM_USUARIO'] ?? 'N/A'); ?></small>
                            </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>