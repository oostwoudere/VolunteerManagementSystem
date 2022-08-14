<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $user_role = empty($user_role) ? 0 : $user_role; ?>
<?php $getPost = empty($method) ? 0 : (strcmp(strtolower($method), 'post') == 0); ?>
<?php $postData = empty($postData) ? 0 : $postData; ?>
<?php $centers = $centers ?? false; ?>

<?php # Heading Builder ?>
<?php $formHeading = function ($title, $width = 50) {
    echo "<!-- ${title} Heading -->
        <div class='d-flex flex-row mt-3 justify-content-evenly'>
            <div class='py-2 my-auto w-100'><hr></div>
            <div class='p-2 w-{$width} text-center'> <h3>${title} Data</h3> </div>
            <div class='py-2 my-auto w-100'><hr></div>
        </div>";
} ?>


<?php # Input Builder ?>
<?php function create_input($field, $placeholder, $cur, $type = 'text', $required = false) : void {
    $req = ($required) ? ' required' : '';
    $val = $cur->input->post($field) ?? ''; ?>
    <input type="<?=$type?>" name="<?=$field?>" id="<?=$field?>" class="form-control text-bg-dark" value="<?=$val?>"<?=$req?> placeholder="<?=$placeholder?>">
<?php } ?>
<?php function create_textarea($field, $placeholder, $cur, $rows = 3, $required = false) : void {
    $req = ($required) ? ' required' : '';
    $val = $cur->input->post($field) ?? ''; ?>
    <textarea name="<?=$field?>" cols="40" rows="<?=$rows?>" id="<?=$field?>" class="form-control text-bg-dark" placeholder="<?=$placeholder?>"<?=$req?>><?=$val?></textarea>
<?php } ?>

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
                <h1> Add A Volunteer </h1>
            </div>
            <div class="card-body">
                <h2 class="card-title"> Volunteer Data Form, Note there are 3 Parts </h2>
                <form  id="addVolunteerForm" enctype="multipart/form-data" method="post" action="<?=base_url('/dashboard/volunteer')?>" class="text-left mx-2 form-holder">
                    <?=$formHeading("Core User Data")?>
                    <!-- Core Data Content -->
                    <!-- Name & Email -->
                    <div class="row my-2">
                        <div class="col-3 px-2">
                            <label for="first_name" class="form-label required">First Name:</label>
                            <?php create_input('first_name', 'Enter Your First Name', $this, 'text', true); ?>
                            <div class="invalid-feedback" id="first_name_validation">
                                First Name Required (Letters and Spaces Only)
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="last_name" class="form-label required">Last Name:</label>
                            <?php create_input('last_name', 'Enter Your Last Name', $this, 'text', true); ?>
                            <div class="invalid-feedback" id="last_name_validation">
                                Last Name Required (Letters and Spaces Only)
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="username" class="form-label required">Username:</label>
                            <?php create_input('username', 'Enter Your Username', $this, 'text', true); ?>
                            <div class="invalid-feedback" id="username_validation">
                                Username Required (Must Be Unique)
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="email" class="form-label required">Email:</label>
                            <?php create_input('email', 'Enter Your Email', $this, 'email', true); ?>
                            <div class="invalid-feedback" id="email_validation">
                                Email Required (Must be valid Email and Unique Ex: name@domain.com)
                            </div>
                        </div>
                    </div>
                    <!-- Pass -->
                    <div class="row my-2">
                        <div class="col-6 px-2">
                            <label for="password" class="form-label required">Password:</label>
                            <?php create_input('password', 'Enter Your Password', $this, 'password', true); ?>
                            <div class="invalid-feedback" id="password_validation">
                                Password Required
                            </div>
                        </div>
                        <div class="col-6 px-2">
                            <label for="pass_confirm" class="form-label required">Confirm Password:</label>
                            <?php create_input('pass_confirm', 'Re-enter Your Password', $this, 'password', true); ?>
                            <div class="invalid-feedback" id="pass_confirm_validation">
                                Password Confirmation Must Match Password
                            </div>
                        </div>
                    </div>
                    <?=$formHeading("Volunteer Data")?>

                    <!-- Extra Data Content -->
                    <!-- Address/Phone/Centers -->
                    <div class="row my-2">
                        <div class="col-3 px-2">
                            <label for="address" class="form-label">Address:</label>
                            <?php create_input('address', 'Enter Your Address', $this); ?>
                            <div class="invalid-feedback" id="address_validation">
                                Max Length 50
                            </div>
                        </div>
                        <div class="col-2 px-2">
                            <label for="home" class="form-label">Home Phone:</label>
                            <?php create_input('home', 'Enter Your Home Phone (Numbers Only)', $this); ?>
                            <div class="invalid-feedback" id="home_validation">
                                Numbers and Spaces Only
                            </div>
                        </div>
                        <div class="col-2 px-2">
                            <label for="work" class="form-label">Work Phone:</label>
                            <?php create_input('work', 'Enter Your Work Phone (Numbers Only)', $this); ?>
                            <div class="invalid-feedback" id="work_validation">
                                Numbers and Spaces Only
                            </div>
                        </div>
                        <div class="col-2 px-2">
                            <label for="cell" class="form-label">Cell Phone:</label>
                            <?php create_input('cell', 'Enter Your Cell Phone (Numbers Only)', $this); ?>
                            <div class="invalid-feedback" id="cell_validation">
                                Numbers and Spaces Only
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="centersList" class="form-label">
                                Centers:
                                <span class="text-info" id="selectedCenters">
                                </span>
                            </label>
                            <select name="centersList" id="centersList" class="form-control text-bg-dark">
                                <?php if($centers !== false && count($centers) > 0) : ?>
                                    <option> Select a Center to Add </option>
                                    <?php foreach ($centers as $center) : ?>
                                        <option value="<?=$center['id']?>"><?=$center['name']?> - <?=$center['location']?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option> Currently No Centers Available </option>
                                <?php endif; ?>
                            </select>
                            <?php create_input('centers', '', $this, 'hidden'); ?>
                        </div>
                    </div>
                    <!-- Skills/Availability -->
                    <div class="row my-2">
                        <div class="col-6 px-2">
                            <label for="skills" class="form-label">Skills:</label>
                            <?php create_textarea('skills', 'Enter Your Skills/Interests', $this); ?>
                            <div class="invalid-feedback" id="skills_validation">
                                Max Length 500
                            </div>
                        </div>
                        <div class="col-6 px-2">
                            <label for="available" class="form-label">Availability:</label>
                            <?php create_textarea('available', 'Enter Your Available Times', $this); ?>
                            <div class="invalid-feedback" id="available_validation">
                                Max Length 500
                            </div>
                        </div>
                    </div>
                    <!-- Education/Uploads -->
                    <div class="row my-2">
                        <div class="col-6 px-2">
                            <label for="licenses" class="form-label">License(s):</label>
                            <?php create_textarea('licenses', 'Enter Your Current License(s) Information', $this); ?>
                            <div class="invalid-feedback" id="licenses_validation">
                                Max Length 500
                            </div>

                            <!-- Uploads -->
                            <div class="row my-2">
                                <div class="col-6 px-2">
                                    <label for="drivers" class="form-label">Upload Drivers License (PNG, JPG, GIF, Or PDF):</label>
                                    <?php create_input('drivers', '', $this, 'file'); ?>
                                    <div class="invalid-feedback" id="drivers_validation">
                                        Only File Types Allowed: .png .jpg .jpeg .gif .pdf
                                    </div>
                                </div>
                                <div class="col-6 px-2">
                                    <label for="social" class="form-label">Upload Social Security Card (PNG, JPG, GIF, Or PDF):</label>
                                    <?php create_input('social', '', $this, 'file'); ?>
                                    <div class="invalid-feedback" id="social_validation">
                                        Only File Types Allowed: .png .jpg .jpeg .gif .pdf
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 px-2">
                            <label for="background" class="form-label">Educational Background:</label>
                            <?php create_textarea('background', 'Enter Your Educational Background', $this, 6); ?>
                            <div class="invalid-feedback" id="background_validation">
                                Max Length 500
                            </div>
                        </div>
                    </div>

                    <?=$formHeading("Volunteer Emergency Data", 100)?>

                    <!-- Emergency Data Content -->
                    <div class="row my-2">
                        <div class="col-3 px-2">
                            <label for="e_name" class="form-label">Emergency Name:</label>
                            <?php create_input('e_name', 'Enter Your Emergency Contact Name', $this); ?>
                            <div class="invalid-feedback" id="e_name_validation">
                                Letters and Spaces Only
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="e_phone" class="form-label">Emergency Phone:</label>
                            <?php create_input('e_phone', 'Enter Your Emergency Contact Phone', $this); ?>
                            <div class="invalid-feedback" id="e_phone_validation">
                                Numbers and Spaces Only
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="e_email" class="form-label">Emergency Email:</label>
                            <?php create_input('e_email', 'Enter Your Emergency Contact Email', $this); ?>
                            <div class="invalid-feedback" id="e_email_validation">
                                Must be a Valid Email
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="e_address" class="form-label">Emergency Address:</label>
                            <?php create_input('e_address', 'Enter Your Emergency Contact Address', $this); ?>
                            <div class="invalid-feedback" id="e_address_validation">
                                Must be valid Email and Unique (Ex: name@domain.com)
                            </div>
                        </div>
                    </div>
                    <hr>

                    <!-- Buttons -->
                    <div class="w-100 mt-2 d-flex justify-content-evenly">
                        <a class="btn btn-outline-secondary w-25" href="<?=base_url('/dashboard/volunteers')?>">Cancel</a>
                        <button class="btn btn-success w-25" type="submit" id="SubmitAddVolunteer">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($getPost) : ?>
<script>$(document).ready(() => { PSA.Volunteer.init.add(<?=json_encode($postData)?>); })</script>
<?php else: ?>
<script>$(document).ready(() => { PSA.Volunteer.init.add(false); })</script>
<?php endif; ?>
