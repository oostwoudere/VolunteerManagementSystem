<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php include('components/header.php'); ?>

<?php
$fields = [
    ['id' => 'social',      'name' => 'create_user_social_label',           'type' => 'upload',   'required' => true],
    ['id' => 'drivers',     'name' => 'create_user_drivers_label',          'type' => 'upload',   'required' => true],
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
            <h1 class="text-center">Test Upload</h1>
        </div>
        <div class="card-body">
            <div class="card-title">
                <span class="text-danger"><?php echo $this->session->flashdata('message'); ?><?php echo validation_errors(); ?></span>
            </div>


            <?= form_open('/auth/TestUploads'); ?>
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
                        <?php $attributes['accept'] = '.png,.jpg,.jpeg,.gif,.pdf'; ?>
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
                <button class="btn btn-success w-25" type="submit" id="SubmitRegisterUserForm">Test</button>
            </div>
            <?=form_close();?>
        </div>
    </div>
    <div class="my-1"></div>
</div> <!-- End Page Container -->

<?php include('components/footer.php'); ?>