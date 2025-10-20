<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-file-medical text-success mr-3"></i>
                    Procedimento #<?php echo htmlspecialchars($procedimento['NR_SEQUENCIA']); ?>
                </h1>
                <p class="text-gray-600 mt-2">
                    Detalhes completos do procedimento
                </p>
            </div>
            <a href="?" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 shadow-sm flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar para Lista</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Coluna Principal -->
        <div class="lg:col-span-2">
            <!-- Card do Procedimento -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-success text-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold">
                            <i class="fas fa-file-medical mr-2"></i>
                            Informações do Procedimento
                        </h2>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- Informações Básicas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Informações do Procedimento -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                Dados do Procedimento
                            </h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Descrição:</dt>
                                    <dd class="text-sm text-gray-900 mt-1"><?php echo htmlspecialchars($procedimento['DS_PROCEDIMENTO'] ?? 'N/A'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Data do Procedimento:</dt>
                                    <dd class="text-sm text-gray-900 mt-1"><?php echo $procedimento['DT_PROCEDIMENTO_FORMATADA'] ?? 'N/A'; ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Usuário Responsável:</dt>
                                    <dd class="text-sm text-gray-900 mt-1"><?php echo htmlspecialchars($procedimento['NM_USUARIO_COMPLETO'] ?? $procedimento['NM_USUARIO'] ?? 'N/A'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Última Atualização:</dt>
                                    <dd class="text-sm text-gray-900 mt-1"><?php echo $procedimento['DT_ATUALIZACAO_FORMATADA'] ?? 'N/A'; ?></dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Informações do Paciente -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                Dados do Paciente
                            </h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Código Paciente:</dt>
                                    <dd class="text-sm text-gray-900 mt-1"><?php echo htmlspecialchars($procedimento['CD_PACIENTE'] ?? 'N/A'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nome Paciente:</dt>
                                    <dd class="text-sm text-gray-900 mt-1"><?php echo htmlspecialchars($procedimento['NM_PACIENTE'] ?? 'N/A'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Data Nascimento:</dt>
                                    <dd class="text-sm text-gray-900 mt-1"><?php echo !empty($procedimento['DT_NASCIMENTO']) ? $procedimento['DT_NASCIMENTO_FORMATADA'] : 'N/A'; ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nome da mãe:</dt>
                                    <dd class="text-sm text-gray-900 mt-1"><?php echo htmlspecialchars($procedimento['NM_MAE_PACIENTE'] ?? 'N/A'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Sexo:</dt>
                                    <dd class="text-sm text-gray-900 mt-1"><?php echo htmlspecialchars($procedimento['IE_SEXO'] ?? 'N/A'); ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Observações -->
                    <?php if (!empty($procedimento['DS_OBSERVACAO'])): ?>
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Observações</h3>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo htmlspecialchars($procedimento['DS_OBSERVACAO']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Informações do Atendimento -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Atendimento</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Número Atendimento:</dt>
                                <dd class="text-sm text-gray-900 mt-1"><?php echo htmlspecialchars($procedimento['NR_ATENDIMENTO'] ?? 'N/A'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Local:</dt>
                                <dd class="text-sm text-gray-900 mt-1"><?php echo htmlspecialchars($procedimento['DS_LOCAL_ATENDIMENTO'] ?? 'N/A'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Especialidade:</dt>
                                <dd class="text-sm text-gray-900 mt-1"><?php echo htmlspecialchars($procedimento['DS_ESPECIALIDADE'] ?? 'N/A'); ?></dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Outros Procedimentos do Paciente -->
            <?php if (!empty($procedimentosPaciente)): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-info text-white px-4 py-3">
                    <h3 class="text-lg font-bold flex items-center">
                        <i class="fas fa-list mr-2"></i>
                        Outros Procedimentos
                    </h3>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($procedimentosPaciente as $proc): ?>
                        <?php if ($proc['NR_SEQUENCIA'] != $procedimento['NR_SEQUENCIA']): ?>
                        <a href="?action=view&id=<?php echo $proc['NR_SEQUENCIA']; ?>" 
                           class="block p-4 hover:bg-gray-50 transition duration-150">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium text-gray-900 text-sm line-clamp-2">
                                    <?php echo htmlspecialchars($proc['DS_PROCEDIMENTO'] ?? 'Procedimento'); ?>
                                </h4>
                                <span class="text-xs text-gray-500 whitespace-nowrap ml-2">
                                    <?php echo $proc['DT_PROCEDIMENTO_FORMATADA'] ?? ''; ?>
                                </span>
                            </div>
                            <?php if (!empty($proc['DS_OBSERVACAO_PREVIEW'])): ?>
                                <p class="text-xs text-gray-600 mb-2 line-clamp-2">
                                    <?php echo htmlspecialchars($proc['DS_OBSERVACAO_PREVIEW']); ?>
                                </p>
                            <?php endif; ?>
                            <div class="text-xs text-gray-500">
                                Por: <?php echo htmlspecialchars($proc['NM_USUARIO_COMPLETO'] ?? $proc['NM_USUARIO'] ?? 'N/A'); ?>
                            </div>
                        </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>