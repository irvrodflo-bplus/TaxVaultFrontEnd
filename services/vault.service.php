<?php
require_once __DIR__ . '/../core/api.php';
require_once __DIR__ . '/../core/base.service.php'; 
require_once __DIR__ . '/../core/http-client.php';
require __DIR__ . '/../php/cfdi_webservice_fixed.php';
require __DIR__ . '/../php/cfdi_webservice_fixed_re.php';

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

    public function getEmitted(array $data): array {
        $rfc = 'GAF220603TC4';
        $response = descargarCFDIsEmitidos($data['start_date'], $data['end_date'], $rfc);
        return $response;
    }

    public function getReceived(array $data): array {
        $rfc = 'GAF220603TC4';
        $response = descargarCFDIsRecibidos($data['start_date'], $data['end_date'], $rfc);
        return $response;
    }

    public function syncLast(): array {
        $rfc = 'GAF220603TC4';

        $endDate = new DateTime(); 
        $endDateStr = $endDate->format('Y-m-d');

        $startDate = clone $endDate;
        $startDate->modify('-3 days');
        $startDateStr = $startDate->format('Y-m-d');

        $data = [
            'start_date' => $startDateStr,
            'end_date'   => $endDateStr,
        ];

        $emited  = descargarCFDIsEmitidos($data['start_date'], $data['end_date'], $rfc);
        $recived = descargarCFDIsRecibidos($data['start_date'], $data['end_date'], $rfc);

        return [
            'insertados'   => $emited['insertados'] + $recived['insertados'],
            'actualizados' => $emited['actualizados'] + $recived['actualizados'],
            'errores'      => $emited['errores'] + $recived['errores'],
        ];
    }

    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton");
    }
}