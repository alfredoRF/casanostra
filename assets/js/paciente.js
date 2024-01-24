const urlStar = "https://controlcasanostra.develobit.com.mx"

function validarFormulario(form) {
    const required = [];
    Array.prototype.slice.call(form).forEach((elm) => {
        if (elm.hasAttribute('required')) {
            if (elm.value == "") {
                elm.classList.add("is-invalid");
                required.push(false);
            } else {
                elm.classList.remove("is-invalid");
                required.push(true);
            }
        }
    });
    return required;
}

function guardarPaciente() {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    let form = document.querySelector("#form-datospaciente");

    const required = validarFormulario(form);
    if (!required.includes(false)) {
        const formData = new FormData(form);
        formData.append('id', $("#formidpaciente").val());
        formData.append('action', '3');
        swal({
            title: "Guardando...",
            text: "Porfavor espere",
            //content: sweet_loader,
            buttons: false,
            closeOnClickOutside: false,
            closeOnEsc: false,
        });
        fetch(url, { method: "post", body: formData })
            .then(function (response) {
                return response.json()
            }).then(function (myJson) {
                swal.close();
                swal({
                    title: "Guardado",
                    text: "La informacion del paciente fue guardada",
                    icon: "success",
                    buttons: {
                        cancel: false,
                        confirm: "Aceptar",
                    },
                });
                getPaciente($("#formidpaciente").val());
                resetform("form-datospaciente");
            });
    }

}

function getPaciente(id) {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php"; 
    const formData = new FormData();
    formData.append('action', '4');
    formData.append('id', id);
    fetch(url, { method: "post", body: formData })
        .then((response) => response.json())
        .then((myJson) => {
            //console.log(myJson);
            let paciente = myJson.paciente
            $("#formidpaciente").val(paciente.id);
            $("#nombre").val(paciente.nombre);
            $("#nacionalidad").val(paciente.nacionalidad);
            $("#fechaingreso").val(paciente.fechaingreso);
            $("#sexo").val(paciente.sexo);
            $("#fecnac").val(paciente.fecnac);
            $("#peso").val(paciente.peso);
            $("#estatura").val(paciente.estatura);
            $("#responsable1").val(paciente.responsable1);
            $("#telefono1").val(paciente.telefono1);
            $("#email1").val(paciente.email1);
            $("#direccion1").val(paciente.direccion1);
            $("#responsable2").val(paciente.responsable2);
            $("#telefono2").val(paciente.telefono2);
            $("#email2").val(paciente.email2);
            $("#direccion2").val(paciente.direccion2);
            if (myJson.expediente.length == 0) {
                document.querySelector("#dwl_expediente").style.display = "none";
                document.querySelector("#expediente").style.display = "block";
            } else {
                document.querySelector("#dwl_expediente").style.display = "block";
                document.querySelector("#expediente").style.display = "none";
                // document.querySelector("#link_expediente").href = "http://control.lacasanostra.com.mx" + myJson.expediente[0].replace("..", "");
                document.querySelector("#link_expediente").href = urlStar + myJson.expediente[0].replace("..", "");
            }

        });
}

function guardarCondicion() {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    const form = document.querySelector("#form-condicionmedica");
    const required = validarFormulario(form);
    if (!required.includes(false)) {
        const formData = new FormData(form);
        let date = new Date();
        formData.append('fecha', date.toISOString().slice(0, 19).replace('T', ' '));
        formData.append('idpaciente', getIdPaciente());
        formData.append('action', '9');
        fetch(url, { method: "post", body: formData })
            .then((response) => response.json())
            .then((condicion) => {
                resetform("form-condicionmedica");
                $('#condicionesmodal').modal('hide');
                getCondicionesMedicas();
                swal({
                    title: 'Guardado',
                    text: 'La condicion fue guardada',
                    icon: 'success',
                    buttons: {
                        cancel: false,
                        conform: 'Aceptar'
                    }
                });
            });
    }

}

