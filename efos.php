<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Configuración
$csv_url = 'http://omawww.sat.gob.mx/cifras_sat/Documents/Listado_Completo_69-B.csv';
$local_path = 'recursos_ext/efos/Listado_Completo_69-B.csv';
$directory = 'recursos_ext/efos/';
// Manejar solicitudes AJAX
if (isset($_POST['action']) || isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? $_GET['action'];
    
    switch ($action) {
        case 'download':
            $result = downloadCSV($csv_url, $local_path, $directory);
            echo json_encode($result);
            break;
            
        case 'get_data':
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
            $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
            $filter = isset($_POST['filter']) ? $_POST['filter'] : 'todos';
            
            $result = parseCSV($local_path, $start, $length, $search, $filter);
            echo json_encode($result);
            break;
            
        case 'get_counts':
            $result = getSituationCounts($local_path);
            echo json_encode($result);
            break;
    }
    exit;
}


// Función para descargar el archivo CSV
function downloadCSV($url, $local_path, $directory) {
    try {
        // Crear directorio si no existe
        if (!file_exists($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new Exception("No se pudo crear el directorio: $directory");
            }
        }
        
        // Verificar permisos de escritura
        if (!is_writable($directory)) {
            throw new Exception("No se tienen permisos de escritura en: $directory");
        }
        
        // Descargar archivo
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Error de cURL: $error");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Error HTTP: $httpCode - No se pudo descargar el archivo");
        }
        
        if ($data === false || empty($data)) {
            throw new Exception("El archivo descargado está vacío o es inválido");
        }
        
        if (file_put_contents($local_path, $data) === false) {
            throw new Exception("No se pudo guardar el archivo en: $local_path");
        }
        
        return ['success' => true, 'message' => 'Archivo descargado exitosamente'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Función para parsear el CSV y obtener datos paginados
function parseCSV($file_path, $start = 0, $length = 25, $search = '', $filter = 'todos') {
    if (!file_exists($file_path)) {
        return [
            'success' => false,
            'message' => 'El archivo CSV no existe',
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        ];
    }

    $data = [];
    $headers = [];
    $total_records = 0;
    $filtered_records = 0;

    try {
        if (($handle = fopen($file_path, 'r')) !== false) {
            $line_number = 0;
            $all_data = [];

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $line_number++;

                // Ignorar las primeras 2 filas (instrucciones)
                if ($line_number <= 2) {
                    continue;
                }

                // Fila 3: encabezados
                if ($line_number == 3) {
                    $headers = array_map(function($h) {
                        return mb_convert_encoding(trim($h), 'UTF-8', 'ISO-8859-1');
                    }, $row);
                    continue;
                }

                // Procesar datos válidos
                if (!empty($row[0]) && is_numeric($row[0])) {
                    $record = [];
                    for ($i = 0; $i < count($headers); $i++) {
                        $valor = isset($row[$i]) ? trim($row[$i]) : '';
                        $record[] = mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
                    }
                    $all_data[] = $record;
                    $total_records++;
                }
            }
            fclose($handle);

            // Filtrado por situación y búsqueda
            $filtered_data = [];
            foreach ($all_data as $record) {
                $include = true;

                // Filtro por situación
                if ($filter !== 'todos' && isset($record[3])) {
                    $situacion = strtolower(trim($record[3]));
                    switch ($filter) {
                        case 'sentencia_favorable':
                            $include = strpos($situacion, 'sentencia favorable') !== false;
                            break;
                        case 'desvirtuado':
                            $include = strpos($situacion, 'desvirtuado') !== false;
                            break;
                        case 'definitivo':
                            $include = strpos($situacion, 'definitivo') !== false;
                            break;
                        case 'presunto':
                            $include = strpos($situacion, 'presunto') !== false;
                            break;
                    }
                }

                // Filtro por búsqueda
                if ($include && !empty($search)) {
                    $rfc = isset($record[1]) ? strtolower($record[1]) : '';
                    $nombre = isset($record[2]) ? strtolower($record[2]) : '';
                    $search_term = strtolower($search);

                    $include = (strpos($rfc, $search_term) !== false || strpos($nombre, $search_term) !== false);
                }

                if ($include) {
                    $filtered_data[] = $record;
                }
            }

            $filtered_records = count($filtered_data);
            $paged_data = array_slice($filtered_data, $start, $length);

            return [
                'success' => true,
                'headers' => $headers,
                'data' => $paged_data,
                'recordsTotal' => $total_records,
                'recordsFiltered' => $filtered_records
            ];
        } else {
            throw new Exception("No se pudo abrir el archivo CSV");
        }

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        ];
    }
}


