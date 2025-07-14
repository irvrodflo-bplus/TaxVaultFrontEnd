
<?php require __DIR__ . '/menu.php' ?>

<div  style="margin-left: 0;">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        CFDIs recibidos 
                        <i class="fas fa-info-circle info-icon" title="Información sobre CFDIs"></i>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <button class="btn btn-outline-secondary" onclick="exportarExcel()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <button class="btn btn-outline-info" onclick="testearConexion()">
                            <i class="fas fa-database"></i> Test DB
                        </button>
                        <button class="btn btn-outline-warning" onclick="mostrarDebug()">
                            <i class="fas fa-bug"></i> Debug
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Messages -->
            <div id="messages"></div>
            
            <!-- Loading -->
            <div id="loading" class="loading">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Cargando datos...</p>
            </div>

            <!-- Debug Info -->
            <div id="debug-section" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información de Debug</h3>
                        <div class="card-tools">
                            <button class="btn btn-tool" onclick="ocultarDebug()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="debug-info" class="debug-info"></div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <?php
                                $currentPeriod = date('Y-m'); 
                            ?>

                            <select class="form-control" id="periodFilter">
                                <option value="2025-07" <?= $currentPeriod === '2025-07' ? 'selected' : '' ?>>2025 - Julio</option>
                                <option value="2025-06" <?= $currentPeriod === '2025-06' ? 'selected' : '' ?>>2025 - Junio</option>
                                <option value="2025-05" <?= $currentPeriod === '2025-05' ? 'selected' : '' ?>>2025 - Mayo</option>
                                <option value="2025-04" <?= $currentPeriod === '2025-04' ? 'selected' : '' ?>>2025 - Abril</option>
                                <option value="2025-03" <?= $currentPeriod === '2025-03' ? 'selected' : '' ?>>2025 - Marzo</option>
                                <option value="2025-02" <?= $currentPeriod === '2025-02' ? 'selected' : '' ?>>2025 - Febrero</option>
                                <option value="2025-01" <?= $currentPeriod === '2025-01' ? 'selected' : '' ?>>2025 - Enero</option>
                                <option value="2024-12" <?= $currentPeriod === '2024-12' ? 'selected' : '' ?>>2024 - Diciembre</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="UUID, RFC o receptor" id="searchInput">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="aplicarFiltros()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="limpiarFiltros()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary btn-block" onclick="cargarDatos()">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-secondary btn-block" onclick="updateWithService()">
                                <i class="fas fa-refresh"></i> Sincronizar
                            </button>
                        </div>
                    </div>
                    
                    <div class="filter-buttons mb-3">
                        <button class="btn btn-primary btn-sm" data-filter="vigentes">Vigentes</button>
                        <button class="btn btn-outline-secondary btn-sm" data-filter="cancelados">Cancelados</button>
                        <button class="btn btn-outline-secondary btn-sm" data-filter="todos">Todos</button>
                        <button class="btn btn-outline-secondary btn-sm" data-filter="pue">PUE</button>
                        <button class="btn btn-outline-secondary btn-sm" data-filter="ppd">PPD</button>
                        <button class="btn btn-primary btn-sm" data-filter="todos-tipos">Todos</button>
                    </div>

                    <!-- Status Tabs -->
                    <ul class="nav nav-tabs status-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-status="ingreso">Ingreso (0)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="egreso">Egreso (0)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="traslado">Traslado (0)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="nomina">Nómina (0)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="pago">Pago (0)</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Totales Section -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Totales</h3>
                    <div class="card-tools">
                        <span id="total-registros" class="badge badge-info">0 registros</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Conteo de CFDIs</th>
                                    <th>Subtotal</th>
                                    <th>Descuento</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="totalesTableBody">
                                <tr>
                                    <td>Filtrados</td>
                                    <td id="total-count">0</td>
                                    <td id="total-subtotal">$0.00</td>
                                    <td id="total-descuento">$0.00</td>
                                    <td id="total-total">$0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- CFDIs Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">CFDIs</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm" id="cfdisTable">
                            <thead>
                                <tr>
                                    <th width="30"></th>
                                    <th>UUID</th>
                                    <th>Fecha Expedición</th>
                                    <th>RFC Emisor</th>
                                    <th>Nombre Emisor</th>
                                    <th>Serie</th>
                                    <th>Folio</th>
                                    <th>Tipo</th>
                                    <th>Método Pago</th>
                                    <th>Forma Pago</th>
                                    <th>Subtotal</th>
                                    <th>Descuento</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="cfdisTableBody">
                                <!-- Table rows will be populated by JavaScript -->
                            </tbody>
                        </table>
                        <div id="paginationControls" class="my-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