function getMedicacion() {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    const formData = new FormData();
    formData.append('paciente', getIdPaciente());
    formData.append('action', '6');
    fetch(url, { method: "post", body: formData })
        .then((response) => response.json())
        .then((medicaciones) => {
            //console.log(myJson)
            // console.log("creando tabla ---------------------");
            let table = $('#table-medicacion').DataTable();
            table.destroy();
            table = $('#table-medicacion').DataTable({
                data: medicaciones,
                columns: [
                    { data: "medicamento" },
                    { data: "dosis" },
                    { data: "frecuencia" },
                    { data: "termina" },
                    {
                        data: "id",
                        render: (data, type) => {
                            const btnaeditar = `<a href='#' class='btn btn-outline-primary btn-rounded' onclick='mostrarMedicacion(${data})'><i class='fas fa-eye' style='font-size:1.5em;'></i></a>`;
                            //return btnagregar+"&nbsp;"+btnsacar+"&nbsp;"+btnhistorico;
                            // console.log(data);
                            return btnaeditar;
                        }
                    }
                ],
                responsive: true,
                pageLength: 20,
                lengthChange: false,
                searching: true,
                ordering: true
            });
            // console.log("tabla reada >>>>>>>>>>>>>>>>>>>>>");
        });
}

function guardarMedicacion() {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    const form = document.querySelector("#form-medicacion");
    let dias = "";
    document.getElementsByName("dia").forEach(el =>{
        if(el.checked){
            dias += el.value+",";
        }
        el.checked = false;
        
    });
    dias = dias.length > 0 ? dias.substring(0, dias.length - 1) : "";
    const required = validarFormulario(form);
    if (!required.includes(false)) {
        const formData = new FormData(form);
        let horarios = (document.querySelector("#horarios").innerHTML).replace(/:/g, "").replace(/ /g, ",");
        let date = new Date();
        let inicio = new Date(document.querySelector("#inicio").value);
        let termina = new Date(document.querySelector("#termina").value);
        formData.append('fecha', date.toISOString().slice(0, 19).replace('T', ' '));
        formData.set('inicio', inicio.toISOString().slice(0, 19).replace('T', ' '));
        formData.set('termina', termina.toISOString().slice(0, 19).replace('T', ' '));
        formData.append('paciente', getIdPaciente());
        formData.append("horarios", horarios);
        formData.append("dias", dias);
        formData.append('action', '5');
        document.querySelector("#seccion_dias").style.display = "none";
        fetch(url, { method: "post", body: formData })
            .then((response) => response.json())
            .then((medicacion) => {
                resetform("form-medicacion");
                $('#medicacionmodal').modal('hide');
                getMedicacion();
                swal({
                    title: 'Guardado',
                    text: 'El medicamento fue agregado',
                    icon: 'success',
                    buttons: {
                        cancel: false,
                        conform: 'Aceptar'
                    }
                });
            });
    }

}

function getCondicionesMedicas() {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    const formData = new FormData();
    formData.append('idpaciente', getIdPaciente());
    formData.append('action', '10');
    fetch(url, { method: "post", body: formData })
        .then(function (response) {
            return response.json();
        })
        .then(function (myJson) {
            console.log(myJson)
            let table = $('#table-condicionesmedicas').DataTable();
            table.destroy();
            table = $('#table-condicionesmedicas').DataTable({
                data: myJson,
                columns: [
                    { data: "fecha" },
                    { data: "titulo" },
                    { data: "descripcion" },
                    {
                        data: "id",
                        render: function (data, type) {
                            var btnaeditar = `<a href='#' class='btn btn-outline-primary btn-rounded' onclick='getCondicion(${data})'><i class='fas fa-eye' style='font-size:1.5em;'></i></a>`;
                            //return btnagregar+"&nbsp;"+btnsacar+"&nbsp;"+btnhistorico;
                            return btnaeditar;
                        }
                    }
                ],
                responsive: true,
                pageLength: 20,
                lengthChange: false,
                searching: true,
                ordering: true
            });
        });
}

function getNotas() {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    const formData = new FormData();
    formData.append('paciente', getIdPaciente());
    formData.append('action', '11');
    fetch(url, { method: "post", body: formData })
        .then((response) => response.json())
        .then((notas) => {
            let table = $('#table-notas').DataTable();
            table.destroy();
            table = $('#table-notas').DataTable({
                data: notas,
                columns: [
                    { data: "fecha" },
                    { data: "nota" },
                    {
                        data: "id",
                        render: function (data, type) {
                            var btnaeditar = `<a href='#' class='btn btn-outline-primary btn-rounded' onclick='getNota(${data})'><i class='fas fa-eye' style='font-size:1.5em;'></i></a>`;
                            //return btnagregar+"&nbsp;"+btnsacar+"&nbsp;"+btnhistorico;
                            return btnaeditar;
                        }
                    }
                ],
                responsive: true,
                pageLength: 20,
                lengthChange: false,
                searching: true,
                ordering: true
            });
        });
}

