<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng kí</title>
    <link href="../asset/logo.png" rel="shortcut icon" type="images/vnd.microsoft.icon">
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-cover bg-center min-h-screen flex items-center justify-center"
    style="background-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80');">
    <div class="bg-black bg-opacity-40 w-full min-h-screen absolute top-0 left-0 z-0"></div>
    <div class="relative z-10 w-full max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-white text-center mb-8 mt-4">ĐĂNG KÝ TÀI KHOẢN</h1>
        <form action="/register" method='post'
            class="flex flex-col md:flex-row gap-6 justify-center items-start">
            <!-- Left section -->
            <div class="w-[80%] mx-auto md:w-1/2 flex-1 bg-white bg-opacity-80 rounded p-4 sm:p-8 mb-4 md:mb-0">
                <div>
                    <div class="mb-6">
                        <label for="full_name" class="block text-lg font-semibold text-gray-700 mb-2">Họ và tên</label>
                        <input type="text" id="full_name" name="full_name" placeholder="Họ và tên"
                            class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 focus:border-blue-400 focus:ring-0 text-gray-800 <?= isset($errors['full_name']) ? ' border-red-500 focus:ring-red-500 focus:border-red-500 rounded-md shadow-sm' : '' ?>" value="<?= isset($old['full_name']) ? $this->e($old['full_name']) : '' ?>"
                            required>

                        <?php if (isset($errors['full_name'])) : ?>
                            <span class="text-red-600">
                                <strong><?= $this->e($errors['full_name']) ?></strong>
                            </span>
                        <?php endif ?>

                    </div>
                    <div class="mb-6">
                        <label for="phone_number" class="block text-lg font-semibold text-gray-700 mb-2">SĐT</label>
                        <input type="text" id="phone_number" name="phone_number" placeholder="Số điện thoại"
                            class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 focus:border-blue-400 focus:ring-0 text-gray-800 <?= isset($errors['phone_number']) ? ' border-red-500 focus:ring-red-500 focus:border-red-500 rounded-md shadow-sm' : '' ?>" value="<?= isset($old['phone_number']) ? $this->e($old['phone_number']) : '' ?>"
                            required>

                        <?php if (isset($errors['phone_number'])) : ?>
                            <span class="text-red-600">
                                <strong><?= $this->e($errors['phone_number']) ?></strong>
                            </span>
                        <?php endif ?>

                    </div>
                </div>
            </div>
            <!-- Right section -->
            <div class="w-[80%] mx-auto md:w-1/2 flex-1 bg-white bg-opacity-80 rounded p-4 sm:p-8">
                <div>
                    <div class="mb-4">
                        <input type="email" id="email" name="email" placeholder="Email"
                            class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 mb-6 focus:border-blue-400 focus:ring-0 text-gray-800 <?= isset($errors['email']) ? ' border-red-500 focus:ring-red-500 focus:border-red-500 rounded-md shadow-sm' : '' ?>" value="<?= isset($old['email']) ? $this->e($old['email']) : '' ?>"
                            required>

                        <?php if (isset($errors['email'])) : ?>
                            <span class="text-red-600">
                                <strong><?= $this->e($errors['email']) ?></strong>
                            </span>
                        <?php endif ?>

                    </div>
                    <div class="mb-4 items-center">
                        <div class="flex">
                            <input type="password" id="password" name="password" placeholder="Mật khẩu"
                                class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 mb-6 focus:border-blue-400 focus:ring-0 text-gray-800"
                                required>
                            <img id="togglePassword" src="./access/icon/eyeclose.svg" class="size-5 mb-2  md:inline md:mb-4" alt="">
                        </div>
                        <?php if (isset($errors['password'])) : ?>
                            <span class="text-red-600">
                                <strong><?= $this->e($errors['password']) ?></strong>
                            </span>
                        <?php endif ?>
                    </div>
                    <div class="mb-6 items-center">
                        <div class="flex">
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu"
                                class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 focus:border-blue-400 focus:ring-0 text-gray-800"
                                required>
                            <img id="toggleConfirmPassword" src="./access/icon/eyeclose.svg" class="size-5 mb-2  md:inline md:mb-4" alt="">
                        </div>
                        <?php if (isset($errors['password'])) : ?>
                            <span class="text-red-600">
                                <strong><?= $this->e($errors['password']) ?></strong>
                            </span>
                        <?php endif ?>
                    </div>
                    <button type="submit"
                        class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition">Đăng ký</button>
                </div>
                <div class="my-4 flex items-center">
                    <div class="flex-grow h-px bg-gray-300"></div>
                    <span class="mx-2 text-gray-400 text-sm">hoặc</span>
                    <div class="flex-grow h-px bg-gray-300"></div>
                </div>
                <div class="space-y-3">
                    <a href="/facebook-login.php"
                        class="flex items-center justify-center w-full bg-blue-700 text-white py-2 rounded hover:bg-blue-800 transition">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22.675 0h-21.35C.595 0 0 .592 0 1.326v21.348C0 23.408.595 24 1.325 24h11.495v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.797.143v3.24l-1.918.001c-1.504 0-1.797.715-1.797 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116C23.406 24 24 23.408 24 22.674V1.326C24 .592 23.406 0 22.675 0"></path>
                        </svg>
                        Đăng nhập bằng Facebook
                    </a>
                    <a href="/google-login.php"
                        class="flex items-center justify-center w-full bg-white border border-gray-300 text-gray-700 py-2 rounded hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 mr-2" viewBox="0 0 48 48">
                            <g>
                                <path d="M44.5 20H24v8.5h11.7C34.6 33.9 29.8 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 6 .9 8.3 2.7l6.2-6.2C34.5 4.5 29.5 2.5 24 2.5 12.7 2.5 3.5 11.7 3.5 23S12.7 43.5 24 43.5c11.3 0 20.5-9.2 20.5-20.5 0-1.4-.1-2.7-.3-4z"
                                    fill="#fbbc05" />
                                <path d="M6.3 14.7l6.8 5c1.8-3.5 5.3-6 9.4-6 2.6 0 5 .9 6.8 2.4l6.4-6.4C32.5 6.5 28.5 5 24 5c-6.1 0-11.3 3.4-14 8.4z"
                                    fill="#ea4335" />
                                <path d="M24 44c4.3 0 8.3-1.4 11.4-3.8l-6.6-5.4c-1.9 1.3-4.3 2-6.8 2-4.1 0-7.6-2.5-9.4-6l-6.7 5.2C12.7 41.6 18 44 24 44z"
                                    fill="#34a853" />
                                <path d="M44.5 20H24v8.5h11.7c-1.1 3.1-3.6 5.7-6.7 7.3l6.6 5.4C41.6 38.2 44.5 31.9 44.5 24c0-1.4-.1-2.7-.3-4z"
                                    fill="#4285f4" />
                            </g>
                        </svg>
                        Đăng nhập bằng Google
                    </a>
                </div>
                <p class="mt-4 text-center text-gray-600">Đã có tài khoản? <a href="/login"
                        class="text-blue-500 hover:underline">Đăng nhập</a></p>
            </div>
        </form>
    </div>
</body>
<script src='./tonglePassword.js'></script>

</html>