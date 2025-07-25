<?php $this->layout("layouts/default", ["title" => "Đặt vé xe - " . APPNAME]) ?>
<?php $this->start("page") ?>

<div class="max-w-7xl mx-auto mt-6 mb-10 px-3">
    <!-- Hiển thị thông báo nếu có -->
    <?php if (!empty($messages)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php foreach ($messages as $message): ?>
                <p><?= htmlspecialchars($message) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-2xl font-bold text-[#183A6C] text-center my-6">Đặt vé</h2>

        <!-- Route Information -->
        <div class="bg-blue-50 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold text-[#183A6C] mb-2">Thông tin tuyến đường</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-semibold">Điểm đi:</span> <?= htmlspecialchars($route->start_point ?? '') ?></div>
                <div><span class="font-semibold">Điểm đến:</span> <?= htmlspecialchars($route->end_point ?? '') ?></div>
                <div><span class="font-semibold">Khoảng cách:</span> <?= htmlspecialchars($route->distance_km ?? '') ?> km</div>
            </div>
        </div>

        <!-- Schedule Selection -->
        <?php if (!empty($schedules)): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-[#183A6C] mb-3">Chọn lịch trình</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($schedules as $schedule): ?>
                        <div class="border rounded-lg p-4 hover:shadow-md <?= ($selectedSchedule && $selectedSchedule->schedule_id == $schedule['schedule_id']) ? 'border-blue-500 bg-blue-50' : '' ?>">
                            <div class="font-semibold">Giờ khởi hành: <?= date('H:i d/m/Y', strtotime($schedule['departure_time'])) ?></div>
                            <div>Giờ đến: <?= date('H:i d/m/Y', strtotime($schedule['arrival_time'])) ?></div>
                            <div class="text-green-600 font-semibold">Giá: <?= number_format($schedule['price']) ?>đ</div>
                            <a href="/booking?route_id=<?= $route->route_id ?>&schedule_id=<?= $schedule['schedule_id'] ?>"
                                class="inline-block mt-2 bg-[#183A6C] text-white px-4 py-2 rounded text-sm hover:bg-blue-900">
                                Chọn
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($selectedSchedule && $bus): ?>
            <form method="POST" action="/booking" id="bookingForm">
                <input type="hidden" name="schedule_id" value="<?= $selectedSchedule->schedule_id ?>">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Bên trái: Chọn ghế -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="text-center mb-4">
                            <h3 class="text-xl font-bold text-[#183A6C] mb-2">CHỌN GHẾ NGỒI</h3>
                            <div class="text-sm text-gray-600">
                                Xe: <span class="font-semibold"><?= htmlspecialchars($bus->license_plate) ?></span> -
                                Tài xế: <span class="font-semibold"><?= htmlspecialchars($bus->driver_name) ?></span>
                            </div>
                        </div>

                        <?php if (!empty($seats)): ?>
                            <!-- Mô phỏng layout xe khách 2 tầng -->
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <!-- Tầng dưới và tầng trên trong cùng một container -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Tầng dưới -->
                                    <div>
                                        <h4 class="text-center font-semibold mb-3 text-sm text-gray-700">TẦNG DƯỚI</h4>
                                        <div class="flex justify-center">
                                            <div class="grid grid-cols-4 gap-1 max-w-48">
                                                <?php
                                                $floorASeats = array_filter($seats, function ($seat) {
                                                    return strpos($seat['seat_number'], 'A') === 0;
                                                });
                                                usort($floorASeats, function ($a, $b) {
                                                    return (int)substr($a['seat_number'], 1) - (int)substr($b['seat_number'], 1);
                                                });

                                                $leftSeats = array_slice($floorASeats, 0, 10);
                                                $rightSeats = array_slice($floorASeats, 10, 10);
                                                ?>

                                                <!-- Bên trái -->
                                                <div class="col-span-2 grid grid-cols-2 gap-1">
                                                    <?php foreach ($leftSeats as $seat): ?>
                                                        <button type="button"
                                                            class="seat-btn w-8 h-8 rounded text-white font-bold text-xs flex items-center justify-center
                                                                   <?= $seat['is_booked'] ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-500 hover:bg-green-600' ?>"
                                                            data-seat-id="<?= $seat['seat_id'] ?>"
                                                            data-seat-number="<?= $seat['seat_number'] ?>"
                                                            <?= $seat['is_booked'] ? 'disabled' : '' ?>>
                                                            <?= htmlspecialchars($seat['seat_number']) ?>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>

                                                <!-- Bên phải -->
                                                <div class="col-span-2 grid grid-cols-2 gap-1">
                                                    <?php foreach ($rightSeats as $seat): ?>
                                                        <button type="button"
                                                            class="seat-btn w-8 h-8 rounded text-white font-bold text-xs flex items-center justify-center
                                                                   <?= $seat['is_booked'] ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-500 hover:bg-green-600' ?>"
                                                            data-seat-id="<?= $seat['seat_id'] ?>"
                                                            data-seat-number="<?= $seat['seat_number'] ?>"
                                                            <?= $seat['is_booked'] ? 'disabled' : '' ?>>
                                                            <?= htmlspecialchars($seat['seat_number']) ?>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tầng trên -->
                                    <div>
                                        <h4 class="text-center font-semibold mb-3 text-sm text-gray-700">TẦNG TRÊN</h4>
                                        <div class="flex justify-center">
                                            <div class="grid grid-cols-4 gap-1 max-w-48">
                                                <?php
                                                $floorBSeats = array_filter($seats, function ($seat) {
                                                    return strpos($seat['seat_number'], 'B') === 0;
                                                });
                                                usort($floorBSeats, function ($a, $b) {
                                                    return (int)substr($a['seat_number'], 1) - (int)substr($b['seat_number'], 1);
                                                });

                                                $leftSeats = array_slice($floorBSeats, 0, 10);
                                                $rightSeats = array_slice($floorBSeats, 10, 10);
                                                ?>

                                                <!-- Bên trái -->
                                                <div class="col-span-2 grid grid-cols-2 gap-1">
                                                    <?php foreach ($leftSeats as $seat): ?>
                                                        <button type="button"
                                                            class="seat-btn w-8 h-8 rounded text-white font-bold text-xs flex items-center justify-center
                                                                   <?= $seat['is_booked'] ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-500 hover:bg-blue-600' ?>"
                                                            data-seat-id="<?= $seat['seat_id'] ?>"
                                                            data-seat-number="<?= $seat['seat_number'] ?>"
                                                            <?= $seat['is_booked'] ? 'disabled' : '' ?>>
                                                            <?= htmlspecialchars($seat['seat_number']) ?>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>

                                                <!-- Bên phải -->
                                                <div class="col-span-2 grid grid-cols-2 gap-1">
                                                    <?php foreach ($rightSeats as $seat): ?>
                                                        <button type="button"
                                                            class="seat-btn w-8 h-8 rounded text-white font-bold text-xs flex items-center justify-center
                                                                   <?= $seat['is_booked'] ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-500 hover:bg-blue-600' ?>"
                                                            data-seat-id="<?= $seat['seat_id'] ?>"
                                                            data-seat-number="<?= $seat['seat_number'] ?>"
                                                            <?= $seat['is_booked'] ? 'disabled' : '' ?>>
                                                            <?= htmlspecialchars($seat['seat_number']) ?>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-gray-500 py-8">
                                <p>Không có thông tin ghế</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h5 class="font-semibold text-[#183A6C] mb-2">Hướng dẫn chọn ghế</h5>
                        <div class="flex justify-center flex-wrap gap-4 text-sm">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                                <span>Tầng dưới - Trống</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
                                <span>Tầng trên - Trống</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-gray-400 rounded mr-2"></div>
                                <span>Đã đặt</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
                                <span>Đang chọn</span>
                            </div>
                        </div>

                        <hr class="my-4">
                        <!-- Bên phải: Thông tin tổng hợp -->
                        <div class="space-y-4">
                            <!-- Thông tin chuyến xe và đặt chỗ trong cùng một hàng - mỗi phần 50% -->
                            <div class="bg-white rounded-lg shadow p-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Thông tin chuyến xe - 50% -->

                                    <div>
                                        <h4 class="font-bold text-[#183A6C] mb-3 text-lg">Thông tin chuyến</h4>
                                        <div class="space-y-2 text-sm">
                                            <div><span class="font-medium">Biển xe:</span> <?= htmlspecialchars($bus->license_plate) ?></div>
                                            <div><span class="font-medium">Tài xế:</span> <?= htmlspecialchars($bus->driver_name) ?></div>
                                            <div><span class="font-medium">Tuyến:</span> <?= htmlspecialchars($route->start_point) ?> → <?= htmlspecialchars($route->end_point) ?></div>
                                            <div><span class="font-medium">Ngày đi:</span> <?= date('d/m/Y H:i', strtotime($selectedSchedule->departure_time)) ?></div>
                                            <div><span class="font-medium">Giá vé:</span> <span class="text-green-600 font-semibold"><?= number_format($selectedSchedule->price) ?>đ</span></div>
                                        </div>
                                    </div>

                                    <!-- Thông tin đặt chỗ - 50% -->
                                    <div>
                                        <h4 class="font-bold text-[#183A6C] mb-3 text-lg">Thông tin đặt chỗ</h4>
                                        <div class="space-y-2 text-sm">
                                            <div><span class="font-medium">Khách hàng:</span> <?= htmlspecialchars($user->full_name) ?></div>
                                            <div><span class="font-medium">Email:</span> <?= htmlspecialchars($user->email) ?></div>
                                            <div><span class="font-medium">SĐT:</span> <?= htmlspecialchars($user->phone_number ?? 'Chưa cập nhật') ?></div>
                                            <div><span class="font-medium">Ghế đã chọn:</span> <span id="selected-seats" class="text-blue-600 font-semibold">--</span></div>
                                            <div><span class="font-medium">Số lượng:</span> <span id="seat-count" class="text-blue-600 font-semibold">0</span> ghế</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chú thích chọn ghế - đặt ở dưới cùng của khối thông tin -->
                            </div>
                        </div>

                        <!-- Tổng tiền và thanh toán -->
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-bold text-[#183A6C] text-lg">Tổng thanh toán</h4>
                                <div class="text-2xl font-bold text-red-500" id="total-price">0đ</div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phương thức thanh toán</label>
                                <select name="payment_method" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#183A6C] focus:border-transparent">
                                    <option value="cash">Tiền mặt</option>
                                    <option value="bank_transfer">Chuyển khoản</option>
                                    <option value="credit_card">Thẻ tín dụng</option>
                                </select>
                            </div>

                            <button type="submit" id="submit-btn"
                                class="w-full bg-[#183A6C] text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-900 transition disabled:bg-gray-400 disabled:cursor-not-allowed"
                                disabled>
                                <span class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    Đặt vé ngay
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="text-center text-gray-500 py-8">
                <p>Vui lòng chọn lịch trình để tiếp tục đặt vé</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const seatBtns = document.querySelectorAll('.seat-btn:not([disabled])');
        const selectedSeatsSpan = document.getElementById('selected-seats');
        const seatCountSpan = document.getElementById('seat-count');
        const totalPriceSpan = document.getElementById('total-price');
        const submitBtn = document.getElementById('submit-btn');
        const bookingForm = document.getElementById('bookingForm');
        const seatPrice = <?= $selectedSchedule->price ?? 0 ?>;

        let selectedSeats = [];

        seatBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const seatId = this.getAttribute('data-seat-id');
                const seatNumber = this.getAttribute('data-seat-number');

                if (selectedSeats.find(s => s.id === seatId)) {
                    // Bỏ chọn
                    selectedSeats = selectedSeats.filter(s => s.id !== seatId);
                    this.classList.remove('bg-red-500');
                    // Restore original color based on floor
                    if (seatNumber.startsWith('A')) {
                        this.classList.add('bg-green-500');
                    } else {
                        this.classList.add('bg-blue-500');
                    }
                } else {
                    // Chọn ghế
                    selectedSeats.push({
                        id: seatId,
                        number: seatNumber
                    });
                    // Remove original color and add selected color
                    this.classList.remove('bg-green-500', 'bg-blue-500');
                    this.classList.add('bg-red-500');
                }

                updateBookingInfo();
            });
        });

        function updateBookingInfo() {
            // Cập nhật thông tin hiển thị
            selectedSeatsSpan.textContent = selectedSeats.length ?
                selectedSeats.map(s => s.number).join(', ') : '--';
            seatCountSpan.textContent = selectedSeats.length;
            totalPriceSpan.textContent = (selectedSeats.length * seatPrice).toLocaleString() + 'đ';

            // Enable/disable submit button
            submitBtn.disabled = selectedSeats.length === 0;

            // Update form với seat IDs
            updateFormSeats();
        }

        function updateFormSeats() {
            // Remove existing seat inputs
            const existingInputs = bookingForm.querySelectorAll('input[name="seat_ids[]"]');
            existingInputs.forEach(input => input.remove());

            // Add new seat inputs
            selectedSeats.forEach(seat => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'seat_ids[]';
                input.value = seat.id;
                bookingForm.appendChild(input);
            });
        }

        // Form validation
        bookingForm.addEventListener('submit', function(e) {
            if (selectedSeats.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một ghế');
                return;
            }

            if (!confirm('Bạn có chắc chắn muốn đặt vé với thông tin đã chọn?')) {
                e.preventDefault();
            }
        });
    });
</script>

<?php $this->stop() ?>