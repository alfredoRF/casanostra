// const urlStar = "https://controlcasanostra.develobit.com.mx"; //servidor
let urlStar = "./"; //local

function validarFormulario(form) {
    const required = [];
    Array.prototype.slice.call(form).forEach((elm) => {
        if (elm.hasAttribute('required') && !elm.disabled) {
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

function guardarCondicion(tipo = 'c') {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    const form = tipo == "m" ? document.querySelector("#form_condicion_m") : document.querySelector("#form-condicionmedica");
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
                if (tipo == "m") {
                    resetform("form_condicion_m");
                    $("#nueva_condicion_modal").modal("hide");
                    $("#medicacionmodal").modal("show");
                    selectCondiciones();
                }
                else {
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
                }

            });
    }

}

function nuevaCondicion() {
    document.querySelector("#form_condicion_m").reset();
    // document.querySelector("#condicion_tipo").value = tipo;
    $("#medicacionmodal").modal("hide");
    $("#nueva_condicion_modal").modal("show");
}

function getMedicacion(tipo) {
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
            let paciente = document.querySelector("#nombre").value;
            let table = $('#table-medicacion').DataTable();
            table.destroy();
            table = $('#table-medicacion').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'pdfHtml5',
                    filename: "Medicacion de paciente " + paciente,
                    title: "Medicacion para " + paciente,
                    orientation: 'portrait',
                    pageSize: 'A4',
                    text: "<i class='fas fa-print'></i>"
                }],
                data: medicaciones,
                columns: [
                    { data: "medicamento" },
                    {
                        data: { dosis: "dosis", unidad: "unidad" }, render: (data, type) => {
                            return data.dosis + " " + (data.unidad === 'TB' ? "TABLETAS" : data.unidad);
                        }
                    },
                    {
                        data: { frecuencia: "frecuencia", dias: "dias" }, render: (data, type) => {
                            let resp = "";
                            switch (data.frecuencia) {
                                case 'PRN':
                                case 'DIARIO': resp = data.frecuencia; break;
                                case "DE":
                                    if (data.dias) {
                                        data.dias.split(",").forEach(dia => {
                                            switch (dia) {
                                                case "l": resp += "Lunes, "; break;
                                                case "m": resp += "Martes, "; break;
                                                case "x": resp += "Miercoles, "; break;
                                                case "j": resp += "Jueves, "; break;
                                                case "v": resp += "Viernes, "; break;
                                                case "s": resp += "Sabado, "; break;
                                                case "d": resp += "Domingo, "; break;
                                            }
                                        });
                                        resp = resp.length > 0 ? resp.substring(0, resp.length - 2) : "";
                                    }
                                    break;


                            }
                            return resp;
                        }
                    },
                    { data: "termina", render: (data) => data ?? "SIN TERMINO" },
                    {
                        data: "id",
                        render: (data, type) => {
                            const btnaeditar = tipo === "p" ? `<a href='#' class='btn btn-outline-primary btn-rounded' onclick='mostrarMedicacion(${data})'><i class='fas fa-info-circle' style='font-size:1.5em;'></i></a>`
                                : `<a href='#' class='btn btn-outline-primary btn-rounded' onclick='darAplicacion(${data})'><i class='fas fa-pills' style='font-size:1.5em;'></i></a>`;
                            //return btnagregar+"&nbsp;"+btnsacar+"&nbsp;"+btnhistorico;
                            // console.log(data);
                            return btnaeditar;
                        }
                    }
                ],
                responsive: true,
                pageLength: 20,
                lengthChange: false,
                // searching: true,
                ordering: true,

            });
            // console.log("tabla reada >>>>>>>>>>>>>>>>>>>>>");
        });
}

