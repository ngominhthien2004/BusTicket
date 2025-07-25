<?php $this->layout("layouts/layout_admin", ["title" => APPNAME]) ?>
<?php $this->start("page") ?>

<main class="flex-1 bg-white">
    <header class="border-b border-gray-200 py-4 px-8">
        <h1 class="text-xl font-bold text-[#183A6C] text-center">
            Quản lý xe bus
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
    <img src="./access/img/multi/backgroundslider.avif" class="w-full h-48 object-cover rounded" alt="banner" />
    <main class="flex-1 bg-white">
        <header class="border-b border-gray-200 py-4 px-8">
            <h1 class="text-xl font-bold text-[#183A6C] text-center">
                Thêm tuyến xe
            </h1>
        </header>
        <section class="p-8">
            <div>
                <form action="" method="POST" class="max-w-3xl mx-auto p-6 bg-white shadow-md rounded-lg space-y-6">

                    <h2 class="text-xl font-semibold text-[#153D77] mb-4">Thêm thông tin xe</h2>

                    <!-- Loại xe -->
                    <div>
                        <label for="bus_type" class="block text-sm font-medium text-gray-700 mb-1">Chọn loại xe</label>
                        <select id="bus_type" name="bus_type" required
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option disabled selected>-- Chọn loại xe --</option>
                            <option value="Giường nằm">Xe giường nằm</option>
                            <option value="Limousine">Xe Limousine</option>
                            <option value="Ghế ngồi">Xe ghế ngồi</option>
                        </select>
                    </div>

                    <!-- Thông tin xe -->
                    <div class="space-y-4 mt-5">
                        <label class="block text-sm font-medium text-gray-700">Thông tin chi tiết</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <input type="text" name="license_plate" placeholder="Biển số xe" required
                                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <input type="text" name="driver_name" placeholder="Tên tài xế" required
                                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <input type="number" name="seat_count" placeholder="Số ghế" min="1" required
                                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Nút hành động -->
                    <div class="flex justify-end gap-4 pt-4">
                        <button type="submit"
                            class="bg-[#153D77] text-white font-semibold px-6 py-2 rounded-md hover:bg-blue-900 transition">
                            Thêm xe
                        </button>
                        <a href="/manage_addBus"
                            class="bg-gray-300 text-gray-800 font-semibold px-6 py-2 rounded-md hover:bg-gray-400 transition">
                            Huỷ
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </main>
    </div>
</main>

<?php $this->stop() ?>

<?php $this->start('scripts') ?>

<?php $this->stop() ?>