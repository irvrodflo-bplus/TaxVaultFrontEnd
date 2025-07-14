<?php
/**
 * Sistema de logging para el dashboard de CFDIs
 */

class Logger {
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const CRITICAL = 'CRITICAL';
    
    private $logFile;
    private $logLevel;
    private $maxFileSize;
    private $maxFiles;
    
    public function __construct($logFile = null, $logLevel = 'INFO') {
        $this->logFile = $logFile ?: (__DIR__ . '/logs/cfdi_' . date('Y-m-d') . '.log');
        $this->logLevel = $logLevel;
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
        $this->maxFiles = 30; // Mantener 30 archivos
        
        $this->ensureLogDirectory();
        $this->rotateLogsIfNeeded();
    }
    
    /**
     * Asegurar que el directorio de logs existe
     */
    private function ensureLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Rotar logs si es necesario
     */
    private function rotateLogsIfNeeded() {
        if (file_exists($this->logFile) && filesize($this->logFile) > $this->maxFileSize) {
            $this->rotateLogs();
        }
        
        $this->cleanOldLogs();
    }
    
    /**
     * Rotar el archivo de log actual
     */
    private function rotateLogs() {
        $timestamp = date('Y-m-d_H-i-s');
        $rotatedFile = str_replace('.log', "_{$timestamp}.log", $this->logFile);
        rename($this->logFile, $rotatedFile);
    }
    