function guardarMedicacion() {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    const form = document.querySelector("#form-medicacion");

    const required = validarFormulario(form);
    if (!required.includes(false)) {
        let dias = "";
        document.getElementsByName("dia").forEach(el => {
            if (el.checked) {
                dias += el.value + ",";
            }
            el.checked = false;

        });
        dias = dias.length > 0 ? dias.substring(0, dias.length - 1) : "";
        const formData = new FormData(form);
        let horarios = document.querySelector("#horarios").innerHTML.replace(/:/g, "").replace(/ /g, "");
        let fhT = document.querySelector("#termina").value;
        let date = new Date();
        let fhI = document.querySelector("#inicio").value;
        let termina = fhT !== "" ? new Date(fhT) : null;
        let inicio = fhI !== "" ? new Date(fhI) : null;
        formData.append('fecha', date.toISOString().slice(0, 19).replace('T', ' '));
        formData.set('inicio', inicio ? inicio.toISOString().slice(0, 19).replace('T', ' ') : "");
        formData.set('termina', termina ? termina.toISOString().slice(0, 19).replace('T', ' ') : "");
        formData.append('paciente', getIdPaciente());
        formData.append("horarios", horarios);
        formData.append("dias", dias);
        formData.append('action', '5');
        formData.append('status', '1');
        document.querySelector("#seccion_dias").style.display = "none";
        fetch(url, { method: "post", body: formData })
            .then((response) => response.json())
            .then((medicacion) => {
                resetform("form-medicacion");
                $('#medicacionmodal').modal('hide');
                getMedicacion('p');
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
        .then(response => response.json())
        .then(myJson => {
            // console.log(myJson)
            let paciente = document.querySelector("#nombre").value;
            let table = $('#table-condicionesmedicas').DataTable();
            table.destroy();
            table = $('#table-condicionesmedicas').DataTable({
                // layaout: '<"top"<"left-col"B><"center-col"l><"right-col"f>>rtip',
                buttons: [{
                    extend: 'pdfHtml5',
                    filename: "Condiciones de paciente " + paciente,
                    title: "Condiciones de " + paciente,
                    orientation: 'portrait',
                    pageSize: 'A4',
                    text: "<i class='fas fa-print'></i>"
                }],
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

            }).catch(error => {
                swal.close();
                console.log(error);
                swal({
                    title: 'A ocurrido un error',
                    text: 'Compruebe su conexion a internet',
                    icon: 'error',
                    buttons: {
                        cancel: false,
                        confirm: 'Aceptar'
                    }
                });
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

function getTratamientos() {
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
            // alert("hola");
            // console.log(signos);
            let table = $('#tabla_signosVitales').DataTable();
            table.destroy();
            table = $('#tabla_signosVitales').DataTable({
                data: signos,
                columns: [
                    { data: "lpm"},
                    { data: "rpm" },
                    { data: "sys", render: data => data ? (data+" mmHg") : ""},
                    { data: "dia", render: data => data ? (data+" mmHg") : ""},
                    { data: "temperatura", render: data => data ? (data+"&#176;C") : "" },
                    { data: "glucosa", render: data => data ? (data+" MG/DL") : ""},
                    {data: "spo2", render: data => data ? (data+"%") : ""},
                    {data: "peso", render: data => data ? (data+" KG") : ""},
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
function mostrarMedicacion(id) {
    const url = urlStar + "/dao/patient.php";
    // alert("holas");
    // let id = getIdPaciente();
    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', '7');
    fetch(url, { method: "post", body: formData })
        .then((response) => response.json())
        .then((medicacion) => {
            medicacion = medicacion[0];
            let frecuencia = ""; let horarios = "";
            if (medicacion.frecuencia == "DE") {
                frecuencia = "Siertos dias de la semana";
                document.querySelector("#panel_dias_i").style.display = "inline-block";
                let dias = "";
                medicacion.dias.split(',').forEach(d => {
                    switch (d) {
                        case "l": dias += "Lunes, "; break;
                        case "m": dias += "Martes, "; break;
                        case "x": dias += "Miercoles, "; break;
                        case "j": dias += "Jueves, "; break;
                        case "v": dias += "Viernes, "; break;
                        case "s": dias += "Sabado, "; break;
                        case "d": dias += "Domingo, "; break;
                    }
                });
                dias = dias.length > 0 ? dias.substring(0, dias.length - 2) : "";
                document.querySelector("#dias_info").innerHTML = dias;
            } else {
                frecuencia = medicacion.frecuencia;
                document.querySelector("#panel_dias_i").style.display = 'none';
            }
            if (medicacion.horarios || medicacion.horarios !== "") {
                medicacion.horarios.split(",").forEach(h => {
                    horarios += h.slice(0, 2) + ":" + h.slice(2) + " ";
                });
            } else {
                horarios = "Sin Horario";
                // alert(horarios);
            }
            document.querySelector("#idMedicacion").value = medicacion.id;
            document.querySelector("#medicamento_i").value = medicacion.medicamento.toUpperCase();
            document.querySelector("#condicion_i").value = medicacion.condicion.toUpperCase();
            document.querySelector("#dosis_i").value = medicacion.dosis;
            document.querySelector("#unidad_i").innerHTML = medicacion.unidad;
            document.querySelector("#frecuencia_i").value = frecuencia;
            document.querySelector("#inicio_i").value = medicacion.inicio;
            document.querySelector("#termina_i").value = medicacion.termina;//horarios
            document.querySelector("#horarios_i").innerHTML = horarios;//descripcion_i
            document.querySelector("#observacion_i").value = medicacion.observacion;//descripcion_i
            $("#medicacion_info_modal").modal("show");
        });
}

function selectCondiciones() {
    // const url = "http://control.lacasanostra.com.mx/dao/patient.php";
    const url = urlStar + "/dao/patient.php";
    // alert("holas");
    let id = getIdPaciente();
    const formData = new FormData();
    formData.append('idpaciente', id);
    formData.append('action', '10');
    fetch(url, { method: "post", body: formData })
        .then((response) => response.json())
        .then((condiciones) => {
            // console.log(condiciones);
            let options = "<option value='' disabled selected>Selecciona una</option>";
            condiciones.forEach(condicion => {
                // alert(condicion.titulo);
                options += `<option value="${condicion.id}">${condicion.titulo.toUpperCase()}</option>`;
            });
            document.querySelector("#condicion").innerHTML = options;
        });
}

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
function borrarMedicacion(idMedicacion, medicamento) {
    $("#medicacion_info_modal").modal("hide");
    let content = document.createElement('div');
    content.innerHTML = '<div class="row col-12"><label for="causa" class="form-label">Causa de suspencion</lable><textarea rows="4" id="causa" name="causa" class="form-control"></textarea> </div>';
    swal({
        title: `El medicamento sera eliminado`,
        text: medicamento,
        icon: "warning",
        content: content,
        closeOnClickOutside: false,
        closeOnEsc: false,
        buttons: ["Cancelar", "Borrar"],
        dangerMode: true,
    }).then((res) => {
        if (res) {
            let causa = document.querySelector("#causa").value;
            const url = urlStar + "/dao/patient.php";
            const formData = new FormData();
            formData.append('id', idMedicacion);
            formData.append('status', 0);
            formData.append('causa', causa);
            formData.append('action', '18');
            fetch(url, { method: "post", body: formData })
                .then((response) => response.json())
                .then((medicacion) => {
                    swal.close();
                    getMedicacion('p');
                    swal({
                        title: 'Eliminado',
                        text: 'La medicacion fue eliminado',
                        icon: 'success',
                        buttons: {
                            cancel: false,
                            conform: 'Aceptar'
                        }
                    });
                });
        }
    });
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
function darAplicacion(medicacion) {
    const formData = new FormData();
    formData.append('action', "15");
    formData.append("medicacion", medicacion);


}
function addHoras() {

    let hora = document.querySelector("#getHora").value;
    if (hora) {
        // document.querySelector("#hora").value += ","+hora.replace(":", "");
        document.querySelector("#horarios").innerHTML += hora + " "
    }

}
function calcularHorario() {
    let fechaInicio = document.querySelector("#inicio");
    let veces = document.querySelector("#veces").value;
    if (fechaInicio.value) {
        // fechaInicio.parentNode.classList.remove("has-danger");
        fechaInicio.classList.remove("is-invalid");
        // alert(fechaInicio.value);
        let minutos = parseInt(1440 / veces);
        let fecha = new Date(fechaInicio.value);
        let html = `${fecha.getHours() < 10 ? "0" + fecha.getHours() : fecha.getHours()}:${fecha.getMinutes() < 10 ? "0" + fecha.getMinutes() : fecha.getMinutes()}`;
        for (let i = 1; i < veces; i++) {

            html += ", ";
            fecha.setMinutes(fecha.getMinutes() + minutos);
            let h = fecha.getHours();
            let m = fecha.getMinutes();
            html += `${h < 10 ? "0" + h : h}:${m < 10 ? "0" + m : m}`;
        }
        document.querySelector("#horarios").innerHTML = html;
    } else {
        // fechaInicio.parentNode.classList.add("has-danger");
        fechaInicio.classList.add("is-invalid");
    }
}

function habilitarDias() {
    let habilitado = document.querySelector("#frecuencia").value;
    // alert(habilitado);
    switch (habilitado) {
        case 'PRN':
            document.querySelector("#seccion_dias").style.display = "none";
            document.querySelector("#seccion_horario").style.display = "none";
            // document.querySelector("#seccion_inicioFin").style.display = "none";
            document.querySelector("#termina").disabled = true;
            document.querySelector("#inicio").disabled = true;
            break;
        case 'DIARIO':
            document.querySelector("#seccion_dias").style.display = "none";
            document.querySelector("#seccion_horario").style.display = "flex";
            // document.querySelector("#seccion_inicioFin").style.display = "flex";
            document.querySelector("#termina").disabled = false;
            document.querySelector("#inicio").disabled = false;
            break;
        case 'DE':
            document.querySelector("#seccion_dias").style.display = "block";
            document.querySelector("#seccion_horario").style.display = "flex";
            // document.querySelector("#seccion_inicioFin").style.display = "flex";
            document.querySelector("#termina").disabled = false;
            document.querySelector("#inicio").disabled = false;
            document.getElementsByName("dia").forEach(el => {
                el.checked = false;

            });
            break;
    }
}

function resetFormMedicacion() {
    document.querySelector('#form-medicacion').reset();
    document.querySelector("#seccion_dias").style.display = "none";
    document.querySelector("#seccion_horario").style.display = "none";
    document.querySelector("#termina").disabled = true;
    document.querySelector("#inicio").disabled = true;
    document.querySelector("#horarios").innerHTML = "";

}

function getActividades() { }