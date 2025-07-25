<?php
define('TITLE', 'Quản lý Admin');
// Kết nối CSDL
include_once __DIR__ . '/../../partials/header_admin.php';
include_once __DIR__ . '/../../../config/db.php';
?>
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php
    include_once __DIR__ . '/../../partials/sidebar_admin.php'
    ?>

    <!-- Main content -->

    <main class="flex-1 bg-white">
        <header class="border-b border-gray-200 py-4 px-8">
            <h1 class="text-xl font-bold text-[#183A6C] text-center">
                Danh sách xe
            </h1>
        </header>
        <section class="p-8">
            <div>
                <?php
                include_once __DIR__ . '/../../partials/search_trip.php';
                include_once __DIR__ . '/../../partials/filter_trip.php';
                ?>
                <?php include_once __DIR__ . '/../../partials/view_bus.php'; ?>
            </div>
        </section>
    </main>
</div>
<?php include_once __DIR__ . '/../../partials/footer.php'; ?>