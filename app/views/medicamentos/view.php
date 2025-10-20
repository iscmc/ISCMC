<?php
/**
 * Portal de contingência ISCMC Off Grid
 *
 * Este arquivo faz parte do framework MVC Projeto Contingenciamento - FrontEnd.
 *
 * @category Framework
 * @package  Servidor de contingência ISCMC
 * @author   Sergio Figueroa <sergio.figueroa@iscmc.com.br>
 * @license  MIT, Apache
 * @link     http://10.132.16.43/ISCMC
 * @version  1.0.0
 * @since    2025-09-01
 * @maindev  Sergio Figueroa
 */

include __DIR__ . '/../layout/header.php';
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-pills text-primary mr-3"></i>
                    Detalhes do Medicamento
                </h1>
                <p class="text-gray-600 mt-2">
                    Informações completas do medicamento prescrito
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="/ISCMC/medicamentos/" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 shadow-sm flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </div>
    </div>

    <?php if ($medicamento): ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informações Principais -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Card Dados do Medicamento -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Dados do Medicamento
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Código</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($medicamento['NR_SEQUENCIA']); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Medicamento</label>
                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($medicamento['DS_MEDICAMENTO'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Código Material</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($medicamento['CD_MATERIAL'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Via de Aplicação</label>
                        <p class="text-gray-900">
                            <?php if (!empty($medicamento['IE_VIA_APLICACAO'])): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?php echo htmlspecialchars($medicamento['IE_VIA_APLICACAO']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card Dosagem -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-syringe text-primary mr-2"></i>
                    Dosagem
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Dose</label>
                        <p class="text-gray-900">
                            <?php if (!empty($medicamento['QT_DOSE'])): ?>
                                <?php echo htmlspecialchars($medicamento['QT_DOSE']); ?>
                                <?php if (!empty($medicamento['DS_UNIDADE_MEDIDA'])): ?>
                                    <span class="text-gray-500"><?php echo htmlspecialchars($medicamento['DS_UNIDADE_MEDIDA']); ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Dosagem</label>
                        <p class="text-gray-900"><?php echo !empty($medicamento['QT_DOSAGEM']) ? htmlspecialchars($medicamento['QT_DOSAGEM']) : '<span class="text-gray-400">-</span>'; ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Unidade Medida</label>
                        <p class="text-gray-900"><?php echo !empty($medicamento['CD_UNIDADE_MEDIDA']) ? htmlspecialchars($medicamento['CD_UNIDADE_MEDIDA']) : '<span class="text-gray-400">-</span>'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Card Período -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                    Período de Administração
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Data Início</label>
                        <p class="text-gray-900"><?php echo $medicamento['DT_INICIO_FORMATADA'] ?? '<span class="text-gray-400">-</span>'; ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Data Fim</label>
                        <p class="text-gray-900"><?php echo $medicamento['DT_FIM_FORMATADA'] ?? '<span class="text-gray-400">-</span>'; ?></p>
                    </div>
                </div>
            </div>

            <?php if (!empty($medicamento['DS_OBSERVACAO'])): ?>
            <!-- Card Observações -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                    Observações
                </h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($medicamento['DS_OBSERVACAO']); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Card Paciente -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user-injured text-primary mr-2"></i>
                    Paciente
                </h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nome</label>
                        <p class="text-gray-900"><?php echo !empty($medicamento['NOME_PACIENTE']) ? htmlspecialchars($medicamento['NOME_PACIENTE']) : '<span class="text-gray-400">-</span>'; ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Atendimento</label>
                        <p class="text-gray-900">
                            <?php if (!empty($medicamento['NR_ATENDIMENTO'])): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($medicamento['NR_ATENDIMENTO']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card Registro -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-history text-primary mr-2"></i>
                    Registro
                </h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Usuário</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($medicamento['NM_USUARIO_COMPLETO'] ?? $medicamento['NM_USUARIO'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Data Atualização</label>
                        <p class="text-gray-900"><?php echo $medicamento['DT_ATUALIZACAO_FORMATADA'] ?? '<span class="text-gray-400">-</span>'; ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Urgência</label>
                        <p class="text-gray-900">
                            <?php if (!empty($medicamento['IE_URGENCIA']) && $medicamento['IE_URGENCIA'] == 'SIM'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Urgente
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400">Normal</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <?php if (!empty($medicamento['DS_HORARIOS'])): ?>
            <!-- Card Horários -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-clock text-primary mr-2"></i>
                    Horários
                </h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($medicamento['DS_HORARIOS']); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
        <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-4"></i>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Medicamento não encontrado</h2>
        <p class="text-gray-600 mb-4">O medicamento solicitado não foi encontrado no sistema.</p>
        <a href="/ISCMC/medicamentos/" class="bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg font-medium transition duration-200 inline-flex items-center space-x-2">
            <i class="fas fa-arrow-left"></i>
            <span>Voltar para a lista</span>
        </a>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>