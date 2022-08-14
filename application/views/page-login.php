<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php include('components/header.php'); ?>

<body class="d-flex h-100 align-items-center">
    <div class="container text-center login-container">
        <h1 class="mb-2">Login</h1>

        <form id="LoginUserForm" action="/auth/UserLogin" method="POST">
        <?php if ($_POST) : ?>
            <span style="color:red;"><?php echo $this->session->flashdata('message'); ?><?php echo validation_errors(); ?></span>
        <?php endif; ?>

            <div class="form-floating">
                <input class="form-control bg-dark text-white text-center" id="user" name="user" type="email" value="" placeholder="User Login" required />
                <label for="user">Email:</label>
            </div>

            <div class="form-floating">
                <input class="form-control bg-dark text-white text-center" id="pass" name="pass" type="password" value="" placeholder="User Password" required />
                <label for="pass">Password:</label>
            </div>

            <button type="submit" id="SubmitLoginUserForm" class="btn btn-primary text-center w-100 mt-2">Login</button>
        </form>

    </div> <!-- End Page Container -->

<?php include('components/footer.php'); ?>