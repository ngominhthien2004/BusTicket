<?php $this->layout("layouts/default", ["title" => APPNAME]) ?>

<?php $this->start("page") ?>
<div class="container">
    <!-- SECTION HEADING -->
    <h2 class="text-center animate__animated animate__bounce">Contacts</h2>
    <div class="row">
        <div class="col-md-6 offset-md-3 text-center">
            <p class="animate__animated animate__fadeInLeft">Add your contacts here.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">

            <form action="/routes" method="POST" class="col-md-6 offset-md-3">

                <!-- Start point -->
                <div class="mb-3">
                    <label for="start_point" class=" flex-1 border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">Điểm đi</label>
                    <input type="text" name="start_point" class="form-control<?= isset($errors['start_point']) ? ' is-invalid' : '' ?>" maxlen="255" id="start_point" placeholder="Nhập điểm đi" value="<?= isset($old['start_point']) ? $this->e($old['start_point']) : '' ?>" />

                    <?php if (isset($errors['start_point'])) : ?>
                        <span class="invalid-feedback">
                            <strong><?= $this->e($errors['start_point']) ?></strong>
                        </span>
                    <?php endif ?>
                </div>

                <!-- End point -->
                <div class="mb-3">
                    <label for="end_point" class="form-label">Điểm đến</label>
                    <input type="text" name="end_point" class="form-control<?= isset($errors['end_point']) ? ' is-invalid' : '' ?>" maxlen="255" id="end_point" placeholder="Nhập điểm đến" value="<?= isset($old['end_point']) ? $this->e($old['end_point']) : '' ?>" />

                    <?php if (isset($errors['end_point'])) : ?>
                        <span class="invalid-feedback">
                            <strong><?= $this->e($errors['end_point']) ?></strong>
                        </span>
                    <?php endif ?>
                </div>

                <!-- Khoảng cách -->
                <div class="mb-3">
                    <label for="notes" class="form-label">Khoảng cách </label>
                    <input type="text" name="distance_km" class="form-control<?= isset($errors['distance_km']) ? ' is-invalid' : '' ?>" maxlen="255" id="distance_km" placeholder="Enter distance_km" value="<?= isset($old['distance_km']) ? $this->e($old['distance_km']) : '' ?>" />

                    <?php if (isset($errors['distance_km'])) : ?>
                        <span class="invalid-feedback">
                            <strong><?= $this->e($errors['distance_km']) ?></strong>
                        </span>
                    <?php endif ?>
                </div>

                <!-- Submit -->
                <button type="submit" name="submit" id="submit" class="btn btn-primary">Thêm tuyến</button>
            </form>

        </div>
    </div>
</div>
<?php $this->stop() ?>