<?php

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
  <link href="./output.css" rel="stylesheet">
  <title><?php
          if (defined('TITLE')) {
            echo TITLE;
          } else {
            echo 'Busticket';
          }
          ?></title>
  <link rel="icon" type="image/png" href="../asset/logo.png">
  <link rel="stylesheet" href="./animation.css">
  <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>


<header class="bg-[#153D77] relative z-50 ">

  <div class="h-18 w-full flex justify-between items-center px-4 relative">
    <img src="./access/img/Header_img/logo.png" class="size-12 md:size-14 rounded-sm" alt="">
    <span class="w-10 h-3 md:bg-white"></span>
    <span class="w-10 h-3 md:bg-white"></span>
    <span class="w-10 h-3 md:bg-white"></span>
    <span class="w-10 h-3 md:bg-white"></span>
    <span class="w-10 h-3 md:bg-white"></span>
    <span class="w-10 h-3 md:bg-white"></span>
    <button onclick="toggleMenu()" class="md:hidden">
      <img src="./access/icon/menu.svg" class="size-6" alt="menu icon">
    </button>
    <img src="./access/img/Header_img/flower.png" class="hidden md:block size-12 absolute bottom-0 right-5" alt="">
    <img src="./access/img/Header_img/yellowCar.png"
      class="hidden md:block size-12 absolute bottom-2 right-5 yellowCar" alt="">
    <img src="./access/img/Header_img/whiteCar.png"
      class="hidden md:block size-12 absolute bottom-2 -left-[40px] whiteCar" alt="">

  </div>


  <nav id="menu"
    class="hidden md:flex flex-col md:flex-row md:items-center bg-[#153D77] md:bg-white md:justify-center w-full absolute md:static top-full left-0 md:h-20 transition-all duration-300 pb-5">
    <div class="md:hidden pt-4 pb-2 text-center">
      <img src="./access/img/Header_img/logo.png" class="size-14 mx-auto rounded-sm" alt="">
    </div>

    <ul class="flex flex-col md:flex-row md:space-x-6 text-white md:text-[#153D77] font-medium w-full md:w-auto">
      <li class="flex items-center justify-center py-2 md:py-0 hover:underline">
        <a href="/" class="flex justify-center md:justify-start items-center gap-2 px-4 ">
          <img src="./access/icon/home.svg" class="size-5  md:inline" alt=""> TRANG CHỦ
        </a>
      </li>
      <li class="flex items-center justify-center py-2 md:py-0 hover:underline">
        <a href="/routes" class="flex justify-center md:justify-start items-center gap-2 px-4">
          <img src="./access/icon/schedule.svg" class="size-5  md:inline" alt=""> TUYẾN
        </a>
      </li>
      <li class="flex items-center justify-center py-2 md:py-0 hover:underline">
        <a href="/ticket_lookup" class="flex justify-center md:justify-start items-center gap-2 px-4">
          <img src="./access/icon/checkticket.svg" class="size-5  md:inline" alt=""> TRA CỨU VÉ
        </a>
      </li>
      <li class="flex items-center justify-center py-2 md:py-0 hover:underline">
        <a href="/invoice_lookup" class="flex justify-center md:justify-start items-center gap-2 px-4 ">
          <img src="./access/icon/bill.svg" class="size-5  md:inline" alt=""> HOÁ ĐƠN
        </a>
      </li>

      <!-- Check if user is logged in - simplified check -->
      <?php $is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']); ?>

      <!-- Account menu item - conditional behavior based on login status -->
      <?php if ($is_logged_in): ?>
        <li class="flex items-center justify-center py-2 md:py-0 hover:underline">
          <a href="/account" class="flex justify-center md:justify-start items-center gap-2 px-4">
            <img src="./access/icon/account.svg" class="size-5  md:inline" alt=""> TÀI KHOẢN
          </a>
        </li>
      <?php else: ?>
        <li class="flex items-center justify-center py-2 md:py-0 hover:underline">
          <a href="#" onclick="showLoginModal(); return false;" class="flex justify-center md:justify-start items-center gap-2 px-4">
            <img src="./access/icon/account.svg" class="size-5  md:inline" alt=""> TÀI KHOẢN
          </a>
        </li>
      <?php endif; ?>

      <?php if ($is_logged_in): ?>
        <!-- Avatar + Account Menu for logged in users -->
        <li class="text-center py-2 md:py-0 flex justify-center md:justify-start items-center gap-2 relative">
          <div class="relative ml-0">
            <?php
            $userId = $_SESSION['user_id'] ?? 'default';
            $avatarWebPath = "/access/img/avatar/" . $userId . ".jpg";
            $defaultWebPath = "/access/img/avatar/avatar_default.png";
            $avatarFilePath = $_SERVER['DOCUMENT_ROOT'] . "/public" . $avatarWebPath;
            $displayPath = (is_numeric($userId) && file_exists($avatarFilePath)) ? $avatarWebPath : $defaultWebPath;
            ?>
            <button id="avatarBtn" class="focus:outline-none flex items-center">
              <img src="<?= $displayPath ?>" class="w-10 h-10 rounded-full border-2 border-white" alt="avatar">
              <svg class="w-4 h-4 ml-1 text-white md:text-[#153D77]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>
            <div id="accountMenu" class="hidden absolute right-0 mt-2 w-40 bg-white rounded shadow-lg z-50">
              <a href="/account" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Tài khoản</a>
              <a href="#" onclick="confirmLogout()" class="block px-2 py-1 rounded hover:bg-gray-100 text-gray-700">Đăng xuất</a>
            </div>
          </div>
        </li>
      <?php else: ?>
        <!-- Login/Register links replacing avatar for non-logged in users -->
        <li class="text-center py-2 md:py-0 flex justify-center md:justify-start items-center gap-2 px-4">
          <a href="/login" class="text-white md:text-[#153D77] hover:underline">Đăng nhập</a>
          <span class="text-white md:text-[#153D77]">|</span>
          <a href="/register" class="text-white md:text-[#153D77] hover:underline">Đăng ký</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
  <hr>
