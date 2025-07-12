<?php 

class Environment {
    private static $instance = null;
    private $variables = [];
    
    private function __construct() {
        $envPath = __DIR__;
        require_once $envPath . '/env-builder.php';
        buildEnv("$envPath/.env");
        $this->variables = $_ENV;
    }
    
    public static function getInstance(): self { 
        return self::$instance ??= new self();
    }
    
    public function get(string $key, $default = null): mixed {
        return $this->variables[$key] ?? $default;
    }
}

?>