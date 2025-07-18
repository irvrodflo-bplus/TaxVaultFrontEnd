<?php 
    require __DIR__ . '/core/api.php';

    global $API_BASE_URL;
?>

<style>
.toggle-view {
    min-width: 80px;
    transition: all 0.3s ease;
}

.view-section {
    transition: opacity 0.3s ease;
}
</style>

<?php require   __DIR__ . '/menu.php' ?>

<div class="card border-0 shadow-sm my-4">
    <div class="card-header bg-white d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 py-3 px-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-chart-line text-info me-2"></i>
            <h5 class="fw-bold mb-0 text-gray-800">Análisis Financiero</h5>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 small text-muted fw-medium">PERIODO:</label>
            <div class="position-relative" style="width: 100px;">
                <select id="year" name="year" class="form-select form-select-sm border-info shadow-none ps-3 pe-4">
                    <?php
                        $currentYear = date('Y'); 
                        for ($year = 2022; $year <= 2025; $year++) {
                            $selected = ($year == $currentYear) ? 'selected' : '';
                            echo "<option value=\"$year\" $selected>$year</option>";
                        }
                    ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto col-10">
    <div id="document-report-container"></div>
</div>
<?php require   __DIR__ . '/footer.php' ?>
<script>
    const baseUrl = <?= json_encode($API_BASE_URL . '/local_vault') ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('year');
        loadYearlyReports(select.value);

        select.addEventListener('change', function () {
            loadYearlyReports(this.value);
        });
    });

    function loadYearlyReports(year) {
        const url = `${baseUrl}/analytics/${year}`;
        showLoader();

        $.get(url, function (response) {
            const container = $('#document-report-container');
            container.empty(); 

            const docTypes = {
                I: 'Ingresos',
                E: 'Egresos',
                P: 'Complementos de Pago',
                N: 'Nómina',
                T: 'Traslados'
            };

            Object.entries(docTypes).forEach(([key, label]) => {
                const data = response.data[key];
                if (!data) return;

                const cardId = `card-${key}`;
                const chartTotalId = `chart-total-${key}`;
                const chartCountId = `chart-count-${key}`;
                const viewAId = `view-${key}-A`;
                const viewBId = `view-${key}-B`;

                const resumenHtml = `
                    <table class="table table-sm table-borderless mb-0 small">
                        <tbody>
                            <tr class="border-bottom border-light">
                                <td class="text-muted ps-1 py-1">Monto emitido:</td>
                                <td class="text-success text-end fw-bold pe-1 py-1">${formatCurrency(data.total.total_emitted)}</td>
                            </tr>
                            <tr class="border-bottom border-light">
                                <td class="text-muted ps-1 py-1">Monto recibido:</td>
                                <td class="text-danger text-end fw-bold pe-1 py-1">${formatCurrency(data.total.total_received)}</td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr class="my-1 border-light"></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-1 py-1">No. CFDI's emitidos:</td>
                                <td class="text-info text-end fw-bold pe-1 py-1">${numberWithCommas(data.total.count_emitted)}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-1 py-1">No. CFDI's recibidos:</td>
                                <td class="text-warning text-end fw-bold pe-1 py-1">${numberWithCommas(data.total.count_received)}</td>
                            </tr>
                        </tbody>
                    </table>
                `;

                const cardHtml = `
                    <div class="card border-0 shadow-sm my-4" id="${cardId}">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="fw-bold">${label}</span>
                            <button class="btn btn-sm btn-outline-info toggle-view" data-target="${key}">
                                <span class="toggle-label">Ver No. CFDI</span>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4">
                                    <div class="card shadow-sm p-0 mx-auto" style="max-width: 220px;">
                                        <div class="card-header py-1 px-2">
                                            <h6 class="fw-bold mb-0 small">Resumen</h6>
                                        </div>
                                        <div class="card-body p-2 resumen-${key}">
                                            ${resumenHtml}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-8 view-section" id="${viewAId}">
                                    <canvas id="${chartTotalId}" style="height: 300px;"></canvas>
                                </div>
                                <div class="col-8 view-section" id="${viewBId}" style="display: none;">
                                    <canvas id="${chartCountId}" style="height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                container.append(cardHtml);

                const monthNames = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                    'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                const monthlyDataMap = {};
                data.monthly.forEach(m => {
                    monthlyDataMap[m.number] = m;
                });

                const fullMonthlyData = [];
                for (let i = 1; i <= 12; i++) {
                    fullMonthlyData.push(monthlyDataMap[i] || {
                        number: i,
                        total_emitted: 0,
                        total_received: 0,
                        count_emitted: 0,
                        count_received: 0
                    });
                }
                const labels = fullMonthlyData.map(m => monthNames[m.number]);
                const totalEmitted = fullMonthlyData.map(m => parseFloat(m.total_emitted));
                const totalReceived = fullMonthlyData.map(m => parseFloat(m.total_received));
                const countEmitted = fullMonthlyData.map(m => m.count_emitted);
                const countReceived = fullMonthlyData.map(m => m.count_received);

                renderBarChart(chartTotalId, labels, 'Monto emitido vs recibido', [
                    {
                        label: 'Emitidos',
                        backgroundColor: '#28a745',
                        data: totalEmitted
                    },
                    {
                        label: 'Recibidos',
                        backgroundColor: '#dc3545',
                        data: totalReceived
                    }
                ]);

                renderBarChart(chartCountId, labels, 'No. CFDIs emitidos vs recibidos', [
                    {
                        label: 'Emitidos',
                        backgroundColor: '#007bff',
                        data: countEmitted
                    },
                    {
                        label: 'Recibidos',
                        backgroundColor: '#ffc107',
                        data: countReceived
                    }
                ]);

                closeLoader();
            });

            $('.toggle-view').off('click').on('click', function () {
                const type = $(this).data('target');
                $(`#view-${type}-A`).toggle();
                $(`#view-${type}-B`).toggle();

                const label = $(this).find('.toggle-label');
                label.text(label.text() === 'Ver No. CFDI' ? 'Ver Montos' : 'Ver No. CFDI');
            });
        });
    }

    function renderBarChart(canvasId, labels, title, datasets) {
        const ctx = document.getElementById(canvasId).getContext('2d');

        if (window[canvasId + '_chart']) {
            window[canvasId + '_chart'].destroy();
        }

        datasets.forEach((dataset, index) => {
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, dataset.backgroundColor + 'CC');
            gradient.addColorStop(1, dataset.backgroundColor + '33');
            dataset.backgroundColor = gradient;
            dataset.borderRadius = 8;
            dataset.barPercentage = 0.6;
            dataset.categoryPercentage = 0.5;
        });

        window[canvasId + '_chart'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: title,
                        font: {
                            size: 18,
                            weight: 'bold',
                            family: 'Segoe UI'
                        },
                        color: '#333',
                        padding: {
                            bottom: 10
                        }
                    },
                    legend: {
                        display: true,
                        labels: {
                            font: {
                                family: 'Segoe UI',
                                size: 12
                            },
                            color: '#444',
                            boxWidth: 16
                        },
                        position: 'bottom'
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#111',
                        bodyColor: '#333',
                        borderColor: '#ccc',
                        borderWidth: 1,
                        padding: 10,
                        titleFont: {
                            weight: 'bold',
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                family: 'Segoe UI'
                            },
                            color: '#666'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value, index, values) {
                                const chartTitle = this.chart.options.plugins.title.text;
                                if (chartTitle.includes('Monto')) {
                                    return '$ ' + value.toLocaleString('es-MX', { minimumFractionDigits: 0 });
                                }
                                return value >= 1000 ? value.toLocaleString('es-MX') : value;
                            },
                            font: {
                                size: 12,
                                family: 'Segoe UI'
                            },
                            color: '#666'
                        },
                        grid: {
                            color: '#eee'
                        }
                    }
                },
                animation: {
                    duration: 800,
                    easing: 'easeOutBounce',
                    animateScale: false,
                    animateRotate: false,
                    animations: {
                        y: {
                            from: 0,
                            type: 'number',
                            easing: 'easeOutBounce',
                            duration: 800
                        }
                    }
                }
            }
        });
    }

    function formatCurrency(value) {
        const num = parseFloat(value);
        return '$' + num.toLocaleString('es-MX', {minimumFractionDigits: 2});
    }

    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
</script>