function guardarNota() {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    const form = document.querySelector("#form-nota");
    const required = validarFormulario(form);
    if (!required.includes(false)) {
        const formData = new FormData(form);
        let date = new Date();
        formData.append('fecha', date.toISOString().slice(0, 19).replace('T', ' '));
        formData.append('patient', getIdPaciente());
        formData.append('action', '12');
        swal({
            title: "Guardando...",
            text: "Porfavor espere",
            //content: sweet_loader,
            buttons: false,
            closeOnClickOutside: false,
            closeOnEsc: false,
        });
        $('#notamodal').modal('hide');
        fetch(url, { method: "post", body: formData })
            .then((response) => response.json())
            .then((res_nota) => {
                swal.close();
                //$('#notamodal').modal('hide');
                resetform("form-nota");
                if (res_nota.file_upload) {
                    swal({
                        title: 'Guardado',
                        text: 'La nota fue guardada',
                        icon: 'success',
                        buttons: {
                            cancel: false,
                            confirm: 'Aceptar'
                        }
                    }).then(() => {
                        getNota(res_nota.nota.id);
                    });

                } else {
                    $("#id_nota").val("");

                    swal({
                        title: 'Guardado',
                        text: 'La nota fue guardada',
                        icon: 'success',
                        buttons: {
                            cancel: false,
                            confirm: 'Aceptar'
                        }
                    });
                }

            });
    }
}

function getIdPaciente() {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    let idpaciente = urlParams.get('paciente');
    // console.log("regreando", idpaciente)
    return idpaciente;
}

function getTratamientoPaciente() {
    getListaMedicamentos();
}

function getTratamientos(){
    // const url="http://control.lacasanostra.com.mx/dao/patient.dao";
    const url = urlStar + "/dao/patient.dao";
    const formData = new FormDtata();
    formData.append("action", "12");
    fetch(url, { method: "post", body: formData })
    .then(response => response.json())
    .then();
}

function getListaMedicamentos() {
    // const url = "http://control.lacasanostra.com.mx/dao/materialmedico.php";
    const url = urlStar + "/dao/materialmedico.php";
    const formData = new FormData();
    formData.append('action', '1');
    fetch(url, { method: "post", body: formData })
        .then(function (response) {
            return response.json();
        })
        .then(function (myJson) {
            var items = "";
            $.each(myJson, function (key, value) {
                items += "<option value='" + value.nombre + "'>";
            });
            $("#listamedicamentos").html(items);
        });
}

function getSignosVitales() {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    const formData = new FormData();
    formData.append('paciente', getIdPaciente());
    formData.append('action', '13');
    fetch(url, { method: "post", body: formData })
        .then((response) => response.json())
        .then((signos) => {
            let table = $('#table-signosVitales').DataTable();
            table.destroy();
            table = $('#table-signosVitales').DataTable({
                data: signos,
                columns: [
                    { data: "lpm" },
                    { data: "rpm" },
                    { data: "sys" },
                    { data: "dia" },
                    { data: "temperatura" },
                    {
                        data: "eva", render: (data, type) => {
                            data = parseInt(data);
                            let info;
                            switch (data) {
                                case 1: info = "Sin dolor"; break;
                                case 2: info = "Dolor leve"; break;
                                case 3: info = "Dolor moderado"; break;
                                case 4: info = "Dolor severo"; break;
                                case 5: info = "Dolor muy severo"; break;
                                case 6: info = "Maximo dolor"; break;
                            }
                            return info;
                        }
                    },
                    {
                        data: "id",
                        render: (data, type) => {
                            let btnaeditar = `<a href='#' class='btn btn-outline-primary btn-rounded' onclick='getSignoVital(${data})'><i class='fas fa-eye' style='font-size:1.5em;'></i></a>`;
                            //return btnagregar+"&nbsp;"+btnsacar+"&nbsp;"+btnhistorico;
                            return btnaeditar;
                        }
                    }
                ],
                responsive: true,
                pageLength: 20,
                lengthChange: false,
                searching: true,
                ordering: true
            });
        });
}


