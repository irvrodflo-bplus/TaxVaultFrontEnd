<?php
/**
 * Configuración para el sistema de descarga masiva de CFDIs
 */

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'tax_vault');
define('DB_USER', 'root');
define('DB_PASS', '1234');
define('DB_CHARSET', 'utf8mb4');

// Configuración del webservice
define('WS_URL', 'https://sistema.bovedafacturalo.com/ws/doReporte');
define('WS_API_KEY', '0401ef52-e39a-4859-a684-67612cdec64b');
define('WS_LAYOUT', 'GBM170505Q78_1');

// Configuración de timeouts
define('WS_TIMEOUT', 300); // 5 minutos
define('WS_MAX_RETRIES', 3);

// Configuración de logging
define('LOG_ENABLED', true);
define('LOG_FILE', __DIR__ . '/logs/cfdi_download.log');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Configuración de archivos
define('TEMP_DIR', __DIR__ . '/temp/');
define('BACKUP_DIR', __DIR__ . '/backups/');

// Configuración de la aplicación
define('APP_NAME', 'CFDI Dashboard');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE', 'America/Mexico_City');

// Configurar zona horaria
date_default_timezone_set(APP_TIMEZONE);

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Crear directorios necesarios
$directories = [
    dirname(LOG_FILE),
    TEMP_DIR,
    BACKUP_DIR
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Configuración de PDO
$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_STRINGIFY_FETCHES => false
];

// Función para obtener conexión PDO
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $GLOBALS['pdoOptions']);
        } catch (PDOException $e) {
            throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Configuración de tipos de comprobante
$tiposComprobante = [
    'I' => 'Ingreso',
    'E' => 'Egreso',
    'T' => 'Traslado',
    'N' => 'Nómina',
    'P' => 'Pago'
];

// Configuración de status SAT
$statusSAT = [
    'Vigente' => 'Vigente',
    'Cancelado' => 'Cancelado',
    'Pendiente' => 'Pendiente'
];

// Configuración de métodos de pago
$metodosPago = [
    'PUE' => 'Pago en una sola exhibición',
    'PPD' => 'Pago en parcialidades o diferido'
];

// Configuración de formas de pago
$formasPago = [
    '01' => 'Efectivo',
    '02' => 'Cheque nominativo',
    '03' => 'Transferencia electrónica de fondos',
    '04' => 'Tarjeta de crédito',
    '05' => 'Monedero electrónico',
    '06' => 'Dinero electrónico',
    '08' => 'Vales de despensa',
    '12' => 'Dación en pago',
    '13' => 'Pago por subrogación',
    '14' => 'Pago por consignación',
    '15' => 'Condonación',
    '17' => 'Compensación',
    '23' => 'Novación',
    '24' => 'Confusión',
    '25' => 'Remisión de deuda',
    '26' => 'Prescripción o caducidad',
    '27' => 'A satisfacción del acreedor',
    '28' => 'Tarjeta de débito',
    '29' => 'Tarjeta de servicios',
    '30' => 'Aplicación de anticipos',
    '99' => 'Por definir'
];

// Configuración de monedas
$monedas = [
    'MXN' => 'Peso Mexicano',
    'USD' => 'Dólar Americano',
    'EUR' => 'Euro'
];

// Configuración de usos de CFDI
$usosCFDI = [
    'G01' => 'Adquisición de mercancías',
    'G02' => 'Devoluciones, descuentos o bonificaciones',
    'G03' => 'Gastos en general',
    'I01' => 'Construcciones',
    'I02' => 'Mobilario y equipo de oficina por inversiones',
    'I03' => 'Equipo de transporte',
    'I04' => 'Equipo de computo y accesorios',
    'I05' => 'Dados, troqueles, moldes, matrices y herramental',
    'I06' => 'Comunicaciones telefónicas',
    'I07' => 'Comunicaciones satelitales',
    'I08' => 'Otra maquinaria y equipo',
    'D01' => 'Honorarios médicos, dentales y gastos hospitalarios',
    'D02' => 'Gastos médicos por incapacidad o discapacidad',
    'D03' => 'Gastos funerales',
    'D04' => 'Donativos',
    'D05' => 'Intereses reales efectivamente pagados por créditos hipotecarios',
    'D06' => 'Aportaciones voluntarias al SAR',
    'D07' => 'Primas por seguros de gastos médicos',
    'D08' => 'Gastos de transportación escolar obligatoria',
    'D09' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones',
    'D10' => 'Pagos por servicios educativos',
    'P01' => 'Por definir'
];

// Función para logging
function writeLog($message, $level = 'INFO') {
    if (!LOG_ENABLED) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND | LOCK_EX);
}

// Función para formatear moneda
function formatCurrency($amount, $currency = 'MXN') {
    return number_format($amount, 2, '.', ',') . ' ' . $currency;
}

// Función para formatear fecha
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) return null;
    
    if ($date instanceof DateTime) {
        return $date->format($format);
    }
    
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

// Función para validar RFC
function validateRFC($rfc) {
    $rfc = strtoupper(trim($rfc));
    
    // Patrón para RFC de persona física (13 caracteres)
    $patternPersonaFisica = '/^[A-Z&Ñ]{4}[0-9]{6}[A-Z0-9]{3}$/';
    
    // Patrón para RFC de persona moral (12 caracteres)
    $patternPersonaMoral = '/^[A-Z&Ñ]{3}[0-9]{6}[A-Z0-9]{3}$/';
    
    return preg_match($patternPersonaFisica, $rfc) || preg_match($patternPersonaMoral, $rfc);
}

// Función para validar UUID
function validateUUID($uuid) {
    $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    return preg_match($pattern, $uuid);
}

// Función para limpiar string
function cleanString($string) {
    return trim(preg_replace('/\s+/', ' ', $string));
}

// Configuración de headers para respuestas JSON
function setJSONHeaders() {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
}

// Función para respuesta JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    setJSONHeaders();
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Función para manejo de errores
function handleError($message, $code = 500) {
    writeLog("ERROR: {$message}", 'ERROR');
    jsonResponse([
        'error' => true,
        'message' => $message,
        'code' => $code,
        'timestamp' => date('c')
    ], $code);
}

// Configuración de límites de memoria y tiempo
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 600); // 10 minutos

writeLog("Configuración cargada correctamente - " . APP_NAME . " v" . APP_VERSION);
?>
