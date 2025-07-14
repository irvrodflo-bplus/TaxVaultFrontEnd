// ===== Filter Manager - Handles all filter functionality =====

// Declare statusNames variable
const statusNames = {
  vigentes: "Active",
  cancelados: "Cancelled",
  ingreso: "Ingreso",
}

// Declare CONFIG variable
const CONFIG = {
  SEARCH_DELAY: 300,
}

function FilterManager() {
  this.activeFilters = {
    status: "vigentes",
    type: "todos-tipos",
    period: "2025-febrero",
  }
  this.searchTerm = ""
  this.searchTimeout = null

  this.initializeFilters()
  this.initializeStatusTabs()
  this.initializeSearch()
  this.initializePeriodFilter()
  this.loadFiltersFromURL()
}

/**
 * Initialize filter buttons
 */
FilterManager.prototype.initializeFilters = function () {
  
  document.querySelectorAll("[data-filter]").forEach((button) => {
    button.addEventListener("click", (e) => {
      this.handleFilterClick(e)
    })
  })
}

/**
 * Initialize status tabs
 */
FilterManager.prototype.initializeStatusTabs = function () {
  
  document.querySelectorAll("[data-status]").forEach((tab) => {
    tab.addEventListener("click", (e) => {
      this.handleStatusTabClick(e)
    })
  })
}

/**
 * Initialize search functionality
 */
FilterManager.prototype.initializeSearch = function () {
  
  const searchInput = document.getElementById("searchInput")
  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      this.handleSearch(e)
    })
    searchInput.addEventListener("keydown", (e) => {
      this.handleSearchKeydown(e)
    })
  }

  // Search button
  const searchButtons = document.querySelectorAll(".input-group-append button")
  if (searchButtons.length > 0) {
    searchButtons[0].addEventListener("click", () => {
      this.executeSearch()
    })
  }
}

/**
 * Initialize period filter
 */
FilterManager.prototype.initializePeriodFilter = function () {
  
  const periodFilter = document.getElementById("periodFilter")
  if (periodFilter) {
    periodFilter.addEventListener("change", (e) => {
      this.handlePeriodChange(e)
    })
  }
}

/**
 * Handle filter button clicks
 */
FilterManager.prototype.handleFilterClick = function (e) {
  const button = e.target
  const filterType = button.getAttribute("data-filter")

  if (!filterType) return

  // Determine filter group
  var filterGroup = ""
  if (["vigentes", "cancelados", "todos"].includes(filterType)) {
    filterGroup = "status"
  } else if (["pue", "ppd", "todos-tipos"].includes(filterType)) {
    filterGroup = "type"
  }

  if (filterGroup) {
    // Update active filter
    this.activeFilters[filterGroup] = filterType

    // Update UI
    this.updateFilterButtonsUI(filterGroup, filterType)

    // Apply filters
    this.applyFilters()

    console.log("Filter applied:", filterType, "Group:", filterGroup)
  }
}

/**
 * Update filter buttons UI
 */
FilterManager.prototype.updateFilterButtonsUI = (filterGroup, activeFilter) => {
  var selectors = []

  if (filterGroup === "status") {
    selectors = ['[data-filter="vigentes"]', '[data-filter="cancelados"]', '[data-filter="todos"]']
  } else if (filterGroup === "type") {
    selectors = ['[data-filter="pue"]', '[data-filter="ppd"]', '[data-filter="todos-tipos"]']
  }

  // Remove active class from all buttons in group
  selectors.forEach((selector) => {
    const buttons = document.querySelectorAll(selector)
    buttons.forEach((btn) => {
      btn.classList.remove("btn-primary", "active")
      btn.classList.add("btn-outline-secondary")
    })
  })

  // Add active class to selected button
  const activeButton = document.querySelector('[data-filter="' + activeFilter + '"]')
  if (activeButton) {
    activeButton.classList.remove("btn-outline-secondary")
    activeButton.classList.add("btn-primary", "active")
  }
}

/**
 * Handle status tab clicks
 */
