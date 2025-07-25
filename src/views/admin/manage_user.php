<?php $this->layout("layouts/layout_admin", ["title" => APPNAME]) ?>
<?php $this->start("page") ?>
<main class="flex-1 bg-white">
    <header class="border-b border-gray-200 py-4 px-8">
        <h1 class="text-xl font-bold text-[#183A6C] text-center">
            Quản lý người dùng Busticket
        </h1>
    </header>

    <!-- THÔNG BÁO TRẠNG THÁI CỦA HÀNH ĐỘNG-->
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
        <div>
            <div>
                <img src="./access/img/multi/backgroundslider.avif" class="w-full h-48 object-cover rounded" alt="banner" />
                <form method="get" class="top-8 -translate-y-[16px] bg-white bg-opacity-90 rounded-lg shadow p-4" style="backdrop-filter: blur(2px);">
                    <div class="flex items-end justify-center p-4 gap-4 ">
                        <div class="flex flex-col">
                            <label class="font-semibold text-[#183A6C] mb-1">Email</label>
                            <input type="text" name="email" class="border border-gray-300 rounded px-3 py-1" placeholder="Nhập email user cần tìm"
                                value="<?= $this->e($filters['email'] ?? '') ?>">
                        </div>
                        <div class="flex flex-col">
                            <label class="font-semibold text-[#183A6C] mb-1">Số điện thoại</label>
                            <input type="text" name="phone_number" class="border border-gray-300 rounded px-3 py-1" placeholder="Nhập sdt user cần tìm"
                                value="<?= $this->e($filters['phone_number'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="flex justify-center gap-2">
                        <button type="submit"
                            class="bg-[#183A6C] text-white px-6 py-2 rounded font-semibold hover:bg-blue-900 transition">
                            Tìm kiếm
                        </button>
                        <a href="/manage_user"
                            class="bg-black text-white px-6 py-2 rounded font-semibold hover:bg-gray-600 transition">
                            Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>
            <h1 class="text-xl font-bold text-[#183A6C] text-center p-4">
                Danh sách Users
            </h1>
            <div class="overflow-x-auto bg-white rounded-xl shadow ring-1 ring-gray-200">
                <table class="min-w-full text-sm text-gray-800">

                    <thead class="bg-[#153D77] text-white uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4 text-left">ID</th>
                            <th class="px-6 py-4 text-left">Email</th>
                            <th class="px-6 py-4 text-left">Họ tên</th>
                            <th class="px-6 py-4 text-left">SĐT</th>
                            <th class="px-6 py-4 text-left">Quyền</th>
                            <th class="px-6 py-4 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <?php if (!empty($users)): ?>
                        <!-- Body -->
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50 border-b border-gray-200 last:border-0">
                                    <td class="px-6 py-5 whitespace-nowrap font-semibold">
                                        <?= $this->e($user['user_id']) ?>
                                    </td>

                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <?= $this->e($user['email']) ?>
                                    </td>


                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <?= $this->e($user['full_name']) ?>
                                    </td>


                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <?= $this->e($user['phone_number']) ?>
                                    </td>


                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-block rounded-full px-3 py-1 text-xs font-semibold
                                        <?= $user['role'] === 'admin'
                                            ? 'bg-red-100 text-red-700'
                                            : 'bg-green-100 text-green-700' ?>">
                                            <?= $this->e($user['role']) ?>
                                        </span>
                                    </td>


                                    <td class="px-6 py-5 whitespace-nowrap text-center space-x-3 mx-auto">
                                        <a onclick="confirmChangeRole(<?= $this->e($user['user_id']) ?> )" href='#'
                                            class="inline-flex items-center justify-center bg-blue-600/10 text-blue-600 hover:bg-blue-600/20 p-3 rounded-full transition">
                                            Phân quyền admin
                                            <img class="w-4 h-4 inline-block" src="./access/icon/editUser.svg" alt="Edit">
                                        </a>
                                        <a onclick="confirmDelete(<?= $this->e($user['user_id']) ?>)" href="#"
                                            class="inline-flex items-center justify-center bg-red-600/10 text-red-600 hover:bg-red-600/20 p-3 rounded-full transition">
                                            <span>Xoá</span>
                                            <img class="w-4 h-4 inline-block" src="./access/icon/deleteUser.svg" alt="Delete">
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <?php if (!empty($filters['email']) || !empty($filters['phone_number'])): ?>
                                        Không tìm thấy người dùng nào với từ khóa tìm kiếm.
                                    <?php else: ?>
                                        Không có người dùng nào trong hệ thống.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                </table>
            </div>
        </div>
    </section>
</main>
<?php $this->stop() ?>

<?php $this->start('scripts') ?>
<script>
    function confirmDelete(userId) {
        console.log('Delete function called with userId:', userId); // Debug log

        Swal.fire({
            title: 'Bạn có chắc muốn xóa người dùng này?',
            text: "Hành động này không thể hoàn tác.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Redirecting to:', `/manage_user/delete/${userId}`); // Debug log
                window.location.href = `/manage_user/delete/${userId}`;
            }
        });
    }

    function confirmChangeRole(userId) {
        console.log('Update role function called with userId:', userId);

        Swal.fire({
            title: 'Bạn có chắc phân quyền admin cho người dùng này không?',
            text: "Hành động này không thể hoàn tác.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tạo form ẩn và submit POST request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/manage_user/update_role/${userId}`;

                // Thêm hidden input cho role
                const roleInput = document.createElement('input');
                roleInput.type = 'hidden';
                roleInput.name = 'role';
                roleInput.value = 'admin';

                form.appendChild(roleInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>


<?php $this->stop() ?>