function gaurdaSignoVital() {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    const form = document.querySelector("#form-signovital");
    const required = validarFormulario(form);
    if (!required.includes(false)) {
        const formData = new FormData(form);
        let date = new Date();
        formData.append('fecha', date.toISOString().slice(0, 19).replace('T', ' '));
        formData.append('paciente', getIdPaciente());
        formData.append('action', '14');
        fetch(url, { method: "post", body: formData })
            .then((response) => response.json())
            .then((signovital) => {
                resetform("form-signovital");
                $('#signovitalModal').modal('hide');
                getSignosVitales();
                swal({
                    title: 'Guardado',
                    text: 'Los signos vitales fueron guardados',
                    icon: 'success',
                    buttons: {
                        cancel: false,
                        confirm: 'Aceptar'
                    }
                });
            });
    }
}
function mostrarMedicacion() { }
function getCondicion() { }
function getNota(id) {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    // let idPaciente = getIdPaciente();
    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', '16');
    fetch(url, { method: "post", body: formData })
        .then((response) => response.json())
        .then((res_nota) => {
            const nota = res_nota.nota;
            const archivos = res_nota.archivos.url;
            const thumbnail = res_nota.archivos.thumbnail;
            $("#id_nota").val(nota.id);
            $("#nota").val(nota.nota);
            let panelArchivos = '<div class="row">';
            document.querySelector("#archivo_text").innerHTML = `${archivos.length} de 5`;
            if (archivos.length > 0) {
                archivos.forEach((archivo) => {
                    archivo = archivo.replace("..", "");
                    let path = "http://control.lacasanostra.com.mx" + archivo;
                    panelArchivos += `<div class="mb-3 col-2"><label><strong>${archivo.split("/")[2]}</strong> <a href="${path}" class="btn btn-primary"><i class="fas fa-file-download"></i></a></label></div>`;
                });
            }
            panelArchivos += '</div>';
            if (archivos.length == 5) {
                document.querySelector("#archivo_nota").disabled = true;
            } else {
                document.querySelector("#archivo_nota").disabled = false;
            }
            document.querySelector("#archivos_nota").innerHTML = panelArchivos;
            $("#notamodal").modal("show");
        });
}

function prepararFormNota() {
    resetform('form-nota');
    $('#id_nota').val('');
    document.querySelector("#archivo_nota").disabled = false;
    document.querySelector("#archivos_nota").innerHTML = "";
}
function resetform(form) {
    $("#" + form)[0].reset();
}
function loadForm(data) {
    Object.keys(data).forEach(key => {
        // console.log(key, obj[key]);
        $("#" + key).val(data[key]);
    });

    // $("#" + )
}

function borrarExpediente() {
    swal({
        title: "Eliminar Expediente",
        text: "El expediente sera borrado",
        icon: "warning",
        buttons: ["Cancelar", "Borrar"],
        dangerMode: true,
    }).then((res) => {
        if (res) {
            // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
            const url = urlStar + "/dao/patient.php";
            let idPaciente = getIdPaciente();
            const formData = new FormData();
            formData.append('id', idPaciente);
            formData.append('action', '15');
            fetch(url, { method: "post", body: formData })
                .then((response) => response.json())
                .then((eliminar) => {
                    if (eliminar.status) {
                        swal({
                            title: 'Expediente eliminado',
                            icon: 'success',
                            buttons: {
                                cancel: false,
                                confirm: 'Aceptar'
                            }
                        });
                        getPaciente(idPaciente);
                    } else {
                        swal({
                            title: 'A ocurrido un error!',
                            text: "Revise su conexión de internet e inténtelo nuevamente",
                            icon: 'error',
                            buttons: {
                                cancel: false,
                                confirm: 'Aceptar'
                            }
                        });
                    }
                })
                .catch(error => {

                    swal({
                        title: 'A ocurrido un error!',
                        text: "Revise su conexión de internet e inténtelo nuevamente",
                        icon: 'error',
                        buttons: {
                            cancel: false,
                            confirm: 'Aceptar'
                        }
                    });
                });
        }
    });
}

function addHoras(){
    
    let hora = document.querySelector("#getHora").value;
    if(hora){
        // document.querySelector("#hora").value += ","+hora.replace(":", "");
        document.querySelector("#horarios").innerHTML += hora + " "
    }
    
}
function habilitarDias(){
    let habilitado = document.querySelector("#frecuencia").value;
    // alert(habilitado);
    if(habilitado == "DE"){
        document.querySelector("#seccion_dias").style.display = "block";
    } else {
        document.querySelector("#seccion_dias").style.display = "none";
        document.getElementsByName("dia").forEach(el =>{
            el.checked = false;
        });
    }
}