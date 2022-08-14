<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $user_role = empty($user_role) ? 0 : $user_role; ?>
<?php $id = empty($id) ? 0 : $id; ?>
<?php $volunteer = []; $volunteerData = false;
// Attempt to Get all Data
$q = $this->db->select('volunteers_data.*, users.*')
    ->join('users', 'volunteers_data.user_id = users.id', 'left')
    ->where('volunteers_data.user_id', $id)
    ->limit(1)->get('volunteers_data');
if(count($q->result_array()) > 0) {
    $volunteer = $q->result_array()[0];
    $volunteerData = true;
} else {
    // Just Get user Data
    $q = $this->db->select('users.*')->where('users.id', $id)->limit(1)->get('users');
    if(count($q->result_array()) > 0) $volunteer = $q->result_array()[0];
}
# Don't Continue if volunteer not found
if(count($volunteer) > 0) : ?>

<?php $drivers = ($volunteerData && empty($volunteer['drivers']) && strlen($volunteer['drivers']) > 1) ? $volunteer['drivers'] : 'No Drivers License Uploaded' ?>
<?php $social = ($volunteerData && empty($volunteer['social']) && strlen($volunteer['social']) > 1) ? $volunteer['social'] : 'No Social Security Card Uploaded' ?>
<?php $generalInfo = ['username' => 'Username', 'email' => 'Email', 'address' => 'Address', 'home' => 'Home Phone', 'work' => 'Work Phone', 'cell' => 'Cell Phone']; ?>
<?php $moreDetail = ['skills' => 'Skills', 'available' => 'Availability', 'licenses' => 'License(s)', 'background' => 'Education Background'] ?>
<?php $moreDetails = ['centers' => 'Preferred Centers', 'drivers' => 'Drivers License', 'social' => 'Social Security Card'] ?>
<?php $emergency = ['e_name' => 'Name', 'e_phone' => 'Phone', 'e_email' => 'Email', 'e_address' => 'Address'] ?>

<?php function createCards (array $arr, array $vol) {
    foreach ($arr as $property => $name) : ?>
    <div class="card text-bg-dark border-secondary">
        <h5 class="card-header"><?=$name?></h5>
        <div class="card-body">
            <p class="card-text">
            <?php if(strcmp($property, 'drivers') == 0 || strcmp($property, 'social') == 0) : ?>
                <?php if (!empty($vol[$property]) && strlen($vol[$property]) > 1) : ?>
                <a class="btn btn-link text-decoration-none" href="/uploads/<?=$vol[$property]?>" target="_blank"><?="Click to View ${name}"?></a>
                <?php else : ?>
                <?=(!empty($vol[$property]) && strlen($vol[$property]) > 1) ? $vol[$property] : "No ${name} Uploaded"?>
                <?php endif; ?>
            <?php else : ?>
                <?=(!empty($vol[$property]) && strlen($vol[$property]) > 1) ? $vol[$property] : "No ${name} Uploaded"?>
            <?php endif; ?>
            </p>
        </div>
    </div>
<?php endforeach;
} ?>

<body style="height: 100%">
<!-- body content -->
<div class="text-bg-secondary text-center h-100 w-100 row">
    <div class="col-2 pe-0">
        <?php include ('menu-sidebar.php') ?>
    </div>
    <div class="col-10">
        <div class="card text-bg-dark text-light m-2">
            <div class="card-header">
                <h1>
                    <?=$volunteer['first_name']?> <?=$volunteer['last_name']?> (<?=$id?>)
                    <small>
                        <a class="float-end " href="<?=base_url('/dashboard/volunteer/edit?id='.$volunteer['id'])?>">
                            <i class="fa fa-pencil" title="Edit"></i>
                        </a>
                    </small>
                </h1>
            </div>
            <div class="card-body">
                <h2 class="card-title pb-2"> General Info </h2>
                <div class="card-group">
                    <?php createCards($generalInfo, $volunteer); ?>
                </div>

                <h2 class="card-title py-2"> More Details </h2>
                <div class="card-group mb-2">
                    <?php createCards($moreDetail, $volunteer); ?>
                </div>
                <div class="card-group">
                    <?php createCards($moreDetails, $volunteer); ?>
                </div>

                <h2 class="card-title py-2"> Emergency Details </h2>
                <div class="card-group">
                    <?php createCards($emergency, $volunteer); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>