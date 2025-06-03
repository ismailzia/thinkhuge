<?php
use App\core\Paginator;

$searchClientId = isset($_GET['client_id']) ? (int) $_GET['client_id'] : null;
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Transactions</h2>
            <p class="text-muted">Manage income and expenses for your clients</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal"
            id="addTransactionBtn">
            <i class="bi bi-plus-circle me-2"></i>Add Transaction
        </button>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Transaction List</h5>
            <div>
                <form method="get" id="clientFilterForm">
                    <select name="client_id" id="clientFilter" class="form-select form-select-sm"
                        onchange="document.getElementById('clientFilterForm').submit()">
                        <option value="">All Clients</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>" <?= ((string) $client['id'] === (string) $searchClientId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- (add other filters/search fields here if you want to preserve them!) -->
                </form>

            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No transactions found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($transaction['client']['name'] ?? 'Unknown') ?>
                                    </td>
                                    <td>
                                        <?php if ($transaction['type'] === 'income'): ?>
                                            <span class="badge bg-success">Income</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Expense</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $transaction['type'] === 'expense' ? '-' : '+' ?>
                                        $<?= number_format($transaction['amount'], 2) ?>
                                    </td>
                                    <td><?= htmlspecialchars($transaction['description'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($transaction['date'] ?? ''))) ?></td>
                                    <td>
                                        <button class="btn btn-sm action-btn text-warning edit-transaction-btn" title="Edit"
                                            data-transaction='<?= htmlspecialchars(json_encode($transaction), ENT_QUOTES, 'UTF-8') ?>'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form class="delete-transaction-form"
                                            action="transactions/delete/<?= $transaction['id'] ?>" method="POST"
                                            style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm action-btn text-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php
            $params = [
                'limit' => $limit,
                'search' => $search,
            ];
            echo Paginator::render(
                $page,
                $totalPages,
                $params,
                2,
                count($transactions),
                $total,
                $limit
            );
            ?>
        </div>
    </div>
</div>

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addTransactionForm" action="transactions/add" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addTransactionModalLabel">Add Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="transactionClient" class="form-label">Client</label>
                        <select class="form-select" id="transactionClient" name="client_id" required>
                            <option value="">Select a client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="transactionType" class="form-label">Type</label>
                        <select class="form-select" id="transactionType" name="type" required>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="transactionAmount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="transactionAmount" name="amount" required
                            step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="transactionDescription" class="form-label">Description</label>
                        <input type="text" class="form-control" id="transactionDescription" name="description"
                            maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="transactionDate" class="form-label">Date</label>
                        <input type="date" class="form-control" id="transactionDate" name="date" required
                            value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Transaction Modal -->
<div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editTransactionForm" action="transactions/update" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" id="editTransactionId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTransactionModalLabel">Update Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editTransactionClient" class="form-label">Client</label>
                        <select class="form-select" id="editTransactionClient" name="client_id" required>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editTransactionType" class="form-label">Type</label>
                        <select class="form-select" id="editTransactionType" name="type" required>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editTransactionAmount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="editTransactionAmount" name="amount" required
                            step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="editTransactionDescription" class="form-label">Description</label>
                        <input type="text" class="form-control" id="editTransactionDescription" name="description"
                            maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="editTransactionDate" class="form-label">Date</label>
                        <input type="date" class="form-control" id="editTransactionDate" name="date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function () {
        // Edit transaction
        $(document).on('click', '.edit-transaction-btn', function () {
            var t = JSON.parse($(this).attr('data-transaction'));
            $('#editTransactionId').val(t.id);
            $('#editTransactionClient').val(t.client_id);
            $('#editTransactionType').val(t.type);
            $('#editTransactionAmount').val(t.amount);
            $('#editTransactionDescription').val(t.description);
            $('#editTransactionDate').val(t.date);
            $('#editTransactionModal').modal('show');
        });

        // Delete confirmation
        $(document).on('submit', '.delete-transaction-form', function (e) {
            e.preventDefault();
            var form = this;
            Swal.fire({
                title: 'Are you sure?',
                text: "This transaction will be deleted. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>