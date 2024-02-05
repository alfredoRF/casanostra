<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

require_once 'conection.php';

$action = $_REQUEST['action'];

switch ($action) {
    case 1:
        getUsuarios();
        break;
    case 2:
        getUsuario();
        break;
    case 3:
        putUsuario();
        break;
    case 4:
        delUsuario();
        break;
}


function getUsuarios()
{
    $usuarios = R::findall('usuario');
    echo json_encode(R::exportAll($usuarios));
}

function getUsuario()
{
    $usuario = R::findOne('usuario', 'WHERE id = ?', [$_REQUEST["idUsuario"]]);
    echo json_encode($usuario);
}

function putUsuario()
{
    $usuario = R::findOne('usuario', 'WHERE id = ?', [$_REQUEST['idUsuario']]);
    $usuario = $usuario ?? R::dispense('usuario');
    $usuario->nombre = $_REQUEST['nombre'];
    $usuario->telefono = $_REQUEST['telefono'];
    $usuario->pin = $_REQUEST['pin'];
    $usuario->perfil = $_REQUEST['perfil'];
    $id = R::store($usuario);
    echo json_encode($usuario);
}

function delUsuario()
{
    $usuario = R::dispense('usuario', 'WHERE id = ?', [$_REQUEST['idUsuario']]);
    $id = R::trash($usuario);
    echo json_encode($usuario); 
}   