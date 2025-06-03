<?php
namespace App\controllers;

use App\core\BaseController;
use App\core\Request;
use App\middlewares\ApiAuth;
use App\middlewares\Auth;
use App\models\Client;
use App\models\Transaction;

/**
 * API controller to handle requests related to clients and transactions.
 * Requires ApiAuth middleware to ensure authorized access.
 */
class ApiController extends BaseController
{
    /**
     * Get paginated list of clients for the authenticated user.
     */
    public function clients(Request $request)
    {
        $user = ApiAuth::user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // Validate pagination parameters
        $result = $request->validate([
            'page' => 'integer|min:1|default:1',
            'limit' => 'integer|min:1|max:100|default:10',
        ], 'return');

        $page = $result['validated']['page'];
        $limit = $result['validated']['limit'];

        // Filter clients by user_id
        $filters = ['user_id' => $user->id];
        $clients = Client::paginate($page, $limit, '', [], false, $filters);
        $total = Client::countPaginated(withTrashed: false, search: '', filters: $filters);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'clients' => $clients,
        ]);
        exit;
    }

    /**
     * Get paginated list of transactions for the authenticated user,
     * optionally filtered by client and date range.
     */
    public function transactions(Request $request)
    {
        $user = ApiAuth::user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // Validate request parameters
        $result = $request->validate([
            'page' => 'integer|min:1|default:1',
            'limit' => 'integer|min:1|max:100|default:10',
            'client_id' => 'integer|nullable',
            'daterange' => 'string|nullable',
        ], 'return');

        $page = $result['validated']['page'];
        $limit = $result['validated']['limit'];
        $clientId = $result['validated']['client_id'] ?? null;
        $dateRange = $result['validated']['daterange'] ?? null;

        // Parse date range or default to last 7 days
        if (!$dateRange) {
            $today = date('Y-m-d');
            $weekAgo = date('Y-m-d', strtotime('-7 days'));
            $dateRange = "$weekAgo to $today";
        }
        [$dateFrom, $dateTo] = $this->parseDateRange($dateRange);

        // Build filters with user_id and optional client_id
        $filters = ['user_id' => $user->id];
        if ($clientId) {
            $filters['client_id'] = $clientId;
        }

        $offset = ($page - 1) * $limit;
        $transactions = Transaction::getMovements($filters, $dateFrom, $dateTo, $offset, $limit);
        $total = Transaction::getMovementsCount($filters, $dateFrom, $dateTo);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'daterange' => "$dateFrom to $dateTo",
            'transactions' => $transactions,
        ]);
        exit;
    }

    /**
     * Get paginated transactions for a specific client owned by the authenticated user.
     */
    public function clientTransactions(Request $request, $id)
    {
        $user = ApiAuth::user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // Validate pagination and date range
        $result = $request->validate([
            'page' => 'integer|min:1|default:1',
            'limit' => 'integer|min:1|max:100|default:10',
            'daterange' => 'string|nullable',
        ], 'return');

        $page = $result['validated']['page'];
        $limit = $result['validated']['limit'];
        $dateRange = $result['validated']['daterange'] ?? null;

        if (!$dateRange) {
            $today = date('Y-m-d');
            $weekAgo = date('Y-m-d', strtotime('-7 days'));
            $dateRange = "$weekAgo to $today";
        }
        [$dateFrom, $dateTo] = $this->parseDateRange($dateRange);

        // Verify that the client belongs to the authenticated user
        $client = Client::find(['id' => $id, 'user_id' => $user->id]);
        if (!$client) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Client not found']);
            exit;
        }

        // Filter transactions by client_id and user_id
        $filters = [
            'client_id' => $id,
            'user_id' => $user->id,
        ];

        $offset = ($page - 1) * $limit;
        $transactions = Transaction::getMovements($filters, $dateFrom, $dateTo, $offset, $limit);
        $total = Transaction::getMovementsCount($filters, $dateFrom, $dateTo);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'daterange' => "$dateFrom to $dateTo",
            'transactions' => $transactions,
        ]);
        exit;
    }

    /**
     * Helper method to parse a date range string "YYYY-MM-DD to YYYY-MM-DD".
     */
    private function parseDateRange($dateRange)
    {
        if (!$dateRange) {
            $today = date('Y-m-d');
            $weekAgo = date('Y-m-d', strtotime('-7 days'));
            return [$weekAgo, $today];
        }
        $parts = explode(' to ', $dateRange);
        if (count($parts) == 2) {
            return [$parts[0], $parts[1]];
        }
        return [date('Y-m-d', strtotime('-7 days')), date('Y-m-d')];
    }

    /**
     * Display API overview page for authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $this->render('api', [
            'api_key' => $user->getApiKey(),
        ]);
    }
}
