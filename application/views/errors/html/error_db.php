<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!DOCTYPE html>
<html>
    <head>
        <title>Pre-Semester Assignment</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/assets/csp/css/main.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>

    <body style="background: #222">
        <div class="top">
            <div class="" id="myNavbar">
                <a href="/dashboard" class="bar-item button">Back to DASHBOARD</a>
            </div>
        </div>

        <!-- First Parallax Image with Logo Text -->
        <div class="h-100 w-100" id="home">
            <div class="display-middle">
                <h1><?=$heading??''?></h1>
                <?=$message??''?>
            </div>
        </div>
    </body>
</html>
