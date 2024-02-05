function notificacion(tipo, titulo="", mensaje=""){
    swal.close();
    switch(tipo){
        case "error": break;
        case "susses": break;
    }
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