<?php $this->layout("layouts/default", ["title" => "Tra cứu vé - " . APPNAME]) ?>
<?php $this->start("page") ?>

<div class="max-w-7xl mx-auto mt-6 mb-10 px-3">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-[#183A6C] text-center mb-6">Tra cứu vé xe</h2>

        <!-- User welcome message if logged in -->
        <?php if ($isLoggedIn): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-blue-800 font-medium">
                        Xin chào, <?= htmlspecialchars($user->full_name) ?>!
                        <?= empty($_POST) && empty($_GET) ? 'Dưới đây là danh sách vé của bạn.' : '' ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search Form -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-[#183A6C] mb-4">
                <?= $isLoggedIn ? 'Tìm kiếm vé khác' : 'Tìm kiếm vé' ?>
            </h3>

            <form method="POST" action="/ticket_lookup" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mã đặt vé</label>
                        <input type="text" name="booking_id"
                            value="<?= htmlspecialchars($searchData['booking_id'] ?? '') ?>"
                            placeholder="Nhập mã đặt vé"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                    </div>

                    <?php if (!$isLoggedIn): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                            <input type="text" name="user_phone"
                                value="<?= htmlspecialchars($searchData['user_phone'] ?? '') ?>"
                                placeholder="Nhập số điện thoại"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="user_email"
                                value="<?= htmlspecialchars($searchData['user_email'] ?? '') ?>"
                                placeholder="Nhập email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                            <option value="">Tất cả trạng thái</option>
                            <option value="booked" <?= ($searchData['status'] ?? '') === 'booked' ? 'selected' : '' ?>>Đã đặt</option>
                            <option value="paid" <?= ($searchData['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                            <option value="confirmed" <?= ($searchData['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                            <option value="cancelled" <?= ($searchData['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="bg-[#183A6C] text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-900 transition">
                        <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Tìm kiếm
                    </button>

                    <?php if ($searchPerformed): ?>
                        <a href="/ticket_lookup" class="bg-gray-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-gray-600 transition">
                            Làm mới
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        <?php if ($searchPerformed): ?>
            <div class="mb-4">
                <?php if (!empty($message)): ?>
                    <div class="<?= empty($tickets) ? 'bg-yellow-100 border-yellow-400 text-yellow-700' : 'bg-green-100 border-green-400 text-green-700' ?> border px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($tickets)): ?>
                    <div class="space-y-4">
                        <?php foreach ($tickets as $ticket): ?>
                            <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition-shadow p-6">
                                <!-- Header with ticket ID and status -->
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-[#183A6C]">
                                        Mã vé: #<?= htmlspecialchars($ticket->booking_id) ?>
                                    </h4>
                                </div>

                                <!-- Ticket info in 3 rows with 3 items each -->
                                <div class="space-y-4 text-sm">
                                    <!-- Row 1: Customer, Route, Seats -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div>
                                            <span class="font-medium text-gray-600">Khách hàng:</span><br>
                                            <span class="text-gray-900"><?= htmlspecialchars($ticket->user_name) ?></span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Tuyến:</span><br>
                                            <span class="text-gray-900"><?= htmlspecialchars($ticket->route_start ?? '') ?> → <?= htmlspecialchars($ticket->route_end ?? '') ?></span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Ghế:</span><br>
                                            <span class="text-gray-900">
                                                <?= !empty($ticket->seats) ? implode(', ', array_column($ticket->seats, 'seat_number')) : 'Chưa có thông tin' ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Row 2: Departure, Vehicle, Driver -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div>
                                            <span class="font-medium text-gray-600">Ngày đi:</span><br>
                                            <span class="text-gray-900"><?= date('d/m/Y H:i', strtotime($ticket->departure_time)) ?></span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Xe:</span><br>
                                            <span class="text-gray-900"><?= htmlspecialchars($ticket->bus_license_plate ?? 'Chưa có thông tin') ?></span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Tài xế:</span><br>
                                            <span class="text-gray-900"><?= htmlspecialchars($ticket->driver_name ?? 'Chưa có thông tin') ?></span>
                                        </div>
                                    </div>

                                    <!-- Row 3: Booking Time, Payment Method, Actions -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                                        <div>
                                            <span class="font-medium text-gray-600">Đặt lúc:</span><br>
                                            <span class="text-gray-900"><?= date('d/m/Y H:i', strtotime($ticket->booking_time)) ?></span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Phương thức TT:</span><br>
                                            <span class="text-gray-900">
                                                <?php
                                                switch ($ticket->payment_method) {
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
                                                        echo $ticket->payment_method ?? 'Chưa xác định';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                            <?php
                                            switch ($ticket->status) {
                                                case 'booked':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                case 'paid':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'confirmed':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'cancelled':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php
                                            switch ($ticket->status) {
                                                case 'booked':
                                                    echo 'Đã đặt';
                                                    break;
                                                case 'paid':
                                                    echo 'Đã thanh toán';
                                                    break;
                                                case 'confirmed':
                                                    echo 'Đã xác nhận';
                                                    break;
                                                case 'cancelled':
                                                    echo 'Đã hủy';
                                                    break;
                                                default:
                                                    echo ucfirst($ticket->status ?? '');
                                            }
                                            ?>
                                        </span>
                                        <div class="text-2xl font-bold text-green-600">
                                            <?= number_format($ticket->total_price) ?> đ
                                        </div>
                                        <?php if ($ticket->status === 'booked'): ?>
                                            <div class="flex gap-2 justify-end">
                                                <?php if ($ticket->payment_method === 'cash'): ?>
                                                    <!-- Payment button for cash - disabled with tooltip -->
                                                    <div class="relative group">
                                                        <button class="bg-gray-400 text-white px-3 py-1 rounded text-sm cursor-not-allowed payment-btn" disabled title="Thanh toán bằng tiền mặt">
                                                            Thanh toán
                                                        </button>
                                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-10">
                                                            Bạn đã chọn thanh toán bằng tiền mặt,<br>
                                                            vui lòng thanh toán với tài xế khi xe xuất phát
                                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <!-- Payment button for other methods - clickable -->
                                                    <button class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition payment-btn"
                                                        onclick="showPaymentModal(<?= $ticket->booking_id ?>, '<?= $ticket->payment_method ?>', <?= $ticket->total_price ?>)">
                                                        Thanh toán
                                                    </button>
                                                <?php endif; ?>

                                                <button class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition cancel-btn"
                                                    onclick="cancelTicket(<?= $ticket->booking_id ?>, this)">
                                                    Hủy vé
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-gray-500 text-sm italic">
                                                <?php
                                                switch ($ticket->status) {
                                                    case 'paid':
                                                        echo 'Đã thanh toán';
                                                        break;
                                                    case 'confirmed':
                                                        echo 'Đã xác nhận';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'Đã hủy vé';
                                                        break;
                                                    default:
                                                        echo 'Trạng thái: ' . ucfirst($ticket->status ?? '');
                                                }
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">

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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    <?= $isLoggedIn ? 'Chưa có vé nào' : 'Tra cứu vé của bạn' ?>
                </h3>
                <p class="text-gray-500">
                    <?= $isLoggedIn ? 'Bạn chưa đặt vé nào. Hãy đặt vé ngay!' : 'Nhập thông tin để tra cứu vé xe của bạn' ?>
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

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-white bg-opacity-50 z-50 hidden border border-black">
    <div class="flex items-center justify-center min-h-screen p-4 border border-black">
        <div class="bg-white rounded-lg max-w-md w-full p-6 border border-black">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[#183A6C]">Thông tin thanh toán</h3>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <h4 class="font-semibold text-[#183A6C] mb-2">Thông tin chuyển khoản</h4>
                    <div class="text-sm space-y-1">
                        <div><span class="font-medium">Ngân hàng:</span> Vietcombank</div>
                        <div><span class="font-medium">Số tài khoản:</span> 1234567890123456</div>
                        <div><span class="font-medium">Chủ tài khoản:</span> CONG TY BUS TICKET</div>
                        <div><span class="font-medium">Chi nhánh:</span> Cần Thơ</div>
                    </div>
                </div>

                <div class="bg-yellow-50 rounded-lg p-4">
                    <h4 class="font-semibold text-orange-600 mb-2">Cú pháp chuyển khoản</h4>
                    <div class="text-sm">
                        <div class="font-mono bg-white p-2 rounded border">
                            <span class="text-blue-600">BUSTICKET</span> <span id="modalBookingId" class="text-green-600"></span> <span id="modalPayerName" class="text-purple-600"></span>
                        </div>
                        <div class="mt-2 text-xs text-gray-600">
                            Ví dụ: BUSTICKET 123 NGUYEN VAN A
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Số tiền cần chuyển:</span>
                        <span id="modalAmount" class="text-xl font-bold text-red-600"></span>
                    </div>
                </div>

                <div class="text-xs text-gray-500">
                    <p>• Vui lòng chuyển khoản đúng số tiền và nội dung để hệ thống tự động xác nhận</p>
                    <p>• Vé sẽ được xác nhận trong vòng 5-10 phút sau khi chuyển khoản thành công</p>
                    <p>• Nếu cần hỗ trợ, vui lòng liên hệ hotline: 0900-BUSTICKET</p>
                </div>

                <div class="flex gap-3">
                    <button onclick="closePaymentModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">
                        Đóng
                    </button>
                    <button onclick="confirmPayment()" class="flex-1 bg-[#183A6C] text-white px-4 py-2 rounded hover:bg-blue-900 transition">
                        Đã chuyển khoản
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentBookingId = null;

    function showPaymentModal(bookingId, paymentMethod, amount) {
        currentBookingId = bookingId;

        document.getElementById('modalBookingId').textContent = bookingId;
        document.getElementById('modalAmount').textContent = new Intl.NumberFormat('vi-VN').format(amount) + 'đ';

        // You might want to get the user's name from somewhere
        document.getElementById('modalPayerName').textContent = 'KHACH_HANG';

        document.getElementById('paymentModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        currentBookingId = null;
    }

    function confirmPayment() {
        if (!currentBookingId) return;

        // Here you would typically send an AJAX request to mark the payment as pending
        // For now, just show a confirmation message
        alert('Cảm ơn bạn! Chúng tôi sẽ xác nhận thanh toán trong vòng 5-10 phút.');
        closePaymentModal();

        // Optionally reload the page to show updated status
        // location.reload();
    }

    function cancelTicket(bookingId, btn) {
        if (!confirm('Bạn có chắc chắn muốn hủy vé này không?')) return;

        // Disable "Hủy vé" button
        btn.disabled = true;
        btn.textContent = 'Đang hủy...';

        // Disable "Thanh toán" button (nếu có)
        var parentDiv = btn.closest('.flex.gap-2');
        if (parentDiv) {
            var paymentBtn = parentDiv.querySelector('.payment-btn');
            if (paymentBtn) {
                paymentBtn.disabled = true;
                paymentBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                paymentBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                paymentBtn.textContent = 'Thanh toán';
                paymentBtn.onclick = null;
            }
        }

        // Đổi trạng thái badge sang "Đã hủy" màu xám
        var statusSpan = btn.closest('.flex.items-center.gap-3').querySelector('span.px-3');
        if (statusSpan) {
            statusSpan.className = 'px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
            statusSpan.textContent = 'Đã hủy';
        }

        // Disable "Hủy vé" button và đổi text
        btn.classList.remove('bg-red-600', 'hover:bg-red-700');
        btn.classList.add('bg-gray-400', 'cursor-not-allowed');
        btn.textContent = 'Đã hủy';
        btn.disabled = true;

        // Optionally show a message
        alert('Vé đã được hủy thành công!');
    }

    // Close modal when clicking outside
    document.getElementById('paymentModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePaymentModal();
        }
    });
</script>

<?php $this->stop() ?>