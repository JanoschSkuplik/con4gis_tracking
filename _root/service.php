<?php

// simple trick for Contao < 2.10
$arrPost = $_POST;
unset($_POST);


// inizialize the contao framework
define('TL_MODE', 'FE');
require('system/initialize.php');


// write the post data back into the array
$_POST = $arrPost;


// create a TrackingService instance
$this->import('TrackingService');
$this->TrackingService->init();