// Función para obtener contadores por situación
function getSituationCounts($file_path) {
    if (!file_exists($file_path)) {
        return ['success' => false, 'message' => 'El archivo CSV no existe'];
    }
    
    $counts = [
        'todos' => 0,
        'sentencia_favorable' => 0,
        'desvirtuado' => 0,
        'definitivo' => 0,
        'presunto' => 0
    ];
    
    if (($handle = fopen($file_path, 'r')) !== false) {
        $line_number = 0;
        
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $line_number++;
            
            if ($line_number <= 3) continue; // Saltar encabezados
            
            if (!empty($row[0]) && is_numeric($row[0])) {
                $counts['todos']++;
                
                if (isset($row[3])) {
                    $situacion = strtolower(trim($row[3]));
                    
                    if (strpos($situacion, 'sentencia favorable') !== false) {
                        $counts['sentencia_favorable']++;
                    } elseif (strpos($situacion, 'desvirtuado') !== false) {
                        $counts['desvirtuado']++;
                    } elseif (strpos($situacion, 'definitivo') !== false) {
                        $counts['definitivo']++;
                    } elseif (strpos($situacion, 'presunto') !== false) {
                        $counts['presunto']++;
                    }
                }
            }
        }
        fclose($handle);
    }
    
    return ['success' => true, 'counts' => $counts];
}



// Verificar si el archivo existe
$file_exists = file_exists($local_path);
$file_date = $file_exists ? date('d/m/Y H:i:s', filemtime($local_path)) : 'N/A';
?>

<?php require __DIR__ . '/menu.php'; ?>

