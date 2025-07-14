-- Esquema de base de datos para CFDIs emitidos
-- Base de datos: bvd_emitidos

CREATE DATABASE IF NOT EXISTS bvd_emitidos 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE bvd_emitidos;

-- Tabla principal de CFDIs emitidos
CREATE TABLE IF NOT EXISTS cfdi_emitidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) UNIQUE NOT NULL COMMENT 'UUID del CFDI',
    fecha_expedicion DATE NOT NULL COMMENT 'Fecha de expedición del CFDI',
    fecha_certificacion DATETIME NULL COMMENT 'Fecha y hora de certificación',
    pac VARCHAR(100) NULL COMMENT 'Proveedor Autorizado de Certificación',
    rfc_emisor VARCHAR(20) NOT NULL COMMENT 'RFC del emisor',
    nombre_emisor VARCHAR(255) NULL COMMENT 'Nombre o razón social del emisor',
    rfc_receptor VARCHAR(20) NOT NULL COMMENT 'RFC del receptor',
    nombre_receptor VARCHAR(255) NULL COMMENT 'Nombre o razón social del receptor',
    uso_cfdi VARCHAR(10) NULL COMMENT 'Uso del CFDI',
    tipo_comprobante VARCHAR(5) NOT NULL COMMENT 'Tipo de comprobante (I,E,T,N,P)',
    metodo_pago VARCHAR(10) NULL COMMENT 'Método de pago',
    forma_pago VARCHAR(10) NULL COMMENT 'Forma de pago',
    version VARCHAR(10) NULL COMMENT 'Versión del CFDI',
    serie VARCHAR(20) NULL COMMENT 'Serie del comprobante',
    folio VARCHAR(50) NULL COMMENT 'Folio del comprobante',
    moneda VARCHAR(10) DEFAULT 'MXN' COMMENT 'Moneda del comprobante',
    tipo_cambio DECIMAL(10,6) DEFAULT 1.000000 COMMENT 'Tipo de cambio',
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal del comprobante',
    descuento DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Descuento aplicado',
    total DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total del comprobante',
    impuestos_trasladados DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Impuestos trasladados',
    impuestos_retenidos DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Impuestos retenidos',
    status_sat VARCHAR(20) DEFAULT 'Vigente' COMMENT 'Estatus en el SAT',
    status_cancelacion VARCHAR(50) NULL COMMENT 'Estatus de cancelación',
    fecha_cancelacion DATETIME NULL COMMENT 'Fecha de cancelación',
    motivo_cancelacion VARCHAR(255) NULL COMMENT 'Motivo de cancelación',
    folio_sustitucion VARCHAR(50) NULL COMMENT 'Folio de sustitución',
    efecto_comprobante VARCHAR(20) NULL COMMENT 'Efecto del comprobante',
    descripcion TEXT NULL COMMENT 'Descripción de productos/servicios',
    observaciones TEXT NULL COMMENT 'Observaciones adicionales',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
    
    -- Índices para optimizar consultas
    INDEX idx_uuid (uuid),
    INDEX idx_fecha_expedicion (fecha_expedicion),
    INDEX idx_rfc_emisor (rfc_emisor),
    INDEX idx_rfc_receptor (rfc_receptor),
    INDEX idx_tipo_comprobante (tipo_comprobante),
    INDEX idx_status_sat (status_sat),
    INDEX idx_serie_folio (serie, folio),
    INDEX idx_fecha_rfc (fecha_expedicion, rfc_emisor),
    INDEX idx_total (total),
    INDEX idx_created_at (created_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabla de CFDIs emitidos obtenidos del webservice';

-- Tabla de log de descargas
CREATE TABLE IF NOT EXISTS descarga_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NULL,
    fecha_inicial_consulta DATE NOT NULL,
    fecha_final_consulta DATE NOT NULL,
    rfc_consultado VARCHAR(20) NOT NULL,
    tipo_comprobante VARCHAR(5) NULL,
    registros_obtenidos INT DEFAULT 0,
    registros_insertados INT DEFAULT 0,
    registros_actualizados INT DEFAULT 0,
    registros_errores INT DEFAULT 0,
    status ENUM('iniciado', 'completado', 'error') DEFAULT 'iniciado',
    mensaje_error TEXT NULL,
    tiempo_ejecucion INT NULL COMMENT 'Tiempo en segundos',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_fecha_inicio (fecha_inicio),
    INDEX idx_rfc_consultado (rfc_consultado),
    INDEX idx_status (status)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log de descargas del webservice';

-- Vista para estadísticas rápidas
CREATE OR REPLACE VIEW v_estadisticas_cfdi AS
SELECT 
    COUNT(*) as total_registros,
    COUNT(DISTINCT rfc_emisor) as total_emisores,
    COUNT(DISTINCT rfc_receptor) as total_receptores,
    COUNT(DISTINCT tipo_comprobante) as tipos_comprobante,
    MIN(fecha_expedicion) as fecha_mas_antigua,
    MAX(fecha_expedicion) as fecha_mas_reciente,
    SUM(total) as suma_total,
    AVG(total) as promedio_total,
    SUM(CASE WHEN status_sat = 'Vigente' THEN 1 ELSE 0 END) as vigentes,
    SUM(CASE WHEN status_sat = 'Cancelado' THEN 1 ELSE 0 END) as cancelados
FROM cfdi_emitidos;

-- Vista para resumen por tipo de comprobante
CREATE OR REPLACE VIEW v_resumen_por_tipo AS
SELECT 
    tipo_comprobante,
    CASE 
        WHEN tipo_comprobante = 'I' THEN 'Ingreso'
        WHEN tipo_comprobante = 'E' THEN 'Egreso'
        WHEN tipo_comprobante = 'T' THEN 'Traslado'
        WHEN tipo_comprobante = 'N' THEN 'Nómina'
        WHEN tipo_comprobante = 'P' THEN 'Pago'
        ELSE 'Otro'
    END as tipo_descripcion,
    COUNT(*) as cantidad,
    SUM(total) as suma_total,
    AVG(total) as promedio_total,
    MIN(fecha_expedicion) as fecha_mas_antigua,
    MAX(fecha_expedicion) as fecha_mas_reciente
FROM cfdi_emitidos
GROUP BY tipo_comprobante
ORDER BY cantidad DESC;

-- Procedimiento almacenado para limpiar registros antiguos
DELIMITER //
CREATE PROCEDURE LimpiarRegistrosAntiguos(IN dias_antiguedad INT)
BEGIN
    DECLARE registros_eliminados INT DEFAULT 0;
    
    DELETE FROM cfdi_emitidos 
    WHERE fecha_expedicion < DATE_SUB(CURDATE(), INTERVAL dias_antiguedad DAY);
    
    SET registros_eliminados = ROW_COUNT();
    
    SELECT CONCAT('Se eliminaron ', registros_eliminados, ' registros anteriores a ', dias_antiguedad, ' días') as resultado;
END //
DELIMITER ;
