<?php

namespace Ct27501Project\Controllers\Auth;

// Add this line after the namespace to ensure AUTHGUARD() is available
require_once __DIR__ . '/../../functions.php';

use Ct27501Project\Models\User;
use Ct27501Project\Controllers\Controller;

class RegisterController extends Controller
{
    public function __construct()
    {
        if (AUTHGUARD()->isUserLoggedIn()) {
            redirect('/');
        }

        parent::__construct();
    }

    public function create()
    {
        $data = [
            'old' => $this->getSavedFormValues(),
            'errors' => session_get_once('errors')
        ];

        $this->sendPage('auth/register', $data);
    }

    public function store()
    {
        $this->saveFormValues($_POST, ['password', 'password_confirmation']);

        $data = $this->filterUserData($_POST);
        $newUser = new User(PDO());
        $model_errors = $newUser->validate($data);
        if (empty($model_errors)) {
            $newUser->fill($data)->save();
            $_SESSION['success'] = 'Registration successful. Please login.'; // Add success message
            redirect('/login');
        }

        // Dữ liệu không hợp lệ...
        redirect('/register', ['errors' => $model_errors]);
    }

    protected function filterUserData(array $data)
    {
        return [
            'full_name' => $data['full_name'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'email' => filter_var($data['email'], FILTER_VALIDATE_EMAIL),
            'password' => $data['password'] ?? null,
            'password_confirmation' => $data['password_confirmation'] ?? null
        ];
    }
}