<style>
    .content-wrapper {
        margin-left: 0 !important;
        background-color: #fff !important;
    }

    .filter-btn {
        background-color: transparent;
        transition: all 0.3s ease;
        cursor: pointer;
        border-width: 4px; 
        color: black;
        padding: 0.375rem 0.75rem;
        border-radius: 0.4rem;
        box-shadow: none;
        transform: scale(1);
    }

    .filter-btn.active {
        background-color: #007bff;
        color: white;
        border-width: 2px; 
        border-color: #007bff;
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
        
    .filter-btn .badge {
        margin-left: 5px;
    }
    
    .situation-badge {
        border-radius: 10px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .info-box {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 0.25rem;
        background: #fff;
        display: flex;
        margin-bottom: 1rem;
        min-height: 80px;
        padding: 0.5rem;
        position: relative;
        width: 100%;
    }
    
    .info-box-icon {
        border-radius: 0.25rem;
        align-items: center;
        display: flex;
        font-size: 1.875rem;
        justify-content: center;
        text-align: center;
        width: 70px;
    }
    
    .info-box-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 1.8;
        flex: 1;
        padding: 0 10px;
    }
    
    .info-box-number {
        display: block;
        font-size: 1.1rem;
        font-weight: 700;
        margin-top: 0;
    }
    
    .info-box-text {
        display: block;
        font-size: 0.875rem;
        font-weight: 400;
        overflow: hidden;
        text-overflow: ellipsis;
        text-transform: uppercase;
        white-space: nowrap;
    }
    
    .table-responsive {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
    
    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border: 0;
        border-radius: 0.25rem;
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255,255,255,0.8);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .loading-spinner {
        text-align: center;
    }
    
    .spinner-border {
        width: 3rem;
        height: 3rem;
    }
</style>
<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <h1 class="m-0"><span class="text-muted">Listado de Contribuyentes </span> - Artículo 69-B CFF</h1>
                </div>
                <div ">
                    <button id="downloadBtn" class="btn btn-primary btn-sm">
                        <i class="fas fa-download"></i> Actualizar Datos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1">
                            <i class="fas fa-file-alt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Estado del Archivo</span>
                            <span class="info-box-number" id="fileStatus">
                                <?= $file_exists ? 'Disponible' : 'No disponible' ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Última Actualización</span>
                            <span class="info-box-number" id="fileDate" style="font-size: 14px;">
                                <?= $file_date ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning elevation-1">
                            <i class="fas fa-users"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Registros</span>
                            <span class="info-box-number" id="totalRecords">0</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1">
                            <i class="fas fa-filter"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Filtrados</span>
                            <span class="info-box-number" id="filteredRecords">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-filter"></i> Filtros por Situación
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="filter-buttons" id="filterButtons">
                                <button class="btn btn-secondary filter-btn active" data-filter="todos">
                                    <i class="fas fa-list"></i> Todos
                                    <span class="badge badge-light" id="count-todos">0</span>
                                </button>
                                <button class="btn btn-success filter-btn" data-filter="sentencia_favorable">
                                    <i class="fas fa-gavel"></i> Sentencia Favorable
                                    <span class="badge badge-light" id="count-sentencia_favorable">0</span>
                                </button>
                                <button class="btn btn-info filter-btn" data-filter="desvirtuado">
                                    <i class="fas fa-check-circle"></i> Desvirtuado
                                    <span class="badge badge-light" id="count-desvirtuado">0</span>
                                </button>
                                <button class="btn btn-danger filter-btn" data-filter="definitivo">
                                    <i class="fas fa-times-circle"></i> Definitivo
                                    <span class="badge badge-light" id="count-definitivo">0</span>
                                </button>
                                <button class="btn btn-warning filter-btn" data-filter="presunto">
                                    <i class="fas fa-question-circle"></i> Presunto
                                    <span class="badge badge-light" id="count-presunto">0</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-table"></i> Listado de Contribuyentes - Artículo 69-B CFF
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="contributorsTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr id="tableHeader">
                                            <!-- Los encabezados se generarán dinámicamente -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Los datos se cargarán dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <div class="mt-2">
                <h5>Procesando datos...</h5>
                <p>Por favor, espere mientras se cargan los datos del SAT.</p>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/footer.php'; ?>
    <script>
        let dataTable;
        let currentFilter = 'todos';
        let situationCounts = {};

        $(document).ready(function() {
            loadCounts();
            initializeDataTable();
            
            // Evento para descargar/actualizar datos
            $('#downloadBtn').click(function() {
                downloadFile();
            });
            
            // Eventos para filtros de situación
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                
                currentFilter = $(this).data('filter');
                dataTable.ajax.reload();
            });
        });

        function showLoading() {
            $('#loadingOverlay').show();
        }

        function hideLoading() {
            $('#loadingOverlay').hide();
        }

        function downloadFile() {
            const btn = $('#downloadBtn');
            const originalText = btn.html();
            
            btn.html('<i class="fas fa-spinner fa-spin"></i> Descargando...').prop('disabled', true);
            showLoading();
            
            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: { action: 'download' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Actualizar contadores y tabla
                        loadCounts();
                        dataTable.ajax.reload();
                        
                        // Actualizar información del archivo
                        updateFileInfo();
                        
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error de Conexión',
                        text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'Cerrar'
                    });
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                    hideLoading();
                }
            });
        }

        function loadCounts() {
            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: { action: 'get_counts' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        situationCounts = response.counts;
                        updateCountBadges();
                    }
                },
                error: function() {
                    console.error('Error al cargar los contadores');
                }
            });
        }

        function updateCountBadges() {
            for (const [key, count] of Object.entries(situationCounts)) {
                $(`#count-${key}`).text(count.toLocaleString());
            }
        }
