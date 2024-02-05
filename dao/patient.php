<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

try {

    require_once 'conection.php';

    $action = $_REQUEST['action'];

    switch ($action) {
        case 1:
            getAll();
            break;
        case 2:
            getActives();
            break;
        case 3:
            putPaciente();
            break;
        case 4:
            getPaciente();
            break;
        case 5:
            putMedicacion();
            break;
        case 6:
            getMedicaciones();
            break;
        case 7:
            getMedicacion();
            break;
        case 8:
            getTreatments();
            break;
        case 9:
            putCondicion();
            break;
        case 10:
            getCondiciones();
            break;
        case 11:
            getNotas();
            break;
        case 12:
            putNota();
            break;
        case 13:
            getSignosVitales();
            break;
        case 14:
            putSignoVital();
            break;
        case 15:
            eliminarExpediente();
            break;
        case 16:
            getNota();
            break;
        case 17:
            getInfoMedicacion();
            break;
        case 18:
            actualisarStatusMedicacion();
            break;
        case 19:
            getTratamiento();
            break;
        case 20:
            putTratamiento();
            break;
        /*case 21: Get para obtener actividades*/
        case 21:
            getActividades();
            break;
        /*Case 22: Put para poner actividades */
        case 22:
            putActividades;
            break;
    }
} catch (Throwable $e) {
    $responseData = array(
        "status" => "Error interno",
        "data" => $e->getLine() . ":" . $e->getMessage()
    );
    echo "error >>" . $e->getLine() . ":" . $e->getMessage();
}


function getAll()
{
    $pacientes = R::findAll('paciente');
    echo json_encode(array_values($pacientes));
}

function getCondiciones()
{
    $condiciones = R::find('condiciones', 'paciente=?', [$_REQUEST['idpaciente']]);
    echo json_encode(array_values($condiciones));
}

function getActives()
{
    $pacientes = R::find('paciente', 'status=1');
    echo json_encode($pacientes);
}

function getPaciente()
{
    $paciente = R::findOne('paciente', 'id=?', [$_REQUEST['id']]);
    $files = glob("../expedientes/Expediente_medico-" . $paciente->id . "*");
    echo json_encode(["paciente" => $paciente, "expediente" => $files]);
}

function getMedicaciones()
{
    $medicaciones = R::getAll('SELECT  mm.nombre AS medicamento, m.dosis, m.unidad, m.frecuencia, m.dias, m.termina, m.id FROM medicacion m INNER JOIN materialmedico mm ON m.medicamento = mm.id WHERE m.paciente = ? AND m.status = ?', [$_REQUEST['paciente'], 1]);
    echo json_encode(array_values($medicaciones));
}

function getMedicacion()
{
    $medicacion = R::findOne('medicacion', 'id = ?', [$_REQUEST["id"]]);
    echo json_encode($medicacion);
}

function getNotas()
{
    $notas = R::find("notas", "patient = ?", [$_REQUEST["paciente"]]);
    echo json_encode(array_values($notas));
}

function getActividades()
{
    $actividades = R::findOne('actividades', 'id = ?', [$_REQUEST["id"]]);
    echo json_encode($actividades);
}


function putPaciente()
{
    $paciente = R::findOne('paciente', 'id=?', [$_REQUEST['id']]);
    if ($paciente == null) {
        $paciente = R::dispense('paciente');
    }

    $columns = R::getAll('SHOW COLUMNS FROM paciente');

    foreach ($columns as $column) {
        if ($column["Field"] != 'id' && $column["Field"] != 'status') {
            if ($_REQUEST[$column["Field"]] != null) {
                $paciente[$column["Field"]] = $_REQUEST[$column["Field"]];
            }
        }
    }
    $id = R::store($paciente);
    $extention = explode('.', $_FILES['expediente']['name']);
    $pathS = '../expedientes/Expediente_medico-' . $id . "." . $extention[count($extention) - 1];
    $status = move_uploaded_file($_FILES['expediente']['tmp_name'], $pathS);
    $files = glob("../expedientes/Expediente_medico-" . $id . "*");
    echo json_encode(["paciente" => $paciente, "upload" => $status, "archivos" => $files]);
}

