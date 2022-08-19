<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $user_role = empty($user_role) ? 0 : $user_role; ?>
<?php $filtered_out = $filtered_out ?? false;?>

<?php # Prepare Variables
if(empty($opportunities) && !$filtered_out) {
    $query = $this->db->select("opportunities.id, opportunities.name, opportunities.date, centers.name as center, centers.location")
        ->join('centers', 'centers.id = opportunities.center_id', 'left')
        ->get('opportunities');
    $opportunities = $query->result_array();
}
?>
<?php $centers = $centers ?? false; ?>

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
                <h1 class="card-title mb-4"> Opportunities </h1>

                <!-- Filters Selection -->
                <form method="get">
                    <div class="row mb-3 g-3">
                        <!-- Center Filter (code copied from add opp page)-->
                        <label for="centerFilter" class="col-2 col-form-label"> Filter By Center: </label>
                        <div class="col-4">
                            <select name="centerFilter" id="centerFilter" class="form-control text-bg-dark">
                                <?php if($centers !== false && count($centers) > 0) : ?>
                                    <option value="0" selected> All </option>
                                    <?php foreach ($centers as $center) : ?>
                                        <option value="<?=$center['id']?>"><?=$center['name']?> - <?=$center['location']?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option> Currently No Centers Available </option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <!-- Date Filter -->
                        <label for="dateFilter" class="col-2 col-form-label"> Filter By Date: </label>
                        <div class="col-4">
                            <select class="form-control text-bg-dark" id="dateFilter" name="dateFilter">
                                <option value="0" selected>All</option>
                                <option value="1">Most Recent</option>
                            </select>
                        </div>
                        <!-- Search String Filter -->
                        <label for="searchString" class="col-2 col-form-label"> Filter By Search Term: </label>
                        <div class="col-4">
                            <input type="text" name="searchString" id="searchString" class="form-control text-bg-dark" placeholder="Enter A Search Term">
                            </select>
                        </div>

                    </div>
                    <input type="submit" class="btn btn-success w-25" value="Filter">
                </form>
                <hr/>

                <!-- Volunteers Data -->
                <table class="table table-striped  table-hover">
                    <!--- Header Row -->
                    <thead class="thead table-light">
                        <tr>
                        <?php if(count($opportunities) > 0) : ?>
                            <th scope="col">ID</th>
                            <th scope="col">NAME</th>
                            <th scope="col">CENTER</th>
                            <th scope="col">LOCATION</th>
                            <th scope="col">DATE</th>
                            <th scope="col">ACTIONS</th>
                        <?php else: ?>
                            <th scope="col"> No Opportunities In System </th>
                        <?php endif; ?>
                        </tr>
                    </thead>

                    <!--- Table Body -->
                    <tbody class="table-dark">
                    <?php foreach ($opportunities as $opportunity) :  ?>
                        <tr data-bs-toggle="tooltip">
                            <td><?= $opportunity['id']; ?></td>
                            <td><?= $opportunity['name'] ?></td>
                            <td><?= $opportunity['center']; ?></td>
                            <td><?= $opportunity['location']; ?></td>
                            <td><?= $opportunity['date']; ?></td>
                            <td>
                                <!-- View -->
                                <a class="text-decoration-none link-success pe-2" title="view" href="<?=base_url('/dashboard/opportunity?id='.$opportunity['id'])?>">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <!-- Edit -->
                                <a class="text-decoration-none pe-2" title="edit" href="<?=base_url('/dashboard/opportunity/edit?id='.$opportunity['id'])?>">
                                    <i class="fa fa-pencil-alt"></i>
                                </a>
                                <!-- Delete -->
                                <i class="fa fa-trash link-danger c-pointer" onclick="PSA.Opportunity.delete(<?=$opportunity['id']?>)" title="delete"></i>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr data-bs-toggle="tooltip">
                        <td colspan="7">
                            <a href="<?=base_url('/dashboard/opportunity')?>" class="btn btn-outline-primary w-100"> Add New Opportunity </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>