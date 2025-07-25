<?php $this->layout("layouts/default", ["title" => "Tra cứu hóa đơn - " . APPNAME]) ?>
<?php $this->start("page") ?>

<div class="max-w-7xl mx-auto mt-6 mb-10 px-3">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-[#183A6C] text-center mb-6">Tra cứu hóa đơn</h2>

        <!-- User welcome message if logged in -->
        <?php if ($isLoggedIn): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-blue-800 font-medium">
                        Xin chào, <?= htmlspecialchars($user->full_name) ?>!
                        <?= empty($_POST) && empty($_GET) ? 'Dưới đây là danh sách hóa đơn của bạn.' : '' ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search Form -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-[#183A6C] mb-4">
                <?= $isLoggedIn ? 'Tìm kiếm hóa đơn khác' : 'Tìm kiếm hóa đơn' ?>
            </h3>

            <form method="POST" action="/invoice_lookup" class="space-y-4">
                <div class="space-y-4">
                    <!-- Row 1: Mã hóa đơn and Mã đặt vé -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col md:flex-row md:items-center gap-4 w-full">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mã hóa đơn</label>
                                <input type="text" name="invoice_id"
                                    value="<?= htmlspecialchars($searchData['invoice_id'] ?? '') ?>"
                                    placeholder="Nhập mã hóa đơn"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mã đặt vé</label>
                                <input type="text" name="booking_id"
                                    value="<?= htmlspecialchars($searchData['booking_id'] ?? '') ?>"
                                    placeholder="Nhập mã đặt vé"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Từ ngày and Đến ngày -->
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
                            <input type="date" name="date_from"
                                value="<?= htmlspecialchars($searchData['date_from'] ?? '') ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
                            <input type="date" name="date_to"
                                value="<?= htmlspecialchars($searchData['date_to'] ?? '') ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                        </div>
                    </div>

                    <!-- Row 3: Trạng thái thanh toán -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái thanh toán</label>
                            <select name="status" class="w-full max-w-sm px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending" <?= ($searchData['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ thanh toán</option>
                                <option value="completed" <?= ($searchData['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Đã thanh toán</option>
                                <option value="refunded" <?= ($searchData['status'] ?? '') === 'refunded' ? 'selected' : '' ?>>Hoàn tiền</option>
                            </select>
                        </div>
                        <div></div> <!-- Empty column for spacing -->
                    </div>

                    <?php if (!$isLoggedIn): ?>
                        <!-- Guest user fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                                <input type="text" name="user_phone"
                                    value="<?= htmlspecialchars($searchData['user_phone'] ?? '') ?>"
                                    placeholder="Nhập số điện thoại"
                                    class="w-full max-w-sm px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="user_email"
                                    value="<?= htmlspecialchars($searchData['user_email'] ?? '') ?>"
                                    placeholder="Nhập email"
                                    class="w-full max-w-sm px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="bg-[#183A6C] text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-900 transition">
                        <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Tìm kiếm
                    </button>

                    <?php if ($searchPerformed): ?>
                        <a href="/invoice_lookup" class="bg-gray-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-gray-600 transition">
                            Làm mới
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        <?php if ($searchPerformed || ($isLoggedIn && empty($_POST) && empty($_GET))): ?>
            <div class="mb-4">
                <?php if (!empty($message)): ?>
                    <div class="<?= empty($invoices) ? 'bg-yellow-100 border-yellow-400 text-yellow-700' : 'bg-green-100 border-green-400 text-green-700' ?> border px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($invoices)): ?>
                    <div class="space-y-4">
                        <?php foreach ($invoices as $invoice): ?>
                            <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition-shadow p-6">
                                <!-- Header -->
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-[#183A6C]">
                                        Hóa đơn #<?= htmlspecialchars($invoice->payment_id) ?>
                                    </h4>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
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

                                <!-- Invoice details in requested layout -->
                                <div class="space-y-3 text-sm">
                                    <!-- Row 1: Mã đặt vé | Khách hàng -->
                                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                                        <div>
                                            <span class="font-medium text-gray-600">Mã đặt vé:</span>
                                            <span class="text-gray-900 ml-2">#<?= htmlspecialchars($invoice->booking_id) ?></span>
                                        </div>
                                        <div class="md:ml-8">
                                            <span class="font-medium text-gray-600">Khách hàng:</span>
                                            <span class="text-gray-900 ml-2"><?= htmlspecialchars($invoice->user_name ?? '') ?></span>
                                        </div>
                                    </div>

                                    <!-- Row 2: Tuyến (full width) -->
                                    <div>
                                        <span class="font-medium text-gray-600">Tuyến:</span>
                                        <span class="text-gray-900 ml-2"><?= htmlspecialchars($invoice->route_start ?? '') ?> → <?= htmlspecialchars($invoice->route_end ?? '') ?></span>
                                    </div>

                                    <!-- Row 3: Ngày thanh toán | Phương thức -->
                                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                                        <div>
                                            <span class="font-medium text-gray-600">Ngày thanh toán:</span>
                                            <span class="text-gray-900 ml-2"><?= $invoice->payment_time ? date('d/m/Y H:i', strtotime($invoice->payment_time)) : 'Chưa thanh toán' ?></span>
                                        </div>
                                        <div class="md:ml-8">
                                            <span class="font-medium text-gray-600">Phương thức:</span>
                                            <span class="text-gray-900 ml-2">
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
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Row 4: Số tiền | Xem chi tiết -->
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2">
                                        <div>
                                            <span class="font-medium text-gray-600">Số tiền:</span>
                                            <span class="text-xl font-bold text-green-600 ml-2"><?= number_format($invoice->amount) ?> đ</span>
                                        </div>
                                        <div>
                                            <a href="/invoice_detail/<?= $invoice->payment_id ?>"
                                                class="bg-[#183A6C] text-white px-4 py-2 rounded text-sm hover:bg-blue-900 transition">
                                                Xem chi tiết
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    <?= $isLoggedIn ? 'Chưa có hóa đơn nào' : 'Tra cứu hóa đơn của bạn' ?>
                </h3>
                <p class="text-gray-500">
                    <?= $isLoggedIn ? 'Bạn chưa có hóa đơn nào. Hãy đặt vé và thanh toán để có hóa đơn!' : 'Nhập thông tin để tra cứu hóa đơn của bạn' ?>
                </p>
                <?php if ($isLoggedIn): ?>
                    <div class="mt-4">
                        <a href="/routes" class="bg-[#183A6C] text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-900 transition">
                            Đặt vé ngay
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->stop() ?>