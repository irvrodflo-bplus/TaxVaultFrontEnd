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

$template = <<<PHP
<?php
require_once __DIR__ . '/../core/api.php';
require_once __DIR__ . '/../core/base.service.php'; 
require_once __DIR__ . '/../core/http-client.php';

class {$serviceNameCap}Service extends BaseService {
    private \$httpClient;
    private \$apiBaseUrl;

    private static \$instance = null;

    private function __construct() {
        global \$API_BASE_URL;
        \$this->apiBaseUrl = \$API_BASE_URL . '/{$endpoint}';
        \$this->httpClient = new HttpClient(\$this->apiBaseUrl);
    }

    public static function getInstance(): self {
        if (self::\$instance === null) {
            self::\$instance = new self();
        }
        return self::\$instance;
    }

    public function create(array \$data): array {
        \$response = \$this->httpClient->post('new', \$data);
        \$this->validateResponse(\$response);
        return \$response['data'];
    }

    public function getById(array \$data): array {
		\$id = \$data['id'];
        \$response = \$this->httpClient->get("show/\$id");
        \$this->validateResponse(\$response);
        return \$response['data'];
    }

    public function getAll(): array {
        \$response = \$this->httpClient->get('');
        \$this->validateResponse(\$response);
        return \$response['data'];
    }

    public function getActive(): array {
        \$response = \$this->httpClient->get('?status=1');
        \$this->validateResponse(\$response);
        return \$response['data'];
    }

    public function update(array \$data): array {
		\$id = \$data['id'];
        \$response = \$this->httpClient->post("update/\$id", \$data);
        \$this->validateResponse(\$response);
        return \$response['data'];
    }

PHP;

if ($toggle) {
    $template .= <<<PHP

    public function toggleStatus(array \$data): array {
        \$id = \$data['id'];
        \$response = \$this->httpClient->get("toggle_status/\$id");
        \$this->validateResponse(\$response);
        return \$response['data'];
    }
PHP;
} else {
    $template .= <<<PHP

    public function delete(int \$id): array {
		\$id = \$data['id'];
        \$response = \$this->httpClient->get("delete/\$id");
        \$this->validateResponse(\$response);
        return \$response['data'];
    }
PHP;
}

$template .= <<<PHP

    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton");
    }
}
PHP;

$servicesDir = __DIR__ . '/../services';
if (!file_exists($servicesDir)) {
    mkdir($servicesDir, 0755, true);
}

$filename = $servicesDir . '/' . $serviceName . '.service' . '.php';

file_put_contents($filename, $template);

echo "Service {$serviceName} created successfully: {$filename}\n";