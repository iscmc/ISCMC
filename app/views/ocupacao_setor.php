<?php
// Valores padrão: 'N/D' para textos, '0' para números, '-' para datas
?>
<div class="container mx-auto px-4 py-8">
    <!-- Cabeçalho -->
    <header class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Setor: <?= htmlspecialchars($setorInfo['DS_SETOR_ATENDIMENTO'] ?? '') ?></h1>
                <p class="text-gray-600">Código: <?= $setorInfo['CD_SETOR_ATENDIMENTO'] ?? '' ?> - <?= count($pacientes) ?> pacientes encontrados</p>
            </div>
            <a href="index.php?controller=dashboard" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition duration-200">
                ← Voltar ao Dashboard
            </a>
        </div>
    </header>

    <!-- Tabela de Pacientes -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-4 py-2 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-800">Pacientes e Leitos</h2>
            <p class="text-sm text-gray-600">Lista detalhada de pacientes no setor</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leito</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prontuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Idade/Sexo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Médico</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entrada</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dias Internado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($pacientes as $paciente): ?>
                    <tr class="hover:bg-gray-100 transition duration-150">
                        <td class="px-4 py-2 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= htmlspecialchars($paciente['LEITO'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <a href="index.php?controller=pacientes&action=view&id=<?= $paciente['CD_PESSOA_FISICA'] ?>" 
                                    class="text-blue-600 hover:text-blue-900 hover:underline"
                                    title="Ver detalhes do paciente">
                                    <?= htmlspecialchars($paciente['PACIENTE'] ?? 'N/D') ?>
                                </a>
                            </div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                            <?= $paciente['NR_PRONTUARIO'] ?? '0' ?>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                            <?php
                            if (!empty($paciente['DT_NASCIMENTO'])) {
                                $idade = date_diff(date_create($paciente['DT_NASCIMENTO']), date_create('today'))->y;
                                echo $idade . ' anos / ' . ($paciente['IE_SEXO'] ?? 'N/D');
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                            <?= htmlspecialchars($paciente['NM_GUERRA'] ?? 'N/D') ?>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                            <?php
                            if (!empty($paciente['DT_ENTRADA_UNIDADE'])) {
                                echo date('d/m/Y', strtotime($paciente['DT_ENTRADA_UNIDADE']));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <?= $paciente['QT_DIA_PERMANENCIA'] ?? '0' ?> dias
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                            <?= htmlspecialchars($paciente['DS_STATUS_UNIDADE'] ?? 'N/D') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($pacientes)): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-2 text-center text-sm text-gray-500">
                            Nenhum paciente encontrado neste setor.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>