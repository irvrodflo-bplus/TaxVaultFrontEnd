// ===== CFDI Data Management =====

// Data for each CFDI type
const cfdisDataByType = {
  ingreso: [
    {
      fecha: "04/02/2025",
      serie: "DFD",
      folio: "25391",
      receptor: "IVONNE MARINA BECERRIL MORALES",
      total: "$59,020.80",
      saldo: "$0.00",
      pagoRelacionados: "",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789012",
    },
    {
      fecha: "04/02/2025",
      serie: "GDLP",
      folio: "8900",
      receptor: "OOL DIGITAL",
      total: "$3,583.24",
      saldo: "$0.00",
      pagoRelacionados: "ab4e6cc4d8c-45e0-810a-26b310d42015",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789013",
    },
    {
      fecha: "04/02/2025",
      serie: "GDLP",
      folio: "8901",
      receptor: "GRUPO BITOL",
      total: "$2,308.40",
      saldo: "$0.00",
      pagoRelacionados: "ba83ee64-1df8-413f-849z-85726751cc9d",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789014",
    },
    {
      fecha: "04/02/2025",
      serie: "DFD",
      folio: "25392",
      receptor: "IVONNE MARINA BECERRIL MORALES",
      total: "$16,385.98",
      saldo: "$0.00",
      pagoRelacionados: "e425a961-52c2-4e51-8836-cc7ddb4ea56a",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789015",
    },
    {
      fecha: "04/02/2025",
      serie: "DFD",
      folio: "25393",
      receptor: "IDNUBE",
      total: "$4,591.86",
      saldo: "$0.00",
      pagoRelacionados: "1b94b041-3bd9-41e7-9801-e3e7c531728B",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789016",
    },
    {
      fecha: "04/02/2025",
      serie: "DFD",
      folio: "25394",
      receptor: "IDNUBE",
      total: "$1,123.46",
      saldo: "$0.00",
      pagoRelacionados: "1b94b041-3bd9-41e7-9801-e3e7c531728B",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789017",
    },
    {
      fecha: "04/02/2025",
      serie: "GDLP",
      folio: "8902",
      receptor: "GEMA ARRIZON VALADEZ",
      total: "$2,539.24",
      saldo: "$0.00",
      pagoRelacionados: "657fc305-0391-4ec8-9861-97afe4117eae",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789018",
    },
    {
      fecha: "04/02/2025",
      serie: "GDLS",
      folio: "15789",
      receptor: "COGOCI CONSULTORIA",
      total: "$5,092.40",
      saldo: "$0.00",
      pagoRelacionados: "a05ab6f2-e65d-4298-b6f4-cbb3266bdd22",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789019",
    },
  ],
  egreso: [
    {
      fecha: "03/02/2025",
      serie: "EGR",
      folio: "1001",
      receptor: "PROVEEDOR ABC SA DE CV",
      total: "$15,000.00",
      saldo: "$0.00",
      pagoRelacionados: "",
      egresoRelacionados: "def456-789-abc-123",
      uuid: "12345678-1234-1234-1234-123456789020",
    },
    {
      fecha: "03/02/2025",
      serie: "EGR",
      folio: "1002",
      receptor: "SERVICIOS TECNICOS XYZ",
      total: "$8,500.00",
      saldo: "$0.00",
      pagoRelacionados: "",
      egresoRelacionados: "ghi789-012-def-456",
      uuid: "12345678-1234-1234-1234-123456789021",
    },
    {
      fecha: "02/02/2025",
      serie: "EGR",
      folio: "1003",
      receptor: "MATERIALES INDUSTRIALES",
      total: "$22,300.50",
      saldo: "$0.00",
      pagoRelacionados: "",
      egresoRelacionados: "jkl012-345-ghi-789",
      uuid: "12345678-1234-1234-1234-123456789022",
    },
  ],
  traslado: [
    // Empty array for traslado as it shows (0)
  ],
  nomina: [
    {
      fecha: "01/02/2025",
      serie: "NOM",
      folio: "2001",
      receptor: "JUAN PEREZ GARCIA",
      total: "$25,000.00",
      saldo: "$0.00",
      pagoRelacionados: "",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789023",
    },
    {
      fecha: "01/02/2025",
      serie: "NOM",
      folio: "2002",
      receptor: "MARIA LOPEZ HERNANDEZ",
      total: "$18,500.00",
      saldo: "$0.00",
      pagoRelacionados: "",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789024",
    },
    {
      fecha: "01/02/2025",
      serie: "NOM",
      folio: "2003",
      receptor: "CARLOS RODRIGUEZ MARTINEZ",
      total: "$32,000.00",
      saldo: "$0.00",
      pagoRelacionados: "",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789025",
    },
  ],
  pago: [
    {
      fecha: "05/02/2025",
      serie: "PAG",
      folio: "3001",
      receptor: "CLIENTE PREMIUM SA",
      total: "$45,000.00",
      saldo: "$0.00",
      pagoRelacionados: "pag123-456-789-abc",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789026",
    },
    {
      fecha: "05/02/2025",
      serie: "PAG",
      folio: "3002",
      receptor: "DISTRIBUIDORA NACIONAL",
      total: "$78,500.00",
      saldo: "$15,000.00",
      pagoRelacionados: "pag456-789-012-def",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789027",
    },
    {
      fecha: "04/02/2025",
      serie: "PAG",
      folio: "3003",
      receptor: "COMERCIALIZADORA REGIONAL",
      total: "$12,300.00",
      saldo: "$0.00",
      pagoRelacionados: "pag789-012-345-ghi",
      egresoRelacionados: "",
      uuid: "12345678-1234-1234-1234-123456789028",
    },
  ],
}