//----------------------------------------------------------------------------------------------------------


function initializeDataTable() {
    // Si la tabla ya existe, destruirla primero
    if ($.fn.DataTable.isDataTable('#contributorsTable')) {
        $('#contributorsTable').DataTable().destroy();
        $('#contributorsTable').empty(); // Limpiar completamente la tabla
    }

    // Crear la estructura básica de la tabla
    $('#contributorsTable').html(`
        <thead>
            <tr id="tableHeader">
                <!-- Los encabezados se generarán dinámicamente -->
            </tr>
        </thead>
        <tbody>
            <!-- Los datos se cargarán dinámicamente -->
        </tbody>
    `);

    dataTable = $('#contributorsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.href,
            type: 'POST',
            data: function(d) {
                d.action = 'get_data';
                d.filter = currentFilter;
                return d;
            },
            dataSrc: function(json) {
                console.log('Server response:', json);
                
                if (!json.success) {
                    Swal.fire({
                        title: 'Error',
                        text: json.message || 'Error al cargar los datos',
                        icon: 'error',
                        confirmButtonText: 'Cerrar'
                    });
                    return [];
                }
                
                // Update headers y reinicializar tabla si es necesario
                if (json.headers && Array.isArray(json.headers)) {
                    updateTableHeaders(json.headers);
                }
                
                // Update counters
                $('#totalRecords').text(json.recordsTotal.toLocaleString());
                $('#filteredRecords').text(json.recordsFiltered.toLocaleString());
                
                return json.data || [];
            }
        },
        // NO definir columnas estáticas aquí - las crearemos dinámicamente
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
        },
        responsive: true,
        searching: true,
        searchDelay: 500,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        drawCallback: function(settings) {
            const info = dataTable.page.info();
            $('#filteredRecords').text(info.recordsDisplay.toLocaleString());
        },
        // Usar columnDefs para definir el comportamiento de columnas específicas
        columnDefs: [
            {
                targets: 0, // Primera columna (No)
                width: "5%",
                className: "text-center"
            },
            {
                targets: 1, // Segunda columna (RFC)
                width: "15%"
            },
            {
                targets: 2, // Tercera columna (Nombre)
                width: "25%"
            },
            {
                targets: 3, // Cuarta columna (Situación)
                width: "15%",
                render: function(data, type, row) {
                    if (type === 'display') {
                        const situation = data.toLowerCase();
                        let badgeClass = 'secondary';
                        
                        if (situation.includes('sentencia favorable')) {
                            badgeClass = 'success';
                        } else if (situation.includes('desvirtuado')) {
                            badgeClass = 'info';
                        } else if (situation.includes('definitivo')) {
                            badgeClass = 'danger';
                        } else if (situation.includes('presunto')) {
                            badgeClass = 'warning';
                        }
                        
                        return `<span class="badge badge-${badgeClass} situation-badge">${data}</span>`;
                    }
                    return data;
                }
            },
            {
                targets: [4, 5, 6, 7], // Columnas restantes
                width: "10%"
            }
        ]
    });
}

// 2. Crear una función separada para actualizar headers:

function updateTableHeaders(headers) {
    const $headerRow = $('#tableHeader');
    
    // Solo actualizar si los headers han cambiado
    const currentHeaders = $headerRow.find('th').map(function() {
        return $(this).text();
    }).get();
    
    const newHeaders = headers.map(h => h.toString());
    
    // Comparar si los headers son diferentes
    if (JSON.stringify(currentHeaders) !== JSON.stringify(newHeaders)) {
        $headerRow.empty();
        
        headers.forEach(function(header) {
            $headerRow.append(`<th>${header}</th>`);
        });
        
        // Forzar redibujado de la tabla
        if (dataTable) {
            dataTable.columns.adjust().draw();
        }
    }
}

// 3. Alternativa más robusta - Función para reinicializar tabla completamente:

