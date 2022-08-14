<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php # Prepare Variables
# Step 1: Get the 'Constants'
$ROW_LIMIT = 25;
$PAGE_COUNT = ceil($this->db->get('audit_log')->num_rows() / $ROW_LIMIT);

# Step 2: Set Up the Current Page
$curPage = (empty($_GET['page'])) ? $PAGE_COUNT : intval($_GET['page']);
$curPage = ($curPage <= 0) ? 1 : $curPage;

# Step 3: Set up the Page Log
$max = $curPage * $ROW_LIMIT;
$min = ($curPage - 1) * $ROW_LIMIT;
$logs = $this->db->where("id >= $min AND id <= $max")->order_by('id', 'desc')->limit($ROW_LIMIT)->get('audit_log');
?>


<body style="height: 100%">
<!-- body content -->
<div class="text-bg-secondary text-center h-100 w-100 row">
    <div class="col-2 pe-0">
        <?php include ('menu-sidebar.php') ?>
    </div>
    <div class="col-10">
        <!-- Audit Log -->
        <div class="card text-bg-dark text-light m-2">
            <div class="card-body">
                <!-- Title -->
                <h1 class="card-title mb-4">Audit Log</h1>

                <!-- Page Selection -->
                <span style="font-size: 20px;">
                    <?php if ($PAGE_COUNT > 1) : ?>
                    Select Page:
                    <select class="form-control" style="display:inline-block; width:50%;" id="AuditPageSelect">
                        <?php for ($int = 1; $int <= $PAGE_COUNT; $int++) : ?>
                            <option value="<?=$int?>" <?=($int == $curPage) ? ' selected' : ''?>><?=$int?></option>
                        <?php endfor; ?>
                    </select>
                    <?php endif; ?>
                </span>
                <hr/>

                <!-- Log Data -->
                <table class="table">
                    <!--- Header Row -->
                    <thead class="thead table-light">
                        <tr>
                            <?php if(count($logs->result()) > 0) : ?>
                            <th scope="col">ID</th>
                            <th scope="col">LOCATION</th>
                            <th scope="col">ACTION</th>
                            <th scope="col">USER</th>
                            <th scope="col">DATE & TIME</th>
                            <th scope="col">STATUS</th>
                            <?php else: ?>
                            <th scope="col"> Log Currently Empty </th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <!--- Table Body -->
                    <tbody class="table-dark">
                    <?php foreach ($logs->result() as $log) :  ?>
                        <tr data-bs-toggle="tooltip" title="<?= $log->data ?>">
                            <td><?= $log->id; ?></td>
                            <td><?= $log->location ?></td>
                            <td><?= $log->action; ?></td>
                            <td>
                                <?php $user = $this->db->select('*')->where('id', $log->user_id)->limit(1)->get('users'); ?>
                                <?= (count($user->result_array()) !== 0) ? $user->result_array()[0]['first_name']." ".$user->result_array()[0]['last_name'] : $log->user_id; ?>
                            </td>
                            <td><?= date("Y-m-d h:i:s", strtotime($log->timestamp)); ?></td>
                            <td class="text-bg-<?= $log->status; ?>">
                                <?= match ($log->status) { "success" => "Success", "danger" => "Fail"} ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Init JS for this Page -->
<script> $(document).ready(() => { PSA.Audit.init(); }) </script>