</header>

<script>
  function toggleMenu() {
    const menu = document.getElementById('menu');
    menu.classList.toggle('hidden');
  }

  function showLoginModal() {
    document.getElementById('loginModal').classList.remove('hidden');
  }

  function closeLoginModal() {
    document.getElementById('loginModal').classList.add('hidden');
  }

  // Avatar/account menu toggle
  document.addEventListener('DOMContentLoaded', function() {
    const avatarBtn = document.getElementById('avatarBtn');
    const accountMenu = document.getElementById('accountMenu');
    if (avatarBtn && accountMenu) {
      avatarBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        accountMenu.classList.toggle('hidden');
      });
      document.addEventListener('click', function() {
        accountMenu.classList.add('hidden');
      });
      accountMenu.addEventListener('click', function(e) {
        e.stopPropagation();
      });
    }

    // Close modal when clicking outside
    document.getElementById('loginModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeLoginModal();
      }
    });
  });
</script>

<?= $this->section("page") ?>


<!-- END CHANGEABLE CONTENT. -->
<footer class="bg-[#153D77] w-full">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
    <div class="flex  md:border-none py-4">
      <div class="img flex-1 flex justify-center align-center my-auto  ">
        <img src="./access/img/Header_img/logo.png" class="size-16  border-b " alt="">
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
        <p class="flex-1">
          <a class="block" href="">Về chúng tôi</a>
          <span class="block text-xs text-gray-200">Email: info@busticket.vn</span>
        </p>
        <p class="flex-1">
          <a class="block" href="">Liên hệ</a>
          <span class="block text-xs text-gray-200">SĐT: 1900 0090</span>
        </p>
      </div>
    </div>

    <div class="flex flex-col  md:border-none text-center text-white py-4">
      <h2 class="pt-2">THEO DÕI CHÚNG TÔI</h2>
      <div class="flex align-center justify-around mx-20 mt-3 md:mt-8">
        <a href=""><img alt="Facebook" class="size-6" src="./access/img/Footer_img/facebook.webp" alt=""></a>
        <a href=""><img alt="Instagram" class="size-6" src="./access/img/Footer_img/instagram.webp" alt=""></a>
        <a href=""><img alt="Thread" class="size-6" src="./access/img/Footer_img/thread.webp" alt=""></a>
      </div>
    </div>
  </div>
  <!-- Nút để mở modal -->

</footer>
<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
<script>
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

<!-- Login Modal - Positioned at the end of body -->
<div id="loginModal" class="hidden fixed top-0 left-0 w-full h-full flex items-center justify-center" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 999999;">
  <div class="absolute inset-0 bg-black bg-opacity-50"></div>
  <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4 relative shadow-2xl border border-[#153D77]" style="z-index: 1000000;">
    <div class="text-center">
      <h3 class="text-xl font-bold text-[#153D77] mb-4">Thông báo</h3>
      <p class="text-gray-700 mb-6">Bạn cần đăng nhập để sử dụng tính năng này</p>
      <div class="flex gap-4 justify-center">
        <a href="/login" class="bg-[#153D77] text-white px-6 py-2 rounded font-semibold hover:bg-blue-900 transition duration-200">Đăng nhập</a>
        <a href="/register" class="bg-green-600 text-white px-6 py-2 rounded font-semibold hover:bg-green-700 transition duration-200">Đăng ký</a>
      </div>
      <button onclick="closeLoginModal()" class="mt-4 text-gray-500 hover:text-gray-700 transition duration-200">Đóng</button>
    </div>
  </div>
</div>

<script>
  // Modal functions to ensure they work
  function showLoginModal() {
    const modal = document.getElementById('loginModal');
    modal.classList.remove('hidden');
  }

  function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    modal.classList.add('hidden');
  }
</script>

<?= $this->section('scripts') ?>
</body>

</html>