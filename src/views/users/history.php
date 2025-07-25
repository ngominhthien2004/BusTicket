<?php $this->layout("layouts/default", ["title" => APPNAME]) ?>
<?php $this->start("page") ?>

<div class="mt-6 flex flex-col md:flex-row gap-6">
    <!-- Sidebar -->
    <aside class="w-full md:w-1/4">
        <div class="bg-white rounded-lg shadow p-4">
            <ul class="space-y-3 font-semibold text-[#183A6C]">
                <li>
                    <a href="/account" class="block px-2 py-1 rounded hover:bg-gray-100 text-[#183A6C]">Thông tin cá nhân</a>
                </li>
                <li>
                    <a href="/user_history" class="block px-2 py-1 rounded bg-gray-100 text-[#183A6C] font-bold">&gt; Thống kê mua vé</a>
                </li>
                <li>
                    <a href="/change_password" class="block px-2 py-1 rounded hover:bg-gray-100 text-[#183A6C]">Đặt lại mật khẩu</a>
                </li>
                <li>
                    <a href="/logout" class="block px-2 py-1 rounded hover:bg-gray-100 text-[#183A6C]">Đăng xuất</a>
                </li>
            </ul>
            <div class="flex items-center mt-6 gap-2">
                <img src="https://i.imgur.com/4M7IWwP.png" alt="logo" class="h-8 w-8 rounded-full bg-white p-1" />
                <span class="font-bold text-[#183A6C] text-lg">BusTicket</span>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1">

        <h1 class="text-center text-[#153D77] text-xl font-bold mb-6">THỐNG KÊ MUA VÉ</h1>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-100 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-[#183A6C]"><?= $stats['total_bookings'] ?? 0 ?></div>
                <div class="text-sm text-gray-600">Tổng vé đã mua</div>
            </div>
            <div class="bg-green-100 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-[#183A6C]"><?= number_format($stats['total_spent'] ?? 0) ?>đ</div>
                <div class="text-sm text-gray-600">Tổng chi tiêu</div>
            </div>
            <div class="bg-yellow-100 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-[#183A6C]"><?= $stats['completed_trips'] ?? 0 ?></div>
                <div class="text-sm text-gray-600">Chuyến đã đi</div>
            </div>
            <div class="bg-purple-100 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-[#183A6C]"><?= $stats['upcoming_trips'] ?? 0 ?></div>
                <div class="text-sm text-gray-600">Chuyến sắp tới</div>
            </div>
        </div>

    </main>
</div>
<?php $this->stop() ?>