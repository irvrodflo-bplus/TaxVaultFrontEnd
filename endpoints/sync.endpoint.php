<?php
require_once __DIR__ . '/../core/request-handler.php'; 
require_once __DIR__ . '/../services/sync.service.php'; 

header('Content-Type: application/json');

try {
    $request = RequestHandler::handleRequest();
    $operation = $request['operation'];
    $data = $request['data'];

    $service = SyncService::getInstance();

    $result = match ($operation) {
        'create' => $service->create($data),
        'index'  => $service->getAll(),
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