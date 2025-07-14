// ===== Table Manager - Handles table population and updates =====

// Declare CONFIG variable
const CONFIG = {
  ITEMS_PER_PAGE: 10,
}

// Declare statusNamesLong variable
const statusNamesLong = {
  ingreso: "CFDIs de Ingreso",
  egreso: "CFDIs de Egreso",
}

// Declare cfdisDataByType variable
const cfdisDataByType = {
  ingreso: [
    {
      uuid: "1",
      fecha: "2023-01-01",
      serie: "A",
      folio: "12345",
      receptor: "Receptor 1",
      total: "100.00",
      saldo: "50.00",
      pagoRelacionados: "Pago 1",
      egresoRelacionados: null,
    },
    {
      uuid: "2",
      fecha: "2023-01-02",
      serie: "B",
      folio: "67890",
      receptor: "Receptor 2",
      total: "200.00",
      saldo: "100.00",
      pagoRelacionados: null,
      egresoRelacionados: "Egreso 1",
    },
  ],
  egreso: [
    {
      uuid: "3",
      fecha: "2023-01-03",
      serie: "C",
      folio: "54321",
      receptor: "Receptor 3",
      total: "150.00",
      saldo: "75.00",
      pagoRelacionados: null,
      egresoRelacionados: "Egreso 2",
    },
  ],
}

// Declare totalesDataByType variable
const totalesDataByType = {
  ingreso: {
    periodo: {
      conteo: 2,
      trasladoIva: "10.00",
      subtotal: "190.00",
      descuento: "0.00",
      neto: "190.00",
      total: "200.00",
    },
    acumulado: {
      conteo: 2,
      trasladoIva: "20.00",
      subtotal: "380.00",
      descuento: "0.00",
      neto: "380.00",
      total: "400.00",
    },
  },
  egreso: {
    periodo: { conteo: 1, trasladoIva: "5.00", subtotal: "145.00", descuento: "0.00", neto: "145.00", total: "150.00" },
    acumulado: {
      conteo: 1,
      trasladoIva: "5.00",
      subtotal: "145.00",
      descuento: "0.00",
      neto: "145.00",
      total: "150.00",
    },
  },
}

// Declare DataUtils object
const DataUtils = {
  filterCFDIs: (data, filterTerm) =>
    data.filter((cfdi) => cfdi.folio.includes(filterTerm) || cfdi.receptor.includes(filterTerm)),
  sortCFDIs: (data, field, direction) =>
    data.sort((a, b) => {
      if (a[field] < b[field]) return direction === "asc" ? -1 : 1
      if (a[field] > b[field]) return direction === "asc" ? 1 : -1
      return 0
    }),
}

function TableManager() {
  this.expandedRows = new Set()
  this.currentSort = { field: null, direction: "asc" }
  this.currentFilter = ""
  this.currentPage = 1
  this.itemsPerPage = CONFIG.ITEMS_PER_PAGE
}

/**
 * Populate the CFDIs table based on current status
 */
TableManager.prototype.populateCFDIsTable = function (status) {
  status = status || "ingreso"
  const tbody = document.getElementById("cfdisTableBody")
  const title = document.getElementById("cfdisTableTitle")

  if (!tbody || !title) {
    console.error("Required table elements not found")
    return
  }

  tbody.innerHTML = ""

  // Update table title
  title.textContent = statusNamesLong[status] || "CFDIs"

  var data = cfdisDataByType[status] || []

  // Apply filters
  if (this.currentFilter) {
    data = DataUtils.filterCFDIs(data, this.currentFilter)
  }

  // Apply sorting
  if (this.currentSort.field) {
    data = DataUtils.sortCFDIs(data, this.currentSort.field, this.currentSort.direction)
  }

  // Handle empty data
  if (data.length === 0) {
    this.renderEmptyState(tbody)
    return
  }

  // Render table rows
  this.renderTableRows(tbody, data, status)

  // Update pagination if needed
  this.updatePagination(data.length)
}

/**
 * Render empty state
 */
TableManager.prototype.renderEmptyState = (tbody) => {
  const row = document.createElement("tr")
  row.innerHTML =
    '<td colspan="11" class="empty-state">' +
    '<i class="fas fa-inbox fa-2x mb-3"></i>' +
    "<p>No hay registros para mostrar en esta sección</p>" +
    "</td>"
  tbody.appendChild(row)
}

/**
 * Render table rows
 */
