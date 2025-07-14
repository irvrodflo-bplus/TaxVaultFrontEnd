<?php
require_once __DIR__ . '/../core/api.php';
require_once __DIR__ . '/../core/base.service.php'; 
require_once __DIR__ . '/../core/http-client.php';

class VaultService extends BaseService {
    private $httpClient;
    private $apiBaseUrl;

    private static $instance = null;

    private function __construct() {
        global $API_BASE_URL;
        $this->apiBaseUrl = $API_BASE_URL . '/pac';
        $this->httpClient = new HttpClient($this->apiBaseUrl);
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getStats(array $data): array {
        $response = $this->httpClient->post('report_stats', $data);
        $this->validateResponse($response);
        return $response['data'];
    } 

    public function getReport(array $data): array {
        $response = $this->httpClient->post('report', $data);
        $this->validateResponse($response);
        return $response['data'];
    }

    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton");
    }
}