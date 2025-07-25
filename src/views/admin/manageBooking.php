<?php $this->layout("layouts/layout_admin", ["title" => "Quản lý thanh toán - " . APPNAME]) ?>
<?php $this->start("page") ?>
<main class="flex-1 bg-white">
    <header class="border-b border-gray-200 py-4 px-8">
        <h1 class="text-xl font-bold text-[#183A6C] text-center">
            Quản lý thanh toán chuyển khoản Busticket
        </h1>
    </header>

    <!-- THÔNG BÁO TRẠNG THÁI CỦA HÀNH ĐỘNG-->
    <div id="status">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mx-8 mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded w-[80%]">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="mx-8 mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded w-[90%]">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    </div>

    <section class="p-8">
        <h2 class="text-lg font-bold text-[#183A6C] mb-6 text-center">Danh sách thanh toán chờ xác nhận chuyển khoản</h2>
        <div class="overflow-x-auto bg-white rounded-xl shadow ring-1 ring-gray-200">
            <table class="min-w-full text-sm text-gray-800">
                <thead class="bg-[#153D77] text-white uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4 text-left">Mã đặt vé</th>
                        <th class="px-6 py-4 text-left">Khách hàng</th>
                        <th class="px-6 py-4 text-left">Email</th>
                        <th class="px-6 py-4 text-left">SĐT</th>
                        <th class="px-6 py-4 text-left">Tuyến</th>
                        <th class="px-6 py-4 text-left">Ngày đi</th>
                        <th class="px-6 py-4 text-left">Ghế</th>
                        <th class="px-6 py-4 text-left">Tổng tiền</th>
                        <th class="px-6 py-4 text-left">Trạng thái</th>
                        <th class="px-6 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr class="hover:bg-gray-50 border-b border-gray-200 last:border-0">
                                <td class="px-6 py-5 font-semibold"><?= $booking->booking_id ?></td>
                                <td class="px-6 py-5"><?= htmlspecialchars($booking->user_name) ?></td>
                                <td class="px-6 py-5"><?= htmlspecialchars($booking->user_email) ?></td>
                                <td class="px-6 py-5"><?= htmlspecialchars($booking->user_phone) ?></td>
                                <td class="px-6 py-5"><?= htmlspecialchars($booking->route_start) ?> → <?= htmlspecialchars($booking->route_end) ?></td>
                                <td class="px-6 py-5"><?= date('d/m/Y H:i', strtotime($booking->departure_time)) ?></td>
                                <td class="px-6 py-5"><?= htmlspecialchars($booking->seat_numbers) ?></td>
                                <td class="px-6 py-5 text-green-700 font-bold"><?= number_format($booking->total_price) ?>đ</td>
                                <td class="px-6 py-5">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold
                                        <?= $booking->status === 'booked' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                        <?= $booking->status === 'booked' ? 'Chờ chuyển khoản' : 'Chờ xác nhận' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <form method="POST" action="/manageBooking/confirm_payment/<?= $booking->booking_id ?>" onsubmit="return confirm('Xác nhận đã nhận chuyển khoản cho booking này?');">
                                        <input type="hidden" name="booking_id" value="<?= $booking->booking_id ?>">
                                        <button type="submit"
                                            class="bg-green-600 text-white px-4 py-2 rounded font-semibold hover:bg-green-700 transition">
                                            Xác nhận thanh toán
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                                Không có thanh toán nào chờ xác nhận chuyển khoản.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php $this->stop() ?>