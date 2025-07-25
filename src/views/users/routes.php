<?php $this->layout("layouts/default", ["title" => APPNAME]) ?>
<?php $this->start("page") ?>

<div class="max-w-7xl mx-auto mt-6 mb-10">
    <!-- Debug information (remove after fixing) -->
    <?php if (isset($_GET['debug'])): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            <p>Total Routes: <?= count($routes ?? []) ?></p>
            <p>Cities: <?= count($routesByCity ?? []) ?></p>
            <p>Search Params: <?= json_encode($searchParams ?? []) ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-4">
        <div>
            <div>
                <img src="./access/img/multi/backgroundslider.avif" class="w-full h-48 object-cover rounded" alt="banner" />
                <form method="get" class="top-8 -translate-y-[16px] bg-white bg-opacity-90 rounded-lg p-6 flex flex-wrap gap-4 items-end justify-center shadow" style="backdrop-filter: blur(2px);">
                    <!-- Basic search fields -->
                    <div class="flex flex-col">
                        <label class="font-semibold text-[#183A6C] mb-1">Điểm đi</label>
                        <input type="text" name="start_point" value="<?= htmlspecialchars($searchParams['start_point'] ?? '') ?>" class="border border-gray-300 rounded px-3 py-1 w-48" placeholder="Nhập điểm đi">
                    </div>
                    <div class="flex flex-col">
                        <label class="font-semibold text-[#183A6C] mb-1">Điểm đến</label>
                        <input type="text" name="end_point" value="<?= htmlspecialchars($searchParams['end_point'] ?? '') ?>" class="border border-gray-300 rounded px-3 py-1 w-48" placeholder="Nhập điểm đến">
                    </div>
                    <div class="flex flex-col">
                        <label class="font-semibold text-[#183A6C] mb-1">Giá vé</label>
                        <select name="price_range" class="border border-gray-300 rounded px-3 py-1 w-48">
                            <option value="">Chọn mức giá</option>
                            <option value="0-100000" <?= ($searchParams['price_range'] ?? '') === '0-100000' ? 'selected' : '' ?>>Dưới 100,000đ</option>
                            <option value="100000-200000" <?= ($searchParams['price_range'] ?? '') === '100000-200000' ? 'selected' : '' ?>>100,000đ - 200,000đ</option>
                            <option value="200000-300000" <?= ($searchParams['price_range'] ?? '') === '200000-300000' ? 'selected' : '' ?>>200,000đ - 300,000đ</option>
                            <option value="300000-500000" <?= ($searchParams['price_range'] ?? '') === '300000-500000' ? 'selected' : '' ?>>300,000đ - 500,000đ</option>
                            <option value="500000-1000000" <?= ($searchParams['price_range'] ?? '') === '500000-1000000' ? 'selected' : '' ?>>Trên 500,000đ</option>
                        </select>
                    </div>

                    <div class="flex flex-col justify-end">
                        <button type="submit" class="bg-[#183A6C] text-white px-6 py-2 rounded font-semibold hover:bg-blue-900 transition">
                            Tìm chuyến
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-10">
            <!-- Hiển thị kết quả tìm kiếm theo thành phố -->
            <div class="mt-8">
                <?php if (empty($routesByCity)): ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500 text-lg">Không tìm thấy chuyến phù hợp</p>
                    </div>
                <?php else: ?>
                    <!-- Show active filters - Updated to properly handle empty price filter -->
                    <?php if (array_filter($searchParams ?? [])): ?>
                        <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                            <h4 class="font-semibold text-[#183A6C] mb-2">Bộ lọc đang áp dụng:</h4>
                            <div class="flex flex-wrap gap-2">
                                <?php if (!empty($searchParams['start_point'])): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                        Điểm đi: <?= htmlspecialchars($searchParams['start_point']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($searchParams['end_point'])): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                        Điểm đến: <?= htmlspecialchars($searchParams['end_point']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($searchParams['price_range']) && $searchParams['price_range'] !== ''): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                        Giá: <?= htmlspecialchars($searchParams['price_range']) ?>
                                    </span>
                                <?php endif; ?>
                                <a href="/routes" class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-700 hover:bg-gray-200">
                                    ✕ Xóa bộ lọc
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-[#183A6C] text-center">
                            Tìm thấy <?= count($routes) ?> chuyến từ <?= count($routesByCity) ?> thành phố
                        </h3>
                    </div>

                    <div class="space-y-10">
                        <?php $cityCounter = 0; ?>
                        <?php foreach ($routesByCity as $city => $cityRoutes): ?>
                            <div class="city-section <?= $cityCounter > 0 ? 'border-t-2 border-gray-100 pt-8' : '' ?>">
                                <!-- Header thành phố -->
                                <div class="bg-gradient-to-r from-gray-700 to-gray-800 text-white p-4 rounded-t-lg">
                                    <h4 class="text-xl font-bold flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="text-black font-semibold"><?= htmlspecialchars($city ?? '') ?></span>
                                        </div>
                                        <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm font-medium">
                                            <?= count($cityRoutes) ?> chuyến
                                        </span>
                                    </h4>
                                </div>

                                <!-- Danh sách chuyến của thành phố -->
                                <div class="bg-white border-x border-b rounded-b-lg shadow-sm">
                                    <?php foreach ($cityRoutes as $index => $route): ?>
                                        <div class="route-item border-b border-gray-200 last:border-b-0 p-4 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center justify-between flex-wrap gap-4">
                                                <!-- Thông tin tuyến đường -->
                                                <div class="flex items-center space-x-8 flex-1">
                                                    <div class="text-center">
                                                        <div class="text-lg font-bold text-[#183A6C]">
                                                            <?= htmlspecialchars($route['start_point'] ?? '') ?>
                                                        </div>
                                                        <div class="text-sm text-gray-600">Điểm đi</div>
                                                    </div>
                                                    <div class="flex items-center justify-center" style="width: 2cm;">
                                                        <!-- Mũi tên từ trái qua phải -->
                                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
                                                        </svg>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="text-lg font-bold text-[#183A6C]">
                                                            <?= htmlspecialchars($route['end_point'] ?? '') ?>
                                                        </div>
                                                        <div class="text-sm text-gray-600">Điểm đến</div>
                                                    </div>
                                                </div>


                                                <!-- Thông tin giá và lịch trình -->
                                                <div class="text-center min-w-0 flex-shrink-0">
                                                    <?php if ($route['min_price'] && $route['max_price']): ?>
                                                        <div class="text-lg font-bold text-green-600">
                                                            <?php if ($route['min_price'] == $route['max_price']): ?>
                                                                <?= number_format($route['min_price'], 0, ',', '.') ?>đ
                                                            <?php else: ?>
                                                                <?= number_format($route['min_price'], 0, ',', '.') ?>đ - <?= number_format($route['max_price'], 0, ',', '.') ?>đ
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-lg font-bold text-gray-500">Chưa có giá</div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Nút đặt vé - Removed "Xem lịch trình" button -->
                                                <div class="text-center min-w-0 flex-shrink-0">
                                                    <?php if (($route['schedule_count'] ?? 0) > 0): ?>
                                                        <?php if (AUTHGUARD()->isUserLoggedIn()): ?>
                                                            <a href="/booking?route_id=<?= $route['route_id'] ?>"
                                                                class="inline-block bg-[#183A6C] text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-900 transition duration-200 shadow-sm hover:shadow-md text-sm">
                                                                Đặt vé
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="/login?redirect=/booking?route_id=<?= $route['route_id'] ?>"
                                                                class="inline-block bg-[#183A6C] text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-900 transition duration-200 shadow-sm hover:shadow-md text-sm">
                                                                Đặt vé
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <button class="inline-block bg-gray-400 text-white px-6 py-2 rounded-lg font-semibold cursor-not-allowed opacity-75 text-sm"
                                                            disabled>
                                                            Chưa có lịch trình
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Horizontal Rule between routes (except for last item) -->
                                        <?php if ($index < count($cityRoutes) - 1): ?>
                                            <hr class="border-gray-200">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php $cityCounter++; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->stop() ?>