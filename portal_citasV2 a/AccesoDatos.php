<?php

/*
 * AccesoDatos.php (portal_citasV2 - versión buena)
 *
 * ÍNDICE RÁPIDO (Ctrl+F):
 *   CONEXION
 *   PA_ListaCitas
 *   PA_ListaDoctores
 *   PA_DameDoctor
 *   PA_AltaDoctor
 *   PA_AltaCita
 *   PA_ActualizarCita
 *
 * Patrón de examen:
 *   SELECT  -> prepare + execute + get_result + while/fetch_assoc
 *   INSERT/UPDATE -> prepare + bind_param + execute + SELECT @resultado
 */
 // =====================================================================
 // EXPLICACIÓN GENERAL (ampliada para estudiar)
 // Este archivo es la CAPA DE ACCESO A DATOS: el puente entre PHP y MySQL.
 // Abre UNA conexión ($connection) y define una función PA_ por cada procedimiento
 // almacenado. A diferencia de otros proyectos, aquí NO hay función genérica:
 // cada función PA_ escribe su propio prepare / bind_param / execute.
 // Solo hay DOS patrones y debes saberlos de memoria:
 //
 //   PATRÓN 1 - SELECT (devuelve filas): PA_ListaCitas, PA_ListaDoctores, PA_DameDoctor
 //      prepare -> (bind_param si hay ?) -> execute -> get_result
 //      -> while + fetch_assoc -> array con las filas
 //
 //   PATRÓN 2 - INSERT / UPDATE con parámetro OUT: PA_AltaDoctor, PA_AltaCita,
 //                                                 PA_ActualizarCita
 //      prepare (CALL ... @resultado) -> bind_param -> execute -> close
 //      -> SELECT @resultado -> fetch_assoc -> return el código
 //
 // REGLAS DE ORO:
 //   - Nº de ? del CALL = nº de parámetros IN del procedure (el OUT NO lleva ?,
 //     lleva @resultado).
 //   - Nº de letras de bind_param = nº de ?, y en el MISMO orden.
 //   - Tipos: i = integer, s = string, d = double, b = blob.
 //   - Los códigos de resultado vuelven como STRING ("0", "-1"...). Por eso las
 //     páginas comparan con === "-1" (con comillas).
 //
 // [EXAMEN] Lo más probable que te quiten:
 //   - Los datos de conexión (usuario, contraseña, base de datos, puerto).
 //   - Una función PA_ entera (p. ej. una nueva PA_BajaCita): copia el patrón 2.
 //   - La cadena de tipos de bind_param o la lista de ? del CALL.
 //   - El SELECT @resultado AS resultado, o el return $fila['resultado'].
 // =====================================================================

// ===== CONEXION =====
// DATOS DE CONEXIÓN: deben coincidir con tu MySQL.
//   $host     127.0.0.1 = tu propio ordenador (equivale a localhost)
//   $port     3306 = puerto por defecto de MySQL
//   $database nombre de la BD creada en generador_clinia.sql (clinica)
//   $username / $password = usuario de MySQL (aquí guilleDB / 1234)
// Si en el examen no conecta, revisa PRIMERO estas cinco variables.
$host = '127.0.0.1';
$port = 3306;
$database = 'clinica';
$username = 'guilleDB';
$password = '1234';

// Desactivamos los warnings automáticos para gestionarlos nosotros mediante excepciones
mysqli_report(MYSQLI_REPORT_OFF);
// Crea la conexión. Orden de new mysqli: (servidor, usuario, contraseña,
// base de datos, puerto). El @ silencia el aviso si falla la conexión.
// Con MYSQLI_REPORT_OFF mysqli NO lanza excepciones: cada llamada devuelve
// false/null si falla, y por eso más abajo se comprueba !$stmt->execute()
// y se lanza a mano una RuntimeException con el mensaje de error.
// OJO: no se comprueba $connection->connect_error; si la conexión falla, el
// error aparecerá más tarde en prepare(). Una comprobación sería:
//   if ($connection->connect_error) { die($connection->connect_error); }
$connection = @new mysqli($host, $username, $password, $database, $port);


/* =========================================================================
   CASO 1: FUNCIONES QUE DEVUELVEN UN CONJUNTO DE FILAS (SELECT)
   Ejemplos: PA_ListaCitas, PA_ListaDoctores, PA_DameDoctor
   - Usan $stmt->get_result() para leer los registros devueltos por MySQL.
   - Recorren los resultados con un bucle while y fetch_assoc().
   ========================================================================= */

