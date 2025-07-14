// ===== Export Manager - Handles Excel export functionality =====

// Declare variables before using them
const XLSX = window.XLSX // Assuming SheetJS library is loaded globally
const totalesDataByType = window.totalesDataByType // Assuming this data is loaded globally
const statusNames = window.statusNames // Assuming this data is loaded globally
const cfdisDataByType = window.cfdisDataByType // Assuming this data is loaded globally

function ExportManager() {
  this.initializeExportButton()
  this.isExporting = false
}

/**
 * Initialize export button event listener
 */
ExportManager.prototype.initializeExportButton = function () {
  const exportBtn = document.getElementById("exportBtn")
  if (exportBtn) {
    
    exportBtn.addEventListener("click", () => {
      this.exportToExcel()
    })
  }
}

/**
 * Main export function
 */
ExportManager.prototype.exportToExcel = function () {
  if (this.isExporting) {
    console.log("Export already in progress")
    return
  }
  
  try {
    this.isExporting = true
    this.showExportProgress()

    const currentDate = new Date().toLocaleDateString("es-MX")
    const currentStatus = window.app ? window.app.currentStatus : "ingreso"

    // Check if XLSX is available
    if (typeof XLSX === "undefined") {
      throw new Error("SheetJS library not loaded")
    }

    // Create workbook
    const wb = XLSX.utils.book_new()

    // Add totals sheet
    this.addTotalsSheet(wb, currentStatus)

    // Add CFDIs sheet
    this.addCFDIsSheet(wb, currentStatus)

    // Add summary sheet
    this.addSummarySheet(wb, currentStatus)

    // Generate filename
    const fileName = this.generateFileName(currentStatus, currentDate)

    // Download file
    XLSX.writeFile(wb, fileName)

    // Show success message
    this.showExportSuccess(fileName)
  } catch (error) {
    console.error("Export error:", error)
    this.showExportError(error.message)
  } finally {
    this.isExporting = false
    this.hideExportProgress()
  }
}

/**
 * Add totals sheet to workbook
 */
ExportManager.prototype.addTotalsSheet = function (wb, currentStatus) {
  const totalesData = totalesDataByType[currentStatus]
  if (!totalesData) return

  const totalesWS = XLSX.utils.aoa_to_sheet([
    ["Dashboard de CFDIs - Totales"],
    ["Tipo de Comprobante:", statusNames[currentStatus]],
    ["Fecha de Exportación:", new Date().toLocaleDateString("es-MX")],
    [""],
    ["Tipo", "Conteo de CFDIs", "Traslado IVA", "Subtotal", "Descuento", "Neto", "Total"],
    [
      "Periodo",
      totalesData.periodo.conteo,
      totalesData.periodo.trasladoIva,
      totalesData.periodo.subtotal,
      totalesData.periodo.descuento,
      totalesData.periodo.neto,
      totalesData.periodo.total,
    ],
    [
      "Acumulado",
      totalesData.acumulado.conteo,
      totalesData.acumulado.trasladoIva,
      totalesData.acumulado.subtotal,
      totalesData.acumulado.descuento,
      totalesData.acumulado.neto,
      totalesData.acumulado.total,
    ],
  ])

  // Apply formatting
  this.formatTotalsSheet(totalesWS)

  XLSX.utils.book_append_sheet(wb, totalesWS, "Totales")
}

/**
 * Add CFDIs sheet to workbook
 */
ExportManager.prototype.addCFDIsSheet = function (wb, currentStatus) {
  const cfdisData = cfdisDataByType[currentStatus] || []
  if (cfdisData.length === 0) return

  const headers = [
    "UUID",
    "Fecha Expedición",
    "Serie",
    "Folio",
    "Receptor",
    "Total",
    "Saldo de la Factura",
    "CFDIs de Pago Relacionados",
    "CFDIs de Egreso Relacionados",
  ]

  const cfdisArray = [
    ["CFDIs " + statusNames[currentStatus] + " - Detalle"],
    ["Exportado el: " + new Date().toLocaleDateString("es-MX") + " " + new Date().toLocaleTimeString("es-MX")],
    ["Total de registros: " + cfdisData.length],
    [""],
    headers,
  ]

  // Add data rows
  cfdisData.forEach((cfdi) => {
    cfdisArray.push([
      cfdi.uuid || "",
      cfdi.fecha,
      cfdi.serie,
      cfdi.folio,
      cfdi.receptor,
      cfdi.total,
      cfdi.saldo,
      cfdi.pagoRelacionados,
      cfdi.egresoRelacionados,
    ])
  })

  const cfdisWS = XLSX.utils.aoa_to_sheet(cfdisArray)

  // Apply formatting
  this.formatCFDIsSheet(cfdisWS, cfdisData.length)

  XLSX.utils.book_append_sheet(wb, cfdisWS, "CFDIs " + statusNames[currentStatus])
}

