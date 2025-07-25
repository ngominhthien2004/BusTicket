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

    <!-- Banner -->
    <img src="/access/img/multi/backgroundslider.avif" class="w-full h-48 object-cover rounded" alt="banner" />

    <header class="border-b border-gray-200 py-4 px-8">
        <h1 class="text-xl font-bold text-[#183A6C] text-center">
            Thêm lịch trình
        </h1>
    </header>

    <section class="p-8">
        <!-- Tìm xe kha dụng trong khoảng Time được chọn -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-4">🔍 Bước 1: Tìm xe khả dụng</h3>

            <form action="/get_available_buses" method="GET" class="space-y-6" id="searchForm">
                <input type="hidden" name="route_id" value="<?= htmlspecialchars($_GET['route_id'] ?? $selected_route_id ?? '') ?>">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- chon ngay giờ bd -->
                    <fieldset class="border border-gray-300 rounded-lg p-4 space-y-2">
                        <legend class="px-2 text-sm font-medium text-gray-600">Thời gian khởi hành</legend>
                        <input type="datetime-local"
                            name="departure_time"
                            value="<?= htmlspecialchars($_GET['departure_time'] ?? $departure_time ?? '') ?>"
                            class="w-full rounded-md border border-gray-300 p-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            required>
                        <p class="text-sm text-gray-500">Chọn thời gian khởi hành</p>
                    </fieldset>

                    <!-- ngay gio den dia diem -->
                    <fieldset class="border border-gray-300 rounded-lg p-4 space-y-2">
                        <legend class="px-2 text-sm font-medium text-gray-600">Thời gian đến địa điểm</legend>
                        <input type="datetime-local"
                            name="arrival_time"
                            value="<?= htmlspecialchars($_GET['arrival_time'] ?? $arrival_time ?? '') ?>"
                            class="w-full rounded-md border border-gray-300 p-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            required>
                        <p class="text-sm text-gray-500">Chọn thời gian đến địa điểm</p>
                    </fieldset>

                    <div class="rounded-lg p-4 space-y-2 flex flex-col justify-end">
                        <button type="submit" class="mt-4 bg-blue-500 text-white rounded-md px-4 py-2 hover:bg-blue-600 transition">
                            Tìm xe khả dụng
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- form tao schedule khi da chon thoi gian -->
        <?php if (isset($buses) && (isset($departure_time) && $departure_time)): ?>
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Bước 2: Tạo lịch trình</h3>

                <form method="POST" action="/create_schedule" class="space-y-6" id="scheduleForm">
                    <!-- cac feild cần thiết -->
                    <input type="hidden" name="departure_time" value="<?= htmlspecialchars($departure_time ?? '') ?>">
                    <input type="hidden" name="arrival_time" value="<?= htmlspecialchars($arrival_time ?? '') ?>">

                    <!-- Chọn tuyến -->
                    <fieldset class="border border-gray-300 rounded-lg p-4 space-y-2">
                        <legend class="px-2 text-sm font-medium text-gray-600">Tuyến xe</legend>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-semibold text-green-800">Tuyến đã chọn:</h4>
                            <p class="text-green-700">
                                <?= htmlspecialchars($route->start_point) ?> → <?= htmlspecialchars($route->end_point) ?>
                                (<?= $route->distance_km ?>km)
                            </p>
                            <input type="hidden" name="route_id" value="<?= $route->route_id ?>">
                        </div>
                    </fieldset>

                    <!-- hien thị time khởi hành đã được chọn ở form trên -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-2">🕐 Thời gian khởi hành:</h4>
                            <p class="text-gray-700"><?= date('d/m/Y H:i', strtotime($departure_time ?? '')) ?></p>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-2">🕐 Thời gian đến:</h4>
                            <p class="text-gray-700"><?= date('d/m/Y H:i', strtotime($arrival_time ?? '')) ?></p>
                        </div>
                    </div>

                    <!-- Chọn xe bằng radio buttons -->
                    <fieldset class="border border-gray-300 rounded-lg p-4 space-y-2">
                        <legend class="px-2 text-sm font-medium text-gray-600">Chọn xe bus</legend>

                        <div class="space-y-3 max-h-64 overflow-y-auto border border-gray-100 rounded-md p-3">
                            <?php if (!empty($buses)): ?>
                                <?php foreach ($buses as $index => $bus): ?>
                                    <label class="bus-option flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 cursor-pointer transition-all duration-200">
                                        <input type="radio"
                                            name="bus_id"
                                            value="<?= $bus->bus_id ?>"
                                            id="bus_<?= $index ?>"
                                            class="mr-4 text-blue-600 focus:ring-blue-500 focus:ring-2 w-4 h-4"
                                            required>

                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-semibold text-gray-900 text-base">
                                                        <?= htmlspecialchars($bus->license_plate) ?>
                                                    </span>
                                                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                        <?= htmlspecialchars($bus->bus_type) ?>
                                                    </span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm text-gray-600">
                                                        👥 <?= $bus->seat_count ?> ghế
                                                    </span>
                                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                                        Khả dụng
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                🚗 Tài xế: <span class="font-medium"><?= htmlspecialchars($bus->driver_name) ?></span>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-8 text-gray-500">
                                    <div class="text-4xl mb-2">🚌</div>
                                    <p class="font-medium">Không có xe bus nào khả dụng</p>
                                    <p class="text-sm">Vui lòng chọn thời gian khác</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center justify-between text-sm text-gray-500 mt-3 pt-2 border-t border-gray-100">
                            <span><?= count($buses ?? []) ?> xe khả dụng</span>
                            <span>⚡ Đã lọc theo thời gian</span>
                        </div>
                    </fieldset>

                    <!-- set Giá vé-->
                    <fieldset class="border border-gray-300 rounded-lg p-4 space-y-2">
                        <legend class="px-2 text-sm font-medium text-gray-600">Giá vé</legend>
                        <div class="flex items-center space-x-2">
                            <input type="number"
                                name="price"
                                min="0"
                                step="1000"
                                placeholder="200000"
                                class="w-full rounded-md border border-gray-300 p-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required>
                            <span class="text-gray-500 font-medium">VND</span>
                        </div>
                        <p class="text-sm text-gray-500">Giá vé cho một ghế (VD: 200,000 VND)</p>
                    </fieldset>

                    <!-- tạo hoặc huỷ  -->
                    <div class="flex gap-4 justify-end pt-4 border-t border-gray-200">
                        <a href="/manage_allRoute"
                            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                            Hủy
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                            Tạo lịch trình
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <div class="text-4xl mb-2">⏰</div>
                <h3 class="text-lg font-semibold text-yellow-800 mb-2">Chưa chọn thời gian</h3>
                <p class="text-yellow-700">Vui lòng chọn thời gian khởi hành và thời gian đến, sau đó click "Tìm xe khả dụng" để tiếp tục.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php $this->stop() ?>