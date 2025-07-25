<?php $this->layout("layouts/layout_admin", ["title" => APPNAME]) ?>
<?php $this->start("page") ?>

<main class="flex-1 bg-white">
    <header class="border-b border-gray-200 py-4 px-8">
        <h1 class="text-xl font-bold text-[#183A6C] text-center">
            Quản lý tuyến xe
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
                    <input type="text" name="start_point" class="border border-gray-300 rounded px-3 py-1" placeholder="Nhập điểm đi"
                        value="<?= $this->e($filters['start_point'] ?? '') ?>">
                </div>
                <div class="flex flex-col">
                    <label class="font-semibold text-[#183A6C] mb-1">Điểm đến</label>
                    <input type="text" name="end_point" class="border border-gray-300 rounded px-3 py-1" placeholder="Nhập điểm đến"
                        value="<?= $this->e($filters['end_point'] ?? '') ?>">
                </div>
                <div class="flex flex-col">
                    <label class="font-semibold text-[#183A6C] mb-1">Khoảng cách</label>
                    <input type="number" min="1" name="distance_km" class="border border-gray-300 rounded px-3 py-1" placeholder="Khoảng cách (km)"
                        value="<?= $this->e($filters['distance_km'] ?? '') ?>">
                </div>
                <button type="submit" class="bg-[#183A6C] text-white px-6 py-2 rounded font-semibold hover:bg-blue-900 transition">
                    Tìm tuyến
                </button>
                <a href="/manage_allRoute" class="bg-[#183A6C] text-white px-6 py-2 rounded font-semibold hover:bg-blue-900 transition">
                    Xoá bộ lọc
                </a>
            </form>
        </div>

        <!-- Danh sách tuyến xe -->
        <header class="border-b border-gray-200 py-4 px-8">
            <h1 class="text-xl font-bold text-[#183A6C] text-center">
                Danh sách tuyến xe
            </h1>
        </header>
        <?php if (empty($routes)): ?>
            <div class="text-center text-gray-500 mt-8">
                Không có tuyến xe nào phù hợp với tiêu chí tìm kiếm.
            </div>
        <?php endif; ?>
        <?php foreach ($routes as $route): ?>
            <div class="flex flex-col md:flex-row items-center bg-white rounded shadow p-4 gap-4 my-4">
                <div class="flex-2 flex items-center gap-4">
                    <img src="https://cdn-icons-png.flaticon.com/512/854/854878.png" class="h-10 w-10" alt="location">
                    <div>
                        <div class="font-bold text-lg text-[#183A6C]"><?= $this->e($route->start_point) ?></div>
                        <div class="text-gray-500">Thông tin điểm đi</div>
                    </div>
                    <div class="mx-4 text-gray-400 font-bold text-xl">
                        ← <?= $this->e($route->distance_km) ?> Km →
                    </div>
                    <div>
                        <div class="font-bold text-lg text-[#183A6C]"><?= $this->e($route->end_point) ?></div>
                        <div class="text-gray-500">Thông tin điểm đến</div>
                    </div>
                    <img src="https://cdn-icons-png.flaticon.com/512/854/854878.png" class="h-10 w-10" alt="location">
                </div>
                <div class="flex-1 justify-end flex flex-col md:flex-row items-center gap-4">
                    <div>
                        <a href="/manage_addSchedule/<?= $this->e($route->route_id) ?>"
                            class="bg-blue-700 text-white px-5 py-2 rounded font-semibold hover:bg-green-600 transition">
                            Tạo lịch trình
                        </a>
                    </div>

                    <div>
                        <a href="/manage_editRoute/<?= $this->e($route->route_id) ?>"
                            class="bg-green-700 text-white px-5 py-2 rounded font-semibold hover:bg-green-600 transition">
                            Cập nhật tuyến
                        </a>
                    </div>

                    <div>
                        <a href="#"
                            class="bg-red-700 text-white px-5 py-2 rounded font-semibold hover:bg-red-600 transition"
                            onclick="confirmDelete(<?= $this->e($route->route_id) ?>)">
                            Xoá tuyến
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<?php $this->stop() ?>

<?php $this->start('scripts') ?>
<script>
    function confirmDelete(routeId) {
        console.log('Delete function called with routeId:', routeId); // Debug log

        Swal.fire({
            title: 'Bạn có chắc muốn xóa tuyến này?',
            text: "Hành động này không thể hoàn tác.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Redirecting to:', `/manage_deleteRoute/delete/${routeId}`); // Debug log
                window.location.href = `/manage_deleteRoute/delete/${routeId}`;
            }
        });
    }
</script>
<?php $this->stop() ?>