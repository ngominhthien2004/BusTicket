<?php $this->layout("layouts/layout_admin", ["title" => APPNAME]) ?>
<?php $this->start("page") ?>

<main class="flex-1 bg-white">
    <header class="border-b border-gray-200 py-4 px-8">
        <h1 class="text-xl font-bold text-[#183A6C] text-center">
            Quản lý lịch trình
        </h1>
    </header>

    <!-- THÔNG BÁO TRẠNG THÁI -->
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
        <!-- Form tìm kiếm -->
        <div>
            <img src="./access/img/multi/backgroundslider.avif" class="w-full h-48 object-cover rounded" alt="banner" />
            <form method="get" class="top-8 -translate-y-[16px] bg-white bg-opacity-90 rounded-lg p-4 flex flex-wrap gap-4 items-end justify-center shadow" style="backdrop-filter: blur(2px);">
                <div class="flex flex-col">
                    <label class="font-semibold text-[#183A6C] mb-1">Điểm đi</label>
                    <input type="text" name="route_start" class="border border-gray-300 rounded px-3 py-1" placeholder="Nhập điểm đi"
                        value='<?= $this->e($filters['route_start'] ?? '') ?>'>
                </div>
                <div class="flex flex-col">
                    <label class="font-semibold text-[#183A6C] mb-1">Điểm đến</label>
                    <input type="text" name="route_end" class="border border-gray-300 rounded px-3 py-1" placeholder="Nhập điểm đến"
                        value='<?= $this->e($filters['route_end'] ?? '') ?>'>
                </div>
                <div class="flex flex-col">
                    <label class="font-semibold text-[#183A6C] mb-1">Ngày đi</label>
                    <input type="date" name="departure_time" class="border border-gray-300 rounded px-3 py-1"
                        value='<?= $this->e($filters['departure_time'] ?? '') ?>'>
                </div>
                <div class="flex flex-col">
                    <label class="font-semibold text-[#183A6C] mb-1">Số vé</label>
                    <input type="number" min="1" name="seat_count" class="border border-gray-300 rounded px-3 py-1" placeholder="Số vé"
                        value='<?= $this->e($filters['seat_count'] ?? '') ?>'>
                </div>
                <button type="submit" class="bg-[#183A6C] text-white px-6 py-2 rounded font-semibold hover:bg-blue-900 transition">
                    Tìm chuyến
                </button>
                <a href="/manage_allSchedules" class="bg-[#183A6C] text-white px-6 py-2 rounded font-semibold hover:bg-blue-900 transition">
                    Xoá bộ lọc
                </a>
            </form>
        </div>



        <!-- Danh sách tuyến xe -->
        <header class="border-b border-gray-200 py-4 px-8">
            <h1 class="text-xl font-bold text-[#183A6C] text-center">
                Danh sách lịch trình
            </h1>
        </header>

        <?php if (empty($schedules)): ?>
            <div class="text-center text-gray-500 mt-8">
                Không có lịch trình nào phù hợp với tiêu chí tìm kiếm.
            </div>
        <?php else: ?>
            <?php foreach ($schedules as $schedule): ?>
                <div class="flex flex-col md:flex-row items-center bg-white rounded shadow p-4 gap-4 my-4">
                    <div class="flex-1 flex items-center gap-4">
                        <img src="https://cdn-icons-png.flaticon.com/512/854/854878.png" class="h-10 w-10" alt="location">
                        <div>
                            <div class="font-bold text-lg text-[#183A6C]"><?= $this->e($schedule->route_start) ?></div>
                            <div class="text-gray-500">Thông tin điểm đi</div>
                        </div>
                        <span class="mx-2 text-gray-400 font-bold text-xl">→</span>
                        <div>
                            <div class="font-bold text-lg text-[#183A6C]"><?= $this->e($schedule->route_end) ?></div>
                            <div class="text-gray-500">Thông tin điểm đến</div>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-1">
                            <div>
                                <div><span class="text-gray-500">ID Xe: </span><span class="font-semibold"><?= $this->e($schedule->bus_id) ?></span></div>
                                <div><span class="text-gray-500">Biển số: </span><span class="font-semibold"><?= $this->e($schedule->bus_license_plate) ?></span></div>
                                <div><span class="text-gray-500">Loại xe: </span><span class="font-semibold"><?= $this->e($schedule->bus_type) ?></span></div>
                                <div><span class="text-gray-500">Số ghế: </span><span class="font-semibold"><?= $this->e($schedule->seat_count) ?></span></div>
                            </div>
                            <div>
                                <div><span class="text-gray-500">Thời gian khởi hành: </span><span class="font-semibold"><?= $this->e($schedule->departure_time) ?></span></div>
                                <div><span class="text-gray-500">Thời gian đến: </span><span class="font-semibold"><?= $this->e($schedule->arrival_time) ?></span></div>
                                <div><span class="text-gray-500">Giá vé/ghế: </span><span class="font-semibold text-red-600"><?= $this->e($schedule->price) ?></span></div>
                            </div>
                        </div>
                    </div>
                    <?php if ($schedule->ableToDelete()): ?>
                        <div class="flex items-center gap-2">
                            <a href="#"
                                class="bg-red-700 text-white px-5 py-2 rounded font-semibold hover:bg-red-600 transition"
                                onclick="confirmDelete(<?= $this->e($schedule->schedule_id) ?>)">
                                Xoá
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-gray-500">Không thể sửa/xoá lịch trình này</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php $this->stop() ?>

<?php $this->start('scripts') ?>
<script>
    function confirmDelete(scheduleId) {
        console.log('Delete function called with scheduleId:', scheduleId); // Debug log

        Swal.fire({
            title: 'Bạn có chắc muốn xóa lịch trình này?',
            text: "Hành động này không thể hoàn tác.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Redirecting to:', `/manage_allSchedules/delete/${scheduleId}`); // Debug log
                window.location.href = `/manage_allSchedules/delete/${scheduleId}`;
            }
        });
    }
</script>
<?php $this->stop() ?>