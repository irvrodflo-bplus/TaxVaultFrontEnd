// ===== Main Application Controller =====

function CFDIApp() {
  this.currentStatus = "ingreso"
  this.tableManager = window.tableManager
  this.exportManager = window.exportManager
  this.filterManager = window.filterManager
  this.isInitialized = false
  this.$ = window.$ // Declare $ variable
  this.CONFIG = window.CONFIG // Declare CONFIG variable
  this.statusNames = window.statusNames // Declare statusNames variable
  this.cfdisDataByType = window.cfdisDataByType // Declare cfdisDataByType variable
  this.totalesDataByType = window.totalesDataByType // Declare totalesDataByType variable

  this.initialize()
}

/**
 * Initialize the application
 */
CFDIApp.prototype.initialize = function () {
  
  // Wait for DOM to be ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      this.onDOMReady()
    })
  } else {
    this.onDOMReady()
  }
}

/**
 * Handle DOM ready event
 */
CFDIApp.prototype.onDOMReady = function () {
  try {
    console.log("🚀 CFDI Dashboard initializing...")

    // Load filters from URL first
    this.filterManager.loadFiltersFromURL()

    // Initialize tables with default status
    this.tableManager.updateTables(this.currentStatus)

    // Set up additional event listeners
    this.setupAdditionalEvents()

    // Set up keyboard shortcuts
    this.setupKeyboardShortcuts()

    // Initialize Bootstrap components
    this.initializeBootstrapComponents()

    // Set up auto-refresh if needed
    this.setupAutoRefresh()

    // Mark as initialized
    this.isInitialized = true

    // Show welcome message
    this.showWelcomeMessage()

    console.log("✅ CFDI Dashboard initialized successfully")
  } catch (error) {
    console.error("❌ Error initializing CFDI Dashboard:", error)
    this.showInitializationError(error)
  }
}

/**
 * Set up additional event listeners
 */
CFDIApp.prototype.setupAdditionalEvents = function () {
  
  // Edit columns buttons
  document.querySelectorAll(".edit-columns-btn, .btn-tool").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      if (e.target.closest(".edit-columns-btn") || e.target.closest(".btn-tool")) {
        this.showEditColumnsModal()
      }
    })
  })

  // Handle window resize
  window.addEventListener(
    "resize",
    this.debounce(() => {
      this.handleWindowResize()
    }, 250),
  )

  // Handle visibility change (tab switching)
  document.addEventListener("visibilitychange", () => {
    this.handleVisibilityChange()
  })

  // Handle beforeunload for unsaved changes
  window.addEventListener("beforeunload", (e) => {
    if (this.hasUnsavedChanges()) {
      e.preventDefault()
      e.returnValue = ""
    }
  })
}

/**
 * Set up keyboard shortcuts
 */
CFDIApp.prototype.setupKeyboardShortcuts = function () {
  
  document.addEventListener("keydown", (e) => {
    this.handleKeyboardShortcuts(e)
  })
}

/**
 * Handle keyboard shortcuts
 */
CFDIApp.prototype.handleKeyboardShortcuts = function (e) {
  // Don't trigger shortcuts when typing in inputs
  if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA") {
    return
  }

  const isCtrlOrCmd = e.ctrlKey || e.metaKey

  switch (true) {
    // Ctrl/Cmd + E for export
    case isCtrlOrCmd && e.key === "e":
      e.preventDefault()
      this.exportManager.exportToExcel()
      break

    // Ctrl/Cmd + F for search focus
    case isCtrlOrCmd && e.key === "f":
      e.preventDefault()
      const searchInput = document.getElementById("searchInput")
      if (searchInput) {
        searchInput.focus()
        searchInput.select()
      }
      break

    // Ctrl/Cmd + R for refresh
    case isCtrlOrCmd && e.key === "r":
      e.preventDefault()
      this.refreshData()
      break

    // Escape to clear search
    case e.key === "Escape":
      this.clearSearch()
      break

    // Number keys for quick tab switching
    case e.key >= "1" && e.key <= "5":
      e.preventDefault()
      this.switchToTabByNumber(Number.parseInt(e.key) - 1)
      break
  }
}

/**
 * Initialize Bootstrap components
 */
CFDIApp.prototype.initializeBootstrapComponents = function () {
  // Initialize tooltips
  if (typeof this.$ !== "undefined" && this.$.fn.tooltip) {
    this.$("[title]").tooltip({
      placement: "top",
      trigger: "hover",
    })
  }

  // Initialize dropdowns
  if (typeof this.$ !== "undefined" && this.$.fn.dropdown) {
    this.$(".dropdown-toggle").dropdown()
  }
}

