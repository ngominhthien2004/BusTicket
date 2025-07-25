<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng nhập</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-cover bg-center min-h-screen flex items-center justify-center"
    style="background-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80');">
    <div class="bg-black bg-opacity-40 w-full min-h-screen absolute top-0 left-0 z-0"></div>
    <div class="relative z-10 w-full max-w-xl mx-auto">
        <div class="bg-white bg-opacity-80 rounded-xl p-8 mx-2 shadow-lg flex flex-col items-center">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-8">ĐĂNG NHẬP</h2>

            <?php if (isset($messages) && !empty($messages)): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded w-full max-w-md">
                    <?php foreach ($messages as $message): ?>
                        <p><?= htmlspecialchars($message) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded w-full max-w-md">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="post" class="w-full max-w-md mx-auto">
                <div class="mb-6">
                    <label for="email" class="block text-lg font-semibold text-gray-700 mb-2">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Email"
                        class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 focus:border-blue-400 focus:ring-0 text-gray-800 text-lg"
                        value="<?= isset($old['email']) ? $this->e($old['email']) : '' ?>"
                        required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-lg font-semibold text-gray-700 mb-2">Mật khẩu:</label>
                    <div class="flex items-center">
                        <input type="password" id="password" name="password" placeholder="Mật khẩu"
                            class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 focus:border-blue-400 focus:ring-0 text-gray-800 text-lg"
                            required>
                        <img id="togglePassword" src="./access/icon/eyeclose.svg" class="size-5 mb-0  md:inline md:mb-4" alt="">
                    </div>
                </div>
                <div class="flex items-center justify-between mb-6">
                    <span class="text-gray-700 text-base">Đăng nhập với</span>
                    <div class="flex space-x-4 ml-2">
                        <a href="/facebook-login.php" class="text-blue-700 hover:text-blue-900">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.675 0h-21.35C.595 0 0 .592 0 1.326v21.348C0 23.408.595 24 1.325 24h11.495v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.797.143v3.24l-1.918.001c-1.504 0-1.797.715-1.797 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116C23.406 24 24 23.408 24 22.674V1.326C24 .592 23.406 0 22.675 0"></path>
                            </svg>
                        </a>
                        <a href="/google-login.php" class="text-gray-700 hover:text-gray-900">
                            <svg class="w-7 h-7" viewBox="0 0 48 48">
                                <g>
                                    <path d="M44.5 20H24v8.5h11.7C34.6 33.9 29.8 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 6 .9 8.3 2.7l6.2-6.2C34.5 4.5 29.5 2.5 24 2.5 12.7 2.5 3.5 11.7 3.5 23S12.7 43.5 24 43.5c11.3 0 20.5-9.2 20.5-20.5 0-1.4-.1-2.7-.3-4z" fill="#fbbc05" />
                                    <path d="M6.3 14.7l6.8 5c1.8-3.5 5.3-6 9.4-6 2.6 0 5 .9 6.8 2.4l6.4-6.4C32.5 6.5 28.5 5 24 5c-6.1 0-11.3 3.4-14 8.4z" fill="#ea4335" />
                                    <path d="M24 44c4.3 0 8.3-1.4 11.4-3.8l-6.6-5.4c-1.9 1.3-4.3 2-6.8 2-4.1 0-7.6-2.5-9.4-6l-6.7 5.2C12.7 41.6 18 44 24 44z" fill="#34a853" />
                                    <path d="M44.5 20H24v8.5h11.7c-1.1 3.1-3.6 5.7-6.7 7.3l6.6 5.4C41.6 38.2 44.5 31.9 44.5 24c0-1.4-.1-2.7-.3-4z" fill="#4285f4" />
                                </g>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-6">
                    <a href="#" class="text-blue-500 hover:underline text-base">Quên mật khẩu ?</a>
                    <button type="submit"
                        class="bg-blue-700 text-white px-8 py-2 rounded hover:bg-blue-800 transition text-lg font-semibold">ĐĂNG NHẬP</button>
                </div>
            </form>
            <p class="mt-4 text-center text-gray-700">
                Chưa có tài khoản?
                <a href="/register" class="text-blue-500 hover:underline">Đăng ký</a>
            </p>
        </div>
    </div>
</body>
<script src="./tonglePassword.js"></script>

</html>