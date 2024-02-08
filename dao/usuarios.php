<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

require_once 'conection.php';
session_start();

$action = $_REQUEST['action'];

if(!sessionStatus()&&$action!=5){
    header("Location: ../");
    die();
}

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
    case 5:
        login();
        break;
    case 6:
        logout();
        break;

    case 7:
        sessionData();
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

function login(){
    $usuario = R::findOne("usuario", "telefono = ?", [$_REQUEST['telefono']]);
    $status = 'error';
    $data = null;
    if(is_null($usuario)){
        $data = ['error'=>'telefono invalido!!!'];
    }else {
        if($usuario->pin == $_REQUEST["pin"]){
            $_SESSION['usuario'] = $usuario;
            $status = 'ok';
            $data = $usuario;
        } else {
            $data = ['error'=>'PIN invalido!!!'];
        }
    }
    $response =  [
        "status"=>$status, 
        "data"=>$data
    ];

    echo json_encode($response);
}

function logout(){
    unset($_SESSION['usuario']);
    $response =  [
        "status"=>"ok", 
        "data"=>null
    ];
    echo json_encode($response);
}

function sessionStatus(){
    if(isset($_SESSION['usuario'])){
        return true;
    }else{
        return false;
    } 
}

function sessionData(){
    $response =  [
        "status"=>"ok", 
        "data"=>$_SESSION['usuario']
    ];
    echo json_encode($response);
}