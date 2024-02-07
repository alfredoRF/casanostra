
$(() => {
    cargarMenu();
});

function cargarMenu() { 
    const url = "/casanostra/dao/usuarios.php";
    const formData = new FormData();
    formData.append('action', '7')
    fetch(url, { method: "post", body: formData })
        .then((response) => response.json())
        .then((res) => {
            if(res.usuario){
                document.querySelector("#left_menu").innerHTML = `<li>
                <a href="pacientes.html"><i class="fas fa-hospital-user"></i>Pacientes</a>
            </li>
            <li>
                <a href="materialmedico.html"><i class="fas fa-pills"></i>Material Medico</a>
            </li>`;
            document.querySelector("#nombre_usuario").innerHTML = res.usuario.nombre.toUpperCase();
            } else {
                window.location.href = "login.html";
            }
        });
}
function logout() {
    const url = "/casanostra/dao/usuarios.php";
    const formData = new FormData();
    formData.append('action', '6')
    fetch(url, { method: "post", body: formData })
        .then((response) => response.json())
        .then((logOut) => {
            if(!logOut.idUsuario){
                window.location.href = "login.html";
            }
        });
}
