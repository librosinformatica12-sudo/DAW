<?php
/* ============================================================
   cerrar.php
   ------------------------------------------------------------------
   Cierra la sesion del usuario y vuelve a la pagina de entrada.
   ============================================================ */

session_start();
session_unset();
session_destroy();

header("Location: index.php");
exit;
?>
