<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $user_role = empty($user_role) ? 0 : $user_role; ?>

<body style="height: 100%">
<!-- body content -->
<div class="text-bg-secondary text-center h-100 w-100 row">
    <div class="col-2 pe-0">
        <?php include ('menu-sidebar.php') ?>
    </div>
    <div class="col-10">
        <!-- Testing Area -->
        <div class="card text-bg-dark text-light m-2">
            <div class="card-header">
                <h1> Dashboard </h1>
            </div>
            <div class="card-body">
                <h5 class="card-title"> Testing Area  </h5>

                <!-- Form Data -->
                <div class="row g-3">
                    <div class="col-4">
                        <select name="entity" class="form-control"></select>
                    </div>
                    <div class="col-4">
                        <select name="action" class="form-control"></select>
                    </div>
                    <div class="col-4">
                        <select name="user" class="form-control">
                            <option value="3" selected>Developer</option>
                            <option value="4">Admin</option>
                            <option value="5">General</option>
                        </select>
                    </div>
                </div>

                <p class="card-text" id="content">Click Below to Run</p>

                <!-- Form Submit -->
                <button type="button" onclick="PSA.Test.checkPermission();" class="btn btn-outline-info">Check Permission</button>
            </div>
            <div class="card-footer text-muted" id="output">
                Data Output Here
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        PSA.Test.init();
    })
</script>