// Configuración global
const API_BASE_URL = 'php/api-re.php';
let currentData = [];
let filteredData = [];
let currentFilters = {
    status_sat: '',
    tipo_comprobante: '',
    metodo_pago: '',
    periodo: '',
    search: ''
};

// Utilidades
const Utils = {
    formatearFecha: function(fecha) {
        if (!fecha) return '-';
        const date = new Date(fecha);
        return date.toLocaleDateString('es-MX');
    },

    formatearMoneda: function(cantidad) {
        if (!cantidad || cantidad === null) return '$0.00';
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        }).format(parseFloat(cantidad));
    },

    obtenerDescripcionTipo: function(tipo) {
        const tipos = {
            'I': 'Ingreso',
            'E': 'Egreso',
            'N': 'Nómina',
            'P': 'Pago',
            'T': 'Traslado'
        };
        return tipos[tipo] || tipo;
    },

    mostrarLoading: function() {
        document.getElementById('loading').style.display = 'block';
    },

    ocultarLoading: function() {
        document.getElementById('loading').style.display = 'none';
    },

    mostrarMensaje: function(mensaje, tipo = 'info') {
        const messagesDiv = document.getElementById('messages');
        const alertClass = tipo === 'error' ? 'error-message' : 'success-message';
        messagesDiv.innerHTML = `<div class="${alertClass}">${mensaje}</div>`;
        
        setTimeout(() => {
            messagesDiv.innerHTML = '';
        }, 8000);
    },

    truncarTexto: function(texto, longitud = 30) {
        if (!texto) return '-';
        return texto.length > longitud ? texto.substring(0, longitud) + '...' : texto;
    },

    logError: function(error, context = '') {
        console.error(`Error ${context}:`, error);
        
        // Mostrar información detallada del error
        let errorMessage = error.message || 'Error desconocido';
        
        if (error.name === 'SyntaxError' && errorMessage.includes('JSON')) {
            errorMessage = 'El servidor devolvió una respuesta inválida. Posible error de PHP.';
        }
        
        this.mostrarMensaje(`${context}: ${errorMessage}`, 'error');
    }
};

// API Client mejorado
const ApiClient = {
    makeRequest: async function(url, options = {}) {
        try {
            console.log('Haciendo petición a:', url, options);
            
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });
            
            console.log('Respuesta recibida:', response.status, response.statusText);
            
            // Verificar si la respuesta es exitosa
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            // Obtener el texto de la respuesta primero
            const responseText = await response.text();
            console.log('Texto de respuesta:', responseText.substring(0, 200) + '...');
            
            // Intentar parsear como JSON
            try {
                const data = JSON.parse(responseText);
                return data;
            } catch (jsonError) {
                console.error('Error al parsear JSON:', jsonError);
                console.error('Respuesta completa:', responseText);
                throw new Error(`Respuesta no es JSON válido. Respuesta: ${responseText.substring(0, 100)}...`);
            }
            
        } catch (error) {
            console.error('Error en makeRequest:', error);
            throw error;
        }
    },

    testearConexion: async function() {
        return await this.makeRequest(`${API_BASE_URL}?ruta=test-conexion`);
    },

    obtenerDebug: async function() {
        return await this.makeRequest(`${API_BASE_URL}?ruta=debug`);
    },

    obtenerCFDIs: async function(limite = 10000, offset = 0) {
        return await this.makeRequest(`${API_BASE_URL}?ruta=cfdis&limite=${limite}&offset=${offset}`);
    },

    filtrarCFDIs: async function(filtros = {}) {
        return await this.makeRequest(`${API_BASE_URL}?ruta=filtrar-cfdis`, {
            method: 'POST',
            body: JSON.stringify({
                limite: 10000,
                offset: 0,
                ...filtros
            })
        });
    },

    obtenerEstadisticas: async function() {
        return await this.makeRequest(`${API_BASE_URL}?ruta=estadisticas`);
    }
};

