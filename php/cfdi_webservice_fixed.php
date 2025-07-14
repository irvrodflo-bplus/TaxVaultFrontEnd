<?php
/**
 * Función corregida para obtener datos del webservice de descarga masiva de CFDIs
 * CORRIGE LOS ERRORES DE PARÁMETROS SQL
 */

class CFDIWebserviceManager {
    private $db;
    private $apiUrl = 'https://sistema.bovedafacturalo.com/ws/doReporte';
    private $apiKey = '0401ef52-e39a-4859-a684-67612cdec64b';
    
    public function __construct() {
        $this->connectDatabase();
    }
    
    /**
     * Conectar a la base de datos MySQL
     */
    private function connectDatabase() {
        try {
            $this->db = new PDO(
                "mysql:host=localhost;dbname=tax_vault;charset=utf8mb4",
                "root",
                "1234",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            // echo "✅ Conexión a base de datos establecida\n";
        } catch (PDOException $e) {
            die("❌ Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
    
    /**
     * Crear tabla si no existe basada en la estructura del CSV
     */
    public function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS cfdi_emitidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            uuid VARCHAR(255) UNIQUE,
            uuid_relacionado VARCHAR(50),
            fecha_expedicion DATE,
            fecha_certificacion DATETIME,
            pac VARCHAR(100),
            rfc_emisor VARCHAR(20),
            nombre_emisor VARCHAR(255),
            rfc_receptor VARCHAR(20),
            nombre_receptor VARCHAR(255),
            uso_cfdi VARCHAR(10),
            tipo_comprobante VARCHAR(5),
            metodo_pago VARCHAR(10),
            forma_pago VARCHAR(10),
            version VARCHAR(10),
            serie VARCHAR(20),
            folio VARCHAR(50),
            moneda VARCHAR(10),
            tipo_cambio DECIMAL(10,6),
            subtotal DECIMAL(15,2),
            descuento DECIMAL(15,2),
            total DECIMAL(15,2),
            IVATrasladado0 DECIMAL(15,2),
            IVATrasladado16 DECIMAL(15,2),
            IVAExento DECIMAL(15,2),
            IVARetenido DECIMAL(15,2),
            ISRRetenido DECIMAL(15,2),
            IEPSTrasladado DECIMAL(15,2),
            IEPSTrasladado0 DECIMAL(15,2),
            IEPSTrasladado45 DECIMAL(15,2),
            IEPSTrasladado54 DECIMAL(15,2),
            IEPSTrasladado66 DECIMAL(15,2),
            IEPSRetenido DECIMAL(15,2),
            LocalRetenido DECIMAL(15,2),
            LocalTrasladado DECIMAL(15,2),
            status_sat VARCHAR(20),
            descripcion TEXT,
            observaciones TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_uuid (uuid),
            INDEX idx_fecha_expedicion (fecha_expedicion),
            INDEX idx_rfc_emisor (rfc_emisor),
            INDEX idx_rfc_receptor (rfc_receptor),
            INDEX idx_status_sat (status_sat)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->exec($sql);
            // echo "✅ Tabla cfdi_emitidos creada o verificada\n";
        } catch (PDOException $e) {
            // echo "❌ Error al crear tabla: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Obtener datos del webservice de descarga masiva
     */
    public function obtenerDatosWebservice($fechaInicial, $fechaFinal, $rfc, $tipoComprobante = null, $layout = 'GBM170505Q78_1') {
        // echo "🔄 Iniciando descarga de datos del webservice...\n";
        // echo "📅 Período: {$fechaInicial} - {$fechaFinal}\n";
        // echo "🏢 RFC: {$rfc}\n";
        
        // Preparar datos para el POST
        $postData = [
            'fInicial' => $fechaInicial,
            'fFinal' => $fechaFinal,
            'rfc' => $rfc,
            'tipo' => 'emitidas',
            'layout' => $layout,
            'apikey' => $this->apiKey
        ];
        
        // Agregar tipo de comprobante si se especifica
        if ($tipoComprobante) {
            $postData['tipoDeComprobante'] = $tipoComprobante;
        }
        
        // Configurar cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300, // 5 minutos timeout
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: CFDI-Downloader/1.0'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Error cURL: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Error HTTP: " . $httpCode . " - Respuesta: " . $response);
        }
        
        // echo "✅ Datos obtenidos del webservice exitosamente\n";
        return $this->procesarRespuestaWebservice($response);
    }
    
    /**
     * Procesar la respuesta del webservice
     */
    private function procesarRespuestaWebservice($response) {
        // Detectar el formato de respuesta
        $data = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Respuesta en JSON
            return $this->procesarRespuestaJSON($data);
        } else {
            // Asumir que es CSV
            return $this->procesarRespuestaCSV($response);
        }
    }
    
    /**
     * Procesar respuesta JSON
     */
    private function procesarRespuestaJSON($data) {
        // echo "📊 Procesando respuesta JSON...\n";
        
        if (isset($data['error'])) {
            throw new Exception("Error del webservice: " . $data['error']);
        }
        
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }
        
        if (isset($data['cfdi']) && is_array($data['cfdi'])) {
            return $data['cfdi'];
        }
        
        // Si la respuesta completa es un array de CFDIs
        if (is_array($data) && !empty($data)) {
            return $data;
        }
        
        throw new Exception("Formato de respuesta JSON no reconocido");
    }
    
    /**
     * Procesar respuesta CSV
     */
    private function procesarRespuestaCSV($csvContent) {
        // echo "📊 Procesando respuesta CSV...\n";
        
        $lines = explode("\n", trim($csvContent));
        if (empty($lines)) {
            throw new Exception("CSV vacío recibido");
        }
        
        // Primera línea contiene los headers
        $headers = str_getcsv(array_shift($lines));
        $data = [];
        
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            
            $values = str_getcsv($line);
            if (count($values) === count($headers)) {
                $data[] = array_combine($headers, $values);
            }
        }
        
        return $data;
    }
    
