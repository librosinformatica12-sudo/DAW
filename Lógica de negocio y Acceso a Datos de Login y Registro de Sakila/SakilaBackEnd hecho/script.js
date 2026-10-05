function validarFormulario() {
    var contrasena = document.getElementById("contrasena").value;
    var confirmaContrasena = document.getElementById("confirmaContrasena").value;

    if (contrasena !== confirmaContrasena) {
        alert("Las contraseñas no coinciden. Por favor, inténtalo de nuevo.");
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', () => {
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) {
        btnLogout.addEventListener('click', () => {
            window.location.href = 'logout.php';
        });
    }
});