<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $user_role = empty($user_role) ? 0 : $user_role; ?>

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
                <h1> Dashboard </h1>
            </div>

            <div class="d-flex flex-column align-items-center flex-shrink-0 text-bg-dark">
                <?php
                $activeStyle = fn($active) => $active ? 'text-bg-secondary' : 'list-group-item-dark';
                $menuItems = [  // The LINK is the most important part, everything else is dynamic based on that
                    'View Dashboard'                => [ 'link' => '/dashboard','active' => false, 'rendered' => true ],
                    'Manage Volunteers'        => [ 'link' => '/dashboard/volunteers','active' => false, 'rendered' => false],
                    'Manage Volunteer Registration'                => [ 'link' => '/dashboard/volunteer','active' => false, 'rendered' => false],
                    'Manage Opportunities'     => [ 'link' => '/dashboard/opportunities','active' => false, 'rendered' => false],
                    'Manage Opportunity Creation'              => [ 'link' => '/dashboard/opportunity','active' => false, 'rendered' => false],
                    'View Audit Log'                => [ 'link' => '/dashboard/audit','active' => false, 'rendered' => false],
                    'System Test'                     => [ 'link' => '/dashboard/test','active' => false, 'rendered' => false],
                ];

                # Set Up the Correct Menu Based on Role/Page
                // Get Role
                $role = $user_role ?? 'Default'; // Return Default Role if all else failed

                // Setup Role Views
                switch ($role){
                    case 'developer': # See Everything for Debug
                        foreach ($menuItems as $menuKey => $menuItem) $menuItems[$menuKey]['rendered'] = true;
                        break;
                    case 'administrator':
                        $menuItems['Manage Volunteers']['rendered'] = true;
                        $menuItems['Manage Opportunities']['rendered'] = true;
                        break;
                }

                // Setup Active Page (Dynamically)
                $page = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); # Get Url Path
                $page = (substr_count($page, '/') > 2) ? substr($page, 0, strpos($page, strrchr($page, '/'))) : $page; # Trim Out 3 Statement
                //$page = substr($page, 0 )
                $item = 'Dashboard';
                foreach ($menuItems as $menuKey => $menuItem) {
                    if($menuItem['link'] == $page) $item = $menuKey;
                }

                // Set Current Menu Item
                $menuItems[$item]['active'] = true;
                ?>
            </div>

                <a href="/dashboard" class="p-3 link-light border-bottom">
                    <span class="float-start fs-5 fw-semibold">Dashboard - <?=ucwords(strtolower($role))?></span>
                </a>
                <div class="list-group list-group-item bg-secondary mb-auto">
                    <?php # Dynamic Load Each of the Core Menu Items ?>
                    <?php foreach ($menuItems as $menuName => $menuItem) : ?>
                        <?php if($menuItem['rendered']): ?>
                            <button class="dashboardbutton" style="vertical-align:middle">
                                <a href="<?=base_url($menuItem['link'])?>" style="text-decoration: none; color: white">
                                    <span><?=$menuName?></span>
                                </a>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
        </div>
    </div>

</div>