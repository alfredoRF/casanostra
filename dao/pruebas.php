<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

require_once 'conection.php';

$action = $_REQUEST['action'];
switch ($action) {
    case 1:
        prueba();
        break;
}

function prueba()
{
    phpinfo();
}