// ===== FUNCION PA_ListaCitas =====
// Devuelve todas las citas. Se usa para cargar la tabla y la sesión.
// PATRÓN 1 (SELECT sin parámetros). Devuelve un ARRAY de arrays asociativos,
// uno por cada cita. OJO a los nombres: la columna de la BD se llama fk_doctor
// pero aquí se guarda con la clave 'licencia_doctor'. Las claves de salida
// son las que luego usa gestionar_citas.php.
function PA_ListaCitas()
{
    global $connection;

    // CALL llama al procedimiento almacenado. Sin ? porque no recibe parámetros,
    // y sin @resultado porque ListaCitas no tiene parámetro OUT (solo hace SELECT).
    $sql = 'CALL ListaCitas()';
    // prepare() crea la sentencia preparada. Los valores (si los hay) se envían
    // después con bind_param, separados del SQL: así se evita la inyección SQL.
    $stmt = $connection->prepare($sql);

    // execute() devuelve false si falla. En ese caso: guardamos el mensaje
    // ($stmt->error), cerramos el statement y lanzamos una excepción.
    // Este bloque se repite igual en TODAS las funciones.
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    // Array donde acumularemos las filas. Si no hay citas se devuelve vacío.
    $citas = [];
    // Comprobamos si la consulta ha devuelto columnas/filas de datos ($field_count > 0)
    if ($stmt->field_count > 0) {
        $resultadoSelect = $stmt->get_result(); // Obtenemos el objeto mysqli_result
        if ($resultadoSelect) {
            // fetch_assoc() devuelve la siguiente fila como array asociativo (clave =
            // nombre de columna) y false cuando no quedan más: eso termina el while.
            while ($fila = $resultadoSelect->fetch_assoc()) {
                // Lo que va entre corchetes de $fila DEBE ser el nombre EXACTO de la columna
                // que devuelve el SELECT de MySQL (aquí SELECT * de citasMedicas). Si no
                // coincide: "Undefined array key".
                $cita['id_cita'] = $fila['id_cita'];
                $cita['nombre_paciente'] = $fila['nombre_paciente'];
                $cita['especialidad'] = $fila['especialidad'];
                $cita['fecha'] = $fila['fecha'];
                $cita['licencia_doctor'] = $fila['fk_doctor'];

                $citas[] = $cita; // Añadimos cada fila al array final
            }
            $resultadoSelect->free(); // Liberamos memoria del resultado
        }
    }
    $stmt->close(); // Cerramos el statement

    return $citas; // Devolvemos el array con todas las citas
}


// ===== FUNCION PA_ListaDoctores =====
// Devuelve todos los doctores. Se usa para cargar la tabla de doctores.
// PATRÓN 1 igual que PA_ListaCitas pero con la tabla doctores.
// Se traducen las claves: n_licencia -> 'id', nombre_doctor -> 'nombre',
// apellidos_doctor -> 'apellidos'.
function PA_ListaDoctores()
{
    global $connection;

    $sql = 'CALL ListaDoctores()';
    $stmt = $connection->prepare($sql);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    $doctores = [];
    if ($stmt->field_count > 0) {
        $resultadoSelect = $stmt->get_result();
        if ($resultadoSelect) {
            while ($fila = $resultadoSelect->fetch_assoc()) {
                $doctor['id'] = $fila['n_licencia'];
                $doctor['nombre'] = $fila['nombre_doctor'];
                $doctor['apellidos'] = $fila['apellidos_doctor'];

                $doctores[] = $doctor;
            }
            $resultadoSelect->free();
        }
    }
    $stmt->close();

    return $doctores;
}


// ===== FUNCION PA_DameDoctor =====
// Busca un doctor por licencia. Se usa para validar que el doctor existe.
// PATRÓN 1 con UN parámetro IN: busca un doctor por su licencia.
// Por eso el CALL lleva un ? y se usa bind_param('i', ...) (entero).
// int $n_licencia es una declaración de tipo: PHP convierte o da error.
// Devuelve un array con 0 filas (no existe) o 1 fila (existe).
// gestionar_citas.php lo usa así: count($doctorEncontrado) == 0 -> no existe.
function PA_DameDoctor(int $n_licencia)
{
    global $connection;

    // Este procedimiento recibe 1 parámetro de entrada (IN), por eso lleva un '?'
    $sql = 'CALL DameDoctor(?)';
    $stmt = $connection->prepare($sql);

    // Vinculamos el parámetro de entrada: 'i' porque es un entero (integer)
    $stmt->bind_param('i', $n_licencia);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    $doctores = [];
    // Al hacer un SELECT por ID, también devuelve filas, así que usamos get_result igual que en las listas
    if ($stmt->field_count > 0) {
        $resultadoSelect = $stmt->get_result();
        if ($resultadoSelect) {
            while ($fila = $resultadoSelect->fetch_assoc()) {
                $doctor['id'] = $fila['n_licencia'];
                $doctor['nombre'] = $fila['nombre_doctor'];
                $doctor['apellidos'] = $fila['apellidos_doctor'];

                $doctores[] = $doctor;
            }
            $resultadoSelect->free();
        }
    }
    $stmt->close();

    return $doctores;
}



