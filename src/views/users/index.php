<?php $this->layout("layouts/default", ["title" => APPNAME]) ?>
<?php $this->start("page") ?>

<div class="max-w-7xl mx-auto mt-8 mb-10 px-3">
    <!-- Debug information (remove this after fixing) -->
    <?php if (isset($_GET['debug'])): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            <p>HCM Routes: <?= count($hcm_routes ?? []) ?></p>
            <p>Cantho Routes: <?= count($cantho_routes ?? []) ?></p>
            <p>Hanoi Routes: <?= count($hanoi_routes ?? []) ?></p>
        </div>
    <?php endif; ?>

    <!-- Hiển thị thông báo nếu có -->
    <?php if (!empty($messages)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php foreach ($messages as $message): ?>
                <p><?= htmlspecialchars($message) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-8 flex flex-col md:flex-row items-center gap-8 hover:shadow-lg">
        <div class="flex-1">
            <h1 class="text-3xl md:text-4xl font-bold text-[#153D77] mb-4">Chào mừng đến với BusTicket!</h1>
            <p class="text-gray-700 mb-4 text-lg">
                Hệ thống bán vé xe khách trực tuyến hiện đại, nhanh chóng và tiện lợi.<br>
                Đặt vé mọi lúc, mọi nơi chỉ với vài thao tác đơn giản.
            </p>
            <ul class="mb-6 space-y-2 text-gray-700">
                <li>✔️ Tìm kiếm và đặt vé xe khách dễ dàng</li>
                <li>✔️ Tra cứu thông tin vé nhanh chóng</li>
                <li>✔️ Quản lý tài khoản, đổi mật khẩu an toàn</li>
                <li>✔️ Hỗ trợ khách hàng 24/7</li>
            </ul>
            <div class="flex flex-wrap gap-4">
                <a href="/routes" class="bg-[#183A6C] text-white px-6 py-2 rounded font-semibold hover:bg-blue-900 transition">Tìm chuyến xe</a>
                <a href="/ticket_lookup" class="bg-blue-100 text-[#183A6C] px-6 py-2 rounded font-semibold hover:bg-blue-200 transition">Tra cứu vé</a>
            </div>
        </div>
    </div>

    <!-- Tuyen pho bien -->
    <div class="container mt-7 mx-auto">
        <h1 class="text-center text-2xl text-[#153D77] font-bold">TUYẾN PHỔ BIẾN</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 bg-[#fff] mt-3 gap-4 rounded-lg shadow px-2 mx-auto">
            <!-- TP.HCM -->
            <div class="text-center sm:col-span-1 md:col-span-4 my-5 bg-[#f9fafb] rounded-xl shadow hover:shadow-lg transition-all duration-300 pb-3">
                <img src="./access/img/Homepage/hcmcity.jpg" class="w-full h-[auto] rounded-sm" alt="Tìm chuyến xe">
                <h2 class="text-2xl text-[#153D77] my-4">Hồ Chí Minh City</h2>
                <div class="grid grid-cols-2 font-semibold text-xl my-3 text-[#153D77]">
                    <p>Điểm đến</p>
                    <p>Giá vé</p>
                </div>
                <?php if (!empty($hcm_routes)): ?>
                    <?php foreach ($hcm_routes as $route): ?>
                        <div class="group grid grid-cols-2">
                            <p class="group-hover:bg-indigo-50 py-2 ">
                                <a href="/booking?route_id=<?= $route['route_id'] ?>" class="text-blue-600 hover:text-blue-800"><?= htmlspecialchars($route['end_point']) ?></a>
                            </p>
                            <p class="group-hover:bg-indigo-50 py-2 ">
                                <a href="/booking?route_id=<?= $route['route_id'] ?>" class="text-blue-600 hover:text-blue-800"><?= number_format($route['price'] ?? 0) ?>đ</a>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-gray-500 py-4">
                        <p>Chưa có tuyến đường nào</p>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Cần Thơ -->
            <div class="text-center sm:col-span-1 md:col-span-4 my-5 bg-[#f9fafb] rounded-xl shadow hover:shadow-lg transition-all duration-300 pb-3">
                <img src="./access/img/Homepage/cantho.jpeg" class="w-full h-[auto] rounded-sm" alt="Tìm chuyến xe">
                <h2 class="text-2xl text-[#153D77] my-4">Cần Thơ City</h2>
                <div class="grid grid-cols-2 font-semibold text-xl my-3 text-[#153D77]">
                    <p>Điểm đến</p>
                    <p>Giá vé</p>
                </div>
                <?php foreach (($cantho_routes ?? []) as $route): ?>
                    <div class="group grid grid-cols-2">
                        <p class="group-hover:bg-indigo-50 py-2 ">
                            <a href="/booking?route_id=<?= $route['route_id'] ?>" class="text-blue-600 hover:text-blue-800"><?= htmlspecialchars($route['end_point']) ?></a>
                        </p>
                        <p class="group-hover:bg-indigo-50 py-2 ">
                            <a href="/booking?route_id=<?= $route['route_id'] ?>" class="text-blue-600 hover:text-blue-800"><?= number_format($route['price'] ?? 0) ?>đ</a>
                        </p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($cantho_routes)): ?>
                    <div class="text-gray-500 py-4">
                        <p>Chưa có tuyến đường nào</p>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Hà Nội -->
            <div class="text-center sm:col-span-2 md:col-span-4 my-5 bg-[#f9fafb] rounded-xl shadow hover:shadow-lg transition-all duration-300 pb-3">
                <img src="./access/img/Homepage/hanoi.jpg" class="w-full h-[auto] rounded-sm" alt="Tìm chuyến xe">
                <h2 class="text-2xl text-[#153D77] my-4">Hà Nội City</h2>
                <div class="grid grid-cols-2 font-semibold text-xl my-3 text-[#153D77]">
                    <p>Điểm đến</p>
                    <p>Giá vé</p>
                </div>
                <?php foreach (($hanoi_routes ?? []) as $route): ?>
                    <div class="group grid grid-cols-2">
                        <p class="group-hover:bg-indigo-50 py-2 ">
                            <a href="/booking?route_id=<?= $route['route_id'] ?>" class="text-blue-600 hover:text-blue-800"><?= htmlspecialchars($route['end_point']) ?></a>
                        </p>
                        <p class="group-hover:bg-indigo-50 py-2 ">
                            <a href="/booking?route_id=<?= $route['route_id'] ?>" class="text-blue-600 hover:text-blue-800"><?= number_format($route['price'] ?? 0) ?>đ</a>
                        </p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($hanoi_routes)): ?>
                    <div class="text-gray-500 py-4">
                        <p>Chưa có tuyến đường nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mx-auto mt-8">
        <h1 class="text-center text-3xl text-[#153D77] font-bold">Hãy đến với dịch vụ của chúng tôi</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 bg-[#fff] mt-2 gap-4 rounded-lg shadow px-2 mx-auto">
            <div class="text-center sm:col-span-1 md:col-span-4 my-5 bg-[#f9fafb] rounded-xl p-4 shadow hover:shadow-lg transition-all duration-300">
                <img src="./access/img/Homepage/map.png" class="size-16 mx-auto my-5" alt="Tìm chuyến xe">
                <h2 class="text-2xl text-[#153D77] my-4">Tìm chuyến xe</h2>
                <p>Dễ dàng tìm kiếm chuyến xe phù hợp với nhu cầu của bạn.</p>
            </div>
            <div class="text-center sm:col-span-1 md:col-span-4 my-5 bg-[#f9fafb] rounded-xl p-4 shadow hover:shadow-lg transition-all duration-300">
                <img src="./access/img/Homepage/bookticket.png" class="size-16 mx-auto my-5" alt="Đặt vé trực tuyến">
                <h2 class="text-2xl text-[#153D77] my-4">Đặt vé trực tuyến</h2>
                <p>Đặt vé nhanh chóng, thanh toán tiện lợi, xác nhận tức thì.</p>
            </div>
            <div class="text-center sm:col-span-2 md:col-span-4 my-5 bg-[#f9fafb] rounded-xl p-4 shadow hover:shadow-lg transition-all duration-300">
                <img src="./access/img/Homepage/support.png" class="size-16 mx-auto my-5" alt="Hỗ trợ tận tâm">
                <h2 class="text-2xl text-[#153D77] my-4">Hỗ trợ tận tâm</h2>
                <p>Đội ngũ hỗ trợ luôn sẵn sàng giải đáp thắc mắc của bạn.</p>
            </div>
        </div>
    </div>

</div>
<?php $this->stop() ?>