function putNota()
{

    $nota = R::findOne('notas', 'id = ?', [$_REQUEST['id']]);
    if ($nota == null) {
        $nota = R::dispense('notas');
    }
    $nota->nota = $_REQUEST["nota"];
    $nota->fecha = $_REQUEST["fecha"];
    $id = R::store($nota);
    //$tmp_files = $_FILES['file']['tmp_name'];

    $extention = explode('.', $_FILES['archivo_nota']['name']);
    $files = glob("../expedientes/Nota_*-" . $id . "*");
    $pathS = '../expedientes/Nota_' . (count($files) + 1) . '-' . $id . "." . $extention[count($extention) - 1];
    $fileDirTumb = '../expedientes/thumbnails/Nota_' . (count($files) + 1) . '-' . $id . "." . $extention[count($extention) - 1];
    $status = move_uploaded_file($_FILES['archivo_nota']['tmp_name'], $pathS);
    // $status = resizeIMG(1024, $_FILES['archivo_nota']['tmp_name'], $pathS);
    $statusT = resizeIMG(300, $_FILES['archivo_nota']['tmp_name'], $fileDirTumb); //status de imagen thumbnail
    echo json_encode(["nota" => $nota, "file_upload" => ["archivo" => $status, "thumbnail" => $statusT], "archivos" => $files]);
}

function getNota()
{
    $nota = R::findOne('notas', 'id = ?', [$_REQUEST['id']]);
    $files = glob("../expedientes/Nota_*-" . $nota->id . "*");
    $thumbmails = glob("../expedientes/thumbnails/Nota_*-" . $nota->id . "*");
    echo json_encode(["nota" => $nota, "archivos" => ["url" => $files, "thumbnail" => $thumbmails]]);
}

function putMedicacion()
{
    $medicacion = R::findOne('medicacion', "id=?", [$_REQUEST['idmedicacion']]);
    if ($medicacion == null) {
        $medicacion = R::dispense('medicacion');
    }
    $medicamento = R::findOne('materialmedico', 'nombre=?', [$_REQUEST['medicamento']]);
    $medicacion->paciente = $_REQUEST['paciente'];
    $medicacion->medicamento = $medicamento->id;
    $medicacion->condicion = $_REQUEST['condicion'];
    $medicacion->dosis = $_REQUEST['dosis'];
    $medicacion->frecuencia = $_REQUEST['frecuencia'];
    $medicacion->termina = $_REQUEST['termina'];
    $medicacion->inicio = $_REQUEST['inicio'];
    $medicacion->fecha = $_REQUEST['fecha'];
    $medicacion->horarios = $_REQUEST['horarios'];
    $medicacion->dias = $_REQUEST['dias'];
    $medicacion->frecuencia = $_REQUEST['frecuencia'];
    $medicacion->unidad = $_REQUEST['unidad'];
    $medicacion->status = $_REQUEST["status"];
    $id = R::store($medicacion);
    echo json_encode($medicacion);
}

function putActividades()
{
    $actividad = R::findOne('actividades', "id=?", [$_REQUEST['idactividades']]);
    if ($actividad == null) {
        $actividad = R::dispense('actividades');
    }
    $actividad->id= $_REQUEST['idactividades'];
    $actividad->nombre = $_REQUEST['nombre'];
    $actividad->descripcion = $_REQUEST['descripcion'];
    $actividad->inicia= $_REQUEST['inicia'];
    $actividad->termina = $_REQUEST['termina'];
    $actividad->horario = $_REQUEST['horario'];
    $id = R::store($actividad);
    echo json_encode($actividad);
}