    /**
     * Mapear campos del webservice a campos de la base de datos
     */
    private function mapearCampos($registro) {
        // Mapeo de campos del webservice a campos de la BD
        $mapeo = [
            'UUID' => 'uuid',
            'UUIDsRelacionados' => 'uuid_relacionado',
            'FechaEmision' => 'fecha_expedicion',
            'FechaCertificacion' => 'fecha_certificacion',
            'PACCertifico' => 'pac',
            'RfcEmisor' => 'rfc_emisor',
            'RazonEmisor' => 'nombre_emisor',
            'RfcReceptor' => 'rfc_receptor',
            'RazonReceptor' => 'nombre_receptor',
            'UsoCFDI' => 'uso_cfdi',
            'Tipo' => 'tipo_comprobante',
            'MetodoPago' => 'metodo_pago',
            'FormaPago' => 'forma_pago',
            'Version' => 'version',
            'Serie' => 'serie',
            'Folio' => 'folio',
            'Moneda' => 'moneda',
            'TipoCambio' => 'tipo_cambio',
            'Subtotal' => 'subtotal',
            'Descuento' => 'descuento',
            'Total' => 'total',
            'IVATrasladado0' => 'IVATrasladado0',
            'IVATrasladado16' => 'IVATrasladado16',
            'IVAExento' => 'IVAExento',
            'IVARetenido' => 'IVARetenido',
            'ISRRetenido' => 'ISRRetenido',
            'IEPSTrasladado' => 'IEPSTrasladado',
            'IEPSTrasladado0' => 'IEPSTrasladado0',
            'IEPSTrasladado45' => 'IEPSTrasladado45',
            'IEPSTrasladado54' => 'IEPSTrasladado54',
            'IEPSTrasladado66' => 'IEPSTrasladado66',
            'IEPSRetenido' => 'IEPSRetenido',
            'LocalRetenido' => 'LocalRetenido',
            'LocalTrasladado' => 'LocalTrasladado',
            'Estado' => 'status_sat',
            'Descripcion' => 'descripcion',
            'Observaciones' => 'observaciones'
        ];
        
        // Inicializar array con todos los campos necesarios
        $registroMapeado = [
            'uuid' => null,
            'uuid_relacionado' => null,
            'fecha_expedicion' => null,
            'fecha_certificacion' => null,
            'pac' => null,
            'rfc_emisor' => null,
            'nombre_emisor' => null,
            'rfc_receptor' => null,
            'nombre_receptor' => null,
            'uso_cfdi' => null,
            'tipo_comprobante' => null,
            'metodo_pago' => null,
            'forma_pago' => null,
            'version' => null,
            'serie' => null,
            'folio' => null,
            'moneda' => 'MXN',
            'tipo_cambio' => 1.000000,
            'subtotal' => 0.00,
            'descuento' => 0.00,
            'total' => 0.00,
            'IVATrasladado0' => 0.00,
            'IVATrasladado16' => 0.00,
            'IVAExento' => 0.00,
            'IVARetenido' => 0.00,
            'ISRRetenido' => 0.00,
            'IEPSTrasladado' => 0.00,
            'IEPSTrasladado0' => 0.00,
            'IEPSTrasladado45' => 0.00,
            'IEPSTrasladado54' => 0.00,
            'IEPSTrasladado66' => 0.00,
            'IEPSRetenido' => 0.00,
            'LocalRetenido' => 0.00,
            'LocalTrasladado' => 0.00,
            'status_sat' => 'Vigente',
            'descripcion' => null,
            'observaciones' => null
        ];
        
        // Mapear los campos del registro
        foreach ($registro as $campoOriginal => $valor) {
            $campoBD = $mapeo[$campoOriginal] ?? strtolower($campoOriginal);
            if (array_key_exists($campoBD, $registroMapeado)) {
                $registroMapeado[$campoBD] = $this->limpiarValor($valor, $campoBD);
            }
        }
        
        return $registroMapeado;
    }
    
