<?php

namespace App\controllers;

use App\core\BaseController;
use App\middlewares\Auth;
use App\models\Client;
use App\core\Request;
use App\models\Transaction;

/**
 * Controller for generating reports: transaction summaries, filtering by client and date.
 */
class ReportsController extends BaseController
{
    /**
     * Display report overview with filters and pagination.
     */
    public function index(Request $request)
    {
        // 1. Validate input parameters with defaults and sanitize
        $result = $request->validate([
            'page' => 'integer|min:1|default:1',
            'limit' => 'integer|min:1|max:100|default:10',
            'search' => 'string|nullable',
            'client_id' => 'integer|nullable',
            'daterange' => 'string|nullable',
        ], 'return');

        $page = $result['validated']['page'];
        $perPage = $result['validated']['limit'];
        $clientId = $result['validated']['client_id'] ?? 'all';
        $dateRange = $result['validated']['daterange'] ?? null;

        // If no date range, default to today only
        if (empty($dateRange)) {
            $today = date('Y-m-d');
            $dateRange = "$today to $today";
        }

        if (!$clientId || !is_numeric($clientId)) {
            $clientId = 'all';
        }

        // Base filter by user ID (authenticated)
        $filters = ['user_id' => Auth::user()->id];

        // Load clients for dropdown or selection list (limited to 1000)
        $clients = Client::paginate(page: 1, perPage: 1000, search: [], withTrashed: false, filters: $filters);

        // Parse date range string "YYYY-MM-DD to YYYY-MM-DD"
        [$dateFrom, $dateTo] = $this->parseDateRange($dateRange);

        // Add client filter if specific client selected
        if ($clientId !== 'all') {
            $filters['client_id'] = $clientId;
        }

        $offset = ($page - 1) * $perPage;

        // 2. Fetch transaction data and totals from model
        $movements = Transaction::getMovements($filters, $dateFrom, $dateTo, $offset, $perPage);
        $totals = Transaction::getTotals($filters, $dateFrom, $dateTo);
        $totalMovements = Transaction::getMovementsCount($filters, $dateFrom, $dateTo);
        $totalPages = ceil($totalMovements / $perPage);

        // 3. Prepare summary variables
        $totalIncome = $totals['total_income'] ?? 0;
        $totalExpenses = $totals['total_expenses'] ?? 0;
        $netBalance = $totalIncome - $totalExpenses;

        // 4. Render the reports view with all data
        $this->render('reports', [
            'pageTitle' => 'Reports/Overview',
            'clients' => $clients,
            'selectedClientId' => $clientId,
            'dateRange' => $dateRange,
            'perPage' => $perPage,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalMovements' => $totalMovements,
            'movements' => $movements,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netBalance' => $netBalance,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Parse a date range string like "YYYY-MM-DD to YYYY-MM-DD"
     * Returns array with start and end dates as strings.
     */
    private function parseDateRange($dateRange)
    {
        if ($dateRange) {
            $parts = explode(' to ', $dateRange);
            if (count($parts) === 2) {
                return [$parts[0], $parts[1]];
            }
        }
        $today = date('Y-m-d');
        return [$today, $today];
    }
}
