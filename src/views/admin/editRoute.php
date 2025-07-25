<?php $this->layout("layouts/layout_admin", ["title" => APPNAME]) ?>
<?php $this->start("page") ?>

<main class="flex-1 bg-white">
    <header class="border-b border-gray-200 py-4 px-8">
        <h1 class="text-xl font-bold text-[#183A6C] text-center">
            Cập nhật tuyến xe
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
    <img src="/access/img/multi/backgroundslider.avif" class="w-full h-48 object-cover rounded" alt="banner" />
    <main class="flex-1 bg-white">
        <header class="border-b border-gray-200 py-4 px-8">
            <h1 class="text-xl font-bold text-[#183A6C] text-center">
                Cập nhật tuyến xe <?= $this->e($route->route_id) ?>
            </h1>
        </header>
        <section class="p-8">
            <div>
                <form action="" method="POST" class="space-y-6">
                    <div id="new-route" class="border border-gray-300 rounded-lg p-4 bg-gray-50 space-y-4">
                        <p class="font-medium text-gray-700">Nhập điểm đi và điểm đến mới:</p>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <input type="text" name="start_point" placeholder="Điểm đi"
                                value="<?= $this->e($route->start_point) ?>"
                                class="flex-1 rounded-md border border-gray-300 p-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500">

                            <input type="text" name="end_point" placeholder="Điểm đến"
                                value="<?= $this->e($route->end_point) ?>"
                                class="flex-1 rounded-md border border-gray-300 p-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500">

                            <input type="text" name="distance_km" placeholder="Khoảng cách (KM)"
                                value="<?= $this->e($route->distance_km) ?>"
                                class="flex-1 rounded-md border border-gray-300 p-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Nút hành động -->
                    <div class="flex gap-4 justify-center">
                        <button type="submit"
                            class="px-5 py-2 rounded-md bg-green-600 text-white font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Cập nhật tuyến
                        </button>

                        <a href="/manage_allRoute"
                            class="px-5 py-2 rounded-md bg-red-600 text-white font-semibold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
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