<?php

require_once 'config/database.php';
require_once 'RestServices.php';

$rest_services = new RestServices($DB_CONFIG);
$rest_services->handle();
$rest_services->close();

