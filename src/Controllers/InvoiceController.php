<?php

namespace Ct27501Project\Controllers;

// Add this line after the namespace to ensure AUTHGUARD() is available
require_once __DIR__ . '/../functions.php';

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\Invoice;

class InvoiceController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function lookup_page()
    {
        $isLoggedIn = AUTHGUARD()->isUserLoggedIn();
        $user = $isLoggedIn ? AUTHGUARD()->user() : null;

        $invoices = [];
        $message = '';
        $searchPerformed = false;
        $searchData = [
            'invoice_id' => $_POST['invoice_id'] ?? $_GET['invoice_id'] ?? '',
            'booking_id' => $_POST['booking_id'] ?? $_GET['booking_id'] ?? '',
            'status' => $_POST['status'] ?? $_GET['status'] ?? '',
            'date_from' => $_POST['date_from'] ?? $_GET['date_from'] ?? '',
            'date_to' => $_POST['date_to'] ?? $_GET['date_to'] ?? '',
            'user_phone' => $_POST['user_phone'] ?? $_GET['user_phone'] ?? '',
            'user_email' => $_POST['user_email'] ?? $_GET['user_email'] ?? ''
        ];

        // Sanitize ID inputs by removing '#' character
        if (!empty($searchData['invoice_id'])) {
            $searchData['invoice_id'] = ltrim($searchData['invoice_id'], '#');
        }
        if (!empty($searchData['booking_id'])) {
            $searchData['booking_id'] = ltrim($searchData['booking_id'], '#');
        }

        // If user is logged in and no search is performed, show their invoices
        if ($isLoggedIn && empty($_POST) && empty($_GET)) {
            $invoices = Invoice::getByUserId($user->user_id);
            if (empty($invoices)) {
                $message = 'Bạn chưa có hóa đơn nào.';
            } else {
                $message = 'Danh sách hóa đơn của bạn.';
            }
        }
        // Handle search
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_GET)) {
            $searchPerformed = true;

            if ($isLoggedIn) {
                // For logged-in users, ALWAYS restrict search to their own invoices
                $conditions = ['user_id' => $user->user_id];

                if (!empty($searchData['invoice_id']) && is_numeric($searchData['invoice_id'])) {
                    $invoice = Invoice::find($searchData['invoice_id']);
                    if ($invoice && $this->belongsToUser($invoice, $user->user_id)) {
                        $invoices = [$invoice];
                    }
                } elseif (!empty($searchData['booking_id']) && is_numeric($searchData['booking_id'])) {
                    $invoice = Invoice::findByBookingId($searchData['booking_id']);
                    if ($invoice && $this->belongsToUser($invoice, $user->user_id)) {
                        $invoices = [$invoice];
                    }
                } else {
                    // Search with other conditions but ALWAYS within user's own invoices
                    if (!empty($searchData['status'])) {
                        $conditions['status'] = $searchData['status'];
                    }
                    if (!empty($searchData['date_from'])) {
                        $conditions['date_from'] = $searchData['date_from'];
                    }
                    if (!empty($searchData['date_to'])) {
                        $conditions['date_to'] = $searchData['date_to'];
                    }

                    // Always include user_id to restrict to current user's invoices
                    $invoices = Invoice::search($conditions);
                }
            } else {
                // For guest users, require invoice ID and phone/email
                if (
                    !empty($searchData['invoice_id']) && is_numeric($searchData['invoice_id']) &&
                    (!empty($searchData['user_phone']) || !empty($searchData['user_email']))
                ) {

                    $invoice = Invoice::find($searchData['invoice_id']);
                    if ($invoice && $this->matchesGuestCriteria($invoice, $searchData)) {
                        $invoices = [$invoice];
                    }
                }
            }

            if (empty($invoices)) {
                $message = 'Không tìm thấy hóa đơn nào phù hợp.';
            } else {
                $message = 'Tìm thấy ' . count($invoices) . ' hóa đơn.';
            }
        }

        return $this->sendPage('users/invoice_lookup', [
            'invoices' => $invoices,
            'message' => $message,
            'searchPerformed' => $searchPerformed,
            'searchData' => $searchData,
            'isLoggedIn' => $isLoggedIn,
            'user' => $user
        ]);
    }

    public function invoice_detail($id)
    {
        $isLoggedIn = AUTHGUARD()->isUserLoggedIn();
        $user = $isLoggedIn ? AUTHGUARD()->user() : null;

        $invoice = Invoice::find($id);

        if (!$invoice) {
            redirect('/invoice_lookup', ['error' => 'Không tìm thấy hóa đơn.']);
            return;
        }

        // Check if user has permission to view this invoice
        if ($isLoggedIn && !$this->belongsToUser($invoice, $user->user_id)) {
            redirect('/invoice_lookup', ['error' => 'Bạn không có quyền xem hóa đơn này.']);
            return;
        }

        return $this->sendPage('users/invoice_detail', [
            'invoice' => $invoice,
            'isLoggedIn' => $isLoggedIn,
            'user' => $user
        ]);
    }

    private function belongsToUser($invoice, $userId)
    {
        // Check if invoice belongs to the user through booking
        $stmt = PDO()->prepare("
            SELECT b.user_id 
            FROM bookings b 
            WHERE b.booking_id = ?
        ");
        $stmt->execute([$invoice->booking_id]);
        $booking = $stmt->fetch();

        return $booking && $booking['user_id'] == $userId;
    }

    private function matchesGuestCriteria($invoice, $searchData)
    {
        $phoneMatch = !empty($searchData['user_phone']) &&
            $invoice->user_phone === $searchData['user_phone'];
        $emailMatch = !empty($searchData['user_email']) &&
            $invoice->user_email === $searchData['user_email'];

        return $phoneMatch || $emailMatch;
    }
}
