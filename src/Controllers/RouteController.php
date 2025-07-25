<?php

namespace Ct27501Project\Controllers;

// Add this line after the namespace to ensure AUTHGUARD() is available
require_once __DIR__ . '/../functions.php';

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\Route;
use Exception;

class RouteController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function pageRoutes()
    {
        return $this->sendPage('users/routes', []);
    }

    // Add this method to handle homepage and pass latest routes
    public function home()
    {
        global $PDO;
        $routeModel = new Route($PDO);

        try {
            // Debug: Check if PDO connection exists
            if (!$PDO) {
                error_log("PDO connection is null");
            }

            // Use exact matching for the database values
            $hcm_routes = $routeModel->getLatestRoutesByCity('TP.HCM', 5);
            $cantho_routes = $routeModel->getLatestRoutesByCity('Cần Thơ', 5);
            $hanoi_routes = $routeModel->getLatestRoutesByCity('Hà Nội', 5);

            // Debug: Log the results
            error_log("HCM routes count: " . count($hcm_routes));
            error_log("Cantho routes count: " . count($cantho_routes));
            error_log("Hanoi routes count: " . count($hanoi_routes));

            return $this->sendPage('users/index', [
                'hcm_routes' => $hcm_routes,
                'cantho_routes' => $cantho_routes,
                'hanoi_routes' => $hanoi_routes
            ]);
        } catch (Exception $e) {
            error_log("Error in RouteController::home(): " . $e->getMessage());
            return $this->sendPage('users/index', [
                'hcm_routes' => [],
                'cantho_routes' => [],
                'hanoi_routes' => []
            ]);
        }
    }
}
