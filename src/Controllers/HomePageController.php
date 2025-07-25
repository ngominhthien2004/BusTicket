<?php

namespace Ct27501Project\Controllers;

// Add this line after the namespace to ensure AUTHGUARD() is available
require_once __DIR__ . '/../functions.php';

use Ct27501Project\Controllers\Controller;

class HomePageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->sendPage('users/index', []);
    }
}
