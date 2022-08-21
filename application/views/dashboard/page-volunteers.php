<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $user_role = empty($user_role) ? 0 : $user_role; ?>
<?php $opportunities = empty($opportunities) ? 0 : $opportunities; ?>
<?php $volunteerFilter = empty($volunteerFilter) ? false : $volunteerFilter; ?>
<?php $volunteers = empty($volunteers) ? false : $volunteers; ?>

<?php # Prepare Variables
if($volunteers === false && $volunteerFilter === false) {
    $query = $this->db->select("users.id, username, CONCAT(first_name, ' ', last_name) as name, email, user_roles.name as role")
        ->join('user_roles', 'user_roles.id = users.role', 'left')
        ->where("users.active = 1")
        ->where('user_roles.name', 'volunteer')
        ->or_where('user_roles.name', 'pending')
        ->or_where('user_roles.name', 'disapproved')
        ->or_where('user_roles.name', 'inactive')
        ->get('users');
    $volunteers = $query->result_array();
}
?>

<?php # Helpers
$selFilter = function ($cur, $sel) : void { if($sel !== false && $sel == $cur) echo ' selected'; }
?>

<body style="height: 100%">
<!-- body content -->
<div class="text-bg-secondary text-center h-100 w-100 row">
    <div class="col-2 pe-0">
        <?php include ('menu-sidebar.php') ?>
    </div>
    <div class="col-10">
        <!-- Volunteers -->
        <div class="card text-bg-dark text-light m-2">
            <div class="card-body">
                <!-- Title -->
                <h1 class="card-title mb-4"> Volunteers </h1>

                <!-- Filters Selection -->
                <form method="get" class="row mb-3 g-3">
                    <div class="col-2"></div>
                    <!-- Example Filter 1 -->
                    <label for="volunteerFilter" class="col-2 col-form-label"> Volunteer Filter: </label>
                    <div class="col-4">
                        <select class="form-control text-bg-dark" id="volunteerFilter"  name="volunteerFilter">
                            <option value="">All</option>
                            <option value="1"<?=$selFilter(1, $volunteerFilter)?>>Approved/Pending Approval</option>
                            <option value="2"<?=$selFilter(2, $volunteerFilter)?>>Approved</option>
                            <option value="3"<?=$selFilter(3, $volunteerFilter)?>>Pending Approval</option>
                            <option value="4"<?=$selFilter(4, $volunteerFilter)?>>Disapproved</option>
                            <option value="5"<?=$selFilter(5, $volunteerFilter)?>>Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success col-2" value="Filter">Submit</button>
                </form>
                <hr/>

                <!-- Volunteers Data -->
                <table class="table table-striped  table-hover">
                    <!--- Header Row -->
                    <thead class="thead table-light">
                    <tr>
                        <?php if(count($volunteers) > 0) : ?>
                            <th scope="col">ID</th>
                            <th scope="col">USERNAME</th>
                            <th scope="col">NAME</th>
                            <th scope="col">EMAIL</th>
                            <th scope="col">ROLE</th>
                            <th scope="col">ACTIONS</th>
                        <?php else: ?>
                            <th scope="col"> No Volunteers In System </th>
                        <?php endif; ?>
                    </tr>
                    </thead>

                    <!--- Table Body -->
                    <tbody class="table-dark">
                    <?php foreach ($volunteers as $volunteer) :  ?>
                        <tr data-bs-toggle="tooltip">
                            <td><?= $volunteer['id']; ?></td>
                            <td><?= $volunteer['username'] ?></td>
                            <td><?= $volunteer['name']; ?></td>
                            <td><a class="text-info" href="mailto:<?= $volunteer['email']; ?>"><?= $volunteer['email']; ?></a></td>
                            <td><?= $volunteer['role']; ?></td>
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
                    <?php endforeach; ?>
                    <tr data-bs-toggle="tooltip">
                        <td colspan="6">
                            <a href="<?=base_url('/dashboard/volunteer')?>" class="btn btn-outline-primary w-100"> Add New Volunteer </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
