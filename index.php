<?php 
    require __DIR__ . '/services/vault.service.php';

    $service = VaultService::getInstance();

    $data = [
        "start_date" => "2025-01-12",
        "end_date" => "2025-06-12",
    ];

    $data = $service->getReport($data);
    $stats = $data['stats'];
?>

<?php require   __DIR__ . '/menu.php' ?>

<div class="row mt-3">
    <h3 class="text-muted">Datos Fiscales</h3>

    <div class="row mb-3">
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="basic-addon2">RFC</span>
                <input type="text" class="form-control" disabled>
            </div>
        </div>
        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text" id="basic-addon2">Razón Social</span>
                <input type="text" class="form-control" disabled>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="basic-addon2">Código Postal</span>
                <input type="text" class="form-control" disabled>
            </div>
        </div>
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="basic-addon2">Calle</span>
                <input type="text" class="form-control" disabled>
            </div>
        </div>
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="basic-addon2">Colonia</span>
                <input type="text" class="form-control" disabled>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="basic-addon2">Municipio</span>
                <input type="text" class="form-control" disabled>
            </div>
        </div>
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="basic-addon2">Estado</span>
                <input type="text" class="form-control" disabled>
            </div>
        </div>
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text" id="basic-addon2">No. Exterior</span>
                <input type="text" class="form-control" disabled>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-1"></div>
    <div class="col-xl-2 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body fs-3 text-center"><?= $stats['expenses'] ?></div>
            <div class="card-footer d-flex align-items-center justify-content-center">
                CFDI's Egreso
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-6">
        <div class="card bg-secondary text-white mb-4">
            <div class="card-body fs-3 text-center"><?= $stats['revenues'] ?></div>
            <div class="card-footer d-flex align-items-center justify-content-center">
                CFDI's Ingreso
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body fs-3 text-center"><?= $stats['received'] ?></div>
            <div class="card-footer d-flex align-items-center justify-content-center">
                Recibidos
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-6">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body fs-3 text-center"><?= $stats['emited'] ?></div>
            <div class="card-footer d-flex align-items-center justify-content-center">
                Emitidos
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-6">
        <div class="card bg-info text-white mb-4">
            <div class="card-body fs-3 text-center"><?= $stats['payrolls'] ?></div>
            <div class="card-footer d-flex align-items-center justify-content-center">
                Nóminas
            </div>
        </div>
    </div>
    <div class="col-1"></div>
</div>

<div class="row mt-2">
    <div class="col-4">
        <h6>Periodo de Descarga</h6>

        <div class="row">
            <div class="form-group col-6">
                <label class="text-muted">Fecha de Inicio</label>
                <input type="date" name="" id="" class="form-control">
            </div>
            <div class="form-group col-6">
                <label class="text-muted">Fecha de Inicio</label>
                <input type="date" name="" id="" class="form-control">
            </div>
        </div>
    </div>
    <div class="col-5">
        <h6>Tipo de Comprobante</h6>

        <div class="row mx-2">
            <div class="btn-group" role="group" ">
                <input type="radio" class="btn-check" name="type" id="option1" autocomplete="off" checked>
                <label class="btn btn-outline-primary btn-sm" for="option1">Ingresos</label>

                <input type="radio" class="btn-check" name="type" id="option2" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="option2">Egresos</label>

                <input type="radio" class="btn-check" name="type" id="option3" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="option3">Nómina</label>

                <input type="radio" class="btn-check" name="type" id="option4" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="option4">Complementos de Pago</label>

                <input type="radio" class="btn-check" name="type" id="option5" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="option5">Traslado</label>
            </div>
        </div>
    </div>
    <div class="col-3">
        <h6>Emitdas o Recibidas</h6>

        <div class="row mx-2">
            <div class="btn-group" role="group" ">
                <input type="radio" class="btn-check" name="status" id="emit" autocomplete="off" checked>
                <label class="btn btn-outline-primary btn-sm" for="emit">Emitidos</label>

                <input type="radio" class="btn-check" name="status" id="receive" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="receive">Recibidos</label>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-4 mx-auto text-center">
        <button class="btn btn-primary">Consultar</button>
        <button class="btn btn-secondary">Descargar ZIP</button>
    </div>
</div>

<?php require   __DIR__ . '/footer.php' ?>