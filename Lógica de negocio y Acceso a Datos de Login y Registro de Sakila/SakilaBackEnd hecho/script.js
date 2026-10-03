		//
        function validarFormulario() {
			var contrasena = document.getElementById("contrasena").value;
			var confirmaContrasena = document.getElementById("confirmaContrasena").value;

			if (contrasena !== confirmaContrasena) {
				alert("Las contraseñas no coinciden. Por favor, inténtalo de nuevo.");
				return false; // Evita que el formulario se envíe
			}
			return true; // Permite que el formulario se envíe
		}