// Totals data for each type
const totalesDataByType = {
  ingreso: {
    periodo: {
      conteo: 964,
      trasladoIva: "$1,091,330.23",
      subtotal: "$8,532,108.22",
      descuento: "$1,711,294.62",
      neto: "$6,820,813.60",
      total: "$7,912,143.83",
    },
    acumulado: {
      conteo: 2507,
      trasladoIva: "$2,472,285.63",
      subtotal: "$19,566,114.48",
      descuento: "$4,114,331.71",
      neto: "$15,451,782.77",
      total: "$17,924,068.40",
    },
  },
  egreso: {
    periodo: {
      conteo: 8,
      trasladoIva: "$7,200.00",
      subtotal: "$45,800.50",
      descuento: "$0.00",
      neto: "$45,800.50",
      total: "$53,000.50",
    },
    acumulado: {
      conteo: 25,
      trasladoIva: "$18,500.00",
      subtotal: "$115,600.75",
      descuento: "$2,300.00",
      neto: "$113,300.75",
      total: "$131,800.75",
    },
  },
  traslado: {
    periodo: {
      conteo: 0,
      trasladoIva: "$0.00",
      subtotal: "$0.00",
      descuento: "$0.00",
      neto: "$0.00",
      total: "$0.00",
    },
    acumulado: {
      conteo: 0,
      trasladoIva: "$0.00",
      subtotal: "$0.00",
      descuento: "$0.00",
      neto: "$0.00",
      total: "$0.00",
    },
  },
  nomina: {
    periodo: {
      conteo: 64,
      trasladoIva: "$0.00",
      subtotal: "$1,472,000.00",
      descuento: "$0.00",
      neto: "$1,472,000.00",
      total: "$1,472,000.00",
    },
    acumulado: {
      conteo: 128,
      trasladoIva: "$0.00",
      subtotal: "$2,944,000.00",
      descuento: "$0.00",
      neto: "$2,944,000.00",
      total: "$2,944,000.00",
    },
  },
  pago: {
    periodo: {
      conteo: 492,
      trasladoIva: "$0.00",
      subtotal: "$6,825,400.00",
      descuento: "$0.00",
      neto: "$6,825,400.00",
      total: "$6,825,400.00",
    },
    acumulado: {
      conteo: 1248,
      trasladoIva: "$0.00",
      subtotal: "$17,325,600.00",
      descuento: "$0.00",
      neto: "$17,325,600.00",
      total: "$17,325,600.00",
    },
  },
}

// Status names mapping
const statusNames = {
  ingreso: "Ingreso",
  egreso: "Egreso",
  traslado: "Traslado",
  nomina: "Nómina",
  pago: "Pago",
}

const statusNamesLong = {
  ingreso: "CFDIs de Ingreso",
  egreso: "CFDIs de Egreso",
  traslado: "CFDIs de Traslado",
  nomina: "CFDIs de Nómina",
  pago: "CFDIs de Pago",
}

// Configuration constants
const CONFIG = {
  ITEMS_PER_PAGE: 50,
  SEARCH_DELAY: 300,
  ANIMATION_DURATION: 200,
  DATE_FORMAT: "DD/MM/YYYY",
  CURRENCY_FORMAT: "es-MX",
  AUTO_REFRESH_ENABLED: false,
  API_ENDPOINTS: {
    DOWNLOAD: "/api/cfdi/download",
    EXPORT: "/api/cfdi/export",
    SEARCH: "/api/cfdi/search",
  },
}

// Utility functions for data manipulation
const DataUtils = {
  /**
   * Filter CFDIs by search term
   */
  filterCFDIs: (data, searchTerm) => {
    if (!searchTerm) return data

    const term = searchTerm.toLowerCase()
    return data.filter(
      (cfdi) =>
        cfdi.receptor.toLowerCase().includes(term) ||
        cfdi.serie.toLowerCase().includes(term) ||
        cfdi.folio.toLowerCase().includes(term) ||
        cfdi.uuid.toLowerCase().includes(term) ||
        cfdi.fecha.includes(term),
    )
  },

  /**
   * Sort CFDIs by field
   */
  sortCFDIs: (data, field, direction) => {
    direction = direction || "asc"
    return data.slice().sort((a, b) => {
      var aVal = a[field]
      var bVal = b[field]

      // Handle dates
      if (field === "fecha") {
        aVal = new Date(aVal.split("/").reverse().join("-"))
        bVal = new Date(bVal.split("/").reverse().join("-"))
      }

      // Handle currency
      if (field === "total" || field === "saldo") {
        aVal = Number.parseFloat(aVal.replace(/[$,]/g, ""))
        bVal = Number.parseFloat(bVal.replace(/[$,]/g, ""))
      }

      if (direction === "asc") {
        return aVal > bVal ? 1 : -1
      } else {
        return aVal < bVal ? 1 : -1
      }
    })
  },

  /**
   * Format currency
   */
  formatCurrency: (amount) =>
    new Intl.NumberFormat(CONFIG.CURRENCY_FORMAT, {
      style: "currency",
      currency: "MXN",
    }).format(amount),

  /**
   * Format date
   */
  formatDate: (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString(CONFIG.CURRENCY_FORMAT)
  },

  /**
   * Get totals for current filter
   */
  calculateTotals: (data) =>
    data.reduce(
      (totals, cfdi) => {
        const total = Number.parseFloat(cfdi.total.replace(/[$,]/g, ""))
        const saldo = Number.parseFloat(cfdi.saldo.replace(/[$,]/g, ""))

        totals.count++
        totals.total += total
        totals.saldo += saldo

        return totals
      },
      { count: 0, total: 0, saldo: 0 },
    ),
}
