<?php 
    require __DIR__ . '/services/sync.service.php';

    $service = SyncService::getInstance();

    $response = $service->getAll();
    $records = $response['records'];
?>

<?php require __DIR__ . '/menu.php'; ?>

<div class="row mt-4">
    <div class="card p-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold">Historial de Sincronizaciones</span>
            <button class="btn btn-primary" onclick="sync()">
                <i class="fas fa-refresh"></i> Sincronizar
            </button>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Status</th>
                        <th>CDFI's Insertados</th>
                        <th>CDFI's Actualizados</th>
                        <th>Errores</th>
                        <th>Usuario</th>
                    </tr>
                    <?php foreach($records as $record): ?>
                        <tr>
                            <td><?= $record['id'] ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($record['created_at'])) ?></td>
                            <td>
                                <?php
                                    $status = strtolower($record['status']);

                                    $badgeClass = match ($status) {
                                        'success' => 'bg-success',
                                        'partial' => 'bg-warning text-dark',
                                        'error'   => 'bg-danger ',
                                        default   => 'bg-secondary',
                                    };

                                    $statusText = match ($status) {
                                        'success' => 'Éxito',
                                        'partial' => 'Parcial',
                                        'error'   => 'Error',
                                        default   => ucfirst($status),
                                    };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                            </td>
                            <td><?= number_format($record['inserted']) ?></td>
                            <td><?= number_format($record['updated']) ?></td>
                            <td><?= number_format($record['errors']) ?></td>
                            <td><?= htmlspecialchars($record['user']) ?></td>
                        </tr>
                    <?php endforeach ?>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>

<script>
    function sync() {
        showLoader();

        $.ajax({
            url: 'endpoints/vault.endpoint.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                operation: 'syncLast'
            }),
            success: function(response) {
                const body = {
                    updated: response.actualizados,
                    errors: response.errores,
                    inserted: response.insertados
                };

                saveRecord(body);
            },
            error: function(xhr, status, error) {
                showToast('Sincronización fallieda', 'error');
                closeLoader();
            }
        });
    }

    function saveRecord(data) {
        $.ajax({
            url: '/endpoints/sync.endpoint.php', 
            method: 'POST',
            data: JSON.stringify({
                data,
                operation: 'create',
            }),
            contentType: 'application/json',
            success: function (response) {
                closeLoader();
                showToast('Información sincronizada con éxito', 'success');
                location.reload();
            },
            error: function (xhr, status, error) {
                closeLoader();
                showToast('Error al sincronizar información', 'error')
            }
        });
    }
</script>