function reinitializeTableWithHeaders(headers) {
    // Destruir tabla existente
    if ($.fn.DataTable.isDataTable('#contributorsTable')) {
        $('#contributorsTable').DataTable().destroy();
    }
    
    // Limpiar y recrear estructura
    const tableHTML = `
        <thead>
            <tr>
                ${headers.map(header => `<th>${header}</th>`).join('')}
            </tr>
        </thead>
        <tbody></tbody>
    `;
    
    $('#contributorsTable').html(tableHTML);
    
    // Crear columnas dinámicamente basadas en los headers
    const dynamicColumns = headers.map((header, index) => {
        const column = { title: header };
        
        // Aplicar configuraciones específicas según el índice o contenido
        switch(index) {
            case 0: // Primera columna
                column.width = "5%";
                column.className = "text-center";
                break;
            case 1: // RFC
                column.width = "15%";
                break;
            case 2: // Nombre
                column.width = "25%";
                break;
            case 3: // Situación
                column.width = "15%";
                column.render = function(data, type, row) {
                    if (type === 'display') {
                        const situation = data.toLowerCase();
                        let badgeClass = 'secondary';
                        
                        if (situation.includes('sentencia favorable')) {
                            badgeClass = 'success';
                        } else if (situation.includes('desvirtuado')) {
                            badgeClass = 'info';
                        } else if (situation.includes('definitivo')) {
                            badgeClass = 'danger';
                        } else if (situation.includes('presunto')) {
                            badgeClass = 'warning';
                        }
                        
                        return `<span class="badge badge-${badgeClass} situation-badge">${data}</span>`;
                    }
                    return data;
                };
                break;
            default: // Columnas restantes
                column.width = "10%";
                break;
        }
        
        return column;
    });
    
    // Inicializar DataTable con columnas dinámicas
    dataTable = $('#contributorsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.href,
            type: 'POST',
            data: function(d) {
                d.action = 'get_data';
                d.filter = currentFilter;
                return d;
            },
            dataSrc: function(json) {
                if (!json.success) {
                    Swal.fire({
                        title: 'Error',
                        text: json.message || 'Error al cargar los datos',
                        icon: 'error',
                        confirmButtonText: 'Cerrar'
                    });
                    return [];
                }
                
                // Update counters
                $('#totalRecords').text(json.recordsTotal.toLocaleString());
                $('#filteredRecords').text(json.recordsFiltered.toLocaleString());
                
                return json.data || [];
            }
        },
        columns: dynamicColumns, // Usar las columnas dinámicas
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
        },
        responsive: true,
        order: [[0, 'asc']],
        searching: true,
        searchDelay: 500,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        drawCallback: function(settings) {
            const info = dataTable.page.info();
            $('#filteredRecords').text(info.recordsDisplay.toLocaleString());
        }
    });
}

// 4. Variable para rastrear si la tabla ya fue inicializada
let tableInitialized = false;
let currentHeaders = [];

// 5. Función mejorada que combina ambos enfoques:
function handleTableInitialization(headers) {
    // Si los headers han cambiado o la tabla no está inicializada
    if (!tableInitialized || JSON.stringify(currentHeaders) !== JSON.stringify(headers)) {
        currentHeaders = [...headers];
        reinitializeTableWithHeaders(headers);
        tableInitialized = true;
    } else {
        // Solo recargar datos si los headers son los mismos
        if (dataTable) {
            dataTable.ajax.reload();
        }
    }
}

