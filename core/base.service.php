<?php
abstract class BaseService {
    
    protected function validateResponse(array $response): void {
        if ($response['status'] < 400) {
            return;
        }

        $errorMessage = $this->extractErrorMessage($response);
        throw new RuntimeException("Error {$response['status']}: $errorMessage");
    }
    
    private function extractErrorMessage(array $response): string {
        $errorData = $response['data'] ?? [];
        
        return $errorData['message'] 
            ?? $errorData['error'] 
            ?? $errorData['description'] 
            ?? $this->getDefaultErrorMessage($response['status']);
    }

    protected function getDefaultErrorMessage(int $statusCode): string {
        $messages = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Acces dennied',
            404 => 'Not found',
            422 => 'Validation rejected',
            500 => 'Server error',
            503 => 'Unavaliable service'
        ];
        
        return $messages[$statusCode] ?? "Error desconocido (Código: $statusCode)";
    }
}