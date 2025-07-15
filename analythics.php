<?php 
    require __DIR__ . '/services/local-vault.service.php';

    function formatCurrency($value) {
        return '$' . number_format($value, 2, '.', ',');
    }

    function formatCount($value) {
        return number_format($value, 0, '', ',');
    }

    global $API_BASE_URL;

    $service = LocalVaultService::getInstance();

    $currentYear = date('Y');

    $data = [
        "year" => $currentYear,
    ];

    $response = $service->getYearReport($data);
    $totals = $response['totals'];
?>

<?php require   __DIR__ . '/menu.php' ?>

<div class="row mt-5">
	<section class="col-4">
		<div class="mb-4">
			<label for="year" class="form-label fw-semibold">Periodo</label>
			<select id="year" name="year" class="form-select">
			<?php
				for ($year = 2022; $year <= 2025; $year++) {
				$selected = ($year == $currentYear) ? 'selected' : '';
				echo "<option value=\"$year\" $selected>$year</option>";
				}
			?>
			</select>
		</div>

		<div class="card shadow-sm">
			<div class="card-header">
				<h5 class="fw-bold">Resumen financiero</h5>
			</div>
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center mb-4">
				<span class="text-muted">Total de ingresos</span>
				<span id="total-revenue" class="fs-6 fw-fold text-success text-end"><?= formatCurrency($totals['revenue']) ?></span>
				</div>

				<div class="d-flex justify-content-between align-items-center mb-4">
				<span class="text-muted">Total de egresos</span>
				<span id="total-expense" class="fs-6 fw-fold text-danger text-end"><?= formatCurrency($totals['expense']) ?></span>
				</div>

				<div class="d-flex justify-content-between align-items-center mb-4">
				<span class="text-muted">Balance</span>
				<span id="total-balance" class="fs-6 fw-fold text-primary text-end"><?= formatCurrency($totals['balance']) ?></span>
				</div>

				<div class="d-flex justify-content-between align-items-center mb-4">
				<span class="text-muted">Total de CFDI's ingresos</span>
					<span id="total-cfdi-revenue" class="fs-6 fw-fold text-end"><?= formatCount($totals['count_revenue']) ?></span>
				</div>

				<div class="d-flex justify-content-between align-items-center">
					<span class="text-muted">Total de CFDI's egresos</span>
					<span id="total-cfdi-expense" class="fs-6 fw-fold text-end"><?= formatCount($totals['count_expense']) ?></span>
				</div>
			</div>
		</div>
		<div class="card shadow-sm mt-4">
  <div class="card-header">
    <h5 class="fw-bold">Estadísticas adicionales</h5>
  </div>
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <span class="text-muted">Meses con ingresos</span>
      <span id="months-with-revenue" class="fs-6 fw-fold text-success text-end">0</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <span class="text-muted">Meses con egresos</span>
      <span id="months-with-expense" class="fs-6 fw-fold text-danger text-end">0</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <span class="text-muted">Ingreso promedio mensual</span>
      <span id="avg-monthly-revenue" class="fs-6 fw-fold text-success text-end">$0.00</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <span class="text-muted">Egreso promedio mensual</span>
      <span id="avg-monthly-expense" class="fs-6 fw-fold text-danger text-end">$0.00</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <span class="text-muted">CFDI emitidos promedio mensual</span>
      <span id="avg-monthly-cfdi-revenue" class="fs-6 fw-fold text-end">0</span>
    </div>

    <div class="d-flex justify-content-between align-items-center">
      <span class="text-muted">CFDI recibidos promedio mensual</span>
      <span id="avg-monthly-cfdi-expense" class="fs-6 fw-fold text-end">0</span>
    </div>
  </div>
</div>

	</section>

	<section class="col-8">
		<div class="mb-4">
			<canvas id="monthlyChart"></canvas>
		</div>
		<div>
			<canvas id="cfdiChart"></canvas>
		</div>
	</section>
</div>

<?php require   __DIR__ . '/footer.php' ?>

<script>
const baseUrl = <?= json_encode($API_BASE_URL . '/local_vault') ?>;

let chart, cfdiChart;

function renderChart(revenues, expenses) {
	const labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

	if (chart) chart.destroy();

	const ctx = document.getElementById('monthlyChart').getContext('2d');
	chart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: labels,
			datasets: [
				{
					label: 'Ingresos',
					data: revenues,
					backgroundColor: 'rgba(75, 192, 192, 0.7)',
					borderColor: 'rgba(75, 192, 192, 1)',
					borderWidth: 2,
					borderRadius: 6
				},
				{
					label: 'Egresos',
					data: expenses,
					backgroundColor: 'rgba(255, 99, 132, 0.7)',
					borderColor: 'rgba(255, 99, 132, 1)',
					borderWidth: 2,
					borderRadius: 6
				}
			]
		},
		options: {
			responsive: true,
			plugins: {
				title: {
					display: true,
					text: 'Ingresos vs Egresos por Mes'
				},
				tooltip: {
					callbacks: {
						label: function(ctx) {
							return `${ctx.dataset.label}: $${new Intl.NumberFormat('es-MX').format(ctx.raw)}`;
						}
					}
				}
			},
			scales: {
				y: {
					beginAtZero: true,
					ticks: {
						callback: value => '$' + new Intl.NumberFormat('es-MX').format(value)
					}
				}
			}
		}
	});
}