// 6. Modificar el document ready:
$(document).ready(function() {
    loadCounts();
    
    // Hacer una llamada inicial para obtener headers
    $.ajax({
        url: window.location.href,
        method: 'POST',
        data: { 
            action: 'get_data',
            start: 0,
            length: 1,
            filter: 'todos'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.headers) {
                handleTableInitialization(response.headers);
            } else {
                // Fallback: inicializar con headers por defecto
                const defaultHeaders = ['No', 'RFC', 'Nombre del Contribuyente', 'Situación', 'Oficio Presunción', 'Pub. SAT Presuntos', 'Oficio DOF Presunción', 'Pub. DOF Presuntos'];
                handleTableInitialization(defaultHeaders);
            }
        },
        error: function() {
            // Fallback: inicializar con headers por defecto
            const defaultHeaders = ['No', 'RFC', 'Nombre del Contribuyente', 'Situación', 'Oficio Presunción', 'Pub. SAT Presuntos', 'Oficio DOF Presunción', 'Pub. DOF Presuntos'];
            handleTableInitialization(defaultHeaders);
        }
    });
    
    // Resto del código...
    $('#downloadBtn').click(function() {
        downloadFile();
    });
    
    $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        currentFilter = $(this).data('filter');
        
        if (dataTable) {
            dataTable.ajax.reload();
        }
    });
});


