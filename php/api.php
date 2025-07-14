<?php
/**
 * API para consultas de CFDIs - Versión mejorada con debugging
 */

// Configurar manejo de errores para evitar output HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Buffer de salida para capturar cualquier output no deseado
ob_start();

try {
    require_once 'config.php';
} catch (Exception $e) {
    // Limpiar buffer y enviar error JSON
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Error al cargar configuración: ' . $e->getMessage(),
        'debug' => 'config.php no encontrado o error en configuración'
    ]);
    exit();
}

// Configurar headers para CORS y JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_clean();
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'CORS preflight OK']);
    exit();
}

class CFDIApi {
    private $pdo;
    private $debug = true; // Activar debug temporalmente
    
    public function __construct() {
        $this->conectarBaseDatos();
    }
    
    private function log($message) {
        if ($this->debug) {
            error_log("CFDI API: " . $message);
        }
    }
    
    private function conectarBaseDatos() {
        try {
            $this->log("Iniciando conexión a base de datos");
            
            // Verificar que las constantes estén definidas
            if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER')) {
                throw new Exception("Constantes de base de datos no definidas");
            }
            
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->log("Conectando con DSN: " . $dsn);
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->log("Conexión establecida exitosamente");
            
        } catch (PDOException $e) {
            $this->log("Error PDO: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            $this->log("Error general: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function manejarSolicitud() {
        try {
            $metodo = $_SERVER['REQUEST_METHOD'];
            $ruta = $_GET['ruta'] ?? '';
            
            $this->log("Procesando solicitud: $metodo $ruta");
            
            switch ($metodo) {
                case 'GET':
                    $this->manejarGet($ruta);
                    break;
                case 'POST':
                    $this->manejarPost($ruta);
                    break;
                default:
                    $this->enviarError("Método no permitido: $metodo", 405);
            }
        } catch (Exception $e) {
            $this->log("Error en manejarSolicitud: " . $e->getMessage());
            $this->enviarError("Error interno: " . $e->getMessage(), 500);
        }
    }
    
    private function manejarGet($ruta) {
        switch ($ruta) {
            case 'cfdis':
                $this->obtenerCFDIs();
                break;
            case 'estadisticas':
                $this->obtenerEstadisticas();
                break;
            case 'test-conexion':
                $this->testearConexion();
                break;
            case 'debug':
                $this->mostrarDebug();
                break;
            default:
                $this->enviarError("Ruta GET no encontrada: $ruta", 404);
        }
    }
    
    private function manejarPost($ruta) {
        switch ($ruta) {
            case 'filtrar-cfdis':
                $this->filtrarCFDIs();
                break;
            default:
                $this->enviarError("Ruta POST no encontrada: $ruta", 404);
        }
    }
    
    private function mostrarDebug() {
        $debug_info = [
            'php_version' => PHP_VERSION,
            'database_constants' => [
                'DB_HOST' => defined('DB_HOST') ? DB_HOST : 'NO DEFINIDO',
                'DB_NAME' => defined('DB_NAME') ? DB_NAME : 'NO DEFINIDO',
                'DB_USER' => defined('DB_USER') ? DB_USER : 'NO DEFINIDO',
                'DB_PASS' => defined('DB_PASS') ? (DB_PASS ? 'DEFINIDO' : 'VACÍO') : 'NO DEFINIDO'
            ],
            'pdo_available' => class_exists('PDO'),
            'mysql_driver' => in_array('mysql', PDO::getAvailableDrivers()),
            'server_info' => [
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'NO DEFINIDO',
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'NO DEFINIDO',
                'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? 'NO DEFINIDO'
            ]
        ];
        
        $this->enviarRespuesta([
            'success' => true,
            'debug' => $debug_info
        ]);
    }
    
    private function testearConexion() {
        try {
            $this->log("Iniciando test de conexión");
            
            // Test básico de conexión
            $sql = "SELECT 1 as test";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['test'] != 1) {
                throw new Exception("Test básico de conexión falló");
            }
            
            // Test de tabla cfdi_emitidos
            $sql = "SHOW TABLES LIKE 'cfdi_emitidos'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $table_exists = $stmt->fetch();
            
            if (!$table_exists) {
                throw new Exception("La tabla 'cfdi_emitidos' no existe");
            }
            
            // Contar registros
            $sql = "SELECT COUNT(*) as total FROM cfdi_emitidos";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $count_result = $stmt->fetch();
            
            $this->log("Test de conexión exitoso");
            
            $this->enviarRespuesta([
                'success' => true,
                'message' => 'Conexión exitosa',
                'database' => DB_NAME,
                'total_registros' => $count_result['total'],
                'tabla_existe' => true
            ]);
            
        } catch (Exception $e) {
            $this->log("Error en test de conexión: " . $e->getMessage());
            $this->enviarError("Error en test de conexión: " . $e->getMessage(), 500);
        }
    }
    
    private function obtenerCFDIs() {
        try {
            $limite = (int)($_GET['limite'] ?? 1000);
            $offset = (int)($_GET['offset'] ?? 0);
            
            $this->log("Obteniendo CFDIs: limite=$limite, offset=$offset");
            
            $sql = "SELECT *
                    FROM cfdi_emitidos 
                    ORDER BY fecha_expedicion DESC, created_at DESC 
                    LIMIT :limite OFFSET :offset";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $cfdis = $stmt->fetchAll();
            
            // Obtener total de registros
            $sqlCount = "SELECT COUNT(*) as total FROM cfdi_emitidos";
            $stmtCount = $this->pdo->prepare($sqlCount);
            $stmtCount->execute();
            $total = $stmtCount->fetch()['total'];
            
            $this->log("CFDIs obtenidos: " . count($cfdis) . " de $total total");
            
            $this->enviarRespuesta([
                'success' => true,
                'data' => $cfdis,
                'total' => $total,
                'limite' => $limite,
                'offset' => $offset
            ]);
            
        } catch (Exception $e) {
            $this->log("Error al obtener CFDIs: " . $e->getMessage());
            $this->enviarError("Error al obtener CFDIs: " . $e->getMessage(), 500);
        }
    }
    
    private function filtrarCFDIs() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON inválido: " . json_last_error_msg());
            }
            
