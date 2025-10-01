<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-procedures me-2"></i>
                            Consulta de Procedimentos
                        </h4>
                        <span class="badge bg-light text-dark">
                            Total: <?php echo number_format($total, 0, ',', '.'); ?> procedimentos
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Barra de Pesquisa -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <form method="post" action="?action=search" class="row g-2">
                                <div class="col-md-4">
                                    <select name="type" class="form-select">
                                        <option value="procedimento">Procedimento</option>
                                        <option value="paciente">Cód. Paciente</option>
                                        <option value="atendimento">Núm. Atendimento</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="search" class="form-control" placeholder="Digite sua busca...">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i> Buscar
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <a href="?action=export" class="btn btn-outline-success">
                                    <i class="fas fa-download me-1"></i> Exportar
                                </a>
                                <a href="?" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync me-1"></i> Atualizar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Procedimentos -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="80">ID</th>
                                    <th width="120">Atendimento</th>
                                    <th>Procedimento</th>
                                    <th width="150">Data</th>
                                    <th width="120">Usuário</th>
                                    <th width="150">Atualização</th>
                                    <th width="80">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($procedimentos)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <br>
                                                Nenhum procedimento encontrado
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($procedimentos as $proc): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($proc['NR_SEQUENCIA']); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($proc['NR_ATENDIMENTO'])): ?>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($proc['NR_ATENDIMENTO']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($proc['DS_PROCEDIMENTO'] ?? 'N/A'); ?></div>
                                            <?php if (!empty($proc['DS_OBSERVACAO_PREVIEW'])): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($proc['DS_OBSERVACAO_PREVIEW']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($proc['DT_PROCEDIMENTO_FORMATADA'])): ?>
                                                <span class="text-nowrap"><?php echo $proc['DT_PROCEDIMENTO_FORMATADA']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($proc['NM_USUARIO_COMPLETO'] ?? $proc['NM_USUARIO'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($proc['DT_ATUALIZACAO_FORMATADA'])): ?>
                                                <small class="text-muted"><?php echo $proc['DT_ATUALIZACAO_FORMATADA']; ?></small>
                                                <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="?action=view&id=<?php echo $proc['NR_SEQUENCIA']; ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Ver detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Paginação">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Anterior
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        Próxima <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>