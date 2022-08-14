<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$activeStyle = fn($active) => $active ? 'text-bg-secondary' : 'list-group-item-dark';
$a = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$menuItems = [  // The LINK is the most important part, everything else is dynamic based on that
    'Dashboard'         => [ 'link' => '/dashboard',                    'icon' => 'home',               'active' => false, 'rendered' => true ],
    'Volunteers'        => [ 'link' => '/dashboard/volunteers',         'icon' => 'user',               'active' => false, 'rendered' => false],
    $a.'Volunteer'      => [ 'link' => '/dashboard/volunteer',          'icon' => 'user-plus',          'active' => false, 'rendered' => false],
    'Opportunities'     => [ 'link' => '/dashboard/opportunities',      'icon' => 'people-group',       'active' => false, 'rendered' => false],
    $a.'Opportunity'    => [ 'link' => '/dashboard/opportunity',        'icon' => 'person-circle-plus', 'active' => false, 'rendered' => false],
    'Audit Log'         => [ 'link' => '/dashboard/audit',              'icon' => 'file-lines',         'active' => false, 'rendered' => false],
    'Test'              => [ 'link' => '/dashboard/test',               'icon' => 'flask-vial',         'active' => false, 'rendered' => false],
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
        $menuItems['Volunteers']['rendered'] = true;
        $menuItems[$a.'Volunteer']['rendered'] = true;
        $menuItems['Opportunities']['rendered'] = true;
        $menuItems[$a.'Opportunity']['rendered'] = true;
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

<div class="d-flex flex-column align-items-stretch flex-shrink-0 text-bg-dark h-100 w-100">
    <a href="/dashboard" class="p-3 link-light border-bottom">
        <span class="float-start fs-5 fw-semibold">Dashboard - <?=ucwords(strtolower($role))?></span>
    </a>
    <div class="list-group list-group-flush bg-dark border-bottom mb-auto">
    <?php # Dynamic Load Each of the Core Menu Items ?>
    <?php foreach ($menuItems as $menuName => $menuItem) : ?>
        <?php if($menuItem['rendered']): ?>
        <a href="<?=base_url($menuItem['link'])?>" class="list-group-item <?=$activeStyle($menuItem['active']);?> py-3" aria-current="true">
            <div class="d-flex w-100 align-items-center justify-content-between">
                <strong class="mb-1"><?=$menuName?></strong>
                <i class="fa fa-<?=$menuItem['icon']?>"></i>
            </div>
        </a>
        <?php endif; ?>
    <?php endforeach; ?>
    </div>
    <!-- Bottom User Actions -->
    <div class="list-group list-group-flush p-3 dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-user rounded-circle m-2 me-3"></i>
            <strong>User Actions</strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
            <li><a class="dropdown-item" href="<?=base_url('/dashboard/profile')?>">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?=base_url('/logout')?>">Sign out</a></li>
        </ul>
    </div>
</div>