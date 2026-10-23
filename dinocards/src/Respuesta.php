<?php
/**
 * Respuesta.php
 * ------------------------------------------------------------------
 * Funcion de ayuda para los endpoints que devuelven JSON
 * (php/login.php, php/registro.php, ...).
 *
 * Antes estaba copiada dentro de cada endpoint; al estar duplicada,
 * si los dos archivos se cargaban en la misma peticion saltaba el
 * error "Cannot redeclare responder()". Ahora vive en un solo sitio.
 * ------------------------------------------------------------------
 */

/**
 * Envia una respuesta JSON y termina la ejecucion.
 *
 * @param bool   $ok      true si la operacion fue bien.
 * @param string $mensaje Texto que se mostrara al usuario.
 * @param array  $extra   Campos adicionales a incluir en el JSON.
 */
function responder(bool $ok, string $mensaje, array $extra = []): void
{
    echo json_encode(
        array_merge(["ok" => $ok, "mensaje" => $mensaje], $extra),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}