            $this->log("Filtrando CFDIs con: " . json_encode($input));
            
            $statusSat = $input['status_sat'] ?? null;
            $tipoComprobante = $input['tipo_comprobante'] ?? null;
            $metodoPago = $input['metodo_pago'] ?? null;
            $fechaInicio = $input['fecha_inicio'] ?? null;
            $fechaFin = $input['fecha_fin'] ?? null;
            $search = $input['search'] ?? null;
            $limite = (int)($input['limite'] ?? 100);
            $offset = (int)($input['offset'] ?? 0);
            
            $condiciones = [];
            $parametros = [];
            
            // Filtro por status SAT
            if ($statusSat && in_array($statusSat, ['Vigente', 'Cancelado'])) {
                $condiciones[] = "status_sat = :status_sat";
                $parametros[':status_sat'] = $statusSat;
            }
            
            // Filtro por tipo de comprobante
            if ($tipoComprobante && in_array($tipoComprobante, ['I', 'E', 'N', 'P', 'T'])) {
                $condiciones[] = "tipo_comprobante = :tipo_comprobante";
                $parametros[':tipo_comprobante'] = $tipoComprobante;
            }
            
            // Filtro por método de pago
            if ($metodoPago && in_array($metodoPago, ['PUE', 'PPD'])) {
                $condiciones[] = "metodo_pago = :metodo_pago";
                $parametros[':metodo_pago'] = $metodoPago;
            }
            
            // Filtro por rango de fechas
            if ($fechaInicio) {
                $condiciones[] = "fecha_expedicion >= :fecha_inicio";
                $parametros[':fecha_inicio'] = $fechaInicio;
            }
            
            if ($fechaFin) {
                $condiciones[] = "fecha_expedicion <= :fecha_fin";
                $parametros[':fecha_fin'] = $fechaFin;
            }
            
