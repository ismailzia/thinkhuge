<?php

namespace App\Controllers;

use App\core\BaseController;
use App\core\Flash;
use App\core\Redirect;
use App\middlewares\Auth;
use App\models\Transaction;
use App\models\Client;
use App\core\Request;
use Exception;

/**
 * Controller to manage user transactions:
 * - Create, update, list, and delete transactions
 * - Enforce ownership and validation
 */
class TransactionController extends BaseController
{
    /**
     * Handle creating a new transaction.
     */
    public function store(Request $request)
    {
        $result = $request->validate([
            'client_id' => 'required|numeric',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|max:255',
            'date' => 'required|date',
        ], 'return');

        if (!empty($result['errors'])) {
            Flash::setMany(['errors' => $result['errors'], 'old' => $request->all()]);
            return Redirect::back("transactions");
        }
        $validated = $result['validated'];

        $userId = Auth::user()->id;

        // Check that client belongs to the user
        $client = Client::find(['id' => $validated['client_id'], 'user_id' => $userId]);
        if (!$client) {
            Flash::setMany([
                'errors' => ['client_id' => ['Invalid client or access denied.']],
                'old' => $request->all()
            ]);
            return Redirect::back("transactions");
        }

        // Create the transaction
        Transaction::create([
            'client_id' => $validated['client_id'],
            'user_id' => $userId,
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? '',
            'date' => $validated['date']
        ]);

        Flash::set('success', "Transaction added successfully!");
        return Redirect::back("transactions");
    }

    /**
     * Handle updating an existing transaction.
     */
    public function update(Request $request)
    {
        $result = $request->validate([
            'id' => 'required|numeric',
            'client_id' => 'required|numeric',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|max:255',
            'date' => 'required|date',
        ], 'return');

        if (!empty($result['errors'])) {
            Flash::setMany(['errors' => $result['errors'], 'old' => $request->all()]);
            return Redirect::back("transactions");
        }
        $validated = $result['validated'];
        $userId = Auth::user()->id;

        // Verify ownership of transaction
        $transaction = Transaction::find(['id' => $validated['id'], 'user_id' => $userId]);
        if (!$transaction) {
            Flash::setMany([
                'errors' => ['id' => ['Transaction not found or access denied.']],
                'old' => $request->all()
            ]);
            return Redirect::back("transactions");
        }

        // Verify ownership of client
        $client = Client::find(['id' => $validated['client_id'], 'user_id' => $userId]);
        if (!$client) {
            Flash::setMany([
                'errors' => ['client_id' => ['Invalid client or access denied.']],
                'old' => $request->all()
            ]);
            return Redirect::back("transactions");
        }

        // Update the transaction
        Transaction::update($validated['id'], [
            'client_id' => $validated['client_id'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? '',
            'date' => $validated['date']
        ]);

        Flash::set('success', "Transaction updated successfully!");
        return Redirect::back("transactions");
    }

    /**
     * Display paginated list of transactions with optional search and filters.
     */
    public function index(Request $request)
    {
        $result = $request->validate([
            'page' => 'integer|min:1|default:1',
            'limit' => 'integer|min:1|max:100|default:10',
            'search' => 'string|nullable',
            'client_id' => 'integer|nullable',
        ], 'return');

        $validated = $result['validated'];
        $userId = Auth::user()->id;
        $page = (int) $validated['page'];
        $limit = (int) $validated['limit'];
        $search = isset($validated['search']) ? trim($validated['search']) : '';

        $filters = ['user_id' => $userId];

        // Filter by client_id if provided
        $searchClientId = isset($validated['client_id']) ? (int) $validated['client_id'] : null;
        if ($searchClientId) {
            $filters['client_id'] = $searchClientId;
        }

        // Fetch paginated transactions
        $transactions = Transaction::paginate($page, $limit, $search, ['description'], false, $filters);
        // Eager load related clients
        $transactions = Transaction::eagerLoad($transactions, 'client_id', Client::class, 'client');

        $total = Transaction::countPaginated($search, ['description'], false, $filters);
        $totalPages = ceil($total / $limit);
        $clients = Client::paginate(1, 1000, '', [], false, ['user_id' => $userId]);

        $this->render('transactions', [
            'pageTitle' => 'Transactions',
            'transactions' => $transactions,
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'clients' => $clients,
            'searchClientId' => $searchClientId,
            'total' => $total,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Delete a transaction by ID, verifying user ownership.
     */
    public function delete(Request $request, $id)
    {
        try {
            $userId = Auth::user()->id;
            $transaction = Transaction::find(['id' => $id, 'user_id' => $userId]);
            if (!$transaction) {
                throw new Exception("Transaction not found or access denied.");
            }
            Transaction::delete($id);

            Flash::set('success', "Transaction deleted successfully.");
            Redirect::back('/transactions');
        } catch (Exception $e) {
            Flash::set('errors', ['delete' => [$e->getMessage()]]);
            Redirect::back('/transactions');
        }
    }
}
