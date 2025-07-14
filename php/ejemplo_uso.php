<?php
/**
 * Ejemplo de uso del sistema de descarga masiva de CFDIs
 */

require_once 'config.php';
require_once 'logger.php';
require_once 'cfdi_webservice_fixed.php';

// Configurar el logger
$logger = new Logger(__DIR__ . '/logs/ejemplo_' . date('Y-m-d') . '.log', 'DEBUG');

echo "🚀 Iniciando ejemplo de descarga masiva de CFDIs\n";
echo "=" . str_repeat("=", 60) . "\n\n";

try {
    // Parámetros de ejemplo
    $parametros = [
        'fechaInicial' => '2025/01/01',
        'fechaFinal' => '2025/01/02',
        'rfc' => 'GAF220603TC4',
        'tipoComprobante' => null // null para todos los tipos
    ];
    
    echo "📋 Parámetros de descarga:\n";
    foreach ($parametros as $key => $value) {
        echo "   • {$key}: " . ($value ?: 'Todos') . "\n";
    }
    echo "\n";
    
    $logger->startProcess('Descarga masiva CFDIs', $parametros);
    $startTime = microtime(true);
    
    // Crear instancia del manager
    $manager = new CFDIWebserviceManager();
    
    // Ejecutar descarga masiva
    $resultado = $manager->ejecutarDescargaMasiva(
        $parametros['fechaInicial'],
        $parametros['fechaFinal'],
        $parametros['rfc'],
        $parametros['tipoComprobante']
    );
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    if ($resultado) {
        echo "\n🎉 ¡Descarga completada exitosamente!\n";
        echo "⏱️ Tiempo total: {$duration} segundos\n\n";
        
        echo "📊 Resultados del proceso:\n";
        echo "   • Registros insertados: {$resultado['insertados']}\n";
        echo "   • Registros actualizados: {$resultado['actualizados']}\n";
        echo "   • Errores encontrados: {$resultado['errores']}\n\n";
        
        // Obtener estadísticas de la base de datos
        echo "📈 Estadísticas de la base de datos:\n";
        $stats = $manager->obtenerEstadisticas();
        
        if ($stats) {
            echo "   • Total de registros: " . number_format($stats['total_registros']) . "\n";
            echo "   • Total de emisores: " . number_format($stats['total_emisores']) . "\n";
            echo "   • Total de receptores: " . number_format($stats['total_receptores']) . "\n";
            echo "   • Fecha más antigua: {$stats['fecha_mas_antigua']}\n";
            echo "   • Fecha más reciente: {$stats['fecha_mas_reciente']}\n";
            echo "   • Suma total: $" . number_format($stats['suma_total'], 2) . "\n";
            echo "   • Promedio por CFDI: $" . number_format($stats['promedio_total'], 2) . "\n";
        }
        
        $logger->endProcess('Descarga masiva CFDIs', $duration, $resultado);
        $logger->stats($stats);
        
        // Ejemplo de consultas adicionales
        echo "\n🔍 Ejemplos de consultas:\n";
        ejemplosConsultas($manager);
        
    } else {
        echo "\n❌ La descarga falló. Revisa los logs para más detalles.\n";
        $logger->failProcess('Descarga masiva CFDIs', 'Proceso falló sin excepción');
    }
    
} catch (Exception $e) {
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "\n❌ Error durante la descarga: " . $e->getMessage() . "\n";
    echo "⏱️ Tiempo antes del error: {$duration} segundos\n";
    
    $logger->failProcess('Descarga masiva CFDIs', $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'duration' => $duration
    ]);
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ Ejemplo completado\n";

/**
 * Ejemplos de consultas a la base de datos
 */
