document.addEventListener('DOMContentLoaded', () => {

    // --- Elementos del DOM ---
    const btnLogin = document.getElementById('tab-login');
    const btnRegistro = document.getElementById('tab-registro');
    const panelLogin = document.getElementById('panel-login');
    const panelRegistro = document.getElementById('panel-registro');
    
    const modalModel = document.getElementById('modal-model');
    const btnOpenModal = document.getElementById('btn-modal-model');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const btnCloseModalBottom = document.getElementById('btn-close-modal-bottom');

    const formLogin = document.getElementById('form-login');
    const formRegistro = document.getElementById('form-registro');
    const regPassInput = document.getElementById('reg-pass');

    const linkToRegistro = document.getElementById('link-to-registro');
    const linkToLogin = document.getElementById('link-to-login');
    const linkForgotPass = document.getElementById('link-forgot-pass');

    // --- Gestión de Pestañas (Tabs SPA) ---
    function switchTab(tabName) {
        if (tabName === 'login') {
            btnLogin.className = "py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 flex items-center justify-center space-x-2 bg-sakila-red text-white shadow-md";
            btnRegistro.className = "py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 flex items-center justify-center space-x-2 text-gray-400 hover:text-white";
            
            panelRegistro.classList.add('opacity-0', 'translate-x-8', 'hidden');
            panelRegistro.classList.remove('opacity-100', 'translate-x-0');
            
            panelLogin.classList.remove('hidden');
            setTimeout(() => {
                panelLogin.classList.remove('opacity-0', '-translate-x-8');
                panelLogin.classList.add('opacity-100', 'translate-x-0');
            }, 50);
        } else {
            btnRegistro.className = "py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 flex items-center justify-center space-x-2 bg-sakila-red text-white shadow-md";
            btnLogin.className = "py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 flex items-center justify-center space-x-2 text-gray-400 hover:text-white";
            
            panelLogin.classList.add('opacity-0', '-translate-x-8', 'hidden');
            panelLogin.classList.remove('opacity-100', 'translate-x-0');
            
            panelRegistro.classList.remove('hidden');
            setTimeout(() => {
                panelRegistro.classList.remove('opacity-0', 'translate-x-8');
                panelRegistro.classList.add('opacity-100', 'translate-x-0');
            }, 50);
        }
    }

    btnLogin.addEventListener('click', () => switchTab('login'));
    btnRegistro.addEventListener('click', () => switchTab('registro'));
    linkToRegistro.addEventListener('click', (e) => { e.preventDefault(); switchTab('registro'); });
    linkToLogin.addEventListener('click', (e) => { e.preventDefault(); switchTab('login'); });

    // --- Visibilidad de Contraseñas ---
    document.querySelectorAll('.btn-toggle-pass').forEach(button => {
        button.addEventListener('click', () => {
            const fieldId = button.getAttribute('data-toggle-pass');
            const input = document.getElementById(fieldId);
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bx bx-hide text-lg';
            } else {
                input.type = 'password';
                icon.className = 'bx bx-show text-lg';
            }
        });
    });

    // --- Medidor de Fortaleza de Contraseña ---
    regPassInput.addEventListener('input', () => {
        const val = regPassInput.value;
        const barra = document.getElementById('barra-fuerza');
        const texto = document.getElementById('texto-fuerza');

        if (!val) {
            barra.style.width = '0%';
            barra.className = 'h-full transition-all duration-300 rounded-full bg-gray-700';
            texto.innerText = 'Fortaleza de contraseña';
            return;
        }

        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        if (score === 1) {
            barra.style.width = '25%';
            barra.className = 'h-full transition-all duration-300 rounded-full bg-red-500';
            texto.innerText = 'Débil';
            texto.className = 'text-[10px] text-red-400 mt-1';
        } else if (score === 2 || score === 3) {
            barra.style.width = '65%';
            barra.className = 'h-full transition-all duration-300 rounded-full bg-sakila-amber';
            texto.innerText = 'Moderada';
            texto.className = 'text-[10px] text-amber-400 mt-1';
        } else if (score >= 4) {
            barra.style.width = '100%';
            barra.className = 'h-full transition-all duration-300 rounded-full bg-emerald-500';
            texto.innerText = 'Excelente (Segura)';
            texto.className = 'text-[10px] text-emerald-400 mt-1';
        }
    });

    // --- Modal Sakila ---
    const toggleModal = (show) => modalModel.classList.toggle('hidden', !show);

    btnOpenModal.addEventListener('click', () => toggleModal(true));
    btnCloseModal.addEventListener('click', () => toggleModal(false));
    btnCloseModalBottom.addEventListener('click', () => toggleModal(false));

    // --- Sistema de Notificaciones Inline ---
    function showNotification(message, type, formType = 'login') {
        const aviso = document.getElementById(`aviso-${formType}`);
        aviso.textContent = message;
        aviso.classList.remove('hidden');

        if (type === 'error') {
            aviso.className = 'text-xs p-3 rounded-lg text-center font-medium bg-red-950/80 border border-red-900 text-red-300';
        } else if (type === 'success') {
            aviso.className = 'text-xs p-3 rounded-lg text-center font-medium bg-emerald-950/80 border border-emerald-900 text-emerald-300';
        } else {
            aviso.className = 'text-xs p-3 rounded-lg text-center font-medium bg-amber-950/80 border border-amber-900 text-amber-300';
        }
    }

    linkForgotPass.addEventListener('click', (e) => {
        e.preventDefault();
        showNotification('Instrucción de recuperación enviada a tu correo de empleado.', 'info', 'login');
    });

    // --- Procesar Login ---
    formLogin.addEventListener('submit', (event) => {
        event.preventDefault();
        const usuario = document.getElementById('log-usuario').value.trim();
        const pass = document.getElementById('log-pass').value.trim();

        if (!usuario || !pass) {
            showNotification('Por favor, completa todos los campos de acceso.', 'error', 'login');
            return;
        }

        showNotification('¡Acceso concedido! Redirigiendo al panel de Sakila...', 'success', 'login');
        setTimeout(() => {
            showNotification(`Bienvenido al videoclub, ${usuario}. Sesión iniciada correctamente.`, 'success', 'login');
        }, 1200);
    });

    // --- Procesar Registro ---
    formRegistro.addEventListener('submit', (event) => {
        event.preventDefault();
        const nombre = document.getElementById('reg-nombre').value.trim();
        const apellido = document.getElementById('reg-apellido').value.trim();
        const email = document.getElementById('reg-email').value.trim();
        const usuario = document.getElementById('reg-usuario').value.trim();
        const pass = regPassInput.value.trim();
        const pass2 = document.getElementById('reg-pass2').value.trim();

        if (!nombre || !apellido || !email || !usuario || !pass || !pass2) {
            showNotification('Todos los campos son obligatorios para el registro en Sakila.', 'error', 'registro');
            return;
        }

        if (usuario.length < 3 || usuario.length > 16) {
            showNotification('El usuario debe tener entre 3 y 16 caracteres.', 'error', 'registro');
            return;
        }

        if (pass.length < 8) {
            showNotification('La contraseña debe tener al menos 8 caracteres.', 'error', 'registro');
            return;
        }

        if (pass !== pass2) {
            showNotification('Las contraseñas introducidas no coinciden.', 'error', 'registro');
            return;
        }

        showNotification(`¡Registro exitoso! Cuenta creada para ${nombre} ${apellido} en Sakila DB.`, 'success', 'registro');
        setTimeout(() => {
            switchTab('login');
            document.getElementById('log-usuario').value = usuario;
            showNotification('Ya puedes iniciar sesión con tu nuevo usuario.', 'success', 'login');
        }, 1800);
    });

    // Console logs explicativos DAW
    console.log("%c[Sakila DB Videoclub] Sistema SPA Front-End inicializado correctamente.", "color: #e50914; font-weight: bold; font-size: 14px;");
    console.log("Objeto SakilaUser listo para mapeo con AccesoDatos.php.");
});