    /**
     * Limpiar logs antiguos
     */
    private function cleanOldLogs() {
        $logDir = dirname($this->logFile);
        $pattern = $logDir . '/cfdi_*.log';
        $files = glob($pattern);
        
        if (count($files) > $this->maxFiles) {
            // Ordenar por fecha de modificación
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Eliminar los más antiguos
            $filesToDelete = array_slice($files, 0, count($files) - $this->maxFiles);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * Verificar si el nivel de log debe ser registrado
     */
    private function shouldLog($level) {
        $levels = [
            self::DEBUG => 0,
            self::INFO => 1,
            self::WARNING => 2,
            self::ERROR => 3,
            self::CRITICAL => 4
        ];
        
        return $levels[$level] >= $levels[$this->logLevel];
    }
    
    /**
     * Escribir mensaje al log
     */
    private function writeLog($level, $message, $context = []) {
        if (!$this->shouldLog($level)) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $pid = getmypid();
        $memory = $this->formatBytes(memory_get_usage(true));
        
        // Formatear contexto si existe
        $contextStr = '';
        if (!empty($context)) {
            $contextStr = ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        // Obtener información del caller
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = isset($backtrace[2]) ? basename($backtrace[2]['file']) . ':' . $backtrace[2]['line'] : 'unknown';
        
        $logMessage = sprintf(
            "[%s] [%s] [PID:%d] [MEM:%s] [%s] %s%s%s",
            $timestamp,
            $level,
            $pid,
            $memory,
            $caller,
            $message,
            $contextStr,
            PHP_EOL
        );
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        // También enviar a syslog en caso de errores críticos
        if ($level === self::CRITICAL || $level === self::ERROR) {
            syslog(LOG_ERR, "CFDI Dashboard - {$level}: {$message}");
        }
    }
    
    /**
     * Formatear bytes a formato legible
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Log de debug
     */
    public function debug($message, $context = []) {
        $this->writeLog(self::DEBUG, $message, $context);
    }
    
    /**
     * Log de información
     */
    public function info($message, $context = []) {
        $this->writeLog(self::INFO, $message, $context);
    }
    
    /**
     * Log de advertencia
     */
    public function warning($message, $context = []) {
        $this->writeLog(self::WARNING, $message, $context);
    }
    
    /**
     * Log de error
     */
    public function error($message, $context = []) {
        $this->writeLog(self::ERROR, $message, $context);
    }
    
    /**
     * Log crítico
     */
    public function critical($message, $context = []) {
        $this->writeLog(self::CRITICAL, $message, $context);
    }
    
    /**
     * Log de inicio de proceso
     */
    public function startProcess($processName, $context = []) {
        $this->info("🚀 Iniciando proceso: {$processName}", $context);
    }
    
    /**
     * Log de fin de proceso
     */
    public function endProcess($processName, $duration = null, $context = []) {
        $durationStr = $duration ? " (Duración: {$duration}s)" : "";
        $this->info("✅ Proceso completado: {$processName}{$durationStr}", $context);
    }
    
    /**
     * Log de proceso fallido
     */
    public function failProcess($processName, $error, $context = []) {
        $this->error("❌ Proceso fallido: {$processName} - Error: {$error}", $context);
    }
    
    /**
     * Log de estadísticas
     */
    public function stats($stats) {
        $this->info("📊 Estadísticas: " . json_encode($stats, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * Log de performance
     */
    public function performance($operation, $duration, $context = []) {
        $level = $duration > 5 ? self::WARNING : self::INFO;
        $this->writeLog($level, "⏱️ Performance - {$operation}: {$duration}s", $context);
    }
    
    /**
     * Log de base de datos
     */
    public function database($query, $duration = null, $context = []) {
        $durationStr = $duration ? " ({$duration}s)" : "";
        $this->debug("🗄️ DB Query{$durationStr}: {$query}", $context);
    }
    
    /**
     * Log de API
     */
    public function api($method, $url, $responseCode, $duration = null, $context = []) {
        $durationStr = $duration ? " ({$duration}s)" : "";
        $level = $responseCode >= 400 ? self::ERROR : self::INFO;
        $this->writeLog($level, "🌐 API {$method} {$url} - {$responseCode}{$durationStr}", $context);
    }
    
    /**
     * Log de seguridad
     */
    public function security($event, $context = []) {
        $this->warning("🔒 Evento de seguridad: {$event}", $context);
    }
    
    /**
     * Obtener estadísticas del log
     */
    public function getLogStats() {
        if (!file_exists($this->logFile)) {
            return null;
        }
        
        $stats = [
            'file' => $this->logFile,
            'size' => filesize($this->logFile),
            'size_formatted' => $this->formatBytes(filesize($this->logFile)),
            'created' => date('Y-m-d H:i:s', filectime($this->logFile)),
            'modified' => date('Y-m-d H:i:s', filemtime($this->logFile)),
            'lines' => 0
        ];
        
        // Contar líneas
        $handle = fopen($this->logFile, 'r');
        if ($handle) {
            while (!feof($handle)) {
                fgets($handle);
                $stats['lines']++;
            }
            fclose($handle);
        }
        
        return $stats;
    }
    
    /**
     * Obtener las últimas líneas del log
     */
    public function getTailLines($lines = 100) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $file = file($this->logFile);
        return array_slice($file, -$lines);
    }
    
    /**
     * Buscar en el log
     */
    public function search($pattern, $maxResults = 100) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $results = [];
        $handle = fopen($this->logFile, 'r');
        $lineNumber = 0;
        
        if ($handle) {
            while (($line = fgets($handle)) !== false && count($results) < $maxResults) {
                $lineNumber++;
                if (stripos($line, $pattern) !== false) {
                    $results[] = [
                        'line' => $lineNumber,
                        'content' => trim($line)
                    ];
                }
            }
            fclose($handle);
        }
        
        return $results;
    }
    
    /**
     * Limpiar logs manualmente
     */
    public function clearLogs() {
        if (file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
            $this->info("🧹 Log limpiado manualmente");
        }
    }
    
    /**
     * Crear backup del log actual
     */
    public function backup() {
        if (!file_exists($this->logFile)) {
            return false;
        }
        
        $backupFile = str_replace('.log', '_backup_' . date('Y-m-d_H-i-s') . '.log', $this->logFile);
        $success = copy($this->logFile, $backupFile);
        
        if ($success) {
            $this->info("💾 Backup creado: " . basename($backupFile));
        }
        
        return $success;
    }
}

// Instancia global del logger
$logger = new Logger();

// Funciones de conveniencia globales
function logDebug($message, $context = []) {
    global $logger;
    $logger->debug($message, $context);
}

function logInfo($message, $context = []) {
    global $logger;
    $logger->info($message, $context);
}

function logWarning($message, $context = []) {
    global $logger;
    $logger->warning($message, $context);
}

function logError($message, $context = []) {
    global $logger;
    $logger->error($message, $context);
}

function logCritical($message, $context = []) {
    global $logger;
    $logger->critical($message, $context);
}

// Registrar handler de errores PHP
set_error_handler(function($severity, $message, $file, $line) {
    global $logger;
    
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_NOTICE => 'NOTICE',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE'
    ];
    
    $type = $errorTypes[$severity] ?? 'UNKNOWN';
    $logger->error("PHP {$type}: {$message} in {$file}:{$line}");
    
    return false; // Permitir que el handler por defecto también procese el error
});

// Registrar handler de excepciones no capturadas
set_exception_handler(function($exception) {
    global $logger;
    $logger->critical("Excepción no capturada: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
});

// Log de inicio del sistema
$logger->info("🎯 Sistema de logging inicializado");
?>