/* =========================================================================
   CASO 2: FUNCIONES DE ACCIÓN CON PARÁMETRO DE SALIDA (INSERT / UPDATE con OUT)
   Ejemplos: PA_AltaDoctor, PA_AltaCita, PA_ActualizarCita
   - Llevan parámetros IN ('?') y un parámetro OUT en la llamada (ej. '@resultado').
   - NO usan get_result() porque no devuelven tablas, sino que consultan 
     la variable de sesión de MySQL (@resultado) justo después de cerrar el statement.
   ========================================================================= */

// ===== FUNCION PA_AltaDoctor =====
// Inserta un doctor y devuelve el código OUT del procedure.
// PATRÓN 2 (INSERT con OUT). Recibe 3 datos y devuelve el código del procedure.
// Los parámetros son string aunque n_licencia sea INT en la BD: desde un
// formulario todo llega como texto y MySQL lo convierte. Por eso 'sss'.
function PA_AltaDoctor(string $n_licencia, string $nombre, string $apellidos)
{
    global $connection;

    // El último parámetro('@resultado') recoge el valor de salida (OUT) del procedimiento en MySQL
    $sql = 'CALL AltaDoctor(?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    // Vinculamos los 3 parámetros de entrada ('sss' de string, string, string)
    $stmt->bind_param('sss', $n_licencia, $nombre, $apellidos);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    
    // IMPORTANTE: Hay que cerrar el statement ANTES de consultar la variable @resultado
    $stmt->close();

    // Consultamos la variable de usuario de MySQL donde se guardó el OUT
    // @resultado es una variable de SESIÓN de MySQL donde el procedure dejó su
    // parámetro OUT. Se lee con una consulta normal ($connection->query) por la
    // MISMA conexión. AS resultado = alias para leerla como $fila['resultado'].
    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    // Solo hay UNA fila, así que basta un fetch_assoc() sin while.
    // $fila vale ['resultado' => '0'] (o '-1', '-2'...).
    $fila = $resultado->fetch_assoc();
    $resultado->free();

    // Devolvemos el código de resultado (por ejemplo, 0 si fue bien, o -1, -2... si hubo error)
    // Devuelve el código como STRING ("0", "-3"...). Por eso en las páginas se
    // compara con comillas: $resultado === "-1".
    return $fila['resultado'];
}


// ===== FUNCION PA_AltaCita =====
// Inserta una cita y devuelve el código OUT del procedure.
// PATRÓN 2: mismo esquema con 4 IN + 1 OUT. Tipos 'sssi': paciente (s),
// especialidad (s), fecha (s, texto tipo 2026-11-15T10:00) y doctor (i).
// El orden de los ? debe coincidir con el orden de IN de AltaCita en el SQL.
function PA_AltaCita(string $nombre_paciente, string $especialidad, string $fecha, int $doctor_asignado)
{
    global $connection;

    // Procedimiento con 4 parámetros IN (?) y 1 parámetro OUT (@resultado)
    $sql = 'CALL AltaCita(?, ?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    // Tipos: string (s), string (s), string (s), integer (i) -> 'sssi'
    $stmt->bind_param('sssi', $nombre_paciente, $especialidad, $fecha, $doctor_asignado);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();

    // Recuperamos el OUT exactamente igual que en el alta de doctor
    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}


// ===== FUNCION PA_ActualizarCita =====
// Modifica una cita existente y devuelve el código OUT del procedure.
// PATRÓN 2 para modificar: 5 IN + 1 OUT. OJO con los nombres: la función PHP
// se llama PA_ActualizarCita pero el procedure de MySQL se llama ModificarCita.
// En el CALL manda el nombre del PROCEDURE, no el de la función PHP.
function PA_ActualizarCita(string $idCita, string $nombre_paciente, string $especialidad, string $fecha, int $doctor_asignado)
{
    global $connection;

    // Procedimiento de modificación con 5 parámetros IN (?) y 1 OUT (@resultado)
    $sql = 'CALL ModificarCita(?, ?, ?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    // Tipos: string, string, string, string, int -> 'ssssi'
    // idCita va como 's' aunque en la BD sea INT (llega desde sesión/POST como
    // texto). Orden: id, paciente, especialidad, fecha (4 strings) y doctor (int).
    $stmt->bind_param('ssssi', $idCita, $nombre_paciente, $especialidad, $fecha, $doctor_asignado);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();

    // Recuperamos el parámetro OUT de la modificación
    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}