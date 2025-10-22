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

// Iniciar sessão caso não estiver iniciada
require_once __DIR__ . '/../../helpers/SessionHelper.php';
SessionHelper::startSession();

// Detectar módulo atual baseado na URL
$moduloAtual = 'cpoe'; // Padrão: CPOE - escolha
$moduloAtualTexto = 'CPOE - escolha';
$moduloAtualIcone = 'fas fa-list';
$moduloAtualBg = 'bg-green-600';

// Verificar se estamos no módulo de medicamentos
if (strpos($_SERVER['REQUEST_URI'], '/ISCMC/medicamentos/') !== false) {
    $moduloAtual = 'medicamentos';
    $moduloAtualTexto = 'Soluções e Medicamentos';
    $moduloAtualIcone = 'fas fa-pills';
    $moduloAtualBg = 'bg-info';
} 
// Verificar se estamos no módulo de procedimentos
elseif (strpos($_SERVER['REQUEST_URI'], '/ISCMC/procedimentos/') !== false) {
    $moduloAtual = 'procedimentos';
    $moduloAtualTexto = 'Procedimentos';
    $moduloAtualIcone = 'fas fa-procedures';
    $moduloAtualBg = 'bg-primary';
}

// Obter dados do estabelecimento atual da sessão
$cd_estabelecimento_atual = SessionHelper::getCurrentEstabelecimento();
$nm_estabelecimento_atual = SessionHelper::getEstabelecimentoName();
$sgl_estabelecimento_atual = SessionHelper::getEstabelecimentoSigla();

// Os estabelecimentos serão passados pelo Controller para a view
$estabelecimentos = $estabelecimentos ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISCMC - Sistema de Contingencia</title>
    <link rel="icon" type="image/x-icon" href="/ISCMC/assets/images/icone-site.png">

    <!-- Tailwind CSS -->
    <link href="/ISCMC/dist/output.css" rel="stylesheet">
    <link href="/ISCMC/assets/css/tailwind.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/ISCMC/assets/css/all.min.css">
    
    <!-- Minha própria folha de estilos  -->
    <link href="/ISCMC/assets/css/style.css" rel="stylesheet">
    <script src="/ISCMC/assets/js/chart.js"></script>
    
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-gray-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/ISCMC/?controller=dashboard" class="flex items-center space-x-3 text-white hover:text-gray-200">
                        <img src="/ISCMC/assets/images/logo-ISCMC.png" style="margin-top:10px;">
                        <span class="font-bold text-lg">ISCMC - Sistema de Contingência</span>
                    </a>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-300 hover:text-white focus:outline-none focus:text-white">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <!-- Dropdown de Estabelecimentos -->
                    <?php if (!empty($estabelecimentos)): ?>
                    <div class="relative group">
                        <form method="POST" action="/ISCMC/?controller=estabelecimento&action=changeEstabelecimento" class="m-0" id="formEstabelecimento">
                            <input type="hidden" name="cd_estabelecimento" id="hiddenCdEstabelecimento">
                            <input type="hidden" name="nm_fantasia_estab" id="hiddenNmFantasia" value="<?php echo htmlspecialchars($nm_estabelecimento_atual); ?>">
                            <button type="button" onclick="toggleEstabelecimentoDropdown()" class="text-white bg-blue-600 px-3 py-2 rounded-md font-medium flex items-center space-x-2 hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-hospital"></i>
                                <span id="currentEstabelecimento" class="text-sm"><?php echo htmlspecialchars($nm_estabelecimento_atual); ?></span>
                                <i class="fas fa-chevron-down text-xs ml-1"></i>
                            </button>
                            
                            <!-- Dropdown Menu Estabelecimentos -->
                            <div id="estabelecimentoDropdown" class="absolute left-0 mt-2 w-80 bg-white rounded-md shadow-lg opacity-0 invisible transition-all duration-300 z-50 border border-gray-200 max-h-96 overflow-y-auto">
                                <div class="py-2">
                                    <?php foreach ($estabelecimentos as $estab): ?>
                                        <button type="button" 
                                                onclick="changeEstabelecimento(<?php echo $estab['CD_ESTABELECIMENTO']; ?>, '<?php echo htmlspecialchars($estab['NM_FANTASIA_ESTAB']); ?>', '<?php echo htmlspecialchars($estab['NM_SIGLA_ESTAB']); ?>')"
                                                class="w-full text-left block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center justify-between <?php echo ($estab['CD_ESTABELECIMENTO'] == $cd_estabelecimento_atual) ? 'bg-blue-50 border-l-4 border-blue-500' : ''; ?>">
                                            <div class="flex items-center space-x-3">
                                                <i class="fas fa-clinic-medical text-green-500 w-5"></i>
                                                <div>
                                                    <div class="text-xs"><?php echo htmlspecialchars($estab['NM_FANTASIA_ESTAB']); ?></div>
                                                    <div class="text-xs text-gray-500">
                                                        Cód: <?php echo $estab['CD_ESTABELECIMENTO']; ?>
                                                        <?php if (!empty($estab['NM_SIGLA_ESTAB'])): ?>
                                                            • <?php echo htmlspecialchars($estab['NM_SIGLA_ESTAB']); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($estab['CD_ESTABELECIMENTO'] == $cd_estabelecimento_atual): ?>
                                                <i class="fas fa-check text-blue-500"></i>
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Dropdown de Procedimentos -->
                    <div class="relative group">
                        <button class="text-white <?php echo $moduloAtualBg; ?> px-3 py-2 rounded-md font-medium flex items-center space-x-2 hover:bg-primary-dark transition duration-200">
                            <i class="<?php echo $moduloAtualIcone; ?> "></i>
                            <span><?php echo $moduloAtualTexto; ?></span>
                            <i class="fas fa-chevron-down text-xs ml-1 text-white"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute left-0 mt-2 w-64 bg-white rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 border border-gray-200">
                            <div class="py-2">
                                <?php if ($moduloAtual !== 'medicamentos'): ?>
                                <a href="/ISCMC/medicamentos/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-pills text-blue-500 w-5"></i>
                                    <span>Soluções e Medicamentos</span>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($moduloAtual !== 'procedimentos'): ?>
                                <a href="/ISCMC/procedimentos/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-procedures text-red-500 w-5"></i>
                                    <span>Procedimentos</span>
                                </a>
                                <?php endif; ?>
                                
                                <?php
                                /*
                                <a href="/ISCMC/nutricao/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-apple-alt text-green-500 w-5"></i>
                                    <span>Nutrição</span>
                                </a>
                                <a href="/ISCMC/gasoterapia/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-wind text-cyan-500 w-5"></i>
                                    <span>Gasoterapia</span>
                                </a>
                                <a href="/ISCMC/recomendacoes/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-stethoscope text-purple-500 w-5"></i>
                                    <span>Recomendações</span>
                                </a>
                                <a href="/ISCMC/hemoterapia/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-tint text-red-600 w-5"></i>
                                    <span>Hemoterapia</span>
                                </a>
                                <a href="/ISCMC/dialise/" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-150 flex items-center space-x-3">
                                    <i class="fas fa-droplet text-orange-500 w-5"></i>
                                    <span>Diálise</span>
                                </a>
                                */
                                ?>
                            </div>
                        </div>
                    </div>

                    <a href="/TASYBackup/" target="_blank" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium flex items-center space-x-2">
                        <i class="fas fa-database"></i>
                        <span>Backup</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1">

