<?php $this->layout("layouts/default", ["title" => APPNAME]) ?>
<?php $this->start("page") ?>

<div class="mt-6 flex flex-col md:flex-row gap-6">
    <!-- Sidebar -->
    <aside class="w-full md:w-1/4">
        <div class="bg-white rounded-lg shadow p-4">
            <ul class="space-y-3 font-semibold text-[#183A6C]">
                <li>
                    <a href="/account" class="block px-2 py-1 rounded bg-gray-100 text-[#183A6C] font-bold">&gt; Thông tin cá nhân</a>
                </li>
                <li>
                    <a href="/user_history" class="block px-2 py-1 rounded hover:bg-gray-100 text-[#183A6C]">Lịch sử mua vé</a>
                </li>
                <li>
                    <a href="/change_password" class="block px-2 py-1 rounded hover:bg-gray-100 text-[#183A6C]">Đặt lại mật khẩu</a>
                </li>
                <li>
                    <a href="#" onclick="confirmLogout()" class="block px-2 py-1 rounded hover:bg-gray-100 text-[#183A6C]">Đăng xuất</a>
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
        <div class="bg-white rounded shadow p-6 flex flex-col md:flex-row gap-8 items-start">
            <!-- Avatar & Info -->
            <div class="flex flex-col items-center md:w-1/3">
                <?php
                // Đường dẫn file vật lý trên server
                $userId = $user->user_id ?? $user->id ?? 'default';
                $avatarWebPath = "/access/img/avatar/" . $userId . ".jpg";
                $defaultWebPath = "/access/img/avatar/avatar_default.png"; // <-- sửa lại đường dẫn mặc định
                $avatarFilePath = $_SERVER['DOCUMENT_ROOT'] . "/public" . $avatarWebPath;
                $displayPath = (is_numeric($userId) && file_exists($avatarFilePath)) ? $avatarWebPath : $defaultWebPath;
                ?>
                <img src="<?= $displayPath ?>" alt="avatar" class="w-28 h-28 rounded-full object-cover border-4 border-[#183A6C] mb-4" />
                <div class="text-left text-sm space-y-1">
                    <div><span class="font-semibold">Họ và tên:</span> <?= htmlspecialchars($user->full_name ?? '') ?></div>
                    <div><span class="font-semibold">Số điện thoại:</span> <?= htmlspecialchars($user->phone_number ?? '') ?></div>
                    <div><span class="font-semibold">Email:</span> <?= htmlspecialchars($user->email ?? '') ?></div>
                </div>
            </div>
            <!-- Update Form -->
            <div class="flex-1">
                <?php if (isset($messages) && !empty($messages)): ?>
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        <?php foreach ($messages as $message): ?>
                            <p><?= htmlspecialchars($message) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/account" enctype="multipart/form-data" class="bg-gray-100 rounded p-6 shadow-inner">
                    <h3 class="text-lg font-bold mb-4 text-center">Cập nhật thông tin cá nhân</h3>
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1 space-y-4">
                            <div>
                                <label class="block font-semibold mb-1">Họ và tên:</label>
                                <input type="text" name="full_name" class="w-full border border-gray-300 rounded px-3 py-2"
                                    value="<?= htmlspecialchars($old['full_name'] ?? $user->full_name ?? '') ?>" required />
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Số điện thoại:</label>
                                <input type="text" name="phone_number" class="w-full border border-gray-300 rounded px-3 py-2"
                                    value="<?= htmlspecialchars($old['phone_number'] ?? $user->phone_number ?? '') ?>" required />
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Email:</label>
                                <input type="email" name="email" class="w-full border border-gray-300 rounded px-3 py-2"
                                    value="<?= htmlspecialchars($old['email'] ?? $user->email ?? '') ?>" required />
                            </div>
                        </div>
                        <div class="flex flex-col items-center justify-center gap-4">
                            <label class="block font-semibold mb-1">Chọn ảnh đại diện</label>
                            <input type="file" name="avatar" accept="image/*" class="block mb-2" />
                            <div class="w-28 h-28 rounded-full bg-white border border-gray-300 flex items-center justify-center overflow-hidden">
                                <img src="<?= $displayPath ?>" alt="preview" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="submit" class="bg-[#183A6C] text-white px-6 py-2 rounded font-semibold hover:bg-blue-900 transition">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php $this->stop() ?>