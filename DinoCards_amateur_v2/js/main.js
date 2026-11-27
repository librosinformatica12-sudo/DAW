/* Seccion de Login y Registro */
const accessView = document.getElementById('access-view');
const loginTab = document.getElementById('login-tab');
const registerTab = document.getElementById('register-tab');
const loginPanel = document.getElementById('login-panel');
const registerPanel = document.getElementById('register-panel');
const passwordField = document.getElementById('register-password');
const confirmPasswordField = document.getElementById('confirm-password');
const btnRegistrar = document.getElementById('btn-registrar');
const btnLogin = document.getElementById('btn-login');
const btnlogout = document.getElementById('btn-logout');

/* Seccion de Coleccion (coleccion cartas, visor de carta) */
const btnTrade = document.getElementById('btn_trade');
const collectionView = document.getElementById('collection-view');
const speciesList = document.getElementById('species-list');
const pColeccionVacia = document.getElementById('p-coleccion-vacia');
const collectionCount = document.getElementById('collection-count');
const btnObtenerCarta = document.getElementById('btn-obtenerCarta');
/* Seccion de trading */
const tradingView = document.getElementById('trading-view');

/*MODELO*/
let usuario;
let coleccion;


function ocultarTodo()
{
    accessView.hidden = true;
    collectionView.hidden = true;
    tradingView.hidden = true;
}

function mostrar(panelName)
{
    if(panelName === 'login')
    {
        ocultarTodo();
        accessView.hidden = false;

        loginTab.classList.toggle('is-active', true);
        registerTab.classList.toggle('is-active', false);
        loginPanel.hidden = false;
        registerPanel.hidden = true;
    }
    if(panelName === 'register')
    {
        ocultarTodo();
        accessView.hidden = false;

        loginTab.classList.toggle('is-active', false);
        registerTab.classList.toggle('is-active', true);
        loginPanel.hidden = true;
        registerPanel.hidden = false;
    }
    if(panelName === 'area-coleccion')
    {
        //OCULTAR TODO
        ocultarTodo();
        //MOSTRAR TODAS LAS SECCIONES DEL AREA DE LA COLECCION
        collectionView.hidden = false;

        cargarColeccion();
    }
    if(panelName === 'area-trading')
    {
       //OCULTAR TODO
       ocultarTodo();
       //MOSTRAR TODAS LAS SECCIONES DEL AREA DE LA TRADING

    }
}

loginTab.addEventListener('click', () => mostrar('login'));
registerTab.addEventListener('click', () => mostrar('register'));
btnTrade.addEventListener('click', () => mostrar('area-trading'));
btnRegistrar.addEventListener('click', function() {
    
    //recopilar los datos de los inputs y formar el objeto FormData
    let usuario = document.getElementById('register-username').value;
    let email = document.getElementById('register-email').value;
    let password = document.getElementById('register-password').value;
    let confirmaPassword = document.getElementById('confirm-password').value;

    const datos = new FormData();
    datos.append('username', usuario);
    datos.append('email', email);
    datos.append('password', password);
    datos.append('confirmPassword', confirmaPassword);

    //Llamar a PHP
    fetch('backend.php?accion=registro', {
        method: 'POST',
        body: datos
    }).then(response => response.json()) // Convertimos la respuesta JSON de PHP a un objeto JS
    .then(data => {
        //Extraemos de data lo que deseemos
        let status = data.status;
        let resultado = data.resultado;
        let mensaje = data.mensaje;
        // 4. Mostramos en pantalla los datos devueltos por el servidor
        document.getElementById('mensajes').innerHTML = status + ' Codigo:' + resultado + ' Mensaje: ' + mensaje;
    })
    .catch(error => {
        document.getElementById('mensajes').innerText = 'Ocurrió un error al procesar la petición.';
    });
}  );
btnLogin.addEventListener('click', function() {   
    //recopilar los datos de los inputs y formar el objeto FormData
    let email = document.getElementById('login-email').value;
    let password = document.getElementById('login-password').value;

    const datos = new FormData();
    datos.append('email', email); 
    datos.append('password', password);

    //Llamar a PHP
    fetch('backend.php?accion=login', {
        method: 'POST',
        body: datos
    }).then(response => response.json()) // Convertimos la respuesta JSON de PHP a un objeto JS
    .then(data => {
        //Extraemos de data lo que deseemos
        let status = data.status;
        let resultado = data.resultado;
        let mensaje = data.mensaje;
        
        usuario = data.usuario;
        coleccion = data.coleccion;

        //Avisar de que todo ha ido bien en el panel de mensajes superior
        document.getElementById('mensajes').innerHTML = status + ' Codigo:' + resultado + ' Mensaje: ' + mensaje;     
        document.getElementById('explorer-name').innerHTML = usuario['username'];

        mostrar('area-coleccion');
        establecerPermisoObtencion();
    })
    .catch(error => {
        document.getElementById('mensajes').innerText = 'Ocurrió un error al procesar la petición.';
    });
}  );
btnlogout.addEventListener('click', function() {   
    //Llamar a PHP
    fetch('backend.php?accion=logout', {
        method: 'POST'
    }).catch(error => {
        document.getElementById('mensajes').innerText = 'Ocurrió un error al procesar la petición.';
    });

    location.reload()

}  );
btnObtenerCarta.addEventListener('click', function() {

    //Llamar a PHP
    fetch('backend.php?accion=anadir-carta', {
        method: 'POST',
    }).then(response => response.json()) // Convertimos la respuesta JSON de PHP a un objeto JS
    .then(data => {
        //Extraemos de data lo que deseemos
        let status = data.status;
        let resultado = data.resultado;
        let mensaje = data.mensaje;
        
        coleccion = data.coleccion;
        cargarColeccion();
        
        //Avisar de que todo ha ido bien en el panel de mensajes superior
        document.getElementById('mensajes').innerHTML = status + ' Codigo:' + resultado + ' Mensaje: ' + mensaje;  
        //Deshabilitar botón de Obtencion de carta.
         
        establecerPermisoObtencion();

    })
    .catch(error => {
        document.getElementById('mensajes').innerText = 'Ocurrió un error al procesar la petición.';
    });

   

}   );


