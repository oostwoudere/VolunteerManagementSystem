<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php include('header.php'); ?>

    <div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-xl-10">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Git Updater</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><strong><a href="/admin/dashboard/update">Updater</a></strong></li>
                                <?php if ($this->ion_auth->logged_in()) : ?>
                                    <li class="breadcrumb-item">
                                        <a onmouseenter="this.innerText = 'Click to Logout'" onmouseleave="this.innerText = 'Logged In'" href="/logout">Logged In</a>
                                    </li>
                                    <?php $user_groups = $this->ion_auth->get_users_groups($this->ion_auth->get_user_id())->result(); ?>
                                    <li class="breadcrumb-item active"><?php echo ($user_groups[0]->description) ? $user_groups[0]->description : 'Unassigned'; ?></li>
                                <?php endif; ?>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-xl-10">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Update Output</h4>
                            <pre style="font-size:16px;"><?php echo $result; ?></pre>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->
        </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

<?php include('footer.php'); ?>