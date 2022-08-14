<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php $this->page = 'Pre-Semester Assignment' . (empty($page) ? '' : ' - '.$page); ?>
<?php $this->styles = (!empty($styles)) ? $styles : '/assets/shared/css/default.css'; ?>
<?php $this->scripts = (!empty($scripts)) ? $scripts : '/assets/shared/js/default.js'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?=ucwords(strtolower($this->page))?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Pre Semester Assignment" name="description" />
    <meta content="JDel" name="author" />
    <!-- App favicon -->
<!--    <link rel="shortcut icon" href="< ? php echo base_url('/assets/images/favicon.ico') ?>">-->

    <!-- ALL CSS -->
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <!-- Font-Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Sweet Alert -->
    <link href="//cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <!-- Customizations -->
    <link href="<?=base_url($this->styles);?>" id="custom-style" rel="stylesheet" type="text/css" />

    <!-- ALL JAVASCRIPT -->
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <!-- Sweet Alert -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <!-- Tiny MCE -->
    <script src="https://cdn.tiny.cloud/1/bf8ka7mx1yvk5lf5cg6qv9fb5kwoz2yl4moian7dfpb026ns/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <!-- Home -->
    <script src="<?=base_url($this->scripts);?>" referrerpolicy="origin"></script>

</head>