    /**
     * Limpiar y formatear valores según el tipo de campo
     */
    private function limpiarValor($valor, $campo) {
        if ($valor === null || $valor === '') {
            // Valores por defecto para campos que no pueden ser null
            if (in_array($campo, ['moneda'])) return 'MXN';
            if (in_array($campo, ['tipo_cambio'])) return 1.000000;
            if (in_array($campo, ['subtotal', 'descuento', 'total', 
                'IVATrasladado0', 'IVATrasladado16', 'IVAExento', 'IVARetenido', 'ISRRetenido', 
                'IEPSTrasladado', 'IEPSTrasladado0', 'IEPSTrasladado45', 'IEPSTrasladado54', 'IEPSTrasladado66', 
                'IEPSRetenido', 'LocalRetenido', 'LocalTrasladado'])) return 0.00;
            if (in_array($campo, ['status_sat'])) return 'Vigente';
            return null;
        }
        
        // Campos de fecha
        if (in_array($campo, ['fecha_expedicion', 'fecha_certificacion'])) {
            return $this->formatearFecha($valor);
        }
        
        // Campos numéricos
        if (in_array($campo, ['tipo_cambio', 'subtotal', 'descuento', 'total', 
            'IVATrasladado0', 'IVATrasladado16', 'IVAExento', 'IVARetenido', 'ISRRetenido', 
            'IEPSTrasladado', 'IEPSTrasladado0', 'IEPSTrasladado45', 'IEPSTrasladado54', 'IEPSTrasladado66', 
            'IEPSRetenido', 'LocalRetenido', 'LocalTrasladado'])) {
            return $this->formatearNumero($valor);
        }
        
        // Campos de texto
        return trim($valor);
    }
    
    /**
     * Formatear fecha para MySQL
     */
    private function formatearFecha($fecha) {
        if (empty($fecha)) return null;
        
        // Intentar diferentes formatos de fecha
        $formatos = [
            'Y-m-d\TH:i:s', // Formato ISO 8601 (2025-06-06T00:19:13)
            'Y-m-d H:i:s',
            'Y-m-d',
            'd/m/Y H:i:s',
            'd/m/Y',
            'Y/m/d H:i:s',
            'Y/m/d'
        ];
        
        foreach ($formatos as $formato) {
            $fechaObj = DateTime::createFromFormat($formato, $fecha);
            if ($fechaObj !== false) {
                return $fechaObj->format('Y-m-d H:i:s');
            }
        }
        
        // Si no coincide con ningún formato, intentar con strtotime como último recurso
        $timestamp = strtotime($fecha);
        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }
        