/**
 * Add summary sheet to workbook
 */
ExportManager.prototype.addSummarySheet = function (wb, currentStatus) {
  const allData = Object.keys(statusNames).map((status) => {
    const data = totalesDataByType[status]
    const cfdisCount = cfdisDataByType[status] ? cfdisDataByType[status].length : 0

    return [statusNames[status], data ? data.periodo.conteo : 0, data ? data.periodo.total : "$0.00", cfdisCount]
  })

  const summaryArray = [
    ["Resumen General de CFDIs"],
    ["Generado el: " + new Date().toLocaleDateString("es-MX") + " " + new Date().toLocaleTimeString("es-MX")],
    [""],
    ["Tipo de Comprobante", "Conteo Período", "Total Período", "Registros en Sistema"],
  ]
    .concat(allData)
    .concat([
      [""],
      ["Notas:"],
      ["- Los datos mostrados corresponden al período seleccionado"],
      ["- Los totales pueden incluir CFDIs vigentes y cancelados"],
      ["- Para más detalles, consulte las pestañas específicas"],
    ])

  const summaryWS = XLSX.utils.aoa_to_sheet(summaryArray)

  // Apply formatting
  this.formatSummarySheet(summaryWS)

  XLSX.utils.book_append_sheet(wb, summaryWS, "Resumen")
}

/**
 * Format totals sheet
 */
ExportManager.prototype.formatTotalsSheet = (ws) => {
  // Set column widths
  ws["!cols"] = [
    { wch: 15 }, // Tipo
    { wch: 15 }, // Conteo
    { wch: 18 }, // Traslado IVA
    { wch: 18 }, // Subtotal
    { wch: 15 }, // Descuento
    { wch: 18 }, // Neto
    { wch: 18 }, // Total
  ]

  // Set row heights
  ws["!rows"] = [
    { hpt: 20 }, // Title
    { hpt: 15 }, // Subtitle
    { hpt: 15 }, // Date
    { hpt: 10 }, // Empty
    { hpt: 18 }, // Headers
  ]
}

/**
 * Format CFDIs sheet
 */
ExportManager.prototype.formatCFDIsSheet = (ws, dataLength) => {
  // Set column widths
  ws["!cols"] = [
    { wch: 38 }, // UUID
    { wch: 15 }, // Fecha
    { wch: 10 }, // Serie
    { wch: 12 }, // Folio
    { wch: 35 }, // Receptor
    { wch: 15 }, // Total
    { wch: 15 }, // Saldo
    { wch: 25 }, // Pago Relacionados
    { wch: 25 }, // Egreso Relacionados
  ]

  // Freeze panes
  ws["!freeze"] = { xSplit: 0, ySplit: 5 }
}

/**
 * Format summary sheet
 */
ExportManager.prototype.formatSummarySheet = (ws) => {
  // Set column widths
  ws["!cols"] = [
    { wch: 25 }, // Tipo
    { wch: 15 }, // Conteo
    { wch: 18 }, // Total
    { wch: 18 }, // Registros
  ]
}

/**
 * Generate filename
 */