function actualisarStatusMedicacion()
{
    $medicacion = R::findOne('medicacion', 'id=?', [$_REQUEST['id']]);
    $medicacion->status = $_REQUEST["status"];
    echo json_encode($medicacion);
}

function putCondicion()
{
    $condicion = R::findOne('condiciones', "id=?", [$_REQUEST['idcondicion']]);
    if ($condicion == null) {
        $condicion = R::dispense('condiciones');
    }
    $condicion->paciente = $_REQUEST['idpaciente'];
    $condicion->titulo = $_REQUEST['titulo'];
    $condicion->descripcion = $_REQUEST['descripcion'];
    $condicion->fecha = $_REQUEST['fecha'];
    $id = R::store($condicion);
    echo json_encode($condicion);
}

function getTratamiento()
{
    $medicacion = R::getAll("SELECT t.*, mm.nombre AS medicamento FROM tratamiento t INNER JOIN medicacion m ON t.medicacion = m.id INNER JOIN materialmedico mm ON m.medicamento = mm.id WHERE medicacion IN (SELECT id FROM medicacion WHERE paciente = ? AND status = 1)", [$_REQUEST['paciente']]);
    echo json_encode($medicacion);
}

function putTratamiento()
{
    $tratamiento = R::find("tratamiento", "id = ?", [$_REQUEST["idTratamiento"]]);
    if ($tratamiento == null) {
        $tratamiento = R::dispense('tratamiento');
    }
    $tratamiento->medicacion = $_REQUEST["medicacion"];
    $tratamiento->fechahora = $_REQUEST["fechahora"];
    $tratamiento->status = $_REQUEST["status"];
    $tratamiento->aplico = 1;
}

function getTreatments()
{
    $tratamientos = R::find('tratamiento', "paciente = ?", [$_REQUEST['idpaciente']]);
    foreach ($tratamientos as $tratamiento) {
        $medicamento = R::findOne("materialmedico", "id=?", [$tratamiento->medicamento]);
        $tratamiento->nombremedicamento = $medicamento->nombre;
        switch ($tratamiento->frecuencia) {
            case 'd':
                $tratamiento->periodo = "Todos los dias";
            case 't':
                $tratamiento->periodo = "Cada terces dia";
            case 's':
                $tratamiento->periodo = "Cada semana";
            case 'm':
                $tratamiento->periodo = "Cada mes";
        }
    }
    echo json_encode($tratamientos);
}

function putPacienteConditions()
{
    $pacienteconditions = R::dispense('condiciones');
    $pacienteconditions->paciente = $_REQUEST['paciente'];
    $pacienteconditions->titulo = $_REQUEST['titulo'];
    $pacienteconditions->descripcion = $_REQUEST['descripcion'];
    $id = R::store($pacienteconditions);
    echo json_encode($pacienteconditions);
}

function putMedicalReport()
{
    $medicalreport = R::dispense('medicalreport');
    $medicalreport->paciente = $_REQUEST['paciente'];
    $medicalreport->medical_situation = $_REQUEST['medical_situation'];
    $medicalreport->description = $_REQUEST['description'];
    $id = R::store($medicalreport);
    echo json_encode($medicalreport);
}

function getMedicalPaciente()
{
    $medicalspaciente = R::find('medicalpaciente', 'paciente=?', [$_REQUEST['idpaciente']]);
    echo json_encode($medicalspaciente);
}

function getMedicalReport()
{
    $medicalreport = R::find('medicalreport', 'paciente=?', [$_REQUEST['id']]);
    echo json_encode($medicalreport);
}

function getPacienteTask()
{
    $pacientetasks = R::find('pacientetask', 'paciente=? AND tasktype=?', [$_REQUEST['paciente'], $_REQUEST['tasktype']]);
    echo json_encode($pacientetasks);
}

function getPacienteConditions()
{
    $pacienteconditions = R::find('condiciones', 'paciente=?', [$_REQUEST['paciente']]);
    echo json_encode($pacienteconditions);
}