            // Filtro por búsqueda general
            if ($search) {
                $condiciones[] = "(uuid LIKE :search OR rfc_receptor LIKE :search OR nombre_receptor LIKE :search OR serie LIKE :search OR folio LIKE :search)";
                $parametros[':search'] = "%{$search}%";
            }
            
            $whereClause = !empty($condiciones) ? 'WHERE ' . implode(' AND ', $condiciones) : '';
            
            $sql = "SELECT *
                    FROM cfdi_emitidos 
                    {$whereClause}
                    ORDER BY fecha_expedicion DESC, created_at DESC 
                    LIMIT :limite OFFSET :offset";
            
            $stmt = $this->pdo->prepare($sql);
            
            // Bind parámetros de filtros
            foreach ($parametros as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Bind parámetros de paginación
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $cfdis = $stmt->fetchAll();
            
            // Obtener total de registros filtrados
            $sqlCount = "SELECT COUNT(*) as total FROM cfdi_emitidos {$whereClause}";
            $stmtCount = $this->pdo->prepare($sqlCount);
            
            foreach ($parametros as $key => $value) {
                $stmtCount->bindValue($key, $value);
            }
            
            $stmtCount->execute();
            $total = $stmtCount->fetch()['total'];
            
            $this->log("Filtros aplicados: " . count($cfdis) . " resultados de $total total");
            
            $this->enviarRespuesta([
                'success' => true,
                'data' => $cfdis,
                'total' => $total,
                'filtros_aplicados' => $input,
                'limite' => $limite,
                'offset' => $offset
            ]);
            
        } catch (Exception $e) {
            $this->log("Error al filtrar CFDIs: " . $e->getMessage());
            $this->enviarError("Error al filtrar CFDIs: " . $e->getMessage(), 500);
        }
    }
    
    private function obtenerEstadisticas() {
        try {
            $this->log("Obteniendo estadísticas");
            
            $sql = "SELECT 
                        COUNT(*) as total_registros,
                        SUM(CASE WHEN status_sat = 'Vigente' THEN 1 ELSE 0 END) as vigentes,
                        SUM(CASE WHEN status_sat = 'Cancelado' THEN 1 ELSE 0 END) as cancelados,
                        SUM(total) as monto_total,
                        AVG(total) as promedio_total,
                        COUNT(DISTINCT rfc_receptor) as total_receptores,
                        MIN(fecha_expedicion) as fecha_mas_antigua,
                        MAX(fecha_expedicion) as fecha_mas_reciente
                    FROM cfdi_emitidos";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $estadisticas = $stmt->fetch();
            
            // Estadísticas por tipo de comprobante
            $sqlTipos = "SELECT 
                            tipo_comprobante,
                            COUNT(*) as cantidad,
                            SUM(total) as monto_total
                         FROM cfdi_emitidos
                         GROUP BY tipo_comprobante
                         ORDER BY cantidad DESC";
            
            $stmtTipos = $this->pdo->prepare($sqlTipos);
            $stmtTipos->execute();
            $porTipo = $stmtTipos->fetchAll();
            
            $this->log("Estadísticas obtenidas exitosamente");
            
            $this->enviarRespuesta([
                'success' => true,
                'estadisticas_generales' => $estadisticas,
                'por_tipo_comprobante' => $porTipo
            ]);
            
        } catch (Exception $e) {
            $this->log("Error al obtener estadísticas: " . $e->getMessage());
            $this->enviarError("Error al obtener estadísticas: " . $e->getMessage(), 500);
        }
    }
    
    private function enviarRespuesta($data, $codigo = 200) {
        // Limpiar cualquier output previo
        ob_clean();
        
        http_response_code($codigo);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
    
    private function enviarError($mensaje, $codigo = 400) {
        // Limpiar cualquier output previo
        ob_clean();
        
        http_response_code($codigo);
        echo json_encode([
            'success' => false,
            'error' => $mensaje,
            'codigo' => $codigo,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

// Manejar la solicitud
try {
    $api = new CFDIApi();
    $api->manejarSolicitud();
} catch (Exception $e) {
    // Limpiar buffer y enviar error JSON
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error crítico: ' . $e->getMessage(),
        'codigo' => 500,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