/**
 * Set up auto-refresh functionality
 */
CFDIApp.prototype.setupAutoRefresh = function () {
  
  // Auto-refresh every 5 minutes (optional)
  const autoRefreshInterval = 5 * 60 * 1000 // 5 minutes

  if (this.CONFIG.AUTO_REFRESH_ENABLED) {
    setInterval(() => {
      if (!document.hidden && this.isInitialized) {
        this.refreshData(true) // Silent refresh
      }
    }, autoRefreshInterval)
  }
}

/**
 * Show edit columns modal
 */
CFDIApp.prototype.showEditColumnsModal = function () {
  // TODO: Implement edit columns functionality
  const modal = this.createModal(
    "Editar Columnas",
    `
            <div class="form-group">
                <label>Selecciona las columnas a mostrar:</label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="col-fecha" checked>
                            <label class="form-check-label" for="col-fecha">Fecha expedición</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="col-serie" checked>
                            <label class="form-check-label" for="col-serie">Serie</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="col-folio" checked>
                            <label class="form-check-label" for="col-folio">Folio</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="col-receptor" checked>
                            <label class="form-check-label" for="col-receptor">Receptor</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="col-total" checked>
                            <label class="form-check-label" for="col-total">Total</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="col-saldo" checked>
                            <label class="form-check-label" for="col-saldo">Saldo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="col-pago">
                            <label class="form-check-label" for="col-pago">CFDIs de pago</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="col-egreso">
                            <label class="form-check-label" for="col-egreso">CFDIs de egreso</label>
                        </div>
                    </div>
                </div>
            </div>
        `,
    [
      {
        text: "Aplicar",
        class: "btn-primary",
        action: () => {
          console.log("Applying column changes...")
          // TODO: Implement column visibility logic
        },
      },
      {
        text: "Cancelar",
        class: "btn-secondary",
        action: "close",
      },
    ],
  )

  modal.show()
}

/**
 * Handle window resize
 */
CFDIApp.prototype.handleWindowResize = () => {
  // Adjust table layouts if needed
  const tables = document.querySelectorAll(".table-responsive")
  tables.forEach((table) => {
    // Force table recalculation
    table.style.width = "100%"
  })
}

/**
 * Handle visibility change
 */
CFDIApp.prototype.handleVisibilityChange = function () {
  if (!document.hidden && this.isInitialized) {
    // Page became visible, refresh data if it's been a while
    const lastRefresh = localStorage.getItem("lastRefresh")
    const now = Date.now()
    const fiveMinutes = 5 * 60 * 1000

    if (!lastRefresh || now - Number.parseInt(lastRefresh) > fiveMinutes) {
      this.refreshData(true)
    }
  }
}

/**
 * Check for unsaved changes
 */
CFDIApp.prototype.hasUnsavedChanges = () => {
  // TODO: Implement unsaved changes detection
  return false
}

/**
 * Clear search
 */
CFDIApp.prototype.clearSearch = function () {
  const searchInput = document.getElementById("searchInput")
  if (searchInput && searchInput.value) {
    searchInput.value = ""
    this.filterManager.searchTerm = ""
    this.filterManager.applyFilters()
  }
}

/**
 * Switch to tab by number
 */
CFDIApp.prototype.switchToTabByNumber = (index) => {
  const tabs = document.querySelectorAll("[data-status]")
  if (tabs[index]) {
    tabs[index].click()
  }
}

/**
 * Show welcome message
 */
CFDIApp.prototype.showWelcomeMessage = () => {
  console.log("🎉 Dashboard de CFDIs cargado correctamente")
  console.log("💡 Atajos de teclado disponibles:")
  console.log("   • Ctrl+E: Exportar a Excel")
  console.log("   • Ctrl+F: Buscar")
  console.log("   • Ctrl+R: Actualizar datos")
  console.log("   • Escape: Limpiar búsqueda")
  console.log("   • 1-5: Cambiar entre pestañas")
}

/**
 * Show initialization error
 */
CFDIApp.prototype.showInitializationError = (error) => {
  const errorDiv = document.createElement("div")
  errorDiv.className = "alert alert-danger"
  errorDiv.innerHTML = `
            <h4><i class="fas fa-exclamation-triangle"></i> Error de Inicialización</h4>
            <p>Hubo un problema al cargar el dashboard. Por favor, recarga la página.</p>
            <small>Error técnico: ${error.message}</small>
        `

  const container = document.querySelector(".container-fluid")
  if (container) {
    container.insertBefore(errorDiv, container.firstChild)
  }
}

/**
 * Change current status programmatically
 */
