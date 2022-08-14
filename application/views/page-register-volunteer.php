<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php include('components/header.php'); ?>

<?php
$fields = [
    ['id' => 'first_name',  'name' => 'create_user_fname_label',            'type' => 'text',     'placeholder' => 'Enter Your First Name', 'required' => true],
    ['id' => 'last_name',   'name' => 'create_user_lname_label',            'type' => 'text',     'placeholder' => 'Enter Your Last Name', 'required' => true],

    ['id' => 'username',    'name' => 'create_user_username_label',         'type' => 'text',     'placeholder' => 'Enter Your Username', 'required' => true],
    ['id' => 'email',       'name' => 'create_user_email_label',            'type' => 'email',    'placeholder' => 'Enter Your Email', 'required' => true],

    ['id' => 'skills',      'name' => 'create_user_skills_label',           'type' => 'textarea', 'placeholder' => 'Enter Your Skills/Interests', 'required' => false],
    ['id' => 'available',   'name' => 'create_user_available_label',        'type' => 'textarea', 'placeholder' => 'Enter Your Available Times', 'required' => false],

    ['id' => 'address',     'name' => 'create_user_address_label',        'type' => 'text',     'placeholder' => 'Enter Your Address', 'required' => true],
    ['id' => 'home',        'name' => 'create_user_h_phone_label',          'type' => 'tel',      'placeholder' => 'Enter Your Home Phone (Numbers Only)', 'required' => false],

    ['id' => 'work',        'name' => 'create_user_w_phone_label',          'type' => 'tel',      'placeholder' => 'Enter Your Work Phone (Numbers Only)', 'required' => false],
    ['id' => 'cell',        'name' => 'create_user_c_phone_label',          'type' => 'tel',      'placeholder' => 'Enter Your Cell Phone (Numbers Only)', 'required' => false],

    ['id' => 'background',  'name' => 'create_user_background_label',       'type' => 'textarea', 'placeholder' => 'Enter Your Educational Background', 'required' => false],
    ['id' => 'licenses',    'name' => 'create_user_license_label',          'type' => 'textarea', 'placeholder' => 'Enter Your Current License', 'required' => false],

    ['id' => 'e_name',      'name' => 'create_user_e_name_label',           'type' => 'text',     'placeholder' => 'Enter Your Emergency Name', 'required' => false],
    ['id' => 'e_phone',     'name' => 'create_user_e_phone_label',          'type' => 'tel',      'placeholder' => 'Enter Your Emergency Phone', 'required' => false],

    ['id' => 'e_email',     'name' => 'create_user_e_email_label',          'type' => 'email',    'placeholder' => 'Enter Your Emergency Email', 'required' => false],
    ['id' => 'e_address',   'name' => 'create_user_e_address_label',        'type' => 'text',     'placeholder' => 'Enter Your Emergency Address', 'required' => false],

    ['id' => 'drivers',     'name' => 'create_user_drivers_label',          'type' => 'upload',   'required' => false],
    ['id' => 'social',      'name' => 'create_user_social_label',           'type' => 'upload',   'required' => false],

    ['id' => 'pass',        'name' => 'create_user_password_label',         'type' => 'password', 'placeholder' => 'Enter Your Password', 'required' => true],
    ['id' => 'pass_confirm','name' => 'create_user_password_confirm_label', 'type' => 'password', 'placeholder' => 'Re-enter Your Password', 'required' => true],
];
$labelAttr = [
    'class' => 'form-label',
];
$fieldClass = 'form-control text-bg-dark';
$fieldCount = 0;
?>

    <body>
<div class="container">
    <div id="RegistrationPage" class="card text-bg-dark my-4">
        <div class="card-header">
            <h1 class="text-center">Register a Volunteer</h1>
        </div>
        <div class="card-body">
            <div class="card-title">
                <span class="text-danger"><?php echo $this->session->flashdata('message'); ?><?php echo validation_errors(); ?></span>
            </div>


            <?= form_open('/auth/UserRegistration'); ?>
            <?php foreach ($fields as $field) :?>
                <?php if (($fieldCount % 2) === 0) : ?>
                    <div class="row my-2">
                <?php endif; ?>
                <div class="col-6 px-2">
                    <?php $attributes = [ 'id' => $field['id'], 'class' => $fieldClass]; ?>
                    <?php if($field['required']) $attributes['required'] = ''; ?>
                    <?php if(!empty($field['placeholder'])) $attributes['placeholder'] = $field['placeholder']; ?>

                    <?= lang($field['name'], $field['id'], $labelAttr); ?>
                    <?php if ($field['type'] === 'textarea') : ?>
                        <?= form_textarea($field['id'],($this->input->post($field['id'])) ?? '', $attributes); ?>
                    <?php elseif ($field['type'] === 'upload') : ?>
                        <?= form_upload($field['id'], ($this->input->post($field['id'])) ?? '', $attributes); ?>
                    <?php else : ?>
                        <?= form_input($field['id'],($this->input->post($field['id'])) ?? '', $field['type'], $attributes); ?>
                    <?php endif; ?>
                </div>
                <?php if (($fieldCount % 2) === 1) : ?>
                    </div>
                <?php endif; ?>
                <?php $fieldCount++; ?>
            <?php endforeach; ?>
            <?php if (($fieldCount % 2) === 1) echo "</div>"; #Fix Uneven Items ?>
            <hr>
            <!-- Buttons -->
            <div class="w-100 mt-2 d-flex justify-content-evenly">
                <a class="btn btn-outline-primary w-25" href="<?=base_url('/login')?>">Login</a>
                <button class="btn btn-success w-25" type="submit" id="SubmitRegisterUserForm">Register</button>
            </div>
            <?=form_close();?>
        </div>
    </div>
    <div class="my-1"></div>
</div> <!-- End Page Container -->

<?php include('components/footer.php'); ?>