function renderCfdiChart(emitted, received) {
	const labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

	if (cfdiChart) cfdiChart.destroy();

	const ctx = document.getElementById('cfdiChart').getContext('2d');
	cfdiChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: labels,
			datasets: [
				{
					label: 'CFDI Emitidos',
					data: emitted,
					backgroundColor: 'rgba(54, 162, 235, 0.7)',
					borderColor: 'rgba(54, 162, 235, 1)',
					borderWidth: 2,
					borderRadius: 6
				},
				{
					label: 'CFDI Recibidos',
					data: received,
					backgroundColor: 'rgba(255, 206, 86, 0.7)',
					borderColor: 'rgba(255, 206, 86, 1)',
					borderWidth: 2,
					borderRadius: 6
				}
			]
		},
		options: {
			responsive: true,
			plugins: {
				title: {
					display: true,
					text: 'CFDI Emitidos vs Recibidos por Mes'
				},
				tooltip: {
					callbacks: {
						label: function(ctx) {
							return `${ctx.dataset.label}: ${new Intl.NumberFormat('es-MX').format(ctx.raw)}`;
						}
					}
				}
			},
			scales: {
				y: {
					beginAtZero: true,
					ticks: {
						callback: value => new Intl.NumberFormat('es-MX').format(value)
					}
				}
			}
		}
	});
}

async function updateChartFromYear(year) {
    try {
        showLoadingToast();
        const url = `${baseUrl}/analythics/${year}`;
        const res = await fetch(url);
        const json = await res.json();

        const revenues = Array(12).fill(0);
        const expenses = Array(12).fill(0);
        const emitted = Array(12).fill(0);
        const received = Array(12).fill(0);

        json.monthly.forEach(item => {
            const i = item.month - 1;
            revenues[i] = item.revenue || 0;
            expenses[i] = item.expense || 0;
            emitted[i] = item.count_revenue || 0;
            received[i] = item.count_expense || 0;
        });

        renderChart(revenues, expenses);
        renderCfdiChart(emitted, received);

        // Actualizar los totales (ya lo tienes)
        document.getElementById('total-revenue').textContent =
            '$' + Number(json.totals.revenue || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 });

        document.getElementById('total-expense').textContent =
            '$' + Number(json.totals.expense || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 });

        document.getElementById('total-balance').textContent =
            '$' + Number(json.totals.balance || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 });

        document.getElementById('total-cfdi-revenue').textContent =
            Number(json.totals.count_revenue || 0).toLocaleString('es-MX');

        document.getElementById('total-cfdi-expense').textContent =
            Number(json.totals.count_expense || 0).toLocaleString('es-MX');

        // --- Aquí las estadísticas nuevas ---
        const monthsWithRevenue = revenues.filter(v => v > 0).length;
        const monthsWithExpense = expenses.filter(v => v > 0).length;
        const avgMonthlyRevenue = revenues.reduce((a, b) => a + b, 0) / 12;
        const avgMonthlyExpense = expenses.reduce((a, b) => a + b, 0) / 12;
        const avgMonthlyCfdiRevenue = emitted.reduce((a, b) => a + b, 0) / 12;
        const avgMonthlyCfdiExpense = received.reduce((a, b) => a + b, 0) / 12;

        document.getElementById('months-with-revenue').textContent = monthsWithRevenue;
        document.getElementById('months-with-expense').textContent = monthsWithExpense;
        document.getElementById('avg-monthly-revenue').textContent =
            '$' + avgMonthlyRevenue.toLocaleString('es-MX', { minimumFractionDigits: 2 });
        document.getElementById('avg-monthly-expense').textContent =
            '$' + avgMonthlyExpense.toLocaleString('es-MX', { minimumFractionDigits: 2 });
        document.getElementById('avg-monthly-cfdi-revenue').textContent =
            Math.round(avgMonthlyCfdiRevenue).toLocaleString('es-MX');
        document.getElementById('avg-monthly-cfdi-expense').textContent =
            Math.round(avgMonthlyCfdiExpense).toLocaleString('es-MX');

        closeLoadingToast();
    } catch (error) {
        console.error('Error al obtener datos:', error);
    }
}

document.addEventListener('DOMContentLoaded', function () {	
	const select = document.getElementById('year');
	updateChartFromYear(select.value);

	select.addEventListener('change', function () {
		updateChartFromYear(this.value);
	});
});
</script>