ExportManager.prototype.generateFileName = (currentStatus, currentDate) => {
  const timestamp = new Date().toISOString().slice(0, 19).replace(/[:-]/g, "")
  const statusName = statusNames[currentStatus] || "CFDIs"
  const dateFormatted = currentDate.replace(/\//g, "-")

  return "CFDIs_" + statusName + "_" + dateFormatted + "_" + timestamp + ".xlsx"
}

/**
 * Show export progress
 */
ExportManager.prototype.showExportProgress = () => {
  const exportBtn = document.getElementById("exportBtn")
  if (exportBtn) {
    exportBtn.disabled = true
    exportBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>' + " Exportando..."
  }
}

/**
 * Hide export progress
 */
ExportManager.prototype.hideExportProgress = () => {
  const exportBtn = document.getElementById("exportBtn")
  if (exportBtn) {
    exportBtn.disabled = false
    exportBtn.innerHTML = '<i class="fas fa-download"></i> Exportar a Excel'
  }
}

/**
 * Show export success message
 */
ExportManager.prototype.showExportSuccess = function (fileName) {
  const alertDiv = document.createElement("div")
  alertDiv.className = "alert alert-success alert-dismissible fade show position-fixed"
  alertDiv.style.cssText = "top: 20px; right: 20px; z-index: 9999; min-width: 350px; max-width: 500px;"
  alertDiv.innerHTML =
    '<div class="d-flex align-items-center">' +
    '<i class="fas fa-check-circle fa-lg me-3"></i>' +
    "<div>" +
    "<strong>¡Exportación exitosa!</strong><br>" +
    "<small>Archivo: " +
    this.escapeHtml(fileName) +
    "</small>" +
    "</div>" +
    "</div>" +
    '<button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">' +
    '<span aria-hidden="true">&times;</span>' +
    "</button>"

  document.body.appendChild(alertDiv)

  // Auto-remove after 5 seconds
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.parentNode.removeChild(alertDiv)
    }
  }, 5000)
}

/**
 * Show export error message
 */
ExportManager.prototype.showExportError = function (message) {
  const alertDiv = document.createElement("div")
  alertDiv.className = "alert alert-danger alert-dismissible fade show position-fixed"
  alertDiv.style.cssText = "top: 20px; right: 20px; z-index: 9999; min-width: 350px; max-width: 500px;"
  alertDiv.innerHTML =
    '<div class="d-flex align-items-center">' +
    '<i class="fas fa-exclamation-triangle fa-lg me-3"></i>' +
    "<div>" +
    "<strong>Error en la exportación</strong><br>" +
    "<small>" +
    this.escapeHtml(message) +
    "</small>" +
    "</div>" +
    "</div>" +
    '<button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">' +
    '<span aria-hidden="true">&times;</span>' +
    "</button>"

  document.body.appendChild(alertDiv)

  // Auto-remove after 8 seconds
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.parentNode.removeChild(alertDiv)
    }
  }, 8000)
}

/**
 * Escape HTML to prevent XSS
 */
ExportManager.prototype.escapeHtml = (text) => {
  const div = document.createElement("div")
  div.textContent = text
  return div.innerHTML
}

/**
 * Export specific CFDI types
 */
ExportManager.prototype.exportSpecificType = function (type) {
  if (!statusNames[type]) {
    throw new Error("Invalid CFDI type: " + type)
  }

  // Temporarily change current status for export
  const originalStatus = window.app ? window.app.currentStatus : "ingreso"
  if (window.app) {
    window.app.currentStatus = type
  }
  
  try {
    this.exportToExcel()
  } finally {
    // Restore original status
    if (window.app) {
      window.app.currentStatus = originalStatus
    }
  }
}

/**
 * Export all CFDI types
 */
ExportManager.prototype.exportAllTypes = function () {
  
  try {
    this.isExporting = true
    this.showExportProgress()

    const wb = XLSX.utils.book_new()
    const currentDate = new Date().toLocaleDateString("es-MX")

    // Add summary sheet first
    this.addSummarySheet(wb, "all")

    // Add sheet for each CFDI type
    Object.keys(statusNames).forEach((type) => {
      this.addTotalsSheet(wb, type)
      this.addCFDIsSheet(wb, type)
    })

    const fileName = "CFDIs_Completo_" + currentDate.replace(/\//g, "-") + "_" + Date.now() + ".xlsx"
    XLSX.writeFile(wb, fileName)

    this.showExportSuccess(fileName)
  } catch (error) {
    console.error("Export all types error:", error)
    this.showExportError(error.message)
  } finally {
    this.isExporting = false
    this.hideExportProgress()
  }
}

// Create global instance
window.exportManager = new ExportManager()
