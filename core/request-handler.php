<?php

class RequestHandler {
    public static function handleRequest(): array {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new RuntimeException('Method must be POST', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $operation = $input['operation'] ?? '';
        $data = $input['data'] ?? [];

        if (empty($operation)) {
            throw new InvalidArgumentException('Param "operation" is required', 400);
        }

        return [
            'operation' => $operation,
            'data' => $data
        ];
    }
}