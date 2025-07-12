#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    exit('Just can exceute this form comman line');
}

if ($argc < 2) {
    echo "Uso: php maker.php <nombre-del-servicio> [--endpoint=<ruta>]\n";
    exit(1);
}

$scripts = [
    __DIR__ . '/make-service.php',
    __DIR__ . '/make-endpoint.php'
];

$serviceName = $argv[1];
$endpoint = '';
$toggle = false;

foreach ($argv as $arg) {
    if (strpos($arg, '--endpoint=') === 0) {
        $endpoint = substr($arg, 11);
    }
    if ($arg === '--toggle') {
        $toggle = true;
    }
}

if (!preg_match('/^[a-zA-Z]+$/', $serviceName)) {
    echo "The name only can have letter\n";
    exit(1);
}

echo "Making service...\n";
$serviceCmd = sprintf(
    'php %s/make-service.php %s %s %s',  
    __DIR__,
    escapeshellarg($serviceName),
    $endpoint ? '--endpoint='.escapeshellarg($endpoint) : '',
    $toggle ? '--toggle' : ''
);

exec($serviceCmd, $output, $returnCode);

if ($returnCode !== 0) {
    echo "Error into create the service:\n".implode("\n", $output)."\n";
    exit(1);
}

echo "Making endpoint...\n";
$endpointCmd = sprintf(
    'php %s/make-endpoint.php %s %s',
    __DIR__,
    escapeshellarg($serviceName),
    $toggle ? '--toggle' : ''
);

exec($endpointCmd, $output, $returnCode);

if ($returnCode !== 0) {
    echo "Error into create the endpoint:\n".implode("\n", $output)."\n";
    exit(1);
}

echo "Maker process completed successfully!\n";
echo "Service: {$serviceName}Service.php\n";
echo "Endpoint: {$serviceName}.endpoint.php\n";