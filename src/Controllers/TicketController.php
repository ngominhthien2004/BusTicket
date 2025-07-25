<?php

namespace Ct27501Project\Controllers;

// Add this line after the namespace to ensure AUTHGUARD() is available
require_once __DIR__ . '/../functions.php';

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\Ticket;

class TicketController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function lookup_page()
    {
        $tickets = [];
        $searchPerformed = false;
        $message = '';
        $isLoggedIn = AUTHGUARD()->isUserLoggedIn();
        $user = $isLoggedIn ? AUTHGUARD()->user() : null;

        // If user is logged in and no search is performed, show their tickets by default
        if ($isLoggedIn && empty($_POST) && empty($_GET)) {
            try {
                $tickets = Ticket::getByUserId($user->user_id);
                $searchPerformed = true;

                if (empty($tickets)) {
                    $message = 'Bạn chưa có vé nào được đặt.';
                } else {
                    $message = 'Hiển thị ' . count($tickets) . ' vé của bạn.';
                }
            } catch (\Exception $e) {
                $message = 'Có lỗi xảy ra khi tải thông tin vé: ' . $e->getMessage();
            }
        }
        // Handle search if form is submitted
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_GET)) {
            $searchData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
            $searchPerformed = true;

            // Build search conditions
            $conditions = [];

            if (!empty($searchData['booking_id'])) {
                $conditions['booking_id'] = trim($searchData['booking_id']);
            }

            if (!empty($searchData['user_phone'])) {
                // Search by phone in user table
                $conditions['phone_number'] = trim($searchData['user_phone']);
            }

            if (!empty($searchData['user_email'])) {
                // Search by email in user table  
                $conditions['email'] = trim($searchData['user_email']);
            }

            if (!empty($searchData['status'])) {
                $conditions['status'] = $searchData['status'];
            }

            if (!empty($searchData['route'])) {
                $conditions['route'] = trim($searchData['route']);
            }

            if (!empty($searchData['date_from'])) {
                $conditions['date_from'] = $searchData['date_from'];
            }

            if (!empty($searchData['date_to'])) {
                $conditions['date_to'] = $searchData['date_to'];
            }

            try {
                if (!empty($conditions)) {
                    $tickets = Ticket::search($conditions, 50); // Limit to 50 results

                    if (empty($tickets)) {
                        $message = 'Không tìm thấy vé nào phù hợp với điều kiện tìm kiếm.';
                    } else {
                        $message = 'Tìm thấy ' . count($tickets) . ' vé phù hợp.';
                    }
                } else {
                    $message = 'Vui lòng nhập ít nhất một điều kiện tìm kiếm.';
                }
            } catch (\Exception $e) {
                $message = 'Có lỗi xảy ra khi tìm kiếm: ' . $e->getMessage();
            }
        }

        return $this->sendPage('users/ticket_lookup', [
            'tickets' => $tickets,
            'searchPerformed' => $searchPerformed,
            'message' => $message,
            'searchData' => $searchData ?? [],
            'isLoggedIn' => $isLoggedIn,
            'user' => $user
        ]);
    }

    public function searchTickets()
    {
        // Redirect to lookup_page with GET parameters
        $queryString = http_build_query($_POST);
        header("Location: /ticket_lookup?" . $queryString);
        exit();
    }
}
