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
                    <a href="/user_history" class="block px-2 py-1 rounded hover:bg-gray-100 text-[#183A6C]">Lịch sử mua vé</a>
                </li>
                <li>
                    <a href="/change_password" class="block px-2 py-1 rounded bg-gray-100 text-[#183A6C] font-bold">&gt; Đặt lại mật khẩu</a>
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
        <div class="bg-white rounded shadow p-6">
            <h2 class="text-2xl font-bold mb-6 text-[#183A6C]">Đổi mật khẩu</h2>

            <?php
            // Chỉ hiển thị thông báo liên quan đến mật khẩu
            if (isset($messages) && !empty($messages)) {
                // Lọc thông báo chứa từ "mật khẩu"
                $passwordMessages = array_filter($messages, function ($msg) {
                    return mb_stripos($msg, 'mật khẩu') !== false;
                });
                if (!empty($passwordMessages)): ?>
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        <?php foreach ($passwordMessages as $message): ?>
                            <p><?= htmlspecialchars($message) ?></p>
                        <?php endforeach; ?>
                    </div>
            <?php endif;
            }
            ?>

            <?php
            if (isset($errors) && !empty($errors)) {
                // Lọc lỗi chứa từ "mật khẩu"
                $passwordErrors = array_filter($errors, function ($err) {
                    return mb_stripos($err, 'mật khẩu') !== false;
                });
                if (!empty($passwordErrors)): ?>
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <?php foreach ($passwordErrors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
            <?php endif;
            }
            ?>

            <form method="POST" action="/change_password" class="max-w-md">
                <div class="mb-4">
                    <label for="current_password" class="block font-semibold mb-2">Mật khẩu hiện tại: <span class="text-red-500">*</span></label>
                    <input type="password" id="current_password" name="current_password"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:border-[#183A6C] focus:outline-none"
                        placeholder="Nhập mật khẩu hiện tại" required />
                </div>

                <div class="mb-4">
                    <label for="new_password" class="block font-semibold mb-2">Mật khẩu mới: <span class="text-red-500">*</span></label>
                    <input type="password" id="new_password" name="new_password"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:border-[#183A6C] focus:outline-none"
                        placeholder="Nhập mật khẩu mới (ít nhất 6 ký tự)" required />
                </div>

                <div class="mb-6">
                    <label for="confirm_password" class="block font-semibold mb-2">Xác nhận mật khẩu mới: <span class="text-red-500">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:border-[#183A6C] focus:outline-none"
                        placeholder="Nhập lại mật khẩu mới" required />
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-[#183A6C] text-white px-6 py-2 rounded font-semibold hover:bg-blue-900 transition">
                        Đổi mật khẩu
                    </button>
                    <a href="/account" class="bg-gray-500 text-white px-6 py-2 rounded font-semibold hover:bg-gray-600 transition">
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // Add client-side validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = this.value;

        if (newPassword !== confirmPassword) {
            this.setCustomValidity('Mật khẩu xác nhận không khớp');
        } else {
            this.setCustomValidity('');
        }
    });

    document.getElementById('new_password').addEventListener('input', function() {
        const confirmPassword = document.getElementById('confirm_password');
        if (this.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Mật khẩu xác nhận không khớp');
        } else {
            confirmPassword.setCustomValidity('');
        }
    });
</script>

<?php $this->stop() ?>