function cargarColeccion()
{
    speciesList.replaceChildren(pColeccionVacia);
    collectionCount.textContent = coleccion.length;
    if(coleccion.length > 0)
        pColeccionVacia.hidden = true;

    for (const carta of coleccion)
    {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'species-item';

        const idCarta = document.createElement('span');
        idCarta.className = 'species-id';
        idCarta.textContent = carta.id;

        const nombre = document.createElement('span');
        nombre.className = 'species-name';
        nombre.textContent = carta.nombre;

        const especie = document.createElement('small');
        especie.textContent = carta.especie;
        nombre.append(especie);
        
        boton.append(idCarta,nombre);
        boton.addEventListener('click', () => seleccionarCarta(carta));
        speciesList.append(boton);
    }
}
function seleccionarCarta(carta)
{
    document.getElementById('viewer-order').textContent = 'ID de carta: ' + carta.id;
    document.getElementById('viewer-family').textContent = carta.especie || 'ESPECIE SIN ESPECIFICAR';
    document.getElementById('viewer-name').textContent = carta.nombre;
    document.getElementById('viewer-period').textContent = carta.periodo || '—';
    document.getElementById('stats-species').textContent = carta.especie || nombre;
    document.getElementById('stat-height').textContent = carta.altura ?? '—';
    document.getElementById('stat-length').textContent = carta.largo ?? '—';
    document.getElementById('stat-weight').textContent = carta.peso ?? '—';
    document.getElementById('stat-period').textContent = carta.periodo || '—';
    document.getElementById('stat-hp').textContent = carta.hp ?? '—';
    document.getElementById('stat-vigor').textContent = carta.vigor ?? '—';
    document.getElementById('stat-attack').textContent = carta.ataque ?? '—';
    document.getElementById('stat-defense').textContent = carta.defensa ?? '—';
    document.getElementById('stat-agility').textContent = carta.agilidad ?? '—';
}
function establecerPermisoObtencion()
{
    //COMPROBAR FECHA DE OBTENCION ULTIMA
    const hoy = new Date();
    const fechaUltimaObtencion = usuario.fechaHoraUltimaObtencionCarta.replace(' ', 'T');
    let diffEnHoras;
    
    //Otra forma de expresar esta comprobacion:
    // diffEnHoras = usuario.fechaHoraUltimaObtencionCarta ? 
    //                (hoy - new Date(fechaUltimaObtencion)) / (1000 * 60 * 60) : 24;
    if(usuario.fechaHoraUltimaObtencionCarta != null)
        diffEnHoras = (hoy - new Date(fechaUltimaObtencion)) / (1000 * 60 * 60);
    else
        diffEnHoras = 24;

    if(diffEnHoras < 24) //EN CASO DE SER menor de 24 horas
    {
        //DESHABILITAR_BOTON();
        //APLICAR CSS gris/deshabilitado
        btnObtenerCarta.disabled = true;
        usuario.fechaHoraUltimaObtencionCarta = new Date();
    }
    else
    {
        //HABILITAR_BOTON()
        //APLICAR CSS dorado/habilitado
        btnObtenerCarta.disabled = false;
    }
}


function obtenerUsuariosParaTrade()
{
    
}

mostrar('login');