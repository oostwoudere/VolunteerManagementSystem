<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php include('header.php');
$DEBUG = isset($_GET['debug']);

// Get User Data
$u = $this->db->select('*')->order_by('id', 'ASC')->get('users');

// Get Employees
$e = $this->db->select('*')->order_by('id', 'ASC')->get('employees');

$toggleClass = [
    'open' => 'fa-angle-down',
    'close' => 'fa-angle-right'
];
?>

    <div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title - Start -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Users - View all Users and Their Employees</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="/dashboard/profile">My Profile</a></li>
                                <li class="breadcrumb-item"><a onmouseenter="this.innerText = 'Click to Logout'" onmouseleave="this.innerText = 'Logged In'" href="/logout">Logged In</a></li>
                                <?php $user_groups = $this->ion_auth->get_users_groups($this->ion_auth->get_user_id())->result(); ?>
                                <li class="breadcrumb-item active"><?php echo $user_groups[0]->description; ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page Title - End -->

            <!-- Users - Start -->
            <div class="row" id="usersContainer">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Enrolled Users</h4>

                            <table class="table">
                                <thead class="thead-light" style="color: black; background-color: white;">
                                <th scope="col">ID</th>
                                <th scope="col">User Name</th>
                                <th scope="col">User Email</th>
                                <th scope="col">User Registered Date</th>
                                <th scope="col">User Groups</th>
                                <th scope="col">Actions</th>
                                </thead>

                                <tbody>
                                <?php foreach ($u->result() as $user):
                                    $g = $user_groups = $this->ion_auth->get_users_groups($user->id);
                                    $groups = ""; foreach ($g->result() as $group) { $groups = $group->id; }
                                    ?>

                                    <tr>
                                        <td><?php echo $user->id; ?></td>
                                        <td><?php echo $user->username; ?></td>
                                        <td><?php echo $user->email; ?></td>
                                        <td><?php echo date("Y-m-d", strtotime($user->created_on)); ?></td>
                                        <td>
                                            <select class="form-control" id="user_<?php echo $user->id; ?>_group"
                                                    onchange="assignUserGroup(<?php echo $user->id; ?>, <?php echo $groups; ?>)">
                                                <option value="0">Select A Group</option>
                                                <?php $gs = $this->db->select('*')->get('groups'); ?>
                                                <?php foreach ($gs->result() as $gsr) : ?>
                                                    <option value="<?php echo $gsr->id; ?>" <?php echo ($groups == $gsr->id) ? 'selected="selected"' : ''; ?>>
                                                        <?php echo $gsr->description; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <a href="/dashboard/users/edit/<?php echo $user->id; ?>" ><i class="fa fa-pencil"></i></a>
                                            <a href="#" onclick="deleteUser(<?php echo $user->id; ?>);"><i class="fa fa-trash"></i></a>
                                            <a href="#" onclick="showEmployees(<?php echo $user->id; ?>);">
                                                <i id="<?php echo $user->id; ?>Indicator" class="fa <?php echo $toggleClass['close'] ?> mx-2"
                                                   data-bs-toggle="tooltip" title="View Employee"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr style="display: none" id="<?php echo $user->id; ?>EmployeesTable">
                                        <td colspan="6">
                                            <table class="table">
                                                <thead class="thead-light" style="color: white; background-color: #FFFFFF11;">
                                                    <th scope="col">ID</th>
                                                    <th scope="col">First Name</th>
                                                    <th scope="col">Last Name</th>
                                                    <th scope="col">Email</th>
                                                    <th scope="col">Client</th>
                                                    <th scope="col">Actions</th>
                                                </thead>

                                                <tbody>
                                                <?php $e = $this->db->select('*')->where('client_id', $user->id)->order_by('id', 'ASC')->get('employees');
                                                if(count($e->result()) > 0) {
                                                    foreach ($e->result() as $employee): ?>
                                                        <!-- Employees - Start -->
                                                        <tr>
                                                            <td><?php echo $employee->id; ?></td>
                                                            <td><?php echo $employee->first_name; ?></td>
                                                            <td><?php echo $employee->last_name; ?></td>
                                                            <td><?php echo $employee->email; ?></td>
                                                            <td><?php
                                                                $clientName = $this->db->select('username')->where('id', $employee->client_id)->limit(1)->get('users');
                                                                echo ($clientName->result_array()[0]['username']) ?: $employee->client_id; ?>
                                                            </td>
                                                            <td>
                                                                <a href="/dashboard/employees/edit/<?php echo $employee->id; ?>" ><i class="fa fa-pencil"></i></a>
                                                                <a href="#" onclick="deleteEmployee(<?php echo $employee->id; ?>)"><i class="fa fa-trash"></i></a>
                                                            </td>
                                                        </tr>
                                                        <!-- Employees - End -->
                                                    <?php endforeach;
                                                } else { ?>
                                                    <tr>
                                                        <td class="text-center" colspan="6">Currently No Employees</td>
                                                    </tr>
                                                <?php } ?>
                                                <tr>
                                                    <td colspan="6">
                                                        <button type="button" class="btn btn-outline-primary w-100" onclick="setupAddEmployee(<?php echo $user->id; ?>)" data-toggle="modal" data-target="#addEmployeeModal">
                                                            Add Employee
                                                        </button>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Users - End -->
            <?php include('modals/addEmployee.php'); ?>
        </div>
    </div>

    <script>
        function assignUserGroup(id, current) {
            <?php echo ($DEBUG) ? 'console.log("assignUserGroup: " + id + ", " + current);' : '' ; ?>

            let request = $.ajax({
                url: "Data/UpdateUserGroup",
                method: "POST",
                data: { group : $(`#user_${id}_group`).val(), current : current, user : id },
                dataType: "json"
            });

            request.done(function( msg ) {
                swal("Group updated successfully.");
            });

            request.fail(function( jqXHR, textStatus ) {
                swal("Group updated failed.");
            });
        }

        function showEmployees(id) {
            <?php echo ($DEBUG) ? 'console.log("showEmployees: " + id);' : '' ; ?>
            // Setup Variables
            let classes = { open: "<?php echo $toggleClass['open'] ?>", close: "<?php echo $toggleClass['close'] ?>", }
            let open = $('#' + id + 'Indicator').hasClass(classes.open);
            <?php echo ($DEBUG) ? 'console.log("showEmployees: " + (open ? "Closing" : "Opening"));' : '' ; ?>
            // Clost Tool Tip
            bootstrap.Tooltip.getInstance($('#' + id + 'Indicator')).hide();
            if(open){
                // Close Table
                $('#' + id + 'EmployeesTable').hide()
                // Fix Arrow
                $('#' + id + 'Indicator').removeClass(classes.open).addClass(classes.close).prop('title', 'View Employees');
            } else {
                // Open Table
                $('#' + id + 'EmployeesTable').show()
                $('#' + id + 'Indicator').removeClass(classes.close).addClass(classes.open).prop('title', 'Hide Employees');
            }
            // Apply ToolTip Change
            bsToolTip = new bootstrap.Tooltip($('#' + id + 'Indicator'));
        }

        function setupAddEmployee(id){
            <?php echo ($DEBUG) ? 'console.log("setupAddEmployee: " + id);' : '' ; ?>
            $('#client_id').val(id);
        }

        function deleteUser(id) {
            <?php echo ($DEBUG) ? 'console.log("deleteUser: " + id);' : '' ; ?>
        }

        function deleteEmployee(id) {
            <?php echo ($DEBUG) ? 'console.log("deleteEmployee: " + id);' : '' ; ?>
        }

        $(document).ready(function () {

        })
    </script>

<?php include('footer.php'); ?>