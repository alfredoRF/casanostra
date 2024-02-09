let userdata = null;

function cargarMenu() { 
    let menu ='';

    menu+='<li><a href="pacientes.html"><i class="fas fa-hospital-user"></i>Pacientes</a></li>';

    if(userdata.perfil=='a'){
        menu+='<li><a href="materialmedico.html"><i class="fas fa-pills"></i>Material Medico</a></li>';
    }
    $("#left_menu").html(menu);    
}
function logout() {
    const url = "./dao/usuarios.php";
    const formData = new FormData();
    formData.append('action', '6')
    fetch(url, { method: "post", body: formData })
        .then((response) => response.json())
        .then((res) => {
            if(res.status = 'ok'){
                window.location.href = "index.html";
            }
        });
}

function sessionuserdata() {
    const url = "./dao/usuarios.php";
    const formData = new FormData();
    formData.append('action', '7')
    fetch(url, { method: "post", body: formData })
        .then((response) => response.json())
        .then((res) => {
            userdata = res.data;
            $('#nombre_usuario').html(userdata.nombre);
            cargarMenu();
        });
}