TableManager.prototype.renderTableRows = function (tbody, data, status) {
  
  data.forEach((cfdi, index) => {
    const row = document.createElement("tr")
    row.className = "cfdi-row"
    row.setAttribute("data-uuid", cfdi.uuid || "")

    row.innerHTML =
      "<td>" +
      '<button class="expand-btn" onclick="window.tableManager.toggleRow(\'' +
      status +
      "', " +
      index +
      ')" ' +
      'title="Expandir/Contraer fila" aria-label="Expandir detalles">' +
      '<i class="fas fa-plus" id="icon-' +
      status +
      "-" +
      index +
      '"></i>' +
      "</button>" +
      "</td>" +
      "<td>" +
      '<input type="checkbox" class="form-check-input" ' +
      'aria-label="Seleccionar CFDI ' +
      this.escapeHtml(cfdi.folio) +
      '">' +
      "</td>" +
      "<td>" +
      this.escapeHtml(cfdi.fecha) +
      "</td>" +
      "<td>" +
      this.escapeHtml(cfdi.serie) +
      "</td>" +
      "<td>" +
      this.escapeHtml(cfdi.folio) +
      "</td>" +
      '<td title="' +
      this.escapeHtml(cfdi.receptor) +
      '">' +
      this.truncateText(cfdi.receptor, 30) +
      "</td>" +
      '<td class="text-right">' +
      this.escapeHtml(cfdi.total) +
      "</td>" +
      '<td class="text-right">' +
      this.escapeHtml(cfdi.saldo) +
      "</td>" +
      "<td>" +
      (cfdi.pagoRelacionados
        ? '<span class="cfdi-id" title="' +
          this.escapeHtml(cfdi.pagoRelacionados) +
          '">' +
          this.truncateText(cfdi.pagoRelacionados, 20) +
          "</span>"
        : "") +
      "</td>" +
      "<td>" +
      (cfdi.egresoRelacionados
        ? '<span class="cfdi-id" title="' +
          this.escapeHtml(cfdi.egresoRelacionados) +
          '">' +
          this.truncateText(cfdi.egresoRelacionados, 20) +
          "</span>"
        : "") +
      "</td>" +
      "<td>" +
      '<div class="dropdown">' +
      '<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" ' +
      'data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
      '<i class="fas fa-ellipsis-v"></i>' +
      "</button>" +
      '<div class="dropdown-menu">' +
      '<a class="dropdown-item" href="#" onclick="window.tableManager.viewDetails(\'' +
      cfdi.uuid +
      "')\">" +
      '<i class="fas fa-eye"></i> Ver detalles' +
      "</a>" +
      '<a class="dropdown-item" href="#" onclick="window.tableManager.downloadPDF(\'' +
      cfdi.uuid +
      "')\">" +
      '<i class="fas fa-file-pdf"></i> Descargar PDF' +
      "</a>" +
      '<a class="dropdown-item" href="#" onclick="window.tableManager.downloadXML(\'' +
      cfdi.uuid +
      "')\">" +
      '<i class="fas fa-file-code"></i> Descargar XML' +
      "</a>" +
      "</div>" +
      "</div>" +
      "</td>"

    tbody.appendChild(row)
  })
}

/**
 * Populate totals table
 */
TableManager.prototype.populateTotalesTable = function (status) {
  status = status || "ingreso"
  const tbody = document.getElementById("totalesTableBody")

  if (!tbody) {
    console.error("Totales table body not found")
    return
  }

  tbody.innerHTML = ""

  const data = totalesDataByType[status]
  if (!data) {
    console.warn("No totals data found for status: " + status)
    return
  }

  // Periodo row
  const periodoRow = document.createElement("tr")
  periodoRow.innerHTML =
    "<td><strong>Periodo</strong></td>" +
    '<td class="text-right">' +
    this.formatNumber(data.periodo.conteo) +
    "</td>" +
    '<td class="text-right">' +
    this.escapeHtml(data.periodo.trasladoIva) +
    "</td>" +
    '<td class="text-right">' +
    this.escapeHtml(data.periodo.subtotal) +
    "</td>" +
    '<td class="text-right">' +
    this.escapeHtml(data.periodo.descuento) +
    "</td>" +
    '<td class="text-right">' +
    this.escapeHtml(data.periodo.neto) +
    "</td>" +
    '<td class="text-right"><strong>' +
    this.escapeHtml(data.periodo.total) +
    "</strong></td>"
  tbody.appendChild(periodoRow)

  // Acumulado row
  const acumuladoRow = document.createElement("tr")
  acumuladoRow.className = "table-info"
  acumuladoRow.innerHTML =
    "<td><strong>Acumulado</strong></td>" +
    '<td class="text-right">' +
    this.formatNumber(data.acumulado.conteo) +
    "</td>" +
    '<td class="text-right">' +
    this.escapeHtml(data.acumulado.trasladoIva) +
    "</td>" +
    '<td class="text-right">' +
    this.escapeHtml(data.acumulado.subtotal) +
    "</td>" +
    '<td class="text-right">' +
    this.escapeHtml(data.acumulado.descuento) +
    "</td>" +
    '<td class="text-right">' +
    this.escapeHtml(data.acumulado.neto) +
    "</td>" +
    '<td class="text-right"><strong>' +
    this.escapeHtml(data.acumulado.total) +
    "</strong></td>"
  tbody.appendChild(acumuladoRow)
}

