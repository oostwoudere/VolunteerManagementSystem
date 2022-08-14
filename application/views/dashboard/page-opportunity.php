<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $user_role = empty($user_role) ? 0 : $user_role; ?>
<?php $id = empty($id) ? 0 : $id; ?>
<?php $opportunity = empty($opportunity) ? 0 : $opportunity; ?>

<?php if($opportunity === 0) {
    $q = $this->db->select('opportunities.*, centers.name as center_name, centers.location as location, count(users.id) as volunteers')
        ->join('centers', 'centers.id = opportunities.center_id', 'left')
        ->join('opportunities_volunteers', 'opportunities_volunteers.opportunity_id = opportunities.id', 'left')
        ->join('users', 'users.id = opportunities_volunteers.volunteer_id', 'left')
        ->where('opportunities.id', $id)
        ->where('users.active =', '1')
        ->limit(1)->get('opportunities');
    if(count($q->result_array()) > 0) {
        $opportunity = $q->result_array()[0];
    }
} ?>
<?php $volunteers = [];
    $v = $this->db->select('users.first_name, users.last_name, users.username, users.email, users.id')
            ->join('users', 'users.id = opportunities_volunteers.volunteer_id', 'left')
            ->where('opportunities_volunteers.opportunity_id =', $id)
            ->get('opportunities_volunteers');
    if(count($v->result_array()) > 0) {
        $volunteers = $v->result_array();
    }
?>

<?php if($opportunity !== 0) : ?>
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
                    <?=$opportunity['name']?> (<?=$id?>)
                    <small>
                        <a class="float-end " href="<?=base_url('/dashboard/opportunity/edit?id='.$opportunity['id'])?>">
                            <i class="fa fa-pencil" title="Edit"></i>
                        </a>
                    </small>
                </h1>
            </div>
            <div class="card-body">
                <h2 class="card-title pb-2"> When and Where </h2>
                <div class="row card-text">
                    <div class="col-4"> <p><?=$opportunity['center_name']?></p> </div>
                    <div class="col-4"> <p><?=$opportunity['location']?></p> </div>
                    <div class="col-4"> <p><?=$opportunity['date']?></p> </div>
                </div>

                <h2 class="card-title pb-2"> Volunteers </h2>
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
                    <?php if(count($volunteers) > 0) : foreach ($volunteers as $volunteer): ?>
                        <tr>
                            <td><?=$volunteer['id']?></td>
                            <td><?=$volunteer['username']?></td>
                            <td><?=$volunteer['first_name']?></td>
                            <td><?=$volunteer['last_name']?></td>
                            <td><?=$volunteer['email']?></td>
                            <td>
                                <!-- View -->
                                <a class="text-decoration-none link-success pe-2" title="view" href="<?=base_url('/dashboard/volunteer?id='.$volunteer['id'])?>">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <!-- Edit -->
                                <a class="text-decoration-none pe-2" title="edit" href="<?=base_url('/dashboard/volunteer/edit?id='.$volunteer['id'])?>">
                                    <i class="fa fa-pencil-alt"></i>
                                </a>
                                <!-- Delete -->
                                <i class="fa fa-trash link-danger c-pointer" onclick="PSA.Volunteer.delete(<?=$volunteer['id']?>)" title="delete"></i>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                        <tr>
                            <td colspan="6">
                                <button type="button" class="btn btn-outline-primary w-100"> Add Volunteer </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>