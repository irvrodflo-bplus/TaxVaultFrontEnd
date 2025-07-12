#!/usr/bin/env php
<?php
if (PHP_SAPI !== 'cli') {
    exit('Only can excecute this from command line');
}

if ($argc < 2) {
    echo "Use: php make.service.php <NombreDelServicio> [--endpoint=<ruta>]\n";
    exit(1);
}

$serviceName = $argv[1];
$serviceNameCap = ucfirst($serviceName);

$toggle = false;

foreach ($argv as $arg) {
    if ($arg === '--toggle') {
        $toggle = true;
        echo 'toggle';
    }
}

$template = <<<PHP
<?php
require_once __DIR__ . '/../core/request-handler.php'; 
require_once __DIR__ . '/../services/{$serviceName}.service.php'; 

header('Content-Type: application/json');

try {
    \$request = RequestHandler::handleRequest();
    \$operation = \$request['operation'];
    \$data = \$request['data'];

    \$service = {$serviceNameCap}Service::getInstance();

    \$result = match (\$operation) {
        'create' => \$service->create(\$data),
        'update' => \$service->update(\$data),
        'index'  => \$service->getAll(),
        'getActive' => \$service->getActive(),
        'show'   => \$service->getById(\$data),
PHP;

if ($toggle) {
    $template .= <<<PHP
    
        'toggleStatus' => \$service->toggleStatus(\$data),
PHP;
} else {
    $template .= <<<PHP

        'delete'   => \$service->delete(\$data),
PHP;
}

$template .= <<<PHP

        default  => throw new RuntimeException('Invalid métod', 400)
    };
    echo json_encode(\$result);
} catch (Exception \$e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => \$e->getMessage(),
        'trace' => \$e->getTrace()
    ]);
}
PHP;

$servicesDir = __DIR__ . '/../endpoints';
if (!file_exists($servicesDir)) {
    mkdir($servicesDir, 0755, true);
}

$filename = $servicesDir . '/' . $serviceName . '.endpoint' . '.php';

file_put_contents($filename, $template);

echo "Endpoints {$serviceName} created successfully: {$filename}\n";