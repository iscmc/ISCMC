<!-- Conteúdo específico do dashboard -->
<div class="container mx-auto px-4 py-8">
    <!-- Cabeçalho -->
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard - Ocupação Hospitalar</h1>
        <p class="text-gray-600">Visão geral em tempo real dos leitos do hospital - <?= date('d/m/Y H:i') ?></p>
    </header>

    <!-- SEÇÃO DO GRÁFICO E ESTATÍSTICAS -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Distribuição</h2>
        
        <!-- Container principal com grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- COLUNA 1: GRÁFICO -->
            <div class="flex flex-col items-center">
                <div class="w-full max-w-md relative">
                    <canvas id="ocupacaoChart" width="400" height="400"></canvas>
                </div>
            </div>
            
            <!-- COLUNA 2: ESTATÍSTICAS -->
            <div class="flex flex-col justify-center">
                <!-- Grid de estatísticas principais -->
                <div class="grid grid-cols-2 gap-2 mb-6">
                    <div class="bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
                        <div class="flex items-center space-x-3">
                            <div class="text-2xl font-bold text-blue-800"><?= $totais['ocupados'] ?></div>
                            <div class="text-xs text-blue-500 tracking-wide font-semibold">
                                Leitos Ocupados
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 px-4 py-2 rounded-lg border border-green-200">
                        <div class="flex items-center space-x-3">
                            <div class="text-2xl font-bold text-green-800"><?= $totais['livres'] ?></div>
                            <div class="text-xs text-green-500 tracking-wide">Leitos Livres</div>
                        </div>
                    </div>
                    <div class="bg-yellow-50 px-4 py-2 rounded-lg border border-yellow-200">
                        <div class="flex items-center space-x-3">
                            <div class="text-2xl font-bold text-yellow-500"><?= $totais['higienizacao'] ?></div>
                            <div class="text-xs text-yellow-500 tracking-wide">Unidades em Higienização</div>
                        </div>
                    </div>
                    <div class="bg-red-50 px-4 py-2 rounded-lg border border-red-200">
                        <div class="flex items-center space-x-3">
                            <div class="text-2xl font-bold text-red-800"><?= $totais['isolados'] ?></div>
                            <div class="text-xs text-red-600 tracking-wide">Leitos Isolados</div>
                        </div>
                    </div>
                    <!-- Estatísticas secundárias -->
                    <div class="bg-orange-50 px-4 py-2 rounded-lg border border-orange-200">
                        <div class="flex items-center space-x-3">
                            <div class="text-2xl font-bold text-orange-500"><?= $totais['aguardando_higienizacao'] ?></div>
                            <div class="text-xs text-orange-500 tracking-wide">Unidades aguardando Higienização</div>
                        </div>
                    </div>
                    <div class="bg-purple-50 px-4 py-2 rounded-lg border border-purple-200">
                        <div class="flex items-center space-x-3">
                            <div class="text-2xl font-bold text-purple-500"><?= $totais['reservados'] ?></div>
                            <div class="text-xs text-purple-500 tracking-wide">Leitos Reservados</div>
                        </div>
                    </div>
                </div>
                
                <!-- Taxa de Ocupação Geral -->
                <div class="ocupacao-gradient p-4 rounded-xl border border-blue-200 shadow-lg">
                    <?php 
                    $ocupacaoGeral = $totais['total'] > 0 ? 
                        round(($totais['ocupados'] / $totais['total']) * 100, 2) : 0;
                    $corOcupacao = $ocupacaoGeral >= 85 ? 'text-red-800' : 
                                 ($ocupacaoGeral >= 70 ? 'text-yellow-500' : 'text-green-800');
                    ?>
                    <div class="text-center">
                        <div class="text-lg font-semibold text-gray-700">Taxa de Ocupação Geral</div>
                        <div class="text-4xl font-bold <?= $corOcupacao ?> mb-2"><?= $ocupacaoGeral ?>%</div>
                        <div class="text-sm text-gray-500 mt-2">Total de leitos: <span class="font-semibold"><?= $totais['total'] ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de ocupação -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-2 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-800">Ocupação por Setor</h2>
            <p class="text-sm text-gray-600"><?= count($dadosOcupacao) ?> setores encontrados</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Setor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ocupados
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Livres
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aguard. Higien.
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Higienização
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Isolados
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reservados
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            % Ocupação
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dadosOcupacao as $setor): ?>
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-2 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <span class="text-sm text-gray-500"><?= $setor['CD_SETOR_ATENDIMENTO'] ?> - </span>
                                <?= htmlspecialchars($setor['DS_SETOR_ATENDIMENTO']) ?>
                            </div>
                            
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-900 font-medium">
                            <?= $setor['QTD_TOTAL'] ?>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?= $setor['QTD_OCUPADAS'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <?= $setor['QTD_LIVRES'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-900">
                            <?= $setor['QTD_AGUARDANDO_HIGIENIZACAO'] ?>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-900">
                            <?= $setor['QTD_HIGIENIZACAO'] ?>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                <?= $setor['QTD_ISOLADO'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-900">
                            <?= $setor['NR_UNIDADES_RESERVADAS'] ?>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap">
                            <?php 
                            $percentual = $setor['PERCENTUAL_OCUPACAO'];
                            $cor = $percentual >= 85 ? 'bg-red-100 text-red-800' : 
                                  ($percentual >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800');
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $cor ?>">
                                <?= $percentual ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <!-- Linha de Totais -->
                    <tr class="bg-gray-50 font-semibold border-t-2 border-gray-300">
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-900">
                            <strong>TOTAL GERAL</strong>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-900">
                            <strong><?= $totais['total'] ?></strong>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-200 text-blue-900">
                                <strong><?= $totais['ocupados'] ?></strong>
                            </span>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-200 text-green-900">
                                <strong><?= $totais['livres'] ?></strong>
                            </span>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-900">
                            <strong><?= $totais['aguardando_higienizacao'] ?></strong>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-900">
                            <strong><?= $totais['higienizacao'] ?></strong>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-200 text-red-900">
                                <strong><?= $totais['isolados'] ?></strong>
                            </span>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-900">
                            <strong><?= $totais['reservados'] ?></strong>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap">
                            <?php 
                            $percentualTotal = $totais['total'] > 0 ? 
                                round(($totais['ocupados'] / $totais['total']) * 100, 2) : 0;
                            $corTotal = $percentualTotal >= 85 ? 'bg-red-200 text-red-900' : 
                                      ($percentualTotal >= 70 ? 'bg-yellow-200 text-yellow-900' : 'bg-green-200 text-green-900');
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $corTotal ?>">
                                <strong><?= $percentualTotal ?>%</strong>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Aguardar o carregamento completo da página
    document.addEventListener('DOMContentLoaded', function() {
        // Calcular percentuais para cada segmento
        const dados = [
            <?= $totais['ocupados'] ?>,
            <?= $totais['livres'] ?>,
            <?= $totais['higienizacao'] ?>,
            <?= $totais['aguardando_higienizacao'] ?>,
            <?= $totais['isolados'] ?>,
            <?= $totais['reservados'] ?>
        ];
        
        const total = dados.reduce((a, b) => a + b, 0);
        const percentuais = dados.map(valor => Math.round((valor / total) * 100));
        
        // Configuração do Gráfico de Pizza
        const ctx = document.getElementById('ocupacaoChart').getContext('2d');
        const ocupacaoChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Ocupados', 'Livres', 'Em Higienização', 'Aguard. Higien.', 'Isolados', 'Reservados'],
                datasets: [{
                    data: dados,
                    backgroundColor: [
                        '#3b82f6', // blue
                        '#10b981', // green
                        '#f59e0b', // yellow
                        '#f97316', // orange
                        '#ef4444', // red
                        '#8b5cf6'  // purple
                    ],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverBorderWidth: 4,
                    hoverBorderColor: '#f3f4f6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '50%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const percentage = percentuais[context.dataIndex];
                                return `${label}: ${value} leitos (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            },
            plugins: [{
                id: 'centerText',
                afterDraw: function(chart) {
                    const ctx = chart.ctx;
                    const width = chart.width;
                    const height = chart.height;
                    
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = 'bold 16px Arial';
                    ctx.fillStyle = '#374151';
                    ctx.fillText('Distribuição', width / 2, height / 2 - 10);
                    
                    ctx.font = 'bold 20px Arial';
                    ctx.fillStyle = '#3b82f6';
                    ctx.fillText('de Leitos', width / 2, height / 2 + 10);
                    
                    ctx.restore();
                }
            }]
        });
    });
</script>