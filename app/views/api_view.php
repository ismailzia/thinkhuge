<?php
// Example: $api_key is set in your controller as $user->getApiKey()
// You may want to adjust BASE_URL below to match your site (use https if needed)
$baseUrl = rtrim($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']), '/');
$apiBase = $baseUrl . '/api';
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-12 col-md-10">

            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-braces me-2"></i>
                    <h5 class="mb-0">Your API Key</h5>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" id="apiKeyField" class="form-control" readonly
                            value="<?= htmlspecialchars($api_key) ?>">
                        <button class="btn btn-outline-secondary" type="button" id="copyApiKeyBtn">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Keep your API key secret. If it is compromised, contact the admin to regenerate a new one.
                    </small>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-code-slash me-2"></i>
                    <h5 class="mb-0">How to use the API</h5>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <h6>Authentication</h6>
                        <ul>
                            <li><b>X-API-KEY</b> (HTTP header): <em>required</em></li>
                            <li><b>api_key</b> (query param): <em>alternative if header is not supported</em></li>
                        </ul>
                    </div>

                    <!-- ENDPOINT: List Clients -->
                    <hr>
                    <div class="mb-4">
                        <h6>1. List Clients</h6>
                        <table class="table table-bordered table-sm mb-2">
                            <thead>
                                <tr>
                                    <th>Endpoint</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <a href="<?= $apiBase ?>/clients?api_key=<?= urlencode($api_key) ?>"
                                            target="_blank">
                                            <?= $apiBase ?>/clients
                                        </a>
                                    </td>
                                    <td>GET</td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered table-sm mb-2">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Default</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>page</td>
                                    <td>integer</td>
                                    <td>1</td>
                                    <td>Page number</td>
                                </tr>
                                <tr>
                                    <td>limit</td>
                                    <td>integer</td>
                                    <td>10</td>
                                    <td>Results per page (max 100)</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mb-2"><b>Example:</b>
                            <a href="<?= $apiBase ?>/clients?api_key=<?= urlencode($api_key) ?>&page=1&limit=3"
                                target="_blank">
                                <?= $apiBase ?>/clients?api_key=<?= htmlspecialchars($api_key) ?>&page=1&limit=3
                            </a>
                        </div>
                        <b>Sample Response:</b>
                        <pre class="bg-light rounded px-3 py-2 mb-0"><code>{
  "success": true,
  "page": 1,
  "limit": 3,
  "total": 5,
  "clients": [
    {
      "id": 1,
      "name": "Sample Client",
      "email": "client@example.com"
    }
  ]
}</code></pre>
                    </div>

                    <!-- ENDPOINT: List Transactions -->
                    <hr>
                    <div class="mb-4">
                        <h6>2. List Transactions</h6>
                        <table class="table table-bordered table-sm mb-2">
                            <thead>
                                <tr>
                                    <th>Endpoint</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <a href="<?= $apiBase ?>/transactions?api_key=<?= urlencode($api_key) ?>"
                                            target="_blank">
                                            <?= $apiBase ?>/transactions
                                        </a>
                                    </td>
                                    <td>GET</td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered table-sm mb-2">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Default</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>page</td>
                                    <td>integer</td>
                                    <td>1</td>
                                    <td>Page number</td>
                                </tr>
                                <tr>
                                    <td>limit</td>
                                    <td>integer</td>
                                    <td>10</td>
                                    <td>Results per page (max 100)</td>
                                </tr>
                                <tr>
                                    <td>daterange</td>
                                    <td>string</td>
                                    <td>Last 7 days</td>
                                    <td>Format: "YYYY-MM-DD to YYYY-MM-DD"</td>
                                </tr>
                                <tr>
                                    <td>client_id</td>
                                    <td>integer</td>
                                    <td>-</td>
                                    <td>Filter by client</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mb-2"><b>Example:</b>
                            <a href="<?= $apiBase ?>/transactions?api_key=<?= urlencode($api_key) ?>&limit=2&daterange=2024-06-01%20to%202026-06-07"
                                target="_blank">
                                <?= $apiBase ?>/transactions?api_key=<?= htmlspecialchars($api_key) ?>&limit=2&daterange=2024-06-01
                                to 2026-06-07
                            </a>
                        </div>
                        <b>Sample Response:</b>
                        <pre class="bg-light rounded px-3 py-2 mb-0"><code>{
  "success": true,
  "page": 1,
  "limit": 2,
  "total": 6,
  "transactions": [
    {
      "id": 11,
      "client_id": 2,
      "user_id": 1,
      "amount": 45.50,
      "type": "expense",
      "description": "Invoice payment",
      "date": "2024-06-06"
    }
  ]
}</code></pre>
                    </div>

                    <!-- ENDPOINT: List Transactions for Specific Client -->
                    <hr>
                    <div class="mb-4">
                        <h6>3. List Transactions for a Specific Client</h6>
                        <table class="table table-bordered table-sm mb-2">
                            <thead>
                                <tr>
                                    <th>Endpoint</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <a href="<?= $apiBase ?>/client/1/transactions?api_key=<?= urlencode($api_key) ?>"
                                            target="_blank">
                                            <?= $apiBase ?>/client/{id}/transactions
                                        </a>
                                    </td>
                                    <td>GET</td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered table-sm mb-2">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Default</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>integer</td>
                                    <td>-</td>
                                    <td>The client ID (in URL path)</td>
                                </tr>
                                <tr>
                                    <td>page</td>
                                    <td>integer</td>
                                    <td>1</td>
                                    <td>Page number</td>
                                </tr>
                                <tr>
                                    <td>limit</td>
                                    <td>integer</td>
                                    <td>10</td>
                                    <td>Results per page (max 100)</td>
                                </tr>
                                <tr>
                                    <td>daterange</td>
                                    <td>string</td>
                                    <td>Last 7 days</td>
                                    <td>Format: "YYYY-MM-DD to YYYY-MM-DD"</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mb-2"><b>Example:</b>
                            <a href="<?= $apiBase ?>/client/1/transactions?api_key=<?= urlencode($api_key) ?>&limit=2&daterange=2024-06-01%20to%202026-06-07"
                                target="_blank">
                                <?= $apiBase ?>/client/1/transactions?api_key=<?= htmlspecialchars($api_key) ?>&limit=2&daterange=2024-06-01
                                to 2026-06-07
                            </a>
                        </div>
                        <b>Sample Response:</b>
                        <pre class="bg-light rounded px-3 py-2 mb-0"><code>{
  "success": true,
  "page": 1,
  "limit": 2,
  "total": 2,
  "transactions": [
    {
      "id": 11,
      "client_id": 1,
      "user_id": 1,
      "amount": 45.50,
      "type": "income",
      "description": "June payment",
      "date": "2024-06-01"
    }
  ]
}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Copy API Key Script -->
<script>
    document.getElementById('copyApiKeyBtn').onclick = function () {
        var input = document.getElementById('apiKeyField');
        input.select();
        input.setSelectionRange(0, 99999); // For mobile
        document.execCommand("copy");
        this.innerHTML = '<i class="bi bi-clipboard-check"></i> Copied!';
        setTimeout(() => {
            this.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
        }, 1300);
    };
</script>