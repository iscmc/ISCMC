<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-10">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-pills text-primary mr-3"></i>
                    Consulta de Medicamentos
                </h1>
                <p class="text-gray-600 mt-2">
                    Gerencie e consulte todos os medicamentos do sistema
                </p>
            </div>
            <div class="bg-gradient-to-r from-primary to-primary-dark text-white px-4 py-3 rounded-lg shadow">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-chart-bar"></i>
                    <span class="font-semibold">
                        Total: <?php echo number_format($total, 0, ',', '.'); ?> medicamentos
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Actions Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <!-- Search Form -->
            <div class="flex-1 max-w-3xl">
                <form method="post" action="?action=search" class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                    <select name="type" class="h-12 px-4 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20 shadow-sm flex-shrink-0 w-full sm:w-48 bg-white">
                        <option value="medicamento" <?php echo ($searchType == 'medicamento') ? 'selected' : ''; ?>>Medicamento</option>
                        <option value="paciente" <?php echo ($searchType == 'paciente') ? 'selected' : ''; ?>>Cód. Paciente</option>
                        <option value="atendimento" <?php echo ($searchType == 'atendimento') ? 'selected' : ''; ?>>Núm. Atendimento</option>
                    </select>
                    
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>" 
                               placeholder="Digite sua busca..." 
                               class="h-12 pl-10 w-full rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20 shadow-sm bg-white">
                    </div>
                    
                    <button type="submit" class="h-12 bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg font-medium transition duration-200 shadow-sm flex items-center justify-center space-x-2">
                        <i class="fas fa-search"></i>
                        <span>Buscar</span>
                    </button>
                </form>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <a href="?action=export" class="h-12 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 shadow-sm flex items-center space-x-2">
                    <i class="fas fa-download"></i>
                    <span>Exportar</span>
                </a>
                <a href="?" class="h-12 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 shadow-sm flex items-center space-x-2">
                    <i class="fas fa-sync"></i>
                    <span>Atualizar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atendimento</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicamento</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dose</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Via</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($medicamentos)): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-inbox text-4xl mb-3"></i>
                                    <p class="text-lg">Nenhum medicamento encontrado</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($medicamentos as $med): ?>
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?php echo htmlspecialchars($med['NR_SEQUENCIA']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($med['NR_ATENDIMENTO'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($med['NR_ATENDIMENTO']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($med['NOME_PACIENTE'])): ?>
                                    <span class="text-sm text-gray-900"><?php echo htmlspecialchars($med['NOME_PACIENTE']); ?></span>
                                <?php else: ?>
                                    <span class="text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($med['DS_MEDICAMENTO'] ?? 'N/A'); ?></div>
                                <?php if (!empty($med['DS_OBSERVACAO_PREVIEW'])): ?>
                                    <div class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($med['DS_OBSERVACAO_PREVIEW']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if (!empty($med['QT_DOSE'])): ?>
                                        <?php echo htmlspecialchars($med['QT_DOSE']); ?>
                                        <?php if (!empty($med['CD_UNIDADE_MEDIDA'])): ?>
                                            <span class="text-gray-500"><?php echo htmlspecialchars($med['CD_UNIDADE_MEDIDA']); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($med['IE_VIA_APLICACAO'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <?php echo htmlspecialchars($med['IE_VIA_APLICACAO']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($med['DT_INICIO_FORMATADA'])): ?>
                                    <span class="text-sm text-gray-900"><?php echo $med['DT_INICIO_FORMATADA']; ?></span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="?action=view&id=<?php echo $med['NR_SEQUENCIA']; ?>" 
                                   class="inline-flex items-center px-3 py-1.5 border border-primary text-primary rounded-lg hover:bg-primary hover:text-white transition duration-200"
                                   title="Ver detalhes">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="bg-white px-6 py-4 border-t border-gray-200">
            <nav class="flex items-center justify-between">
                <div class="flex justify-between flex-1 sm:hidden">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Anterior
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Próxima
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center">
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>" 
                                   class="<?php echo $i == $page ? 'z-10 bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>