CFDIApp.prototype.changeStatus = function (newStatus) {
  if (this.statusNames[newStatus]) {
    this.currentStatus = newStatus

    // Update active tab
    document.querySelectorAll("[data-status]").forEach((tab) => {
      tab.classList.remove("active")
      if (tab.getAttribute("data-status") === newStatus) {
        tab.classList.add("active")
      }
    })

    // Update tables
    this.tableManager.clearExpandedRows()
    this.tableManager.updateTables(newStatus)

    console.log("Status changed to:", newStatus)
  }
}

/**
 * Get current data
 */
CFDIApp.prototype.getCurrentData = function () {
  return {
    status: this.currentStatus,
    cfdisData: this.cfdisDataByType[this.currentStatus] || [],
    totalesData: this.totalesDataByType[this.currentStatus] || null,
    filters: this.filterManager.getCurrentFilters(),
  }
}

/**
 * Refresh all data
 */
CFDIApp.prototype.refreshData = function (silent) {
  silent = silent || false
  if (!silent) {
    console.log("🔄 Refreshing data...")
  }

  try {
    // Update timestamp
    localStorage.setItem("lastRefresh", Date.now().toString())

    // Refresh tables
    this.tableManager.updateTables(this.currentStatus)

    if (!silent) {
      this.showRefreshSuccess()
    }

    console.log("✅ Data refreshed successfully")
  } catch (error) {
    console.error("❌ Error refreshing data:", error)
    if (!silent) {
      this.showRefreshError(error.message)
    }
  }
}

/**
 * Show refresh success message
 */
CFDIApp.prototype.showRefreshSuccess = function () {
  const toast = this.createToast("Datos actualizados", "success")
  toast.show()
}

/**
 * Show refresh error message
 */
CFDIApp.prototype.showRefreshError = function (message) {
  const toast = this.createToast("Error al actualizar: " + message, "error")
  toast.show()
}

/**
 * Create a modal
 */
CFDIApp.prototype.createModal = function (title, content, buttons) {
  buttons = buttons || []
  const modalId = "modal-" + Date.now()
  const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                        <div class="modal-footer">
                            ${buttons
                              .map(
                                (btn) => `
                                <button type="button" class="btn ${btn.class}" data-action="${btn.action || "custom"}">
                                    ${btn.text}
                                </button>
                            `,
                              )
                              .join("")}
                        </div>
                    </div>
                </div>
            </div>
        `

  document.body.insertAdjacentHTML("beforeend", modalHTML)
  const modal = document.getElementById(modalId)

  // Add button event listeners
  buttons.forEach((btn, index) => {
    if (btn.action && typeof btn.action === "function") {
      const button = modal.querySelectorAll(".modal-footer .btn")[index]
      button.addEventListener("click", btn.action)
    }
  })

  // Auto-remove modal when hidden
  if (typeof this.$ !== "undefined") {
    this.$(modal).on("hidden.bs.modal", () => {
      modal.remove()
    })
    return this.$(modal).modal()
  }

  return {
    show: () => {
      modal.style.display = "block"
      modal.classList.add("show")
    },
  }
}

/**
 * Create a toast notification
 */
CFDIApp.prototype.createToast = (message, type) => {
  type = type || "info"
  const toastId = "toast-" + Date.now()
  const iconClass =
    {
      success: "fa-check-circle",
      error: "fa-exclamation-triangle",
      warning: "fa-exclamation-circle",
      info: "fa-info-circle",
    }[type] || "fa-info-circle"

  const bgClass =
    {
      success: "alert-success",
      error: "alert-danger",
      warning: "alert-warning",
      info: "alert-info",
    }[type] || "alert-info"

  const toastHTML = `
            <div id="${toastId}" class="alert ${bgClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${iconClass}"></i> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `

  document.body.insertAdjacentHTML("beforeend", toastHTML)
  const toast = document.getElementById(toastId)

  // Auto-remove after 3 seconds
  setTimeout(() => {
    if (toast && toast.parentNode) {
      toast.remove()
    }
  }, 3000)

  return {
    show: () => {
      toast.classList.add("show")
    },
    hide: () => {
      toast.classList.remove("show")
    },
  }
}

/**
 * Utility function for debouncing
 */
CFDIApp.prototype.debounce = (func, wait) => {
  var timeout
  return function () {
    
    var args = arguments
    var later = () => {
      timeout = null
      func.apply(this, args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Create global app instance
window.app = new CFDIApp()

// Global utility functions for backward compatibility
function toggleRow(status, index) {
  if (window.tableManager) {
    window.tableManager.toggleRow(status, index)
  }
}

// Export app instance for external access
window.CFDIApp = CFDIApp
