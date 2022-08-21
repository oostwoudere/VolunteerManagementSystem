<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $user_role = empty($user_role) ? 0 : $user_role; ?>
<?php $id = empty($id) ? 0 : $id; ?>
<?php $centers = $centers ?? false; ?>

<?php $o = $this->db->select('*')->where('id', $id)->get('opportunities'); ?>
<?php $opportunity = $o->result_array()[0]; ?>
<?php $v = $this->db->select('users.id, users.username, users.first_name, users.last_name, users.email')
                ->join('opportunities_volunteers', 'opportunities_volunteers.volunteer_id = users.id')
                ->where('opportunities_volunteers.opportunity_id', $id)
                ->get('users'); ?>
<?php $volunteers = $v->result_array(); ?>

<?php if($centers === false): ?>
<?php $c = $this->db->select('*')->get('centers'); ?>
<?php $centers = $c->result_array(); ?>
<?php endif; ?>


<?php $v = '';
if (count($volunteers) > 0) {
    foreach ($volunteers as $volunteer) {
        if($volunteer['id']) {
            if(strpos($v, $volunteer['id']) === false) $v .= $volunteer['id'].',';
        }
    }
} ?>

<?php $formHeading = function ($title, $width = 50) {
    echo "<!-- ${title} Heading -->
        <div class='d-flex flex-row mt-3 justify-content-evenly'>
            <div class='py-2 my-auto w-100'><hr></div>
            <div class='p-2 w-{$width} text-center'> <h3>${title} Data</h3> </div>
            <div class='py-2 my-auto w-100'><hr></div>
        </div>";
};
$sel = function ($cur, $test) { if(strcmp($cur, $test) == 0) echo " selected"; };
$arrVal = function ($key, $dataSet) { echo array_key_exists($key, $dataSet) ? $dataSet[$key] : ucwords($key).' No Found'; }; ?>

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
                <h1> Edit Opportunity <?=$id?> </h1>
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
                            <input type="text" name="name" value="<?=$arrVal('name', $opportunity)?>" id="name" class="form-control text-bg-dark" required placeholder="Enter Opportunity Name">
                            <div class="invalid-feedback" id="name_validation">
                                Opportunity Name Required (Letters and Spaces Only)
                            </div>
                        </div>
                        <div class="col-4 px-2">
                            <label for="data" class="form-label required">Time:</label>
                            <input type="date" name="date" value="<?=$arrVal('date', $opportunity)?>" id="date" class="form-control text-bg-dark" required placeholder="Enter Your Last Name">
                            <div class="invalid-feedback" id="date_validation">
                                Opportunity Date Required
                            </div>
                        </div>
                        <div class="col-4 px-2">
                            <label for="centersList" class="form-label">
                                Center:
                            </label>
                            <select name="center" id="center" class="form-control text-bg-dark">
                                <?php if($centers !== false && count($centers) > 0) : ?>
                                    <?php foreach ($centers as $center) : ?>
                                        <option value="<?=$center['id']?>"<?=$sel($center['id'], $opportunity['center_id'])?>><?=$center['name']?> - <?=$center['location']?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option> Currently No Centers Available </option>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="center_validation">
                                Opportunity Date Required
                            </div>
                        </div>
                    </div>

                    <?=$formHeading("Volunteer Data")?>
                    <!-- Extra Data Content -->
                    <!-- Address/Phone/Centers -->
                    <div class="row my-2">
                        <input type="hidden" name="volunteers" id="volunteers" value="<?=$v?>">
                        <table class="w-100 table table-dark table-striped table-hover">
                            <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Username</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Last Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($volunteers) > 0) : ?>
                            <?php foreach ($volunteers as $volunteer) :?>
                                <tr id="<?=$arrVal('id', $volunteer)?>Volunteer">
                                    <td><?=$arrVal('id', $volunteer)?></td>
                                    <td><?=$arrVal('username', $volunteer)?></td>
                                    <td><?=$arrVal('first_name', $volunteer)?></td>
                                    <td><?=$arrVal('last_name', $volunteer)?></td>
                                    <td><?=$arrVal('email', $volunteer)?></td>
                                    <td><i class="fa fa-trash link-danger c-pointer" onclick="PSA.Opportunity.removeVolunteer(<?=$arrVal('id', $volunteer)?>)"></i></td>
                                </tr>
                            <?php endforeach;?>
                            <?php endif; ?>
                            <tr id="addVolunteerRow"><td colspan="6"><button type="button" id="addVolunteerBtn" class="btn btn-outline-primary w-100"> Add Volunteer </button></td></tr>
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <!-- Buttons -->
                    <div class="w-100 mt-2 d-flex justify-content-evenly">
                        <a class="btn btn-outline-secondary w-25" href="<?=base_url('/dashboard/opportunities')?>">Cancel</a>
                        <button class="btn btn-success w-25" type="button" id="SubmitEditOpportunity">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>$(document).ready(() => { PSA.Opportunity.init.edit(<?=$id?>); })</script>