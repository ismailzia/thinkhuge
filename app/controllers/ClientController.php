<?php

namespace App\controllers;

use App\core\BaseController;
use App\core\Flash;
use App\core\Redirect;
use App\middlewares\Auth;
use App\models\Client;
use App\core\Request;
use Exception;

/**
 * Controller for managing clients: listing, creating, updating, deleting.
 */
class ClientController extends BaseController
{
    /**
     * Store a new client after validation.
     */
    public function store(Request $request)
    {
        // Validate inputs, return errors without exception
        $result = $request->validate([
            'name' => 'required|min:2|max:120|regex:/^[A-Za-z \'-]+$/',
            'email' => 'required|email',
            'phone' => 'required|min:6|max:30|regex:/^[0-9+\s\-()]+$/',
        ], 'return');

        if (!empty($result['errors'])) {
            Flash::setMany(['errors' => $result['errors'], 'old' => $request->all()]);
            return Redirect::back("clients");
        }

        $validated = $result['validated'];
        $userId = Auth::user()->id;

        // Check for duplicate email under the same user
        $existing = Client::find(['email' => $validated['email'], 'user_id' => $userId]);
        if ($existing) {
            Flash::setMany([
                'errors' => ['email' => ['This email is already used for one of your clients.']],
                'old' => $request->all()
            ]);
            return Redirect::back("clients");
        }

        // Create new client record
        Client::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'user_id' => $userId
        ]);

        Flash::set('success', "Client added successfully!");
        return Redirect::back("clients");
    }

    /**
     * Update an existing client after validation.
     */
    public function update(Request $request)
    {
        $result = $request->validate([
            'id' => 'required|numeric',
            'name' => 'required|min:2|max:120|regex:/^[A-Za-z \'-]+$/',
            'email' => 'required|email',
            'phone' => 'required|min:6|max:30|regex:/^[0-9+\s\-()]+$/',
        ], 'return');

        if (!empty($result['errors'])) {
            Flash::setMany(['errors' => $result['errors'], 'old' => $request->all()]);
            return Redirect::back("clients");
        }

        $validated = $result['validated'];
        $userId = Auth::user()->id;

        // Verify client belongs to current user
        $client = Client::find(['id' => $validated['id'], 'user_id' => $userId]);
        if (!$client) {
            Flash::setMany([
                'errors' => ['id' => ['Client not found.']],
                'old' => $request->all()
            ]);
            return Redirect::back("clients");
        }

        // Check email conflict with other clients
        $existing = Client::find(['email' => $validated['email'], 'user_id' => $userId]);
        if ($existing && $existing->id != $validated['id']) {
            Flash::setMany([
                'errors' => ['email' => ['This email is already used for another one of your clients.']],
                'old' => $request->all()
            ]);
            return Redirect::back("clients");
        }

        // Perform update
        Client::update($validated['id'], [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone']
        ]);

        Flash::set('success', "Client updated successfully!");
        return Redirect::back("clients");
    }

    /**
     * List clients with pagination, search, and balance info.
     */
    public function index(Request $request)
    {
        $result = $request->validate([
            'page' => 'integer|min:1|default:1',
            'limit' => 'integer|min:1|max:100|default:5',
            'search' => 'string|nullable',
        ], 'return');

        $validated = $result['validated'];
        $page = (int) $validated['page'];
        $limit = (int) $validated['limit'];
        $search = isset($validated['search']) ? trim($validated['search']) : '';

        $searchColumns = ['name', 'email', 'phone'];
        $filters = ['user_id' => Auth::user()->id];

        $clients = Client::paginateWithBalances($page, $limit, $search, $searchColumns, false, $filters);
        $total = Client::countPaginated($search, $searchColumns, false, $filters);
        $totalPages = ceil($total / $limit);

        $this->render('clients', [
            'pageTitle' => 'Clients List',
            'clients' => $clients,
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'total' => $total,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Delete a client owned by the current user.
     */
    public function delete(Request $request, $id)
    {
        try {
            $userId = Auth::user()->id;

            // Ensure client belongs to user
            $client = Client::find(['id' => $id, 'user_id' => $userId]);
            if (!$client) {
                throw new Exception("Client not found or access denied.");
            }

            // Delete client and optionally related transactions
            $client->deleteClient(true);

            Flash::set('success', "Client deleted successfully.");
            Redirect::back('/clients');

        } catch (Exception $e) {
            Flash::set('errors', ['delete' => [$e->getMessage()]]);
            Redirect::back('/clients');
        }
    }
}
