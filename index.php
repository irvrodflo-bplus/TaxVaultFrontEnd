<?php 
    require __DIR__ . '/services/local-vault.service.php';

    global $API_BASE_URL;

    $service = LocalVaultService::getInstance();

    $endDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime('-1 month'));

    $data = [
        "start_date" => $startDate,
        "end_date" => $endDate
    ];

    $data = $service->getStats($data);
    $stats = $data['stats'];

    $received = $stats['received'];
    $emited = $stats['emited'];
?>

<?php require   __DIR__ . '/menu.php' ?>

<section class="row mt-3">
    <h3 class="text-muted">Datos Fiscales</h3>

    <div class="row mb-3">
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="rfc">RFC</span>
                <input value="EKU9003173C9" type="text" class="form-control" disabled>
            </div>
        </div>
        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text" id="reason">Razón Social</span>
                <input value="ESCUELA KEMPER URGATE" type="text" class="form-control" disabled>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="cp">Código Postal</span>
                <input value="12345" type="text" class="form-control" disabled>
            </div>
        </div>
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="street">Calle</span>
                <input value="Avenida Principal" type="text" class="form-control" disabled>
            </div>
        </div>
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="neighborhood">Colonia</span>
                <input value="Centro" type="text" class="form-control" disabled>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="city">Municipio</span>
                <input value="Ciudad" type="text" class="form-control" disabled>
            </div>
        </div>
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="state">Estado</span>
                <input value="Estado" type="text" class="form-control" disabled>
            </div>
        </div>
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="extNumber">No. Exterior</span>
                <input value="100" type="text" class="form-control" disabled>
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <div class="row align-items-start">
        <!-- Gráfica Emitidos -->
        <div class="col-md-4 mb-4">
            <h4 class="text-center">Emitidos</h4>
            <div class="chart-container">
                <canvas id="chartEmitted"></canvas>
            </div>
        </div>

        <!-- Tarjetas Emitidos -->
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body fs-3 text-center"><?= $emited['total'] ?></div>
                        <div class="card-footer text-center">CFDI's Emitidos</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card bg-secondary text-white mb-4">
                        <div class="card-body fs-3 text-center"><?= $emited['revenues'] ?></div>
                        <div class="card-footer text-center">Ingresos Emitidos</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body fs-3 text-center"><?= $emited['payrolls'] ?></div>
                        <div class="card-footer text-center">Nómina Emitidos</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body fs-3 text-center"><?= $emited['payment_supplements'] ?></div>
                        <div class="card-footer text-center">Complementos Emitidos</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body fs-3 text-center"><?= $emited['translates'] ?></div>
                        <div class="card-footer text-center">Traslados Emitidos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <div class="row align-items-start">
        <!-- Gráfica Recibidos -->
        <div class="col-md-4 mb-4">
            <h4 class="text-center">Recibidos</h4>
            <div class="chart-container">
                <canvas id="chartReceived"></canvas>
            </div>
        </div>

        <!-- Tarjetas Recibidos -->
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body fs-3 text-center"><?= $received['total'] ?></div>
                        <div class="card-footer text-center">CFDI's Recibidos</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card bg-secondary text-white mb-4">
                        <div class="card-body fs-3 text-center"><?= $received['revenues'] ?></div>
                        <div class="card-footer text-center">Ingresos Recibidos</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body fs-3 text-center"><?= $received['payrolls'] ?></div>
                        <div class="card-footer text-center">Nómina Recibidos</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body fs-3 text-center"><?= $received['payment_supplements'] ?></div>
                        <div class="card-footer text-center">Complementos Recibidos</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body fs-3 text-center"><?= $received['translates'] ?></div>
                        <div class="card-footer text-center">Traslados Recibidos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<form id="dataForm" class="row mt-2 mb-5">
    <div class="col-4 d-flex flex-column">
        <div>
            <h6>Periodo de Descarga</h6>
        </div>
        <div class="mt-auto">
            <div class="row">
                <div class="form-group col-6">
                    <label class="text-muted">Fecha de Inicio</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?= $startDate ?>">
                </div>
                <div class="form-group col-6">
                    <label class="text-muted">Fecha de Fin</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?= $endDate ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="col-5 d-flex flex-column">
        <div>
            <h6>Tipo de Comprobante</h6>
        </div>
        <div class="mt-auto">
            <div class="row mx-2">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="document_type" id="I" autocomplete="off" checked>
                    <label class="btn btn-outline-primary btn-sm" for="I">Ingresos</label>

                    <input type="radio" class="btn-check" name="document_type" id="E" autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm" for="E">Egresos</label>

                    <input type="radio" class="btn-check" name="document_type" id="N" autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm" for="N">Nómina</label>

                    <input type="radio" class="btn-check" name="document_type" id="P" autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm" for="P">Complementos de Pago</label>

                    <input type="radio" class="btn-check" name="document_type" id="T" autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm" for="T">Traslado</label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-3 d-flex flex-column">
        <div>
            <h6>Emitidas o Recibidas</h6>
        </div>
        <div class="mt-auto">
            <div class="row mx-2">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="type" id="emit" autocomplete="off" checked>
                    <label class="btn btn-outline-primary btn-sm" for="emit">Emitidos</label>

                    <input type="radio" class="btn-check" name="type" id="receive" autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm" for="receive">Recibidos</label>
                </div>
            </div>
        </div>
    </div>