        return null;
    }
    
    /**
     * Formatear número para MySQL
     */
    private function formatearNumero($numero) {
        if (empty($numero)) return 0;
        
        // Remover caracteres no numéricos excepto punto y coma
        $numero = preg_replace('/[^0-9.,\-]/', '', $numero);
        
        // Convertir coma decimal a punto
        $numero = str_replace(',', '.', $numero);
        
        return floatval($numero);
    }
    
    /**
     * FUNCIÓN CORREGIDA: Insertar o actualizar registros en la base de datos
     * SOLUCIONA EL ERROR DE PARÁMETROS SQL
     */
    public function insertarRegistros($registros) {
        // echo "💾 Insertando " . count($registros) . " registros en la base de datos...\n";
        
        // SQL CORREGIDO - Todos los placeholders coinciden con los parámetros
        $sql = "INSERT INTO cfdi_emitidos (
            uuid, uuid_relacionado, fecha_expedicion, fecha_certificacion, pac, rfc_emisor, nombre_emisor,
            rfc_receptor, nombre_receptor, uso_cfdi, tipo_comprobante, metodo_pago,
            forma_pago, version, serie, folio, moneda, tipo_cambio, subtotal,
            descuento, total, 
            IVATrasladado0, IVATrasladado16, IVAExento, IVARetenido, ISRRetenido,
            IEPSTrasladado, IEPSTrasladado0, IEPSTrasladado45, IEPSTrasladado54, IEPSTrasladado66,
            IEPSRetenido, LocalRetenido, LocalTrasladado,
            status_sat, descripcion, observaciones
        ) VALUES (
            :uuid, :uuid_relacionado, :fecha_expedicion, :fecha_certificacion, :pac, :rfc_emisor, :nombre_emisor,
            :rfc_receptor, :nombre_receptor, :uso_cfdi, :tipo_comprobante, :metodo_pago,
            :forma_pago, :version, :serie, :folio, :moneda, :tipo_cambio, :subtotal,
            :descuento, :total, 
            :IVATrasladado0, :IVATrasladado16, :IVAExento, :IVARetenido, :ISRRetenido,
            :IEPSTrasladado, :IEPSTrasladado0, :IEPSTrasladado45, :IEPSTrasladado54, :IEPSTrasladado66,
            :IEPSRetenido, :LocalRetenido, :LocalTrasladado,
            :status_sat, :descripcion, :observaciones
        )
         ON DUPLICATE KEY UPDATE
            uuid_relacionado = VALUES(uuid_relacionado),
            fecha_certificacion = VALUES(fecha_certificacion),
            pac = VALUES(pac),
            nombre_emisor = VALUES(nombre_emisor),
            nombre_receptor = VALUES(nombre_receptor),
            uso_cfdi = VALUES(uso_cfdi),
            metodo_pago = VALUES(metodo_pago),
            forma_pago = VALUES(forma_pago),
            version = VALUES(version),
            moneda = VALUES(moneda),
            tipo_cambio = VALUES(tipo_cambio),
            subtotal = VALUES(subtotal),
            descuento = VALUES(descuento),
            total = VALUES(total),
            IVATrasladado0 = VALUES(IVATrasladado0),
            IVATrasladado16 = VALUES(IVATrasladado16),
            IVAExento = VALUES(IVAExento),
            IVARetenido = VALUES(IVARetenido),
            ISRRetenido = VALUES(ISRRetenido),
            IEPSTrasladado = VALUES(IEPSTrasladado),
            IEPSTrasladado0 = VALUES(IEPSTrasladado0),
            IEPSTrasladado45 = VALUES(IEPSTrasladado45),
            IEPSTrasladado54 = VALUES(IEPSTrasladado54),
            IEPSTrasladado66 = VALUES(IEPSTrasladado66),
            IEPSRetenido = VALUES(IEPSRetenido),
            LocalRetenido = VALUES(LocalRetenido),
            LocalTrasladado = VALUES(LocalTrasladado),
            status_sat = VALUES(status_sat),
            descripcion = VALUES(descripcion),
            observaciones = VALUES(observaciones)";
        
        $stmt = $this->db->prepare($sql);
        
        $insertados = 0;
        $actualizados = 0;
        $errores = 0;
        
        foreach ($registros as $registro) {
            try {
                $registroMapeado = $this->mapearCampos($registro);
                
                // VALIDACIÓN: Verificar que todos los parámetros estén presentes
                $parametrosRequeridos = [
                    'uuid', 'uuid_relacionado', 'fecha_expedicion', 'fecha_certificacion', 'pac', 'rfc_emisor', 'nombre_emisor',
                    'rfc_receptor', 'nombre_receptor', 'uso_cfdi', 'tipo_comprobante', 'metodo_pago',
                    'forma_pago', 'version', 'serie', 'folio', 'moneda', 'tipo_cambio', 'subtotal',
                    'descuento', 'total', 
                    'IVATrasladado0', 'IVATrasladado16', 'IVAExento', 'IVARetenido', 'ISRRetenido',
                    'IEPSTrasladado', 'IEPSTrasladado0', 'IEPSTrasladado45', 'IEPSTrasladado54', 'IEPSTrasladado66',
                    'IEPSRetenido', 'LocalRetenido', 'LocalTrasladado',
                    'status_sat', 'descripcion', 'observaciones'
                ];
                
                // Asegurar que todos los parámetros existan
                foreach ($parametrosRequeridos as $param) {
                    if (!array_key_exists($param, $registroMapeado)) {
                        $registroMapeado[$param] = null;
                    }
                }
                
                $stmt->execute($registroMapeado);
                
                if ($stmt->rowCount() > 0) {
                    $insertados++;
                } else {
                    $actualizados++;
                }
                
            } catch (PDOException $e) {
                $errores++;
                // echo "⚠️ Error al insertar registro UUID: " . ($registro['UUID'] ?? 'N/A') . " - " . $e->getMessage() . "\n";
                
                // Debug: Mostrar información del error
                if ($errores <= 3) { // Solo mostrar los primeros 3 errores para no saturar
                    // echo "🔍 Debug - Parámetros enviados:\n";
                    foreach ($registroMapeado as $key => $value) {
                        // echo "   {$key}: " . (is_null($value) ? 'NULL' : $value) . "\n";
                    }
                    // echo "\n";
                }
            }
        }
        
        // echo "✅ Proceso completado:\n";
        // echo "   📥 Insertados: {$insertados}\n";
        // echo "   🔄 Actualizados: {$actualizados}\n";
        // echo "   ❌ Errores: {$errores}\n";
        
        return [
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'errores' => $errores
        ];
    }
    
    /**
     * Función principal para ejecutar todo el proceso
     */
    public function ejecutarDescargaMasiva($fechaInicial, $fechaFinal, $rfc, $tipoComprobante = null) {
        try {
            // echo "🚀 Iniciando proceso de descarga masiva de CFDIs\n";
            // echo "=" . str_repeat("=", 50) . "\n";
            
            // Crear tabla si no existe
            $this->createTableIfNotExists();
            
            // Obtener datos del webservice
            $registros = $this->obtenerDatosWebservice($fechaInicial, $fechaFinal, $rfc, $tipoComprobante);
            
            if (empty($registros)) {
                // echo "⚠️ No se encontraron registros para el período especificado\n";
                return false;
            }
            
            // echo "📊 Se obtuvieron " . count($registros) . " registros\n";
            
            // Insertar registros en la base de datos
            $resultado = $this->insertarRegistros($registros);
            
            // echo "=" . str_repeat("=", 50) . "\n";
            // echo "🎉 Proceso completado exitosamente\n";
            
            return $resultado;
            
        } catch (Exception $e) {
            // echo "❌ Error en el proceso: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de la base de datos
     */
    public function obtenerEstadisticas() {
        $sql = "SELECT 
            COUNT(*) as total_registros,
            COUNT(DISTINCT rfc_emisor) as total_emisores,
            COUNT(DISTINCT rfc_receptor) as total_receptores,
            MIN(fecha_expedicion) as fecha_mas_antigua,
            MAX(fecha_expedicion) as fecha_mas_reciente,
            SUM(total) as suma_total,
            AVG(total) as promedio_total
        FROM cfdi_emitidos";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
}

// Función de uso simple
function descargarCFDIsEmitidos($fechaInicial, $fechaFinal, $rfc, $tipoComprobante = null) {
    $manager = new CFDIWebserviceManager();
    return $manager->ejecutarDescargaMasiva($fechaInicial, $fechaFinal, $rfc, $tipoComprobante);
}

// Ejemplo de uso
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    // echo "🔧 Ejecutando ejemplo de descarga masiva...\n\n";
    
    // Parámetros de ejemplo
    $fechaInicial = '2025/01/01';
    $fechaFinal = '2025/01/31';
    $rfc = 'GAF220603TC4';
    
    // Ejecutar descarga
    $resultado = descargarCFDIsEmitidos($fechaInicial, $fechaFinal, $rfc);
    
    if ($resultado) {
        // echo "\n📈 Estadísticas finales:\n";
        $manager = new CFDIWebserviceManager();
        $stats = $manager->obtenerEstadisticas();
        
        foreach ($stats as $key => $value) {
            // echo "   {$key}: {$value}\n";
        }
    }
}
?>
