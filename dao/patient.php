<?php
// header('Access-Control-Allow-Origin: *');
// header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
// header("Allow: GET, POST, OPTIONS, PUT, DELETE");


require_once 'conection.php';
session_start();

$action = $_REQUEST['action'];

if(!sessionStatus()){
    //header("Location: ../");
    //die();
}

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
        putActividades();
        break;
    case 23:
        getLaboratorios();
        break;
    case 24:
        putLaboratorio();
        break;
    case 25:
        getCitas();
        break;
    case 26:
        putCita();
        break;
    case 27:
        borrarFotoNota();
        break;
}

/**funciones para pacientes */
function getAll()
{
    $pacientes = R::findAll('paciente');
    echo json_encode(array_values($pacientes));
}

function getPaciente()
{
    $paciente = R::findOne('paciente', 'id=?', [$_REQUEST['id']]);
    $files = glob("../expedientes/Expediente_medico-" . $paciente->id . "*");
    echo json_encode(["paciente" => $paciente, "expediente" => $files]);
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
/**fin pacientes */


/**Funciones para condiciones medicas */
function getCondiciones()
{
    $condiciones = R::find('condiciones', 'paciente=?', [$_REQUEST['idpaciente']]);
    echo json_encode(array_values($condiciones));
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

function putPacienteConditions()
{
    $pacienteconditions = R::dispense('condiciones');
    $pacienteconditions->paciente = $_REQUEST['paciente'];
    $pacienteconditions->titulo = $_REQUEST['titulo'];
    $pacienteconditions->descripcion = $_REQUEST['descripcion'];
    $id = R::store($pacienteconditions);
    echo json_encode($pacienteconditions);
}

function getPacienteConditions()
{
    $pacienteconditions = R::find('condiciones', 'paciente=?', [$_REQUEST['paciente']]);
    echo json_encode($pacienteconditions);
}

/**Fin condiciones medicas */

/**funciones para medicacion */
function getMedicaciones()
{
    $medicaciones = R::getAll('SELECT  mm.nombre AS medicamento, m.dosis, m.unidad, m.frecuencia, m.dias, m.termina, m.id FROM medicacion m INNER JOIN materialmedico mm ON m.medicamento = mm.id WHERE m.paciente = ? AND m.status = ?', [$_REQUEST['paciente'], 1]);
    echo json_encode(array_values($medicaciones));
}

function getMedicacion()
{
    $medicacion = R::getAll('SELECT m.id, mm.nombre AS medicamento, c.titulo AS condicion, m.dosis, m.unidad, m.via, m.frecuencia, m.termina, m.inicio, m.observacion, m.dias, m.horarios FROM medicacion m INNER JOIN materialmedico mm ON m.medicamento = mm.id INNER JOIN condiciones c ON m.condicion = c.id WHERE m.id = ?', [$_REQUEST["id"]]);
    echo json_encode($medicacion);
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
    if($_REQUEST['termina']){
        $medicacion->termina = $_REQUEST['termina'];
    }
    if($_REQUEST['inicio']){
        $medicacion->inicio = $_REQUEST['inicio'];
    }
    $medicacion->fecha = $_REQUEST['fecha'];
    $medicacion->horarios = $_REQUEST['horarios'];
    $medicacion->dias = $_REQUEST['dias'];
    $medicacion->frecuencia = $_REQUEST['frecuencia'];
    $medicacion->unidad = $_REQUEST['unidad'];
    $medicacion->status = $_REQUEST["status"];
    $id = R::store($medicacion);
    echo json_encode($medicacion);
}

function actualisarStatusMedicacion()
{
    $medicacion = R::findOne('medicacion', 'id=?', [$_REQUEST['id']]);
    $medicacion->status = $_REQUEST["status"];
    $medicacion->causa = $_REQUEST["causa"];
    $id = R::store($medicacion);
    echo json_encode($medicacion);
}

function getInfoMedicacion()
{
    $condiciones = R::find('condiciones', 'paciente=?', [$_REQUEST['paciente']]);
    $medicaciones = R::getAll('SELECT  mm.nombre AS medicamento, m.dosis, m.frecuencia, m.termina, m.id FROM medicacion m INNER JOIN materialmedico mm ON m.medicamento = mm.id WHERE m.paciente = ?', [$_REQUEST['paciente']]);
    echo json_encode(["condiciones" => $condiciones, "medicacion" => $medicaciones]);
}
/**fin funciones medicacion */


/**funciones para actividades */
function getActives()
{
    $pacientes = R::find('paciente', 'status=1');
    echo json_encode($pacientes);
}

function getActividades()
{
    $actividades = R::findOne('actividades', 'id = ?', [$_REQUEST["id"]]);
    echo json_encode($actividades);
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
/**fin actividades */



/**funcioes para notas */
function getNotas()
{
    $notas = R::find("notas", "patient = ?", [$_REQUEST["paciente"]]);
    echo json_encode(array_values($notas));
}

function putNota(){
    $nota = R::findOne('notas', 'id = ?', [$_REQUEST['id']]);
    if ($nota == null) {
        $nota = R::dispense('notas');
    }
    $nota->nota = $_REQUEST["nota"];
    $nota->fecha = $_REQUEST["fecha"];
    $id = R::store($nota);
    $extention = explode('.', $_FILES['archivo_nota']['name']);
    $ruta = '../expedientes/Nota_' .$id."_" . time() .".". $extention[count($extention) - 1];
    $status = move_uploaded_file($_FILES['archivo_nota']['tmp_name'], $ruta);
    echo json_encode(["nota" => $nota, "file_upload" => ["archivo" => $ruta, "status"=>$status]]);
}

function getNota()
{
    $nota = R::findOne('notas', 'id = ?', [$_REQUEST['id']]);
    $files = glob("../expedientes/Nota_" . $nota->id . "*");
    echo json_encode(["nota" => $nota, "archivos" => $files]);
}
/**fin notas */


/**funciones para tratamiento medico */
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


/**funciones medicalReport */
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

function removeMedicalReport()
{
    $medicalreport = R::findOne('medicalpaciente', 'id=?', [$_REQUEST['id']]);
    R::trash($medicalreport);
    echo json_encode($medicalreport);
}
/**fin medicalReport */



function getPacienteTask()
{
    $pacientetasks = R::find('pacientetask', 'paciente=? AND tasktype=?', [$_REQUEST['paciente'], $_REQUEST['tasktype']]);
    echo json_encode($pacientetasks);
}

/**funciones para signos vitales */
function getSignosVitales()
{
    $signosVitales = R::find("signosvitales", "paciente = ? ", [$_REQUEST['paciente']]);
    echo json_encode(R::exportAll($signosVitales));
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
    $signoVital->glucosa = $_REQUEST['glucosa'];
    $signoVital->spo2 = $_REQUEST['spo2'];
    $signoVital->peso = $_REQUEST['peso'];
    $signoVital->observaciones = $_REQUEST['observaciones'];
    $signoVital->temperatura = $_REQUEST['temperatura'];
    $signoVital->fecha = $_REQUEST['fecha'];
    $id = R::store($signoVital);
    echo json_encode($signoVital);
}
/**fin signos vitales */

/**funcines para laboratorios */
function getLaboratorios()
{
    $laboratorios = R::find("laboratorios", "paciente = ?", [$_REQUEST["paciente"]]);
    echo json_encode(R::exportAll($laboratorios));
}

function putLaboratorio()
{
    $laboratorio = R::dispense("laboratorios");
    $laboratorio->paciente = $_REQUEST["paciente"];
    $laboratorio->fechacaptura = $_REQUEST["fechacaptura"];
    $laboratorio->descripcion = $_REQUEST["descripcion"];
    $id = R::store($laboratorio);
    $path = '../expedientes/Laboratorio_'.$id.".pdf";
    $statusTemp = move_uploaded_file($_FILES['doc_laboratorio']['tmp_name'], $path);
    echo json_encode(["laboratorio"=>$laboratorio, "archivo"=>$statusTemp]);
}
/** fin laboratorios*/

/**funcines para citas */
function getCitas()
{
    $citas = R::find("cita", "paciente = ?", [$_REQUEST["paciente"]]);
    echo json_encode(R::exportAll($citas));
}

function putCita()
{
    $cita = R::dispense("cita");
    $cita->paciente = $_REQUEST["paciente"];
    $cita->fecha = $_REQUEST["fecha"];
    $cita->descripcion = $_REQUEST["descripcion"];
    $id = R::store($cita);
    // $path = '../expedientes/Laboratorio_'.$id.".pdf";
    // $statusTemp = move_uploaded_file($_FILES['archivo_nota']['tmp_name'], $path);
    echo json_encode($cita);
}
/** fin citas*/

/**funciones comunes */
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

function borrarFotoNota()
{
    $archivo = "..".$_REQUEST["archivo"];
    $status=false;
    if (file_exists($archivo)) {
        chmod($archivo, 0755); 
        $status = unlink($archivo); 
    }
    echo json_encode(["status" => $status,"archivo"=>$archivo]);
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

//   } resizer($_, $path, $rs)
function resizer ($source, $destination, $size, $quality=null){
    // $source - Original image file
    // $destination - Resized image file name
    // $size - Single number for percentage resize
    //         Array of 2 numbers for fixed width + height
    // $quality - Optional image quality. JPG & WEBP = 0 to 100, PNG = -1 to 9
    
      // (A) CHECKS
      if (!file_exists($source)) { throw new Exception("Source image file not found"); }
      $sExt = strtolower(pathinfo($source)["extension"]);
      $dExt = strtolower(pathinfo($destination)["extension"]);
      $allowed = ["bmp", "gif", "jpg", "jpeg", "png", "webp"];
      if (!in_array($sExt, $allowed)) { throw new Exception("$sExt - Invalid image file type"); }
      if (!in_array($dExt, $allowed)) { throw new Exception("$dExt - Invalid image file type"); }
      if ($quality != null) {
        if (in_array($dExt, ["jpg", "jpeg", "webp"]) && ($quality<0 || $quality>100)) { $quality = 70; }
        if ($dExt == "png" && ($quality<-1 || $quality>9)) { $quality = -1; }
        if (!in_array($dExt, ["png", "jpg", "jpeg", "webp"])) { $quality = null; }
      }
    
      // (B) NEW IMAGE DIMENSIONS
      if (is_array($size)) {
        $new_width = $size[0];
        $new_height = $size[1];
      } else {
        $dimensions = getimagesize($source);
        $new_width = ceil(($size/100) * $dimensions[0]);
        $new_height = ceil(($size/100) * $dimensions[1]);
      }
    
      // (C) RESIZE
    //   $fnCreate = "imagecreatefrom" . ($sExt=="jpg" ? "jpeg" : $sExt);
    //   $original = $fnCreate($source);
    //   $resized = imagescale($original, $new_width, $new_height, IMG_BICUBIC);
      
    //   // (D) OUTPUT & CLEAN UP
    //   $fnOutput = "image" . ($dExt=="jpg" ? "jpeg" : $dExt);
    //   if (is_numeric($quality)) {
    //     $fnOutput($resized, $destination, $quality);
    //   } else {
    //     $fnOutput($resized, $destination);
    //   }
    //   imagedestroy($original);
    //   imagedestroy($resized);
    //   return $fnOutput;
    return ["image"];
    }
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


function sessionStatus(){
    if(isset($_SESSION['usuario'])){
        return true;
    }else{
        return false;
    } 
}
/**fin funciones comunes */