//----------------------------------------------------------------------------------------------------------------------------------
        function updateFileInfo() {
            // Actualizar información del archivo después de la descarga
            setTimeout(function() {
                location.reload();
            }, 1000);
        }

        // Función para manejar errores de red
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            if (xhr.status === 0) {
                Swal.fire({
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor. Verifique su conexión a internet.',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            } else if (xhr.status === 500) {
                Swal.fire({
                    title: 'Error del Servidor',
                    text: 'Ocurrió un error interno del servidor. Por favor, contacte al administrador.',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        });

        // Verificar si el archivo existe al cargar la página
        $(document).ready(function() {
            <?php if (!$file_exists): ?>
                Swal.fire({
                    title: 'Archivo no encontrado',
                    text: 'El archivo CSV no existe. Por favor, descargue los datos del SAT.',
                    icon: 'warning',
                    confirmButtonText: 'Descargar ahora',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        downloadFile();
                    }
                });
            <?php endif; ?>
        });

        // Función para exportar datos (opcional)
        function exportData() {
            Swal.fire({
                title: 'Exportar Datos',
                text: 'Seleccione el formato de exportación',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'CSV',
                cancelButtonText: 'PDF',
                showDenyButton: true,
                denyButtonText: 'Excel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Exportar a CSV
                    exportToCSV();
                } else if (result.isDenied) {
                    // Exportar a Excel
                    exportToExcel();
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Exportar a PDF
                    exportToPDF();
                }
            });
        }

        function exportToCSV() {
            // Implementar exportación a CSV
            Swal.fire({
                title: 'Funcionalidad en desarrollo',
                text: 'La exportación a CSV estará disponible próximamente.',
                icon: 'info',
                confirmButtonText: 'Cerrar'
            });
        }

        function exportToExcel() {
            // Implementar exportación a Excel
            Swal.fire({
                title: 'Funcionalidad en desarrollo',
                text: 'La exportación a Excel estará disponible próximamente.',
                icon: 'info',
                confirmButtonText: 'Cerrar'
            });
        }

        function exportToPDF() {
            // Implementar exportación a PDF
            Swal.fire({
                title: 'Funcionalidad en desarrollo',
                text: 'La exportación a PDF estará disponible próximamente.',
                icon: 'info',
                confirmButtonText: 'Cerrar'
            });
        }

        // Función para mostrar detalles del contribuyente
        function showContributorDetails(rowData) {
            let detailsHTML = `
                <div class="contributor-details">
                    <table class="table table-striped table-sm">
                        <tr><td><strong>RFC:</strong></td><td>${rowData[1]}</td></tr>
                        <tr><td><strong>Nombre:</strong></td><td>${rowData[2]}</td></tr>
                        <tr><td><strong>Situación:</strong></td><td>${rowData[3]}</td></tr>
                        <tr><td><strong>Oficio Presunción:</strong></td><td>${rowData[4]}</td></tr>
                        <tr><td><strong>Pub. SAT Presuntos:</strong></td><td>${rowData[5]}</td></tr>
                        <tr><td><strong>Oficio DOF Presunción:</strong></td><td>${rowData[6]}</td></tr>
                        <tr><td><strong>Pub. DOF Presuntos:</strong></td><td>${rowData[7]}</td></tr>
                    </table>
                </div>
            `;
            
            Swal.fire({
                title: 'Detalles del Contribuyente',
                html: detailsHTML,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        }

        // Agregar evento click a las filas de la tabla
        $(document).on('click', '#contributorsTable tbody tr', function() {
            const rowData = dataTable.row(this).data();
            if (rowData) {
                showContributorDetails(rowData);
            }
        });

        // Estilo adicional para las filas clickeables
        $(document).ready(function() {
            $('<style>')
                .prop('type', 'text/css')
                .html(`
                    #contributorsTable tbody tr {
                        cursor: pointer;
                        transition: background-color 0.3s ease;
                    }
                    
                    #contributorsTable tbody tr:hover {
                        background-color: #f8f9fa !important;
                    }
                    
                    .contributor-details .table td {
                        border: none;
                        padding: 8px 12px;
                    }
                    
                    .contributor-details .table td:first-child {
                        width: 200px;
                        background-color: #f8f9fa;
                    }
                    
                    .swal2-html-container {
                        max-height: 400px;
                        overflow-y: auto;
                    }
                    
                    .filter-btn:hover {
                        transform: translateY(-2px);
                    }
                    
                    .situation-badge {
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                `)
                .appendTo('head');
        });

        // Función para actualizar el estado de la aplicación
        function updateAppStatus() {
            const now = new Date();
            const statusText = `Última verificación: ${now.toLocaleString()}`;
            
            // Agregar información de estado en la consola para debugging
            console.log('Estado de la aplicación:', {
                archivo_existe: <?= $file_exists ? 'true' : 'false' ?>,
                filtro_actual: currentFilter,
                registros_cargados: dataTable ? dataTable.page.info().recordsDisplay : 0,
                timestamp: now.toISOString()
            });
        }

        // Actualizar estado cada 5 minutos
        setInterval(updateAppStatus, 300000);

        // Función para mostrar ayuda
        function showHelp() {
            Swal.fire({
                title: 'Ayuda - Listado de Contribuyentes SAT',
                html: `
                    <div class="text-left">
                        <h6><i class="fas fa-info-circle"></i> Funcionalidades:</h6>
                        <ul>
                            <li><strong>Búsqueda:</strong> Use el campo de búsqueda para filtrar por RFC o nombre</li>
                            <li><strong>Filtros:</strong> Use los botones de situación para filtrar los registros</li>
                            <li><strong>Detalles:</strong> Haga clic en cualquier fila para ver los detalles completos</li>
                            <li><strong>Actualización:</strong> Use el botón "Actualizar Datos" para descargar la versión más reciente</li>
                        </ul>
                        
                        <h6><i class="fas fa-exclamation-triangle"></i> Situaciones:</h6>
                        <ul>
                            <li><span class="badge badge-success">Sentencia Favorable</span> - Contribuyente con resolución favorable</li>
                            <li><span class="badge badge-info">Desvirtuado</span> - Contribuyente que desvirtúo la presunción</li>
                            <li><span class="badge badge-danger">Definitivo</span> - Contribuyente en listado definitivo</li>
                            <li><span class="badge badge-warning">Presunto</span> - Contribuyente con presunción de operaciones inexistentes</li>
                        </ul>
                        
                        <h6><i class="fas fa-book"></i> Marco Legal:</h6>
                        <p>Este listado se publica conforme al Artículo 69-B del Código Fiscal de la Federación.</p>
                    </div>
                `,
                width: '700px',
                confirmButtonText: 'Cerrar'
            });
        }

        // Agregar botón de ayuda al navbar
        $(document).ready(function() {
            const helpButton = `
                <li class="nav-item">
                    <button class="btn btn-info btn-sm mr-2" onclick="showHelp()">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </button>
                </li>
            `;
            $('.navbar-nav.ml-auto').prepend(helpButton);
        });
    </script>
