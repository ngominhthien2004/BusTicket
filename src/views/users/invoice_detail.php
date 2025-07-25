<?php $this->layout("layouts/default", ["title" => "Chi tiết hóa đơn - " . APPNAME]) ?>
<?php $this->start("page") ?>

<div class="max-w-3xl mx-auto mt-10 mb-10 px-3">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <!-- Header -->
        <div class="border-b pb-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <h2 class="text-2xl font-bold text-[#183A6C]">Hóa đơn thanh toán</h2>
            <a href="/invoice_lookup" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Quay lại danh sách
            </a>
        </div>
        <div class="mb-6 text-gray-600">
            <span class="font-semibold">Mã hóa đơn:</span>
            <span class="text-[#183A6C] font-bold">#<?= htmlspecialchars($invoice->payment_id) ?></span>
        </div>

        <!-- Invoice Status -->
        <div class="mb-6">
            <span class="px-4 py-2 rounded-full text-sm font-medium
                <?php
                switch ($invoice->status) {
                    case 'pending':
                        echo 'bg-yellow-100 text-yellow-800';
                        break;
                    case 'completed':
                        echo 'bg-green-100 text-green-800';
                        break;
                    case 'refunded':
                        echo 'bg-blue-100 text-blue-800';
                        break;
                    default:
                        echo 'bg-gray-100 text-gray-800';
                }
                ?>">
                <?php
                switch ($invoice->status) {
                    case 'pending':
                        echo 'Chờ thanh toán';
                        break;
                    case 'completed':
                        echo 'Đã thanh toán';
                        break;
                    case 'refunded':
                        echo 'Hoàn tiền';
                        break;
                    default:
                        echo ucfirst($invoice->status ?? '');
                }
                ?>
            </span>
        </div>

        <!-- Invoice Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Customer Info -->
            <div class="bg-gray-50 rounded-lg p-4 shadow-sm">
                <h3 class="text-lg font-semibold text-[#183A6C] mb-3">Thông tin khách hàng</h3>
                <div class="space-y-2 text-sm">
                    <div><span class="font-medium">Họ tên:</span> <?= htmlspecialchars($invoice->user_name ?? '') ?></div>
                    <div><span class="font-medium">Email:</span> <?= htmlspecialchars($invoice->user_email ?? '') ?></div>
                    <div><span class="font-medium">Điện thoại:</span> <?= htmlspecialchars($invoice->user_phone ?? '') ?></div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="bg-gray-50 rounded-lg p-4 shadow-sm">
                <h3 class="text-lg font-semibold text-[#183A6C] mb-3">Thông tin thanh toán</h3>
                <div class="space-y-2 text-sm">
                    <div><span class="font-medium">Mã đặt vé:</span> #<?= htmlspecialchars($invoice->booking_id) ?></div>
                    <div><span class="font-medium">Phương thức:</span>
                        <?php
                        switch ($invoice->payment_method) {
                            case 'cash':
                                echo 'Tiền mặt';
                                break;
                            case 'bank_transfer':
                                echo 'Chuyển khoản';
                                break;
                            case 'credit_card':
                                echo 'Thẻ tín dụng';
                                break;
                            default:
                                echo $invoice->payment_method ?? 'Chưa xác định';
                        }
                        ?>
                    </div>
                    <div><span class="font-medium">Ngày thanh toán:</span>
                        <?= $invoice->payment_time ? date('d/m/Y H:i:s', strtotime($invoice->payment_time)) : 'Chưa thanh toán' ?>
                    </div>
                    <?php if ($invoice->transaction_code): ?>
                        <div><span class="font-medium">Mã giao dịch:</span> <?= htmlspecialchars($invoice->transaction_code) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Trip Details -->
        <div class="bg-blue-50 rounded-lg p-4 mb-8 shadow-sm">
            <h3 class="text-lg font-semibold text-[#183A6C] mb-3">Thông tin chuyến đi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><span class="font-medium">Tuyến:</span> <?= htmlspecialchars($invoice->route_start ?? '') ?> → <?= htmlspecialchars($invoice->route_end ?? '') ?></div>
                <div><span class="font-medium">Xe:</span> <?= htmlspecialchars($invoice->bus_license_plate ?? '') ?></div>
                <div><span class="font-medium">Khởi hành:</span> <?= $invoice->departure_time ? date('d/m/Y H:i', strtotime($invoice->departure_time)) : '' ?></div>
                <div><span class="font-medium">Dự kiến đến:</span> <?= $invoice->arrival_time ? date('d/m/Y H:i', strtotime($invoice->arrival_time)) : '' ?></div>
            </div>
            <?php if (!empty($invoice->seats)): ?>
                <div class="mt-3">
                    <span class="font-medium">Ghế:</span>
                    <span class="font-semibold text-blue-700"><?= implode(', ', array_column($invoice->seats, 'seat_number')) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Amount Summary -->
        <div class="border-t pt-4 mb-6">
            <div class="flex justify-between items-center text-xl font-bold">
                <span>Tổng tiền:</span>
                <span class="text-green-600"><?= number_format($invoice->amount) ?> đ</span>
            </div>
        </div>

        <!-- Print Button -->
        <div class="mt-6 text-center">
            <button onclick="window.print()" class="bg-[#183A6C] text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-900 transition no-print">
                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                In hóa đơn
            </button>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: white !important;
        }

        .shadow,
        .shadow-lg,
        .shadow-sm {
            box-shadow: none !important;
        }

        .bg-white,
        .bg-blue-50,
        .bg-gray-50 {
            background: white !important;
        }

        .max-w-3xl,
        .max-w-4xl,
        .mx-auto,
        .px-3,
        .p-6,
        .p-8 {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>

<?php $this->stop() ?>