FilterManager.prototype.handleStatusTabClick = function (e) {
  e.preventDefault()

  const tab = e.target
  const newStatus = tab.getAttribute("data-status")

  if (!newStatus || !statusNames[newStatus]) return

  // Remove active class from all tabs
  document.querySelectorAll("[data-status]").forEach((t) => {
    t.classList.remove("active")
  })

  // Add active class to clicked tab
  tab.classList.add("active")

  // Update current status and refresh tables
  if (window.app) {
    window.app.currentStatus = newStatus
    window.tableManager.clearExpandedRows()
    this.applyFilters()
  }

  console.log("Status tab selected:", newStatus)
}

/**
 * Handle search input
 */
FilterManager.prototype.handleSearch = function (e) {
  const searchTerm = e.target.value
  

  // Clear previous timeout
  if (this.searchTimeout) {
    clearTimeout(this.searchTimeout)
  }

  // Debounce search
  this.searchTimeout = setTimeout(() => {
    this.searchTerm = searchTerm.toLowerCase().trim()
    this.applyFilters()
    console.log("Search applied:", this.searchTerm)
  }, CONFIG.SEARCH_DELAY)
}

/**
 * Handle search keydown events
 */
FilterManager.prototype.handleSearchKeydown = function (e) {
  if (e.key === "Enter") {
    e.preventDefault()
    this.executeSearch()
  } else if (e.key === "Escape") {
    e.target.value = ""
    this.searchTerm = ""
    this.applyFilters()
  }
}

/**
 * Execute immediate search
 */
FilterManager.prototype.executeSearch = function () {
  const searchInput = document.getElementById("searchInput")
  if (searchInput) {
    this.searchTerm = searchInput.value.toLowerCase().trim()
    this.applyFilters()
    console.log("Immediate search executed:", this.searchTerm)
  }
}

/**
 * Handle period change
 */
FilterManager.prototype.handlePeriodChange = function (e) {
  const selectedPeriod = e.target.value
  this.activeFilters.period = selectedPeriod

  console.log("Period changed to:", selectedPeriod)

  // Show loading state
  this.showLoadingState()
  
  // Simulate API call delay
  setTimeout(() => {
    this.applyFilters()
    this.hideLoadingState()
  }, 500)
}

/**
 * Apply all active filters
 */
FilterManager.prototype.applyFilters = function () {
  if (!window.app || !window.tableManager) return

  const currentStatus = window.app.currentStatus

  // Set search filter in table manager
  window.tableManager.setFilter(this.searchTerm)

  // Update tables with current status
  window.tableManager.updateTables(currentStatus)

  // Update URL parameters (for bookmarking)
  this.updateURLParameters()

  // Update filter summary
  this.updateFilterSummary()
}

/**
 * Update URL parameters for bookmarking
 */
FilterManager.prototype.updateURLParameters = function () {
  const params = new URLSearchParams()

  if (window.app && window.app.currentStatus !== "ingreso") {
    params.set("status", window.app.currentStatus)
  }

  if (this.activeFilters.status !== "vigentes") {
    params.set("filter", this.activeFilters.status)
  }

  if (this.activeFilters.type !== "todos-tipos") {
    params.set("type", this.activeFilters.type)
  }

  if (this.activeFilters.period !== "2025-febrero") {
    params.set("period", this.activeFilters.period)
  }

  if (this.searchTerm) {
    params.set("search", this.searchTerm)
  }

  // Update URL without page reload
  const newURL = params.toString() ? window.location.pathname + "?" + params.toString() : window.location.pathname
  window.history.replaceState({}, "", newURL)
}

/**
 * Load filters from URL parameters
 */
