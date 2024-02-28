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
    $rutaT = '../expedientes/thumbnail/Nota_' .$id."_" . time() .".". $extention[count($extention) - 1];
    $status = move_uploaded_file($_FILES['archivo_nota']['tmp_name'], $ruta);
    $statusT = createThumbnail($ruta, $rutaT, 400);
    echo json_encode(["nota" => $nota, "file_upload" => ["archivo" => $ruta, "status"=>$status, "status thumbnail"=>$statusT]]);
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


/**rezice image */
// Link image type to correct image loader and saver
// - makes it easier to add additional types later on
// - makes the function easier to read
const IMAGE_HANDLERS = [
    IMAGETYPE_JPEG => [
        'load' => 'imagecreatefromjpeg',
        'save' => 'imagejpeg',
        'quality' => 100
    ],
    IMAGETYPE_PNG => [
        'load' => 'imagecreatefrompng',
        'save' => 'imagepng',
        'quality' => 0
    ],
    IMAGETYPE_GIF => [
        'load' => 'imagecreatefromgif',
        'save' => 'imagegif'
    ]
];

/**
 * @param $src - a valid file location
 * @param $dest - a valid file target
 * @param $targetWidth - desired output width
 * @param $targetHeight - desired output height or null
 */
function createThumbnail($src, $dest, $targetWidth, $targetHeight = null) {

    // 1. Load the image from the given $src
    // - see if the file actually exists
    // - check if it's of a valid image type
    // - load the image resource

    // get the type of the image
    // we need the type to determine the correct loader
    $type = exif_imagetype($src);

    // if no valid type or no handler found -> exit
    if (!$type || !IMAGE_HANDLERS[$type]) {
        return null;
    }

    // load the image with the correct loader
    $image = call_user_func(IMAGE_HANDLERS[$type]['load'], $src);

    // no image found at supplied location -> exit
    if (!$image) {
        return null;
    }


    // 2. Create a thumbnail and resize the loaded $image
    // - get the image dimensions
    // - define the output size appropriately
    // - create a thumbnail based on that size
    // - set alpha transparency for GIFs and PNGs
    // - draw the final thumbnail

    // get original image width and height
    $width = imagesx($image);
    $height = imagesy($image);

    // maintain aspect ratio when no height set
    if ($targetHeight == null) {

        // get width to height ratio
        $ratio = $width / $height;

        // if is portrait
        // use ratio to scale height to fit in square
        if ($width > $height) {
            $targetHeight = floor($targetWidth / $ratio);
        }
        // if is landscape
        // use ratio to scale width to fit in square
        else {
            $targetHeight = $targetWidth;
            $targetWidth = floor($targetWidth * $ratio);
        }
    }

    // create duplicate image based on calculated target size
    $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

    // set transparency options for GIFs and PNGs
    if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG) {

        // make image transparent
        imagecolortransparent(
            $thumbnail,
            imagecolorallocate($thumbnail, 0, 0, 0)
        );

        // additional settings for PNGs
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
    }

    // copy entire source image to duplicate image and resize
    imagecopyresampled(
        $thumbnail,
        $image,
        0, 0, 0, 0,
        $targetWidth, $targetHeight,
        $width, $height
    );


    // 3. Save the $thumbnail to disk
    // - call the correct save method
    // - set the correct quality level

    // save the duplicate version of the image to disk
    return call_user_func(
        IMAGE_HANDLERS[$type]['save'],
        $thumbnail,
        $dest,
        IMAGE_HANDLERS[$type]['quality']
    );
}

/**fin rezice */


function sessionStatus(){
    if(isset($_SESSION['usuario'])){
        return true;
    }else{
        return false;
    } 
}
/**fin funciones comunes */