// Gestor de datos
const DataManager = {
    currentPage: 1,
    pageSize: 100, // Cambia este valor para registros por página

    cargarDatos: async function(filtros = {}) {
        try {
            Utils.mostrarLoading();
            
            let response;
            if (Object.keys(filtros).length > 0) {
                response = await ApiClient.filtrarCFDIs(filtros);
            } else {
                response = await ApiClient.obtenerCFDIs();
            }
            
            if (!response.success) {
                throw new Error(response.error || 'Error en la respuesta del servidor');
            }
            
            currentData = response.data || [];
            filteredData = [...currentData];

            this.currentPage = 1; // Reinicia paginación al cargar datos
            
            this.actualizarTabla();
            this.actualizarTotales();
            this.actualizarContadores();
            
            Utils.mostrarMensaje(`Se cargaron ${currentData.length} registros exitosamente`, 'success');
            
        } catch (error) {
            Utils.logError(error, 'Error al cargar datos');
        } finally {
            Utils.ocultarLoading();
        }
    },

    aplicarFiltros: function() {
        const filtrosActivos = {};
        
        // Recopilar filtros activos
        if (currentFilters.status_sat) filtrosActivos.status_sat = currentFilters.status_sat;
        if (currentFilters.tipo_comprobante) filtrosActivos.tipo_comprobante = currentFilters.tipo_comprobante;
        if (currentFilters.metodo_pago) filtrosActivos.metodo_pago = currentFilters.metodo_pago;
        if (currentFilters.search) filtrosActivos.search = currentFilters.search;
        
        // Aplicar filtro de período si está seleccionado
        let periodo = currentFilters.periodo;
        if (!periodo) {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0'); // meses: 0-11
            periodo = `${year}-${month}`;
            currentFilters.periodo = periodo; // lo guardamos también
        }

        const [year, month] = periodo.split('-');
        filtrosActivos.fecha_inicio = `${year}-${month}-01`;
        const lastDay = new Date(year, month, 0).getDate();
        filtrosActivos.fecha_fin = `${year}-${month}-${lastDay}`;

        console.log(filtrosActivos);

        this.currentPage = 1; // Reinicia paginación al aplicar filtros
        this.cargarDatos(filtrosActivos);
    },

    actualizarTabla: function() {
        const tbody = document.getElementById('cfdisTableBody');
        tbody.innerHTML = '';

        if (filteredData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="14" class="text-center">No se encontraron registros</td></tr>';
            this.actualizarPaginacion();
            return;
        }

        const startIndex = (this.currentPage - 1) * this.pageSize;
        const endIndex = startIndex + this.pageSize;
        const pageData = filteredData.slice(startIndex, endIndex);

        pageData.forEach((cfdi, index) => {
            const realIndex = startIndex + index;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="white-space: nowrap;">
                    <button class="expand-btn mr-2" onclick="DataManager.mostrarDetalle(${realIndex})" title="Ver detalles">
                        <i class="fas fa-plus" id="icon-${realIndex}"></i>
                    </button>
                    <button class="btn btn-info btn-sm" onclick="DataManager.mostrarStatus(${realIndex})" title="Mostrar Status">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </td>
                <td title="${cfdi.uuid}">${Utils.truncarTexto(cfdi.uuid, 20)}</td>
                <td>${Utils.formatearFecha(cfdi.fecha_expedicion)}</td>
                <td>${cfdi.rfc_emisor || '-'}</td>
                <td title="${cfdi.nombre_emisor}">${Utils.truncarTexto(cfdi.nombre_emisor, 25)}</td>
                <td>${cfdi.serie || '-'}</td>
                <td>${cfdi.folio || '-'}</td>
                <td>${Utils.obtenerDescripcionTipo(cfdi.tipo_comprobante)}</td>
                <td>${cfdi.metodo_pago || '-'}</td>
                <td>${cfdi.forma_pago ? cfdi.forma_pago.split(',')[0] : '-'}</td>
                <td>${Utils.formatearMoneda(cfdi.subtotal)}</td>
                <td>${Utils.formatearMoneda(cfdi.descuento)}</td>
                <td>${Utils.formatearMoneda(cfdi.total)}</td>
                <td>
                    <span class="badge ${cfdi.status_sat === 'Vigente' ? 'badge-success' : 'badge-danger'}">
                        ${cfdi.status_sat}
                    </span>
                </td>
            `;
            tbody.appendChild(row);
        });

        this.actualizarPaginacion();
    },

    actualizarPaginacion: function () {
        const totalPages = Math.ceil(filteredData.length / this.pageSize);
        const container = document.getElementById('paginationControls');
        container.innerHTML = '';

        if (totalPages <= 1) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex justify-content-between align-items-center flex-wrap p-2 w-100 gap-2';

        const btnGroup = document.createElement('div');
        btnGroup.className = 'd-flex align-items-center flex-wrap gap-1 me-auto';

        const crearBoton = (text, page, disabled = false, active = false) => {
            const btn = document.createElement('button');
            btn.textContent = text;
            btn.className = 'btn btn-sm ' + (active ? 'btn-primary' : 'btn-outline-primary');
            btn.disabled = disabled;
            btn.onclick = () => {
                this.currentPage = page;
                this.actualizarTabla();

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            };
            return btn;
        };

        const crearEllipsis = () => {
            const span = document.createElement('span');
            span.textContent = '...';
            span.className = 'mx-1';
            return span;
        };

        btnGroup.appendChild(
            crearBoton('Anterior', this.currentPage - 1, this.currentPage === 1)
        );

        let start = Math.max(1, this.currentPage - 2);
        let end = Math.min(totalPages, this.currentPage + 2);

        if (start > 1) {
            btnGroup.appendChild(crearBoton('1', 1, false, this.currentPage === 1));
            if (start > 2) btnGroup.appendChild(crearEllipsis());
        }

        for (let i = start; i <= end; i++) {
            btnGroup.appendChild(crearBoton(i, i, false, i === this.currentPage));
        }

        if (end < totalPages) {
            if (end < totalPages - 1) btnGroup.appendChild(crearEllipsis());
            btnGroup.appendChild(crearBoton(totalPages, totalPages, false, this.currentPage === totalPages));
        }

        btnGroup.appendChild(
            crearBoton('Siguiente', this.currentPage + 1, this.currentPage === totalPages)
        );

        const totalItems = filteredData.length;
        const startItem = (this.currentPage - 1) * this.pageSize + 1;
        const endItem = Math.min(startItem + this.pageSize - 1, totalItems);

        const info = document.createElement('div');
        info.className = 'text-muted small text-end';
        info.textContent = `Mostrando ${startItem} a ${endItem} de ${totalItems} resultados`;

        wrapper.appendChild(btnGroup);
        wrapper.appendChild(info);
        container.appendChild(wrapper);
    },

    mostrarDetalle: function(index) {
        const cfdi = filteredData[index];
        if (!cfdi) {
            Swal.fire('Error', 'No se encontró la información del registro.', 'error');
            return;
        }

        // Función helper para formatear o mostrar '0.00'
        function formatoMoneda(valor) {
            return Utils.formatearMoneda(parseFloat(valor) || 0);
        }

        const detallesHtml = `
            <table class="table table-sm table-bordered text-left" style="width:100%; font-size: 0.9em;">
                <tbody>
                    <tr><th>IVATrasladado0</th><td>${formatoMoneda(cfdi.IVATrasladado0)}</td></tr>
                    <tr><th>IVATrasladado16</th><td>${formatoMoneda(cfdi.IVATrasladado16)}</td></tr>
                    <tr><th>IVAExento</th><td>${formatoMoneda(cfdi.IVAExento)}</td></tr>
                    <tr><th>IVARetenido</th><td>${formatoMoneda(cfdi.IVARetenido)}</td></tr>
                    <tr><th>ISRRetenido</th><td>${formatoMoneda(cfdi.ISRRetenido)}</td></tr>
                    <tr><th>IEPSTrasladado</th><td>${formatoMoneda(cfdi.IEPSTrasladado)}</td></tr>
                    <tr><th>IEPSTrasladado0</th><td>${formatoMoneda(cfdi.IEPSTrasladado0)}</td></tr>
                    <tr><th>IEPSTrasladado45</th><td>${formatoMoneda(cfdi.IEPSTrasladado45)}</td></tr>
                    <tr><th>IEPSTrasladado54</th><td>${formatoMoneda(cfdi.IEPSTrasladado54)}</td></tr>
                    <tr><th>IEPSTrasladado66</th><td>${formatoMoneda(cfdi.IEPSTrasladado66)}</td></tr>
                    <tr><th>IEPSRetenido</th><td>${formatoMoneda(cfdi.IEPSRetenido)}</td></tr>
                    <tr><th>LocalRetenido</th><td>${formatoMoneda(cfdi.LocalRetenido)}</td></tr>
                    <tr><th>LocalTrasladado</th><td>${formatoMoneda(cfdi.LocalTrasladado)}</td></tr>
                </tbody>
            </table>
        `;

        Swal.fire({
            title: `Detalle CFDI: ${cfdi.uuid ? Utils.truncarTexto(cfdi.uuid, 10) : ''}`,
            html: detallesHtml,
            width: '600px',
            confirmButtonText: 'Cerrar',
            customClass: {
                popup: 'p-3'
            }
        });
    },
    mostrarStatus: function(index) {
        const cfdi = filteredData[index];
        if (!cfdi) {
            Swal.fire('Error', 'No se encontró la información del registro.', 'error');
            return;
        }

        // Datos hardcodeados para mostrar en el modal
        const statusData = {
            codigo_estatus: '200',
            estado_cfdi: 'Vigente',
            es_cancelable: 'Sí',
            estatus_cancelacion: 'No cancelado',
            validacion_efos: 'Validado'
        };

        const statusHtml = `
            <table class="table table-bordered text-left" style="width:100%; font-size: 0.9em;">
                <tbody>
                    <tr><th>Código Estatus</th><td>${statusData.codigo_estatus}</td></tr>
                    <tr><th>Estado CFDI</th><td>${statusData.estado_cfdi}</td></tr>
                    <tr><th>Es Cancelable</th><td>${statusData.es_cancelable}</td></tr>
                    <tr><th>Estatus Cancelación</th><td>${statusData.estatus_cancelacion}</td></tr>
                    <tr><th>Validación EFOS</th><td>${statusData.validacion_efos}</td></tr>
                </tbody>
            </table>
        `;

        Swal.fire({
            title: `Status CFDI: ${cfdi.uuid ? Utils.truncarTexto(cfdi.uuid, 10) : ''}`,
            html: statusHtml,
            width: '500px',
            confirmButtonText: 'Cerrar',
            customClass: {
                popup: 'p-3'
            }
        });
    },

    actualizarTotales: function() {
        const totales = filteredData.reduce((acc, item) => {
            acc.count++;
            acc.subtotal += parseFloat(item.subtotal || 0);
            acc.descuento += parseFloat(item.descuento || 0);
            acc.total += parseFloat(item.total || 0);
            return acc;
        }, { count: 0, subtotal: 0, descuento: 0, total: 0 });

        document.getElementById('total-count').textContent = totales.count;
        document.getElementById('total-subtotal').textContent = Utils.formatearMoneda(totales.subtotal);
        document.getElementById('total-descuento').textContent = Utils.formatearMoneda(totales.descuento);
        document.getElementById('total-total').textContent = Utils.formatearMoneda(totales.total);
        document.getElementById('total-registros').textContent = `${totales.count} registros`;
    },

    actualizarContadores: function() {
        const contadores = currentData.reduce((acc, item) => {
            const tipo = item.tipo_comprobante;
            acc[tipo] = (acc[tipo] || 0) + 1;
            return acc;
        }, {});

        // Actualizar tabs de estado
        document.querySelectorAll('[data-status]').forEach(tab => {
            const status = tab.getAttribute('data-status');
            let count = 0;
            
            switch(status) {
                case 'ingreso': count = contadores['I'] || 0; break;
                case 'egreso': count = contadores['E'] || 0; break;
                case 'traslado': count = contadores['T'] || 0; break;
                case 'nomina': count = contadores['N'] || 0; break;
                case 'pago': count = contadores['P'] || 0; break;
            }
            
            const text = tab.textContent.split('(')[0].trim();
            tab.textContent = `${text} (${count})`;
        });
    }
};

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Cargar datos iniciales
    cargarDatos();

    // Filtros de botones
    document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Actualizar estado visual de botones
            document.querySelectorAll('[data-filter]').forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-primary');
            
            // Aplicar filtro
            switch(filter) {
                case 'vigentes':
                    currentFilters.status_sat = 'Vigente';
                    break;
                case 'cancelados':
                    currentFilters.status_sat = 'Cancelado';
                    break;
                case 'pue':
                    currentFilters.metodo_pago = 'PUE';
                    break;
                case 'ppd':
                    currentFilters.metodo_pago = 'PPD';
                    break;
                case 'todos':
                    currentFilters.status_sat = '';
                    break;
                case 'todos-tipos':
                    currentFilters.metodo_pago = '';
                    break;
            }
            
            DataManager.aplicarFiltros();
        });
    });

    // Tabs de tipo de comprobante
    document.querySelectorAll('[data-status]').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            const status = this.getAttribute('data-status');
            
            // Actualizar estado visual
            document.querySelectorAll('[data-status]').forEach(t => {
                t.classList.remove('active');
            });
            this.classList.add('active');
            
            // Aplicar filtro de tipo
            switch(status) {
                case 'ingreso': currentFilters.tipo_comprobante = 'I'; break;
                case 'egreso': currentFilters.tipo_comprobante = 'E'; break;
                case 'traslado': currentFilters.tipo_comprobante = 'T'; break;
                case 'nomina': currentFilters.tipo_comprobante = 'N'; break;
                case 'pago': currentFilters.tipo_comprobante = 'P'; break;
            }
            
            DataManager.aplicarFiltros();
        });
    });

    // Búsqueda
    document.getElementById('searchInput').addEventListener('input', function() {
        currentFilters.search = this.value;
        // Aplicar filtro con delay para evitar muchas consultas
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            DataManager.aplicarFiltros();
        }, 500);
    });

    // Filtro de período
    document.getElementById('periodFilter').addEventListener('change', function() {
        currentFilters.periodo = this.value;
        DataManager.aplicarFiltros();
    });
});

// Funciones globales
function cargarDatos() {
    DataManager.aplicarFiltros();
}

function aplicarFiltros() {
    DataManager.aplicarFiltros();
}

function limpiarFiltros() {
    // Limpiar filtros
    currentFilters = {
        status_sat: '',
        tipo_comprobante: '',
        metodo_pago: '',
        periodo: '',
        search: ''
    };
    
    // Limpiar campos de UI
    document.getElementById('searchInput').value = '';
    
    // Resetear botones
    document.querySelectorAll('[data-filter]').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-secondary');
    });
    
    // Resetear tabs
    document.querySelectorAll('[data-status]').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector('[data-status="ingreso"]').classList.add('active');
    
    // Cargar todos los datos
    DataManager.cargarDatos();
}

async function testearConexion() {
    try {
        Utils.mostrarLoading();
        const result = await ApiClient.testearConexion();
        
        if (result.success) {
            Utils.mostrarMensaje(`✅ Conexión exitosa a la base de datos: ${result.database}. Total de registros: ${result.total_registros}`, 'success');
        } else {
            Utils.mostrarMensaje('❌ Error en la conexión: ' + result.error, 'error');
        }
    } catch (error) {
        Utils.logError(error, 'Error al probar la conexión');
    } finally {
        Utils.ocultarLoading();
    }
}

async function mostrarDebug() {
    try {
        Utils.mostrarLoading();
        const result = await ApiClient.obtenerDebug();
        
        if (result.success) {
            document.getElementById('debug-info').innerHTML = '<pre>' + JSON.stringify(result.debug, null, 2) + '</pre>';
            document.getElementById('debug-section').style.display = 'block';
        } else {
            Utils.mostrarMensaje('Error al obtener información de debug: ' + result.error, 'error');
        }
    } catch (error) {
        Utils.logError(error, 'Error al obtener debug');
    } finally {
        Utils.ocultarLoading();
    }
}

function ocultarDebug() {
    document.getElementById('debug-section').style.display = 'none';
}

function toggleRow(index) {
    const icon = document.getElementById(`icon-${index}`);
    if (icon.classList.contains('fa-plus')) {
        icon.classList.remove('fa-plus');
        icon.classList.add('fa-minus');
        // Aquí podrías mostrar detalles adicionales del CFDI
    } else {
        icon.classList.remove('fa-minus');
        icon.classList.add('fa-plus');
    }
}

function exportarExcel() {
    if (!filteredData || filteredData.length === 0) {
        Utils.mostrarMensaje('No hay datos para exportar', 'error');
        return;
    }

    try {
        const datosExcel = filteredData.map(item => ({
            'UUID': item.uuid,
            'UUID Relacionado': item.uuid_relacionado || '',
            'Fecha Expedición': Utils.formatearFecha(item.fecha_expedicion),
            'Fecha Certificación': Utils.formatearFecha(item.fecha_certificacion),
            'RFC Receptor': item.rfc_receptor,
            'Nombre Receptor': item.nombre_receptor,
            'Uso CFDI': item.uso_cfdi,
            'Tipo Comprobante': Utils.obtenerDescripcionTipo(item.tipo_comprobante),
            'Método Pago': item.metodo_pago,
            'Forma Pago': item.forma_pago,
            'Versión': item.version,
            'Serie': item.serie,
            'Folio': item.folio,
            'Moneda': item.moneda,
            'Tipo Cambio': item.tipo_cambio,
            'Subtotal': item.subtotal,
            'Descuento': item.descuento,
            'Total': item.total,
            'Estado SAT': item.status_sat
        }));

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.json_to_sheet(datosExcel);
        
        XLSX.utils.book_append_sheet(wb, ws, 'CFDIs Recibidos');
        
        const fecha = new Date().toISOString().slice(0, 10);
        const nombreArchivo = `CFDIs_Emitidos_${fecha}.xlsx`;
        
        XLSX.writeFile(wb, nombreArchivo);
        
        Utils.mostrarMensaje(`✅ Archivo ${nombreArchivo} exportado exitosamente con ${datosExcel.length} registros`, 'success');
        
    } catch (error) {
        Utils.logError(error, 'Error al exportar');
    }
}

    function updateWithService(){
        const period = document.getElementById('periodFilter').value;

        showLoader();


        const [year, month] = period.split('-');
        const start_date = `${year}-${month}-01`;
        const end_date = `${year}-${month}-${new Date(year, month, 0).getDate()}`;

        const data = {
            start_date: start_date,
            end_date: end_date
        };

        $.ajax({
            url: '/endpoints/vault.endpoint.php', 
            method: 'POST',
            data: JSON.stringify({
                data,
                operation: 'updateReceived',
            }),
            contentType: 'application/json',
            success: function (response) {
                closeLoader();
                showToast('Información sincronizada con éxito', 'success');
                DataManager.aplicarFiltros();
            },
            error: function (xhr, status, error) {
                closeLoader();
                showToast('Error al sincronizar información', 'error')
            }
        });
    }
</script>

<?php require __DIR__ . '/footer.php' ?>