</form>


<div class="row">
    <div class="col-4 mx-auto text-center">
        <button onclick="onExport()" class="btn btn-primary">Consultar</button>
        <button onclick="onDownload()" type="button" class="btn btn-secondary">Descargar ZIP</button>
    </div>
</div>

<?php require   __DIR__ . '/footer.php' ?>

<script>
    const apiUrl = <?= json_encode($API_BASE_URL) ?>;
    const baseUrl = <?= json_encode($API_BASE_URL . '/local_vault') ?>;

    $(document).ready(function() {
        initDateSub();
    });

    function initDateSub(){
        $('#start_date, #end_date').on('change', function() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();

            const data = {
                start_date: startDate,
                end_date: endDate
            };

            showLoadingToast('Cargando estadísticas...');

            $.ajax({
                url: `${baseUrl}/report_stats`,
                method: 'POST',
                data: data,
                success: function(response) {
                    const emited = response.stats.emited;
                    const received = response.stats.received;

                    // Actualizar tarjetas
                    $('#emited_total').text(emited.total);
                    $('#emited_revenues').text(emited.revenues);
                    $('#emited_payrolls').text(emited.payrolls);
                    $('#emited_payment_supplements').text(emited.payment_supplements);
                    $('#emited_translates').text(emited.translates);

                    $('#received_total').text(received.total);
                    $('#received_revenues').text(received.revenues);
                    $('#received_payrolls').text(received.payrolls);
                    $('#received_payment_supplements').text(received.payment_supplements);
                    $('#received_translates').text(received.translates);

                    // Actualizar gráficas
                    chartEmitted.data.datasets[0].data = keys.map(k => emited[k]);
                    chartEmitted.update();

                    chartReceived.data.datasets[0].data = keys.map(k => received[k]);
                    chartReceived.update();

                    closeLoadingToast();
                },
                error: function() {
                    closeLoadingToast();
                    showToast('Error al obtener estadísticas', 'error');
                }
            });
        });
    }

    function onDownload(){
        const data = getFormValue();
        showLoader();

        $.ajax({
            url: `${apiUrl}/pac/download_files`,
            method: 'POST',
            data,
            xhrFields: {
                responseType: 'blob' 
            },
            success: function(res, status, xhr) {
                closeLoader();

                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');    
                const day = String(today.getDate()).padStart(2, '0');

                const fileName = `Archivos-${year}-${month}-${day}.zip`;
                const url = window.URL.createObjectURL(res);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            },
            error: function(xhr) {
                closeLoader();

                showToast('Error al descargar .zip', 'error');
            }
        });
    }

    function onExport(){
        const data = getFormValue();
        showLoader();

        $.ajax({
            url: `${baseUrl}/export_report`,
            method: 'POST',
            data,
            xhrFields: {
                responseType: 'blob' 
            },
            success: function(res, status, xhr) {
                closeLoader();

                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');    
                const day = String(today.getDate()).padStart(2, '0');

                const fileName = `Reporte-${year}-${month}-${day}.xlsx`;
                const url = window.URL.createObjectURL(res);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            },
            error: function(xhr) {
                closeLoader();
                
                showToast('Error al descargar el archivo', 'error');
            }
        });
    }

    function getFormValue(){
        const statusValue = $('input[name="type"]:checked').attr('id') || 'emit';
        const documentType = $('input[name="document_type"]:checked').attr('id') || 'I';
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        const statusMapping = {
            'emit': 'emitidas',
            'receive': 'recibidas'
        };
        
        const formData = {
            "type": statusMapping[statusValue],
            "document_type": documentType,
            "start_date": startDate,
            "end_date": endDate
        };

        return formData;
    }
</script>
<script>
    const emittedData = <?= json_encode($emited); ?>;
    const receivedData = <?= json_encode($received); ?>;

    const labels = ['Nóminas', 'Complementos de pago', 'Ingresos', 'Traslados'];
    const keys = ['payrolls', 'payment_supplements', 'revenues', 'translates'];

    const backgroundColors = [
        '#198754', 
        '#ffc107', 
        '#adb5bd', 
        '#0dcaf0' 
    ];

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 10
                    }
                }
            },
            tooltip: {
                bodyFont: {
                    size: 12
                }
            }
        }
    };

    let chartEmitted, chartReceived;

    function renderChartInstances() {
        const emittedValues = keys.map(k => emittedData[k]);
        const receivedValues = keys.map(k => receivedData[k]);

        const emittedCtx = document.getElementById('chartEmitted').getContext('2d');
        const receivedCtx = document.getElementById('chartReceived').getContext('2d');

        chartEmitted = new Chart(emittedCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Emitidos',
                    data: emittedValues,
                    backgroundColor: backgroundColors
                }]
            },
            options: options
        });

        chartReceived = new Chart(receivedCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Recibidos',
                    data: receivedValues,
                    backgroundColor: backgroundColors
                }]
            },
            options: options
        });
    }
    
    renderChartInstances();
</script>