function removeMedicalReport()
{
    $medicalreport = R::findOne('medicalpaciente', 'id=?', [$_REQUEST['id']]);
    R::trash($medicalreport);
    echo json_encode($medicalreport);
}
function getSignosVitales()
{
    $signosVitales = R::getAll("SELECT lpm, rpm, sys, dia, temperatura, eva, id FROM signosvitales WHERE paciente = ? ", [$_REQUEST['paciente']]);
    echo json_encode($signosVitales);
}
function putSignoVital()
{
    $signoVital = R::findOne('signosvitales', "id=?", [$_REQUEST['idsignovital']]);
    if ($signoVital == null) {
        $signoVital = R::dispense('signosvitales');
    }
    $signoVital->paciente = $_REQUEST['paciente'];
    $signoVital->lpm = $_REQUEST['lpm'];
    $signoVital->rpm = $_REQUEST['rpm'];
    $signoVital->sys = $_REQUEST['sys'];
    $signoVital->dia = $_REQUEST['dia'];
    $signoVital->eva = $_REQUEST['eva'];
    $signoVital->temperatura = $_REQUEST['temperatura'];
    $signoVital->fecha = $_REQUEST['fecha'];
    $id = R::store($signoVital);
    echo json_encode($signoVital);
}

function eliminarExpediente()
{
    $id = $_REQUEST["id"];
    $path = glob("../expedientes/Expediente_medico-" . $id . "*")[0];
    $status = false;
    if (file_exists($path)) {
        chmod($path, 0755); //Change the file permissions if allowed
        $status = unlink($path); //remove the file
    }
    //$path = '../dist/pdf/polizas/'.$_REQUEST['num_eco'];
    echo json_encode(["status" => $status]);
}


/**
 * Funcion para guardar imagen comprimida y thumbnail
 */
// function subirImagen(){
//     // $servicio = $_REQUEST["servicio"];
//     // $cantidad = count(glob("../../cabina/dist/img/fotos/".$servicio."*.jpg"));
//     // $filename = $servicio."_".($cantidad+1);
//     $fileDir = '../../cabina/dist/img/fotos/'.$filename.".jpg";
//     $fileDirTumb = '../../cabina/dist/img/thumbnail/'.$filename.".jpg";
//     $statusO = false; $statusT = false;
//     $statusO = resizeIMG(800, $_FILES['file']['tmp_name'], $fileDir); //status de imagen original comprimida
//     $statusT = resizeIMG(300, $_FILES['file']['tmp_name'], $fileDirTumb); //status de imagen thumbnail
//     $cantidad = count(glob("../../cabina/dist/img/fotos/".$servicio."*.jpg"));

//     echo json_encode(["status"=>$statusO&&$statusT, "cantidad"=>$cantidad]);

//   }

/**
 * Funcion para cambiar resolucion de imagen
 */
function resizeIMG($thumbWidth, $sourceImg, $fileDir)
{
    $sourceImage = imagecreatefromjpeg($sourceImg);
    $orgWidth = imagesx($sourceImage);
    $orgHeight = imagesy($sourceImage);
    $thumbHeight = floor($orgHeight * ($thumbWidth / $orgWidth));
    $destImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
    imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $orgWidth, $orgHeight);
    $status  = imagejpeg($destImage, $fileDir);
    imagedestroy($sourceImage);
    imagedestroy($destImage);

    return $status;
}
function getInfoMedicacion()
{
    $condiciones = R::find('condiciones', 'paciente=?', [$_REQUEST['paciente']]);
    $medicaciones = R::getAll('SELECT  mm.nombre AS medicamento, m.dosis, m.frecuencia, m.termina, m.id FROM medicacion m INNER JOIN materialmedico mm ON m.medicamento = mm.id WHERE m.paciente = ?', [$_REQUEST['paciente']]);
    echo json_encode(["condiciones" => $condiciones, "medicacion" => $medicaciones]);
}
