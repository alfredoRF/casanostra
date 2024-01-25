function notificacion(tipo, titulo="", mensaje=""){
    swal.close();
    swal({
        title: 'Guardado',
        text: 'El medicamento fue agregado',
        icon: 'success',
        buttons: {
            cancel: false,
            conform: 'Aceptar'
        }
    });
}