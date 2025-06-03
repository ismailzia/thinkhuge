<?php

use App\core\Paginator;

?>
<!-- Bootstrap and daterangepicker CSS (include in your main layout if not already present) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><?= htmlspecialchars($pageTitle ?? 'Reports/Overview') ?></h2>
            <p class="text-muted">Get insights into clients transactions</p>
        </div>


        <form method="get" class="row mb-4 align-items-end" autocomplete="off" id="reportFilters">
            <div class="col-lg-5 mb-3">
                <label for="clientSelect" class="form-label">Client</label>
                <select class="form-select" id="clientSelect" name="client_id">
                    <option value="all" <?= ($selectedClientId ?? 'all') === 'all' ? 'selected' : '' ?>>All Clients
                    </option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= htmlspecialchars($client['id']) ?>" <?= ($selectedClientId ?? 'all') == $client['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($client['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-7 mb-3">
                <label for="daterange" class="form-label">Date Range</label>
                <input type="text" class="form-control" id="daterange" name="daterange"
                    value="<?= htmlspecialchars($dateRange ?? '') ?>" autocomplete="off">
            </div>
            <div class="col-lg-0 mb-3 d-none align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-graph-up"></i> Generate Report
                </button>
            </div>
        </form>
    </div>






    <!-- Summary Cards -->
    <div class="card p-3">

        <div class="row ">
            <div class="col-md-4 mb-2">
                <div class="p-3 bg-light rounded text-center">
                    <span class="fw-bold">Total Income:</span>
                    <span class="text-success fw-bold"><?= number_format($totalIncome ?? 0, 2) ?>$</span>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="p-3 bg-light rounded text-center">
                    <span class="fw-bold">Total Expenses:</span>
                    <span class="text-danger fw-bold"><?= number_format($totalExpenses ?? 0, 2) ?>$</span>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="p-3 bg-light rounded text-center">
                    <span class="fw-bold">Net Balance:</span>
                    <span class="fw-bold"><?= number_format($netBalance ?? 0, 2) ?>$</span>
                </div>
            </div>
        </div>

    </div>
    <!-- Movements Table -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Movements</h5>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($movements)): ?>
                            <?php foreach ($movements as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['date']) ?></td>
                                    <td>
                                        <?php
                                        // Find the client name (optimized for small lists)
                                        $clientName = '';
                                        foreach ($clients as $c) {
                                            if ($c['id'] == $row['client_id']) {
                                                $clientName = $c['name'];
                                                break;
                                            }
                                        }
                                        echo htmlspecialchars($clientName);
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($row['type'] === 'income'): ?>
                                            <span class="badge bg-success">Income</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Expense</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td class="text-end <?= $row['type'] === 'income' ? 'text-success' : 'text-danger' ?>">
                                        <?= ($row['type'] === 'income' ? '+' : '-') . number_format($row['amount'], 2) ?>$
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No data found for this selection.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>


            <?php

            $params = [
                'limit' => $perPage,
                'client_id' => $selectedClientId !== 'all' ? $selectedClientId : null,
                'daterange' => $dateRange,
                // Add more params if your report has more filters
            ];

            echo Paginator::render(
                $page,
                $totalPages,
                $params,
                2,
                count($movements),
                $totalMovements,
                $perPage
            );


            ?>

        </div>
    </div>
</div>

<!-- JS Dependencies (add only if not already included in layout) -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<script>
    $(function () {
        var $form = $('#reportFilters');
        var $clientSelect = $('#clientSelect');
        var $daterange = $('#daterange');

        // Date Range Picker Initialization
        $daterange.daterangepicker({
            opens: 'right',
            autoUpdateInput: false,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last 3 Months': [moment().subtract(2, 'months').startOf('month'), moment().endOf('month')],
                'All time': [moment().subtract(1000, 'months'), moment()],
            },
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            }
        });

        // Set default date range if empty (today)
        var initialDate = <?= json_encode($dateRange ?? '') ?>;
        if (!initialDate) {
            var today = moment().format('YYYY-MM-DD');
            initialDate = today + ' to ' + today;
            $daterange.val(initialDate);
        } else {
            $daterange.val(initialDate);
        }

        // Update input on picker select/cancel
        $daterange.on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
            $form.submit();
        });
        $daterange.on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('');
            $form.submit();
        });

        // Auto-submit when client changes
        $clientSelect.on('change', function () {
            $form.submit();
        });
    });
</script>