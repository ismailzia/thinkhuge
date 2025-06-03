<?php
namespace App\controllers;

use App\core\BaseController;
use App\core\Flash;
use App\core\Redirect;
use App\core\Request;
use App\models\SessionModel;
use App\models\User;
use Exception;

/**
 * Controller handling user authentication: login, registration, and logout.
 */
class AuthController extends BaseController
{
    /**
     * Show login form.
     */
    public function showLogin(Request $request)
    {
        $this->render('login', [], null);
    }

    /**
     * Show registration form.
     */
    public function showRegister(Request $request)
    {
        $this->render('register', [], null);
    }

    /**
     * Process login form submission.
     */
    public function login(Request $request)
    {
        // Validate inputs without throwing exceptions
        $result = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], 'return');

        // On validation error, flash errors and old input, then redirect back
        if (!empty($result['errors'])) {
            Flash::setMany(['errors' => $result['errors'], 'old' => $request->all()]);
            return Redirect::back("login");
        }
        $validated = $result['validated'];

        // Find user by email
        $user = User::findByEmail($validated['email']);

        // Check if user exists and is active
        if (!$user || !$user->is_active) {
            Flash::setMany(['errors' => ['email' => ['Invalid credentials or inactive user.']], 'old' => $request->all()]);
            return Redirect::back("login");
        }

        // Verify password hash
        if (!password_verify($validated['password'], $user->password)) {
            Flash::setMany(['errors' => ['password' => ['Invalid credentials.']], 'old' => $request->all()]);
            return Redirect::back("login");
        }

        // Regenerate session ID to prevent fixation attacks
        session_regenerate_id(true);

        // Store user ID in session only
        $_SESSION['user_id'] = $user->id;

        // Log session info to DB for multi-device support
        SessionModel::create([
            'user_id' => $user->id,
            'session_id' => session_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        // Redirect to reports/dashboard page
        Redirect::toBased("reports");
    }

    /**
     * Process registration form submission.
     */
    public function register(Request $request)
    {
        // Validate inputs without exceptions
        $result = $request->validate([
            'first_name' => 'required|min:2|max:60|regex:/^[A-Za-z \'-]+$/',
            'last_name' => 'required|min:2|max:60|regex:/^[A-Za-z \'-]+$/',
            'email' => 'required|email',
            'password' => 'required|min:8|max:100|confirmed|regex:/^(?=.*[A-Z])(?=.*\d).{8,}$/',
        ], 'return');

        // On validation error, flash and redirect back
        if (!empty($result['errors'])) {
            Flash::setMany(['errors' => $result['errors'], 'old' => $request->all()]);
            return Redirect::back("register");
        }
        $validated = $result['validated'];

        // Check for existing email
        if (User::findByEmail($validated['email'])) {
            Flash::setMany([
                'errors' => ['email' => ['Email is already registered.']],
                'old' => $request->all()
            ]);
            return Redirect::back("register");
        }

        // Hash password securely
        $passwordHash = password_hash($validated['password'], PASSWORD_DEFAULT);

        // Generate unique API key
        $api_key = User::generateUniqueApiKey(64);

        // Create user record
        $userId = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $passwordHash,
            'api_key' => $api_key,
            'role' => 'user'
        ]);

        // Regenerate session ID
        session_regenerate_id(true);

        // Store user ID in session
        $_SESSION['user_id'] = $userId;

        // Log session info to DB
        SessionModel::create([
            'user_id' => $userId,
            'session_id' => session_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        // Redirect to reports/dashboard page
        Redirect::toBased('reports');
    }

    /**
     * Log the user out, clear session and session DB entry.
     */
    public function logout(Request $request)
    {
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $sessionId = session_id();

            // Remove session record from DB
            SessionModel::delete(['user_id' => $userId, 'session_id' => $sessionId]);

            // Unset user session
            unset($_SESSION['user_id']);
        }

        // Destroy and regenerate session ID
        session_destroy();
        session_regenerate_id(true);

        // Redirect dynamically to login page based on base path
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        $basePath = dirname($scriptName);
        if ($basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }
        header('Location: ' . $basePath . '/login');
        exit;
    }
}
