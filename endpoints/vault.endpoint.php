<?php
require_once __DIR__ . '/../core/request-handler.php'; 
require_once __DIR__ . '/../services/vault.service.php'; 

header('Content-Type: application/json');

try {
    $request = RequestHandler::handleRequest();
    $operation = $request['operation'];
    $data = $request['data'];

    $service = VaultService::getInstance();

    $result = match ($operation) {
        'updateEmited' => $service->getEmitted($data),
        'updateReceived' => $service->getReceived($data),
        default  => throw new RuntimeException('Invalid métod', 400)
    };
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTrace()
    ]);
}