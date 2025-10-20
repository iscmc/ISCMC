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
                    <i class="fas fa-user-injured text-primary mr-3"></i>
                    Detalhes do Paciente
                </h1>
                <p class="text-gray-600 mt-2">
                    Informações completas do paciente, procedimentos, medicamentos e materiais utilizados
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="javascript:history.back()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 shadow-sm flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Informações do Paciente e Estatísticas -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Informações do Paciente (2/3) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-primary text-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold flex items-center">
                            <i class="fas fa-user mr-2"></i>
                            Informações do Paciente
                        </h2>
                        <span class="bg-primary-light bg-opacity-50 px-3 py-1 rounded-full text-sm font-medium">
                            ID: <?= htmlspecialchars($paciente['CD_PACIENTE']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Informações Pessoais -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-id-card text-gray-500 mr-2"></i>
                                Dados Pessoais
                            </h3>
                            <dl class="space-y-4">
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 flex-1">Nome Completo:</dt>
                                    <dd class="text-sm text-gray-900 font-medium text-right flex-1"><?= htmlspecialchars($paciente['NM_PACIENTE']) ?></dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 flex-1">Nome da Mãe:</dt>
                                    <dd class="text-sm text-gray-900 text-right flex-1"><?= htmlspecialchars($paciente['NM_MAE'] ?? 'Não informado') ?></dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 flex-1">Data de Nascimento:</dt>
                                    <dd class="text-sm text-gray-900 text-right flex-1">
                                        <?= $paciente['DT_NASCIMENTO'] ?> 
                                        <span class="text-gray-500 ml-2">(<?= $paciente['IDADE'] ?> anos)</span>
                                    </dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 flex-1">Sexo:</dt>
                                    <dd class="text-sm text-gray-900 text-right flex-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            <?= $paciente['IE_SEXO'] == 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' ?>">
                                            <?= $paciente['IE_SEXO'] ?>
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Documentos e Contato -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-address-book text-gray-500 mr-2"></i>
                                Documentos e Contato
                            </h3>
                            <dl class="space-y-4">
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 flex-1">CPF:</dt>
                                    <dd class="text-sm text-gray-900 text-right flex-1"><?= htmlspecialchars($paciente['NR_CPF'] ?? 'Não informado') ?></dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 flex-1">RG:</dt>
                                    <dd class="text-sm text-gray-900 text-right flex-1"><?= htmlspecialchars($paciente['NR_IDENTIDADE'] ?? 'Não informado') ?></dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 flex-1">Celular:</dt>
                                    <dd class="text-sm text-gray-900 text-right flex-1"><?= htmlspecialchars($paciente['NR_CELULAR'] ?? 'Não informado') ?></dd>
                                </div>
                            </dl>

                            <!-- Endereço -->
                            <?php if ($paciente['DS_ENDERECO']): ?>
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                                    <i class="fas fa-map-marker-alt text-gray-500 mr-2"></i>
                                    Endereço
                                </h4>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm text-gray-900 font-medium"><?= htmlspecialchars($paciente['DS_ENDERECO']) ?></p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <?= htmlspecialchars($paciente['DS_BAIRRO']) ?> - 
                                        <?= htmlspecialchars($paciente['DS_CIDADE']) ?>/<?= htmlspecialchars($paciente['DS_UF']) ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">CEP: <?= htmlspecialchars($paciente['NR_CEP']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas Rápidas (1/3) -->
        <div class="lg:col-span-1">
            <div class="space-y-4">
                <!-- Procedimentos -->
                <a href="#procedimentos" class="group">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200 group-hover:border-blue-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-900 mb-1">Procedimentos</p>
                                <p class="text-3xl font-bold text-blue-600"><?= count($procedimentos) ?></p>
                            </div>
                            <div class="bg-blue-100 group-hover:bg-blue-200 p-4 rounded-xl transition-colors duration-200">
                                <i class="fas fa-procedures text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center text-xs text-blue-700">
                            <i class="fas fa-history mr-1"></i>
                            <span>Últimos procedimentos realizados</span>
                        </div>
                    </div>
                </a>

                <!-- Medicamentos -->
                <a href="#medicamentos" class="group">
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200 group-hover:border-yellow-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-900 mb-1">Medicamentos</p>
                                <p class="text-3xl font-bold text-yellow-600"><?= count($medicamentos) ?></p>
                            </div>
                            <div class="bg-yellow-100 group-hover:bg-yellow-200 p-4 rounded-xl transition-colors duration-200">
                                <i class="fas fa-pills text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center text-xs text-yellow-700">
                            <i class="fas fa-prescription mr-1"></i>
                            <span>Prescrições médicas ativas</span>
                        </div>
                    </div>
                </a>

                <!-- Materiais -->
                <a href="#materiais" class="group block">
                    <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200 group-hover:border-green-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-800 mb-1">Materiais</p>
                                <p class="text-3xl font-bold text-green-600"><?= count($materiais) ?></p>
                            </div>
                            <div class="bg-green-100 group-hover:bg-green-600 p-4 rounded-xl transition-colors duration-200">
                                <i class="fas fa-boxes text-green-500 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center text-xs text-green-800">
                            <i class="fas fa-cube mr-1"></i>
                            <span>Materiais consumidos</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Procedimentos -->
    <div id="procedimentos" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-info text-white px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold flex items-center">
                    <i class="fas fa-procedures mr-2"></i>
                    Procedimentos Realizados
                </h3>
            </div>
            <p class="text-gray-100 text-sm">Lista de todos os procedimentos realizados pelo paciente</p>
        </div>
        <div class="p-6">
            <?php if (!empty($procedimentos)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Procedimento</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atendimento</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observação</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($procedimentos as $proc): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?= $proc['DT_PROCEDIMENTO'] ?></td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($proc['DS_PROCEDIMENTO'] ?? '') ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php if ($proc['NR_ATENDIMENTO']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= $proc['NR_ATENDIMENTO'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    <?php if (!empty($proc['DS_OBSERVACAO'])): ?>
                                        <span title="<?= htmlspecialchars($proc['DS_OBSERVACAO']) ?>">
                                            <?= strlen($proc['DS_OBSERVACAO']) > 50 ? substr($proc['DS_OBSERVACAO'], 0, 50) . '...' : htmlspecialchars($proc['DS_OBSERVACAO']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500"><?= htmlspecialchars($proc['NM_USUARIO_COMPLETO'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-procedures text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">Nenhum procedimento encontrado para este paciente.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Medicamentos -->
    <div id="medicamentos" class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
        <div class="bg-primary text-white px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold flex items-center">
                    <i class="fas fa-pills mr-2"></i>
                    Medicamentos Prescritos
                </h3>
            </div>
            <p class="text-gray-100 text-sm">Medicamentos prescritos durante os atendimentos</p>
        </div>
        <div class="p-6">
            <?php if (!empty($medicamentos)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicamento</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosagem</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Via</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horários</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atendimento</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($medicamentos as $med): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($med['DS_MEDICAMENTO'] ?? '') ?></span>
                                        <?php if ($med['IE_URGENCIA'] == 'S'): ?>
                                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800" title="Urgência">
                                                URG
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($med['DS_OBSERVACAO'])): ?>
                                        <p class="text-xs text-gray-500 mt-1" title="<?= htmlspecialchars($med['DS_OBSERVACAO']) ?>">
                                            <?= strlen($med['DS_OBSERVACAO']) > 60 ? substr($med['DS_OBSERVACAO'], 0, 60) . '...' : htmlspecialchars($med['DS_OBSERVACAO']) ?>
                                        </p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    <?= $med['DT_INICIO'] ?>
                                    <?php if ($med['DT_FIM']): ?>
                                        <br><span class="text-xs text-gray-500">até <?= $med['DT_FIM'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <?php if ($med['QT_DOSE']): ?>
                                        <span class="font-medium"><?= $med['QT_DOSE'] ?></span>
                                        <?php if ($med['CD_UNIDADE_MEDIDA']): ?>
                                            <span class="text-gray-500"><?= $med['CD_UNIDADE_MEDIDA'] ?></span>
                                        <?php endif; ?>
                                        <?php if ($med['QT_DOSAGEM']): ?>
                                            <br><span class="text-xs text-gray-500"><?= $med['QT_DOSAGEM'] ?> mg</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php if ($med['IE_VIA_APLICACAO'] && $med['IE_VIA_APLICACAO'] != 'N/A'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            <?= htmlspecialchars($med['IE_VIA_APLICACAO'] ?? '') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    <?php if ($med['DS_HORARIOS']): ?>
                                        <span class="font-mono text-xs"><?= htmlspecialchars($med['DS_HORARIOS']) ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php if ($med['NR_ATENDIMENTO']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?= $med['NR_ATENDIMENTO'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-pills text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">Nenhum medicamento encontrado para este paciente.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Materiais Utilizados -->
    <div id="materiais" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-success text-white px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold flex items-center">
                    <i class="fas fa-boxes mr-2"></i>
                    Materiais Utilizados
                </h3>
            </div>
            <p class="text-gray-100 text-sm">Materiais consumidos durante os atendimentos</p>
        </div>
        <div class="p-6">
            <?php if (!empty($materiais)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observação</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($materiais as $mat): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?= $mat['DT_UTILIZACAO'] ?></td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-gray-900"><?= $mat['CD_MATERIAL'] ?></span>
                                    <?php if (!empty($mat['DS_MATERIAL'])): ?>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($mat['DS_MATERIAL']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <?= $mat['QT_UTILIZADA'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    <?php if (!empty($mat['DS_OBSERVACAO'])): ?>
                                        <span title="<?= htmlspecialchars($mat['DS_OBSERVACAO']) ?>">
                                            <?= strlen($mat['DS_OBSERVACAO']) > 50 ? substr($mat['DS_OBSERVACAO'], 0, 50) . '...' : htmlspecialchars($mat['DS_OBSERVACAO']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500"><?= htmlspecialchars($mat['NM_USUARIO_COMPLETO'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-boxes text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">Nenhum material utilizado encontrado para este paciente.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>