<script>
// Funções para controle do dropdown de estabelecimentos
function toggleEstabelecimentoDropdown() {
    const dropdown = document.getElementById('estabelecimentoDropdown');
    const isVisible = dropdown.classList.contains('opacity-100');
    
    if (isVisible) {
        dropdown.classList.remove('opacity-100', 'visible');
        dropdown.classList.add('opacity-0', 'invisible');
    } else {
        dropdown.classList.remove('opacity-0', 'invisible');
        dropdown.classList.add('opacity-100', 'visible');
    }
}

function changeEstabelecimento(cd_estabelecimento, nm_fantasia_estab, nm_sigla_estab) {
    // Fechar dropdown
    toggleEstabelecimentoDropdown();
    
    console.log('Mudando estabelecimento:', cd_estabelecimento, nm_fantasia_estab, nm_sigla_estab);
    
    // Usar o formulário existente em vez de criar um novo
    const form = document.getElementById('formEstabelecimento');
    const inputCd = document.getElementById('hiddenCdEstabelecimento');
    const inputNm = document.getElementById('hiddenNmFantasia');
    
    // Criar inputs se não existirem
    if (!inputCd) {
        const newInputCd = document.createElement('input');
        newInputCd.type = 'hidden';
        newInputCd.name = 'cd_estabelecimento';
        newInputCd.id = 'hiddenCdEstabelecimento';
        form.appendChild(newInputCd);
    }
    
    if (!inputNm) {
        const newInputNm = document.createElement('input');
        newInputNm.type = 'hidden';
        newInputNm.name = 'nm_fantasia_estab';
        newInputNm.id = 'hiddenNmFantasia';
        form.appendChild(newInputNm);
    }
    
    // Atualizar valores
    document.getElementById('hiddenCdEstabelecimento').value = cd_estabelecimento;
    document.getElementById('hiddenNmFantasia').value = nm_fantasia_estab;
    
    // Adicionar sigla se fornecida
    let inputSgl = document.getElementById('hiddenNmSigla');
    if (!inputSgl && nm_sigla_estab) {
        inputSgl = document.createElement('input');
        inputSgl.type = 'hidden';
        inputSgl.name = 'nm_sigla_estab';
        inputSgl.id = 'hiddenNmSigla';
        form.appendChild(inputSgl);
    }
    if (inputSgl) {
        inputSgl.value = nm_sigla_estab;
    }
    
    console.log('Submetendo formulário com:', {
        cd_estabelecimento: document.getElementById('hiddenCdEstabelecimento').value,
        nm_fantasia_estab: document.getElementById('hiddenNmFantasia').value,
        nm_sigla_estab: inputSgl ? inputSgl.value : ''
    });
    
    // Submeter formulário
    form.submit();
}

// Fechar dropdown ao clicar fora
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('estabelecimentoDropdown');
    const button = document.querySelector('button[onclick="toggleEstabelecimentoDropdown()"]');
    
    if (button && !button.contains(event.target) && dropdown && !dropdown.contains(event.target)) {
        dropdown.classList.remove('opacity-100', 'visible');
        dropdown.classList.add('opacity-0', 'invisible');
    }
});
</script>