<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php include('header.php'); ?>

<?php $u = $this->db->select('*')->where('id', $this->session->userdata('user_id'))->get('users');
    $user = ""; foreach ($u->result() as $usr) { $user = $usr; }
    $companies = $this->db->get('companies');
?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">User Profile - <?php echo $user->first_name . ' ' . $user->last_name; ?></h4>
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
                <!-- end page title -->

                <!-- row start -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Edit Your Information</h4>
                                <span style="color:red;" "><?php echo $this->session->flashdata('message'); ?><?php echo validation_errors(); ?></span>

                                <form action="/auth/EditUser" method="post" id="user_edit">
                                    <!-- First Name -->
                                    <p>
                                        <label for="first_name">First Name</label>
                                        <input type="text" class="form-control" name="first_name" value="<?php echo $user->first_name; ?>">
                                    </p>
                                    <p>
                                        <label for="last_name">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" value="<?php echo $user->last_name; ?>">
                                    </p>
                                    <p>
                                        <label for="username">Username</label>
                                        <input type="text" class="form-control" name="username" value="<?php echo $user->username; ?>">
                                    </p>
                                    <p>
                                        <label for="email">Email</label>
                                        <input type="text" class="form-control" name="email" value="<?php echo $user->email; ?>">
                                    </p>
                                    <p>
                                        <label for="company">Company</label>
                                        <select class="form-control" name="company" onchange="checkCustomCompany(this.value);">
                                            <?php $found = false;
                                            foreach($companies->result() As $company) {
                                                $found = $found ?: ($company->id == $user->company);
                                                $sel = ($company->id == $user->company) ? ' selected' : '';
                                                if($company->id != 1) { echo '<option value="' . $company->id . '"' . $sel . '>' . $company->name . '</option>'; }
                                            }
                                            $sel = $found ? '' : ' selected'; ?>
                                            <option value="0"<?php echo $sel ?>>Other</option>
                                        </select>
                                        <?php $sel = $found ? ' style="display: none;"' : '';  ?>
                                        <input<?php echo $sel ?> id="companyCustom" type="text" class="form-control" name="companyCustom" placeholder="Enter a New Company Name">
                                    </p>
                                    <p>
                                        <label for="phone">Phone</label>
                                        <input type="text" class="form-control" name="phone" value="<?php echo $user->phone; ?>">
                                    </p>
                                    <p>
                                        <label for="password">Password <small>(Leave empty to keep current password)</small></label>
                                        <input type="password" class="form-control" id="password" name="password" value="">
                                    </p>
                                    <p>
                                        <label for="passconf">Password Confirm</label>
                                        <input type="password" class="form-control" id="passconf" name="passconf" value="">
                                    </p>
                                    <p class="text-right">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

<?php include('footer.php'); ?>