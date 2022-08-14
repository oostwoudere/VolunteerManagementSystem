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
                <h1> Add An Opportunity </h1>
            </div>
            <div class="card-body">
                <h2 class="card-title"> New Opportunity Data Form </h2>
                <form id="addOpportunityForm" enctype="multipart/form-data" method="post" class="text-left mx-2 form-holder">
                    <?=$formHeading("Core Data")?>
                    <!-- Core Data Content -->
                    <!-- Name & Email -->
                    <div class="row my-2">
                        <div class="col-4 px-2">
                            <label for="name" class="form-label required">Name:</label>
                            <input type="text" name="name" id="name" class="form-control text-bg-dark" required placeholder="Enter Opportunity Name">
                            <div class="invalid-feedback" id="name_validation">
                                Opportunity Name Required (Letters and Spaces Only)
                            </div>
                        </div>
                        <div class="col-4 px-2">
                            <label for="date" class="form-label required">Date:</label>
                            <input type="date" name="date" id="date" class="form-control text-bg-dark" required placeholder="Enter Opportunity Date">
                            <div class="invalid-feedback" id="date_validation">
                                Date Required (After Today)
                            </div>
                        </div>
                        <div class="col-4 px-2">
                            <label for="center" class="form-label">Center: </label>
                            <select name="center" id="center" class="form-control text-bg-dark" required>
                                <?php if($centers !== false && count($centers) > 0) : ?>
                                    <option> Select a Center to Add </option>
                                    <?php foreach ($centers as $center) : ?>
                                        <option value="<?=$center['id']?>"><?=$center['name']?> - <?=$center['location']?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option> Currently No Centers Available </option>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="center_validation">
                                Center Required
                            </div>
                        </div>
                    </div>
                    <hr>

                    <!-- Buttons -->
                    <div class="w-100 mt-2 d-flex justify-content-evenly">
                        <a class="btn btn-outline-secondary w-25" href="<?=base_url('/dashboard/volunteers')?>">Cancel</a>
                        <button class="btn btn-success w-25" type="button" id="SubmitAddOpportunity">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>$(document).ready(() => { PSA.Opportunity.init.add(); })</script>