FilterManager.prototype.loadFiltersFromURL = function () {
  const params = new URLSearchParams(window.location.search)

  // Load status
  const status = params.get("status")
  if (status && statusNames[status]) {
    const statusTab = document.querySelector('[data-status="' + status + '"]')
    if (statusTab) {
      statusTab.click()
    }
  }

  // Load filters
  const filter = params.get("filter")
  if (filter) {
    const filterButton = document.querySelector('[data-filter="' + filter + '"]')
    if (filterButton) {
      filterButton.click()
    }
  }

  const type = params.get("type")
  if (type) {
    const typeButton = document.querySelector('[data-filter="' + type + '"]')
    if (typeButton) {
      typeButton.click()
    }
  }

  // Load period
  const period = params.get("period")
  if (period) {
    const periodSelect = document.getElementById("periodFilter")
    if (periodSelect) {
      periodSelect.value = period
      this.activeFilters.period = period
    }
  }

  // Load search
  const search = params.get("search")
  if (search) {
    const searchInput = document.getElementById("searchInput")
    if (searchInput) {
      searchInput.value = search
      this.searchTerm = search.toLowerCase().trim()
    }
  }
}

/**
 * Update filter summary display
 */
FilterManager.prototype.updateFilterSummary = function () {
  var summaryText = ""
  const activeParts = []

  if (this.activeFilters.status !== "vigentes") {
    activeParts.push("Estado: " + this.activeFilters.status)
  }

  if (this.activeFilters.type !== "todos-tipos") {
    activeParts.push("Tipo: " + this.activeFilters.type)
  }

  if (this.searchTerm) {
    activeParts.push('Búsqueda: "' + this.searchTerm + '"')
  }

  if (activeParts.length > 0) {
    summaryText = "Filtros activos: " + activeParts.join(", ")
  }

  // Update summary display (if element exists)
  const summaryElement = document.getElementById("filterSummary")
  if (summaryElement) {
    summaryElement.textContent = summaryText
    summaryElement.style.display = summaryText ? "block" : "none"
  }
}

/**
 * Clear all filters
 */
FilterManager.prototype.clearAllFilters = function () {
  // Reset filters to default
  this.activeFilters = {
    status: "vigentes",
    type: "todos-tipos",
    period: "2025-febrero",
  }
  this.searchTerm = ""

  // Reset UI
  const searchInput = document.getElementById("searchInput")
  if (searchInput) {
    searchInput.value = ""
  }

  const periodFilter = document.getElementById("periodFilter")
  if (periodFilter) {
    periodFilter.value = "2025-febrero"
  }

  // Reset filter buttons
  this.updateFilterButtonsUI("status", "vigentes")
  this.updateFilterButtonsUI("type", "todos-tipos")

  // Apply filters
  this.applyFilters()

  console.log("All filters cleared")
}

/**
 * Show loading state
 */
FilterManager.prototype.showLoadingState = () => {
  const tables = document.querySelectorAll(".table-responsive")
  tables.forEach((table) => {
    table.classList.add("loading")
  })
}

/**
 * Hide loading state
 */
FilterManager.prototype.hideLoadingState = () => {
  const tables = document.querySelectorAll(".table-responsive")
  tables.forEach((table) => {
    table.classList.remove("loading")
  })
}

/**
 * Get current filter state
 */
FilterManager.prototype.getCurrentFilters = function () {
  return {
    status: this.activeFilters.status,
    type: this.activeFilters.type,
    period: this.activeFilters.period,
    search: this.searchTerm,
    currentStatus: window.app ? window.app.currentStatus : "ingreso",
  }
}

/**
 * Set filters programmatically
 */
FilterManager.prototype.setFilters = function (filters) {
  if (filters.status && statusNames[filters.status]) {
    const statusTab = document.querySelector('[data-status="' + filters.status + '"]')
    if (statusTab) {
      statusTab.click()
    }
  }

  if (filters.filter) {
    const filterButton = document.querySelector('[data-filter="' + filters.filter + '"]')
    if (filterButton) {
      filterButton.click()
    }
  }

  if (filters.search !== undefined) {
    const searchInput = document.getElementById("searchInput")
    if (searchInput) {
      searchInput.value = filters.search
      this.searchTerm = filters.search.toLowerCase().trim()
    }
  }

  this.applyFilters()
}

// Create global instance
window.filterManager = new FilterManager()
