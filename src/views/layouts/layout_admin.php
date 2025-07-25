<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_administrator($user = 'me')
{
    return (isset($_SESSION['user']) && ($_SESSION['user'] === $user));
}

?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/output.css" rel="stylesheet">
    <title><?php
            if (defined('TITLE')) {
                echo TITLE;
            } else {
                echo 'Busticket';
            }
            ?></title>
    <link rel="icon" type="image/png" href="/asset/logo.png">
    <link rel="stylesheet" href="/animation.css">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <header class="bg-[#153D77] relative z-50">
        <div class="h-18 w-full flex justify-between items-center px-4 relative">
            <img src="/access/img/Header_img/logo.png" class="size-12 md:size-14 rounded-sm" alt="">
            <span class="w-10 h-3 md:bg-white"></span>
            <span class="w-10 h-3 md:bg-white"></span>
            <span class="w-10 h-3 md:bg-white"></span>
            <span class="w-10 h-3 md:bg-white"></span>
            <span class="w-10 h-3 md:bg-white"></span>
            <span class="w-10 h-3 md:bg-white"></span>
            <img src="/access/img/Header_img/flower.png" class="hidden md:block size-12 absolute bottom-0 right-5" alt="">
            <img src="/access/img/Header_img/yellowCar.png"
                class="hidden md:block size-12 absolute bottom-2 right-5 yellowCar" alt="">
            <img src="/access/img/Header_img/whiteCar.png"
                class="hidden md:block size-12 absolute bottom-2 -left-[40px] whiteCar" alt="">
        </div>
        <hr>
    </header>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-56 bg-[#183A6C] text-white flex flex-col">
            <div class="flex items-center gap-2 px-4 py-4 border-b border-gray-200">
                <img src="/access/img/Header_img/logo.png" alt="Logo" class="w-10 h-10 rounded-full border-2 border-white">
                <div>
                    <div class="font-bold text-lg">ADMIN</div>
                    <div class="text-xs text-gray-200">BusTicket</div>
                </div>
            </div>
            <nav class="flex-1 px-2 py-4 space-y-2">
                <!-- Quản lý tuyến -->
                <div class="menu-section">
                    <details class="group">
                        <summary class="flex items-center justify-between p-3 font-semibold cursor-pointer hover:bg-blue-700 rounded">
                            <span>Quản lý tuyến</span>
                            <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </summary>
                        <div class="ml-4 mt-2 space-y-1">
                            <a href="/manage_allRoute" class="block p-2 text-sm hover:bg-blue-700 rounded">▷ Danh sách tuyến</a>
                            <a href="/manage_addRoute" class="block p-2 text-sm hover:bg-blue-700 rounded">▷ Thêm tuyến xe</a>
                        </div>
                    </details>
                </div>
                <!-- Quản lý lịch trình -->
                <div class="menu-section">
                    <details class="group">
                        <summary class="flex items-center justify-between p-3 font-semibold cursor-pointer hover:bg-blue-700 rounded">
                            <span>Quản lý lịch trình</span>
                            <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </summary>
                        <div class="ml-4 mt-2 space-y-1">
                            <a href="/manage_allSchedules" class="block p-2 text-sm hover:bg-blue-700 rounded">▷ Danh sách lịch trình</a>
                        </div>
                    </details>
                </div>
                <!-- Quản lý thanh toán -->
                <div class="menu-section">
                    <a href="/manageBooking" class="flex items-center p-3 font-semibold hover:bg-blue-700 rounded">
                        <span>Quản lý thanh toán</span>
                    </a>
                </div>
                <!-- Quản lý xe -->
                <div class="menu-section">
                    <details class="group">
                        <summary class="flex items-center justify-between p-3 font-semibold cursor-pointer hover:bg-blue-700 rounded">
                            <span>Quản lý xe</span>
                            <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </summary>
                        <div class="ml-4 mt-2 space-y-1">
                            <a href="/manage_allBus" class="block p-2 text-sm hover:bg-blue-700 rounded">▷ Danh sách xe</a>
                            <a href="/manage_addBus" class="block p-2 text-sm hover:bg-blue-700 rounded">▷ Thêm xe</a>
                        </div>
                    </details>
                </div>

                <!-- Quản lý người dùng -->
                <div class="menu-section">
                    <details class="group">
                        <summary class="flex items-center justify-between p-3 font-semibold cursor-pointer hover:bg-blue-700 rounded">
                            <span>Quản lý người dùng</span>
                            <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </summary>
                        <div class="ml-4 mt-2 space-y-1">
                            <a href="/manage_admin" class="block p-2 text-sm hover:bg-blue-700 rounded">▷ Admin</a>
                            <a href="/manage_user" class="block p-2 text-sm hover:bg-blue-700 rounded">▷ Users</a>
                        </div>
                    </details>
                </div>


                <div class="menu-section p-3">
                    <a href="#" onclick="confirmLogout()" class="block px-2 py-1 rounded hover:bg-red-700 text-white">Đăng xuất</a>
                </div>
            </nav>
        </aside>
        <?= $this->section("page") ?>
    </div>

    <!-- Footer -->
    <footer class="bg-[#153D77] w-full">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
            <div class="flex  md:border-none py-4">
                <div class="img flex-1 flex justify-center align-center my-auto  ">
                    <img src="/access/img/Header_img/logo.png" class="size-16  border-b " alt="">
                </div>
                <div class="content flex-1 text-white text-xs mx-auto my-auto">
                    <p>
                        <strong>Địa chỉ:</strong> 512, Ninh Kiều, Cần Thơ
                    </p>
                    <p>
                        Hotline: 1900 0090
                    </p>
                </div>
            </div>


            <div class="flex flex-col border-y-2 border-white md:border-x-2 md:border-y-0 text-center text-white py-4">
                <h2 class="pt-2">THÔNG TIN LIÊN HỆ</h2>
                <div class="flex align-center justify-center text-s py-4 ">
                    <p class="flex-1"><a class="block" href="">Về chúng tôi</a></p>
                    <p class="flex-1"><a class="block" href="">Liên hệ</a></p>
                </div>
            </div>

            <div class="flex flex-col  md:border-none text-center text-white py-4">
                <h2 class="pt-2">THEO DÕI CHÚNG TÔI</h2>
                <div class="flex align-center justify-around mx-20 mt-3 md:mt-8">
                    <a href=""><img alt="Facebook" class="size-6" src="/access/img/Footer_img/facebook.webp" alt=""></a>
                    <a href=""><img alt="Instagram" class="size-6" src="/access/img/Footer_img/instagram.webp" alt=""></a>
                    <a href=""><img alt="Thread" class="size-6" src="/access/img/Footer_img/thread.webp" alt=""></a>
                </div>
            </div>


        </div>
    </footer>

    <?= $this->section('scripts') ?>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script>
        let status = document.getElementById('status');
        setTimeout(() => {
            status.remove();
        }, 5000)

        function confirmLogout() {
            Swal.fire({
                title: 'Bạn có chắc muốn đăng xuất?',
                text: "Bạn sẽ phải đăng nhập lại sau khi đăng xuất.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/logout';
                }
            });
        }
    </script>

</body>


</html>