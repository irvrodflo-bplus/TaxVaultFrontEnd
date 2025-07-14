<?php 
    require __DIR__ . '/services/vault.service.php';

    global $API_BASE_URL;

    $service = VaultService::getInstance();

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

<section>
    <div class="row">
        <div class="col-1"></div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div id="emited_total" class="card-body fs-3 text-center"><?= $emited['total'] ?></div>
                <div class="card-footer d-flex align-items-center justify-content-center">
                    CFDI's Emitidos
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-secondary text-white mb-4">
                <div id="emited_revenues" class="card-body fs-3 text-center"><?= $emited['revenues'] ?></div>
                <div class="card-footer d-flex align-items-center justify-content-center">
                    Ingresos Emitidos
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div id="emited_payrolls" class="card-body fs-3 text-center"><?= $emited['payrolls'] ?></div>
                <div class="card-footer d-flex align-items-center justify-content-center">
                    Nómina Emitidos
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div id="emited_payment_supplements" class="card-body fs-3 text-center"><?= $emited['payment_supplements'] ?></div>
                <div class="card-footer d-flex align-items-center justify-content-center">
                    Complementos Emitidos
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div id="emited_translates" class="card-body fs-3 text-center"><?= $emited['translates'] ?></div>
                <div class="card-footer d-flex align-items-center justify-content-center">
                    Traslados Emitidos
                </div>
            </div>
        </div>
        <div class="col-1"></div>
    </div>

    <div class="row">
        <div class="col-1"></div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div id="received_total" class="card-body fs-3 text-center"><?= $received['total'] ?></div>
                <div class="card-footer d-flex align-items-center justify-content-center">
                    CFDI's Recibidos
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-secondary text-white mb-4">
                <div id="received_revenues" class="card-body fs-3 text-center"><?= $received['revenues'] ?></div>
                <div class="card-footer d-flex align-items-center justify-content-center">
                    Ingresos Recibidos
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div id="received_payrolls" class="card-body fs-3 text-center"><?= $received['payrolls'] ?></div>
                <div class="card-footer d-flex align-items-center justify-content-center">
                    Nómina Recibidos
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div id="received_payment_supplements" class="card-body fs-3 text-center"><?= $received['payment_supplements'] ?></div>
                <div class="card-footer d-flex align-items-center justify-content-center">
                    Complementos Recibidos
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div id="received_translates" class="card-body fs-3 text-center"><?= $received['translates'] ?></div>
                <div class="card-footer d-flex align-items-center justify-content-center">
                    Traslados Recibidos
                </div>
            </div>
        </div>
        <div class="col-1"></div>
    </div>
</section>


<form id="dataForm" class="row mt-2">
    <div class="col-4">
        <h6>Periodo de Descarga</h6>

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
    <div class="col-5">
        <h6>Tipo de Comprobante</h6>

        <div class="row mx-2">
            <div class="btn-group" role="group" ">
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
    <div class="col-3">
        <h6>Emitdas o Recibidas</h6>

        <div class="row mx-2">
            <div class="btn-group" role="group" ">
                <input type="radio" class="btn-check" name="type" id="emit" autocomplete="off" checked>
                <label class="btn btn-outline-primary btn-sm" for="emit">Emitidos</label>

                <input type="radio" class="btn-check" name="type" id="receive" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="receive">Recibidos</label>
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
    const baseUrl = <?= json_encode($API_BASE_URL . '/pac') ?>;

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

            $.ajax({
                url: `${baseUrl}/report_stats`,
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success && response.stats) {
                        const emited = response.stats.emited;
                        const received = response.stats.received;

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
                    } else {
                        showToast('Datos inválidos en la respuesta', 'warning');
                    }
                },
                error: function() {
                    showToast('Error al obtener estadísticas', 'error');
                }
            });
        });
    }


    function onDownload(){
        const data = getFormValue();

        $.ajax({
            url: `${baseUrl}/download_files`,
            method: 'POST',
            data,
            xhrFields: {
                responseType: 'blob' 
            },
            success: function(res, status, xhr) {
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
                showToast('Error al descargar .zip', 'error');
            }
        });
    }

    function onExport(){
        const data = getFormValue();

        $.ajax({
            url: `${baseUrl}/export_report`,
            method: 'POST',
            data,
            xhrFields: {
                responseType: 'blob' 
            },
            success: function(res, status, xhr) {
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