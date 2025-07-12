<?php
require_once __DIR__ . '/api.php';

class HttpClient {
    private $baseUrl;
    private $pipes = [];

    private static $apiKey = '';
    public static $userId = null;

    public function __construct(string $baseUrl = '') {
        $this->baseUrl = rtrim($baseUrl, '/');
        self::initFromSession();
    }
    
    public static function setApiKey(string $apiKey): void {
        self::$apiKey = $apiKey;
    }

    public static function setUserId(int $userId): void {
        self::$userId = $userId;
    }

    private static function initFromSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['id']) && !self::isLoginRequest()) {
            self::$userId = $_SESSION['id'];
        }
    }

    private static function isLoginRequest(): bool {
        return str_contains($_SERVER['REQUEST_URI'] ?? '', 'login.php');
    }

    public function get(string $url, array $body = [], array $headers = []): array {
        return $this->request('GET', $url, $body, $headers);
    }

    public function post(string $url, array $body, array $headers = []): array {
        return $this->request('POST', $url, $body, $headers);
    }

    public function put(string $url, array $body, array $headers = []): array {
        return $this->request('PUT', $url, $body, $headers);
    }

    public function delete(string $url, array $headers = []): array {
        return $this->request('DELETE', $url, null, $headers);
    }

    public function pipe(callable $transformer): self {
        $this->pipes[] = $transformer;
        return $this;
    }
    
    public function map(callable $mapper): self {
        return $this->pipe(function(array $response) use ($mapper) {
            $response['data'] = $mapper($response['data']);
            return $response;
        });
    }

	public function onlyData(): self {
        return $this->pipe(function(array $response) {
            return $response['data'];
        });
    }
    
    private function request(string $method, string $url, ?array $body = null, array $headers = []): array {
        $fullUrl = parse_url($url, PHP_URL_HOST) ? $url : $this->baseUrl . '/' . ltrim($url, '/');

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $mergedHeaders = $this->buildHeadersArray($headers);
        $headerLines = [];
        foreach ($mergedHeaders as $name => $value) {
            $headerLines[] = "$name: $value";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerLines);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Connection error: " . $error);
        }
        
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        $responseHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);
        
        curl_close($ch);
        
        $responseData = json_decode($responseBody, true) ?? $responseBody;
        
        $processedResponse = [
            'status' => $statusCode,
            'data' => $responseData,
            'headers' => $this->parseHeaders(explode("\r\n", $responseHeaders))
        ];
        
        return $this->applyPipes($processedResponse);
    }
    
    private function applyPipes(array $response): array {
        foreach ($this->pipes as $pipe) {
            $response = $pipe($response);
        }
        $this->pipes = [];
        return $response;
    }
    
    private function buildHeadersArray(array $additionalHeaders): array {
        $baseHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-API-KEY' => self::$apiKey
        ];
        
        if (self::$userId !== null) {
            $baseHeaders['X-USER-ID'] = self::$userId;
        }
        
        return array_merge($baseHeaders, $additionalHeaders);
    }
    
    private function getStatusCode(array $responseHeaders): int {
        return (int) preg_match('/HTTP\/\d\.\d\s(\d{3})/', $responseHeaders[0] ?? '', $matches) 
            ? $matches[1] 
            : 0;
    }
    
    private function parseHeaders(array $responseHeaders): array {
        $headers = [];
        foreach ($responseHeaders as $header) {
            if (strpos($header, ':') !== false) {
                [$name, $value] = explode(':', $header, 2);
                $headers[trim($name)] = trim($value);
            }
        }
        return $headers;
    }
}

HttpClient::setApiKey($API_KEY);