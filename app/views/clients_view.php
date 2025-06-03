<?php
use App\core\Paginator;


?>


<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Clients</h2>
            <p class="text-muted">Manage your client information</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal" id="addClientBtn">
            <i class="bi bi-plus-circle me-2"></i>Add New Client
        </button>

    </div>

    <!-- Clients Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Client List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>All time ballance</th>
                            <th>Created at</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clients)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No clients found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clients as $client): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($client['name']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($client['email']) ?></td>
                                    <td><?= htmlspecialchars($client['phone']) ?></td>
                                    <td class="<?= ($client['balance'] < 0 ? 'text-danger' : 'text-success') ?>">
                                        $<?= htmlspecialchars(number_format($client['balance'], 2)) ?>
                                    </td>
                                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($client['created_at'] ?? ''))) ?></td>
                                    <td>
                                        <a href="transactions?client_id=<?= $client["id"] ?>"><button
                                                class="btn btn-sm action-btn text-primary" title="View">
                                                <i class="bi bi-cash-stack"></i>
                                            </button></a>
                                        <button class="btn btn-sm action-btn text-warning edit-btn" title="Edit"
                                            data-client='<?= htmlspecialchars(json_encode($client), ENT_QUOTES, 'UTF-8') ?>'>
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <form class="delete-client-form" action="clients/delete/<?= $client['id'] ?>"
                                            method="POST" style="display:inline;">
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
                $params,   // e.g. ['limit' => $limit, 'search' => $search]
                2,              // window size (optional)
                count($clients),// number of items currently displayed
                $total,         // total items
                $limit          // items per page
            );
            ?>

        </div>
    </div>
</div>


<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> <!-- <- Added modal-dialog-centered here -->
        <div class="modal-content">
            <form id="addClientForm" action="clients/add" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel">Add New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="clientFullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="clientFullName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="clientEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientPhone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="clientPhone" name="phone" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Client Modal -->
<div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editClientForm" action="clients/update" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" id="editClientId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel">Update Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editClientName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editClientName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editClientEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editClientEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editClientPhone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="editClientPhone" name="phone" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Client</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(function () {
        $(document).on('click', '.edit-btn', function () {
            // Decode and parse client data
            var client = JSON.parse($(this).attr('data-client'));
            // Fill form fields
            $('#editClientId').val(client.id);
            $('#editClientName').val(client.name);
            $('#editClientEmail').val(client.email);
            $('#editClientPhone').val(client.phone);
            // Show the modal
            $('#editClientModal').modal('show');
        });


        $(document).on('submit', '.delete-client-form', function (e) {
            e.preventDefault(); // Stop the form from submitting immediately
            var form = this;

            Swal.fire({
                title: 'Are you sure?',
                text: "This client will be deleted. This action cannot be undone!",
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