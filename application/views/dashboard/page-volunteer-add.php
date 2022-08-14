<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $user_role = empty($user_role) ? 0 : $user_role; ?>

<?php $formHeading = function ($title, $width = 50) {
    echo "<!-- ${title} Heading -->
        <div class='d-flex flex-row mt-3 justify-content-evenly'>
            <div class='py-2 my-auto w-100'><hr></div>
            <div class='p-2 w-{$width} text-center'> <h3>${title} Data</h3> </div>
            <div class='py-2 my-auto w-100'><hr></div>
        </div>";
} ?>
<?php $centers = $centers ?? false; ?>


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
                <form id="addVolunteerForm" enctype="multipart/form-data" method="post" class="text-left mx-2 form-holder">
                    <?=$formHeading("Core User Data")?>
                    <!-- Core Data Content -->
                    <!-- Name & Email -->
                    <div class="row my-2">
                        <div class="col-3 px-2">
                            <label for="first_name" class="form-label required">First Name:</label>
                            <input type="text" name="first_name" id="first_name" class="form-control text-bg-dark" required placeholder="Enter Your First Name">
                            <div class="invalid-feedback" id="first_name_validation">
                                First Name Required (Letters and Spaces Only)
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="last_name" class="form-label required">Last Name:</label>
                            <input type="text" name="last_name" id="last_name" class="form-control text-bg-dark" required placeholder="Enter Your Last Name">
                            <div class="invalid-feedback" id="last_name_validation">
                                Last Name Required (Letters and Spaces Only)
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="username" class="form-label required">Username:</label>
                            <input type="text" name="username" id="username" class="form-control text-bg-dark" required placeholder="Enter Your Username">
                            <div class="invalid-feedback" id="username_validation">
                                Username Required (Must Be Unique)
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="email" class="form-label required">Email:</label>
                            <input type="email" name="email" id="email" class="form-control text-bg-dark" required placeholder="Enter Your Email">
                            <div class="invalid-feedback" id="email_validation">
                                Email Required (Must be valid Email and Unique Ex: name@domain.com)
                            </div>
                        </div>
                    </div>
                    <!-- Pass -->
                    <div class="row my-2">
                        <div class="col-6 px-2">
                            <label for="password" class="form-label required">Password:</label>
                            <input type="password" name="password" id="password" class="form-control text-bg-dark" required placeholder="Enter Your Password">
                            <div class="invalid-feedback" id="password_validation">
                                Password Required
                            </div>
                        </div>
                        <div class="col-6 px-2">
                            <label for="pass_confirm" class="form-label required">Confirm Password:</label>
                            <input type="password" name="pass_confirm" id="pass_confirm" class="form-control text-bg-dark" required placeholder="Re-enter Your Password">
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
                            <input type="text" name="address" id="address" class="form-control text-bg-dark" placeholder="Enter Your Address">
                            <div class="invalid-feedback" id="address_validation">
                                Max Length 50
                            </div>
                        </div>
                        <div class="col-2 px-2">
                            <label for="home" class="form-label">Home Phone:</label>
                            <input type="tel" name="home" id="home" class="form-control text-bg-dark" placeholder="Enter Your Home Phone (Numbers Only)">
                            <div class="invalid-feedback" id="home_validation">
                                Numbers and Spaces Only
                            </div>
                        </div>
                        <div class="col-2 px-2">
                            <label for="work" class="form-label">Work Phone:</label>
                            <input type="tel" name="work" id="work" class="form-control text-bg-dark" placeholder="Enter Your Work Phone (Numbers Only)">
                            <div class="invalid-feedback" id="work_validation">
                                Numbers and Spaces Only
                            </div>
                        </div>
                        <div class="col-2 px-2">
                            <label for="cell" class="form-label">Cell Phone:</label>
                            <input type="tel" name="cell" id="cell" class="form-control text-bg-dark" placeholder="Enter Your Cell Phone (Numbers Only)">
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
                            <input type="hidden" name="centers" id="centers" value="">
                        </div>
                    </div>
                    <!-- Skills/Availability -->
                    <div class="row my-2">
                        <div class="col-6 px-2">
                            <label for="skills" class="form-label">Skills:</label>
                            <textarea name="skills" cols="40" rows="3" id="skills" class="form-control text-bg-dark" placeholder="Enter Your Skills/Interests"></textarea>
                            <div class="invalid-feedback" id="skills_validation">
                                Max Length 500
                            </div>
                        </div>
                        <div class="col-6 px-2">
                            <label for="available" class="form-label">Availability:</label>
                            <textarea name="available" cols="40" rows="3" id="available" class="form-control text-bg-dark" placeholder="Enter Your Available Times"></textarea>
                            <div class="invalid-feedback" id="available_validation">
                                Max Length 500
                            </div>
                        </div>
                    </div>
                    <!-- Education/Uploads -->
                    <div class="row my-2">
                        <div class="col-6 px-2">
                            <label for="licenses" class="form-label">License(s):</label>
                            <textarea name="licenses" id="licenses" cols="40" rows="3" class="form-control text-bg-dark" placeholder="Enter Your Current License(s) Information"></textarea>
                            <div class="invalid-feedback" id="licenses_validation">
                                Max Length 500
                            </div>

                            <!-- Uploads -->
                            <div class="row my-2">
                                <div class="col-6 px-2">
                                    <label for="drivers" class="form-label">Upload Drivers License (PNG, JPG, GIF, Or PDF):</label>
                                    <input type="file" name="drivers" id="drivers" class="form-control text-bg-dark">
                                    <div class="invalid-feedback" id="drivers_validation">
                                        Only File Types Allowed: .png .jpg .jpeg .gif .pdf
                                    </div>
                                </div>
                                <div class="col-6 px-2">
                                    <label for="social" class="form-label">Upload Social Security Card (PNG, JPG, GIF, Or PDF):</label>
                                    <input type="file" name="social" id="social" class="form-control text-bg-dark">
                                    <div class="invalid-feedback" id="social_validation">
                                        Only File Types Allowed: .png .jpg .jpeg .gif .pdf
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 px-2">
                            <label for="background" class="form-label">Educational Background:</label>
                            <textarea name="background" cols="40" rows="6" id="background" class="form-control text-bg-dark" placeholder="Enter Your Educational Background"></textarea>
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
                            <input type="text" name="e_name" id="e_name" class="form-control text-bg-dark" placeholder="Enter Your Emergency Name">
                            <div class="invalid-feedback" id="e_name_validation">
                                Letters and Spaces Only
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="e_phone" class="form-label">Emergency Phone:</label>
                            <input type="tel" name="e_phone" id="e_phone" class="form-control text-bg-dark" placeholder="Enter Your Emergency Phone">
                            <div class="invalid-feedback" id="e_phone_validation">
                                Numbers and Spaces Only
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="e_email" class="form-label">Emergency Email:</label>
                            <input type="email" name="e_email" id="e_email" class="form-control text-bg-dark" placeholder="Enter Your Emergency Email">
                            <div class="invalid-feedback" id="e_email_validation">
                                Must be a Valid Email
                            </div>
                        </div>
                        <div class="col-3 px-2">
                            <label for="e_address" class="form-label">Emergency Address:</label>
                            <input type="text" name="e_address" id="e_address" class="form-control text-bg-dark" placeholder="Enter Your Emergency Address">
                            <div class="invalid-feedback" id="e_address_validation">
                                Must be valid Email and Unique (Ex: name@domain.com)
                            </div>
                        </div>
                    </div>
                    <hr>

                    <!-- Buttons -->
                    <div class="w-100 mt-2 d-flex justify-content-evenly">
                        <a class="btn btn-outline-secondary w-25" href="<?=base_url('/dashboard/volunteers')?>">Cancel</a>
                        <button class="btn btn-success w-25" type="button" id="SubmitAddVolunteer">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>$(document).ready(() => { PSA.Volunteer.init.add(); })</script>