/**
 * Toggle row expansion
 */
TableManager.prototype.toggleRow = function (status, index) {
  const rowKey = status + "-" + index
  const icon = document.getElementById("icon-" + status + "-" + index)

  if (!icon) return

  if (this.expandedRows.has(rowKey)) {
    this.expandedRows.delete(rowKey)
    icon.classList.remove("fa-minus")
    icon.classList.add("fa-plus")
    icon.parentElement.setAttribute("aria-label", "Expandir detalles")
  } else {
    this.expandedRows.add(rowKey)
    icon.classList.remove("fa-plus")
    icon.classList.add("fa-minus")
    icon.parentElement.setAttribute("aria-label", "Contraer detalles")
  }
}

/**
 * Clear expanded rows
 */
TableManager.prototype.clearExpandedRows = function () {
  this.expandedRows.clear()
}

/**
 * Update both tables
 */
TableManager.prototype.updateTables = function (status) {
  try {
    this.populateCFDIsTable(status)
    this.populateTotalesTable(status)
  } catch (error) {
    console.error("Error updating tables:", error)
    this.showError("Error al actualizar las tablas")
  }
}

/**
 * Set filter
 */
TableManager.prototype.setFilter = function (filterTerm) {
  this.currentFilter = filterTerm
  this.currentPage = 1 // Reset to first page
}

/**
 * Set sort
 */
TableManager.prototype.setSort = function (field, direction) {
  this.currentSort = { field: field, direction: direction }
}

/**
 * View CFDI details
 */
TableManager.prototype.viewDetails = (uuid) => {
  console.log("Viewing details for UUID:", uuid)
  alert("Ver detalles del CFDI: " + uuid)
}

/**
 * Download PDF
 */
TableManager.prototype.downloadPDF = (uuid) => {
  console.log("Downloading PDF for UUID:", uuid)
  alert("Descargando PDF del CFDI: " + uuid)
}

/**
 * Download XML
 */
TableManager.prototype.downloadXML = (uuid) => {
  console.log("Downloading XML for UUID:", uuid)
  alert("Descargando XML del CFDI: " + uuid)
}

/**
 * Update pagination
 */
TableManager.prototype.updatePagination = function (totalItems) {
  const totalPages = Math.ceil(totalItems / this.itemsPerPage)
  console.log("Pagination: " + this.currentPage + "/" + totalPages + " (" + totalItems + " items)")
}

/**
 * Show error message
 */
TableManager.prototype.showError = function (message) {
  const alertDiv = document.createElement("div")
  alertDiv.className = "alert alert-danger alert-dismissible fade show"
  alertDiv.innerHTML =
    '<i class="fas fa-exclamation-triangle"></i>' +
    "<strong>Error:</strong> " +
    this.escapeHtml(message) +
    '<button type="button" class="close" data-dismiss="alert">' +
    "<span>&times;</span>" +
    "</button>"

  const container = document.querySelector(".container-fluid")
  if (container) {
    container.insertBefore(alertDiv, container.firstChild)

    // Auto-remove after 5 seconds
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove()
      }
    }, 5000)
  }
}

/**
 * Utility functions
 */
TableManager.prototype.escapeHtml = (text) => {
  const div = document.createElement("div")
  div.textContent = text
  return div.innerHTML
}

TableManager.prototype.truncateText = (text, maxLength) => {
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + "..."
}

TableManager.prototype.formatNumber = (number) => new Intl.NumberFormat("es-MX").format(number)

// Create global instance
window.tableManager = new TableManager()