function ejemplosConsultas($manager) {
    try {
        $db = getDBConnection();
        
        // 1. CFDIs por tipo de comprobante
        echo "\n   📋 CFDIs por tipo de comprobante:\n";
        $sql = "SELECT tipo_comprobante, COUNT(*) as cantidad, SUM(total) as suma_total 
                FROM cfdi_emitidos 
                GROUP BY tipo_comprobante 
                ORDER BY cantidad DESC";
        $stmt = $db->query($sql);
        while ($row = $stmt->fetch()) {
            echo "      • {$row['tipo_comprobante']}: " . number_format($row['cantidad']) . 
                 " CFDIs ($" . number_format($row['suma_total'], 2) . ")\n";
        }
        
        // 2. Top 5 receptores por monto
        echo "\n   🏆 Top 5 receptores por monto:\n";
        $sql = "SELECT nombre_receptor, rfc_receptor, COUNT(*) as cantidad, SUM(total) as suma_total 
                FROM cfdi_emitidos 
                GROUP BY rfc_receptor, nombre_receptor 
                ORDER BY suma_total DESC 
                LIMIT 5";
        $stmt = $db->query($sql);
        $position = 1;
        while ($row = $stmt->fetch()) {
            echo "      {$position}. {$row['nombre_receptor']} ({$row['rfc_receptor']})\n";
            echo "         " . number_format($row['cantidad']) . " CFDIs - $" . number_format($row['suma_total'], 2) . "\n";
            $position++;
        }
        
        // 3. CFDIs por mes
        echo "\n   📅 CFDIs por mes:\n";
        $sql = "SELECT DATE_FORMAT(fecha_expedicion, '%Y-%m') as mes, 
                       COUNT(*) as cantidad, 
                       SUM(total) as suma_total 
                FROM cfdi_emitidos 
                GROUP BY DATE_FORMAT(fecha_expedicion, '%Y-%m') 
                ORDER BY mes DESC 
                LIMIT 6";
        $stmt = $db->query($sql);
        while ($row = $stmt->fetch()) {
            echo "      • {$row['mes']}: " . number_format($row['cantidad']) . 
                 " CFDIs ($" . number_format($row['suma_total'], 2) . ")\n";
        }
        
        // 4. Status de CFDIs
        echo "\n   📊 Status de CFDIs:\n";
        $sql = "SELECT status_sat, COUNT(*) as cantidad 
                FROM cfdi_emitidos 
                GROUP BY status_sat 
                ORDER BY cantidad DESC";
        $stmt = $db->query($sql);
        while ($row = $stmt->fetch()) {
            echo "      • {$row['status_sat']}: " . number_format($row['cantidad']) . " CFDIs\n";
        }
        
    } catch (Exception $e) {
        echo "      ❌ Error en consultas de ejemplo: " . $e->getMessage() . "\n";
    }
}

/**
 * Función para mostrar ayuda de uso
 */
function mostrarAyuda() {
    echo "\n📖 Ayuda de uso:\n";
    echo "   Este script demuestra cómo usar el sistema de descarga masiva de CFDIs.\n\n";
    echo "   Parámetros que puedes modificar:\n";
    echo "   • fechaInicial: Fecha de inicio en formato YYYY/MM/DD\n";
    echo "   • fechaFinal: Fecha de fin en formato YYYY/MM/DD\n";
    echo "   • rfc: RFC del emisor\n";
    echo "   • tipoComprobante: I (Ingreso), E (Egreso), T (Traslado), N (Nómina), P (Pago)\n\n";
    echo "   Archivos importantes:\n";
    echo "   • config.php: Configuración general del sistema\n";
    echo "   • logger.php: Sistema de logging\n";
    echo "   • cfdi_webservice_fixed.php: Lógica principal de descarga\n\n";
    echo "   Logs generados en: " . __DIR__ . "/logs/\n";
    echo "   Base de datos: " . DB_NAME . " (tabla: cfdi_emitidos)\n\n";
}

// Mostrar ayuda si se ejecuta con parámetro --help
if (isset($argv[1]) && $argv[1] === '--help') {
    mostrarAyuda();
}
?>
