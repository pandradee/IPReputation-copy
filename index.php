<?php
// Habilitar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Informações sobre o PHP
echo '<h1>Informações de Debug</h1>';
echo '<h2>Versão do PHP</h2>';
echo 'PHP Version: ' . phpversion();

// Verificar extensões necessárias
echo '<h2>Extensões PHP</h2>';
$required_extensions = ['json', 'curl', 'xml', 'mbstring', 'gd'];
foreach ($required_extensions as $ext) {
    echo $ext . ': ' . (extension_loaded($ext) ? 'Carregada' : 'Não carregada') . '<br>';
}

// Verificar diretórios e permissões
echo '<h2>Diretórios e Permissões</h2>';
$dirs = ['var', 'service', 'actions', 'views', 'assets'];
foreach ($dirs as $dir) {
    echo $dir . ': ' . (is_dir(__DIR__ . '/' . $dir) ? 'Existe' : 'Não existe') . ' - ';
    
    if (is_dir(__DIR__ . '/' . $dir)) {
        echo 'Permissões: ' . substr(sprintf('%o', fileperms(__DIR__ . '/' . $dir)), -4) . '<br>';
    } else {
        echo 'N/A<br>';
    }
}

// Verificar se o arquivo de configuração pode ser lido
echo '<h2>Arquivos de Configuração</h2>';
$manifest_file = __DIR__ . '/manifest.json';
echo 'manifest.json: ' . (file_exists($manifest_file) ? 'Existe' : 'Não existe') . ' - ';
if (file_exists($manifest_file)) {
    echo 'Pode ser lido: ' . (is_readable($manifest_file) ? 'Sim' : 'Não') . '<br>';
    echo 'Conteúdo: <pre>' . htmlspecialchars(file_get_contents($manifest_file)) . '</pre>';
} else {
    echo 'N/A<br>';
}

// Testar a classe ConfigStorage
echo '<h2>Teste da Classe ConfigStorage</h2>';
try {
    require_once __DIR__ . '/service/StorageAbstract.php';
    require_once __DIR__ . '/service/ConfigStorage.php';
    
    $storage = new \Modules\IPReputation\Service\ConfigStorage(['data' => []]);
    echo 'Instância criada com sucesso.<br>';
    
    // Testar métodos
    echo 'Método get: ';
    $result = $storage->get(['type' => 'domain']);
    echo 'OK - Retornou: ' . print_r($result, true) . '<br>';
    
    echo 'Método set: ';
    $test_domain = [
        'id' => 'test_domain',
        'type' => 'domain',
        'domain' => 'example.com',
        'description' => 'Test domain',
        'added' => time()
    ];
    $storage->set($test_domain);
    echo 'OK<br>';
    
    echo 'Método get após set: ';
    $result = $storage->get(['type' => 'domain']);
    echo 'OK - Retornou: <pre>' . print_r($result, true) . '</pre>';
    
} catch (\Exception $e) {
    echo 'Erro: ' . $e->getMessage() . '<br>';
    echo 'Stack trace: <pre>' . $e->getTraceAsString() . '</pre>';
}

// Testar função JSON
echo '<h2>Teste de JSON</h2>';
$test_array = ['test' => 'value', 'number' => 123];
echo 'Original: ' . print_r($test_array, true) . '<br>';
echo 'JSON encode: ' . json_encode($test_array) . '<br>';
echo 'JSON decode: ' . print_r(json_decode(json_encode($test_array), true), true) . '<br>';

// Verificar logs
echo '<h2>Logs</h2>';
$log_file = __DIR__ . '/var/error_log.txt';
if (file_exists($log_file)) {
    echo 'Conteúdo do log: <pre>' . htmlspecialchars(file_get_contents($log_file)) . '</pre>';
} else {
    echo 'Arquivo de log não existe.<br>';
} 