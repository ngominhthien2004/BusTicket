<?php $this->layout("layouts/layout_admin", ["title" => APPNAME]) ?>
<?php $this->start("page") ?>

<main class="flex-1 bg-white">
    <header class="border-b border-gray-200 py-4 px-8">
        <h1 class="text-xl font-bold text-[#183A6C] text-center">
            Quản lý xe
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
    <!-- main content -->
    <div>
        <img src="/access/img/multi/backgroundslider.avif" class="w-full h-48 object-cover rounded" alt="banner" />
        <form method="get" action="/manage_allBus" class="top-8 -translate-y-[16px] bg-white bg-opacity-90 rounded-lg p-4 flex flex-wrap gap-4 items-end justify-center shadow" style="backdrop-filter: blur(2px);">
            <div class="flex flex-col">
                <label class="font-semibold text-[#183A6C] mb-1">Tên tài xế</label>
                <input type="text" name="driver_name" class="border border-gray-300 rounded px-3 py-1" placeholder="Nhập tên tài xế"
                    value='<?= $this->e($filters['driver_name'] ?? '') ?>'>
            </div>
            <div class="flex flex-col">
                <label class="font-semibold text-[#183A6C] mb-1">Chọn loại xe</label>
                <select name="bus_type" class="border border-gray-300 rounded px-3 py-1">
                    <option value="">Chọn loại xe</option>
                    <option value="Giường nằm" <?= ($filters['bus_type'] ?? '') === 'Giường nằm' ? 'selected' : '' ?>>Giường nằm</option>
                    <option value="Ghế ngồi" <?= ($filters['bus_type'] ?? '') === 'Ghế ngồi' ? 'selected' : '' ?>>Ghế ngồi</option>
                    <option value="Limousine" <?= ($filters['bus_type'] ?? '') === 'Limousine' ? 'selected' : '' ?>>Limousine</option>
                </select>
            </div>
            <div class="flex flex-col">
                <label class="font-semibold text-[#183A6C] mb-1">Biển số xe</label>
                <input type="text" name="license_plate" class="border border-gray-300 rounded px-3 py-1" placeholder="Nhập biển số xe"
                    value='<?= $this->e($filters['license_plate'] ?? '') ?>'>
            </div>
            <div class="flex flex-col">
                <label class="font-semibold text-[#183A6C] mb-1">Số ghế</label>
                <input type="number" min="1" name="seat_count" class="border border-gray-300 rounded px-3 py-1" placeholder="Số ghế"
                    value='<?= $this->e($filters['seat_count'] ?? '') ?>'>
            </div>
            <button type="submit" class="bg-[#183A6C] text-white px-6 py-2 rounded font-semibold hover:bg-blue-900 transition">
                Tìm xe
            </button>
            <a href="/manage_allBus"
                class="bg-black text-white px-6 py-2 rounded font-semibold hover:bg-gray-600 transition">
                Xóa bộ lọc
            </a>
        </form>
    </div>
    <main class="flex-1 bg-white">
        <header class="border-b border-gray-200 py-4 px-8">
            <h1 class="text-xl font-bold text-[#183A6C] text-center">
                Danh sách xe
            </h1>
        </header>
        <section class="p-8">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-300 rounded-lg overflow-hidden shadow">
                <thead class="bg-[#153D77] text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Biển số</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Loại xe</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Số ghế</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Tài xế</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold uppercase">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-gray-800">
                    <?php foreach ($buses as $bus): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4"><?= $this->e($bus->bus_id) ?></td>
                            <td class="px-6 py-4"><?= $this->e($bus->license_plate) ?></td>
                            <td class="px-6 py-4"><?= $this->e($bus->bus_type) ?></td>
                            <td class="px-6 py-4"><?= $this->e($bus->seat_count) ?></td>
                            <td class="px-6 py-4"><?= $this->e($bus->driver_name) ?></td>
                            <td class="px-6 py-4 text-center">
                                <a href="/manage_editBus/<?= $bus->bus_id ?>"
                                    class=" inline-flex items-center gap-1 text-blue-600 hover:underline font-medium">
                                    Sửa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($buses)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
    </div>
</main>

<?php $this->stop() ?>

<?php $this->start('scripts') ?>
<script>
    function toggleInput(selectType) {
        const oldRoute = document.getElementById("old-route");
        const newRoute = document.getElementById("new-route");
        if (selectType === "old") {
            oldRoute.classList.remove("hidden");
            newRoute.classList.add("hidden");
        } else {
            oldRoute.classList.add("hidden");
            newRoute.classList.remove("hidden");
        }
    }
</script>
<?php $this->stop() ?>