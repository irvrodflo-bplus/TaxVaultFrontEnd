<?php
require_once __DIR__ . '/../core/api.php';
require_once __DIR__ . '/../core/base.service.php'; 
require_once __DIR__ . '/../core/http-client.php';

class SyncService extends BaseService {
    private $httpClient;
    private $apiBaseUrl;

    private static $instance = null;

    private function __construct() {
        global $API_BASE_URL;
        $this->apiBaseUrl = $API_BASE_URL . '/sync';
        $this->httpClient = new HttpClient($this->apiBaseUrl);
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function create(array $data): array {
        $response = $this->httpClient->post('new', $data);
        $this->validateResponse($response);
        return $response['data'];
    }

    public function getAll(): array {
        $response = $this->httpClient->get('');
        $this->validateResponse($response);
        return $response['data'];
    }

    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton");
    }
}