<?php
/**
 * api/coleccion.php
 * Devuelve la colección del usuario actual (agrupada) y un resumen.
 */
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../includes/conexion.php';

if (!esta_logueado()) {
    responder_json(['ok' => false, 'mensaje' => 'Debes iniciar sesión.'], 401);
}

$uid = usuario_actual();

try {
    $stmt = $pdo->prepare('SELECT dinosaurio_id FROM colecciones WHERE usuario_id = :u');
    $stmt->execute([':u' => $uid]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Contamos cuántas copias hay de cada dinosaurio
    $copias = [];
    foreach ($ids as $id) {
        $id = (int) $id;
        $copias[$id] = ($copias[$id] ?? 0) + 1;
    }

    // Traemos los datos de esos dinosaurios
    $cartas = [];
    if ($copias) {
        $lista = array_keys($copias);
        $marcas = implode(',', array_fill(0, count($lista), '?'));
        $st = $pdo->prepare("SELECT * FROM dinosaurios WHERE id IN ($marcas) ORDER BY id");
        $st->execute($lista);

        foreach ($st->fetchAll() as $dino) {
            $dino['copias'] = $copias[(int) $dino['id']];
            $cartas[] = $dino;
        }
    }

    $total_catalogo = (int) $pdo->query('SELECT COUNT(*) FROM dinosaurios')->fetchColumn();
    $total_especies = count($copias);
    $total_cartas   = count($ids);
    $porcentaje     = $total_catalogo > 0 ? (int) round($total_especies / $total_catalogo * 100) : 0;

    responder_json([
        'ok'      => true,
        'cartas'  => $cartas,
        'resumen' => [
            'cartas'     => $total_cartas,
            'especies'   => $total_especies,
            'catalogo'   => $total_catalogo,
            'porcentaje' => $porcentaje,
        ],
    ]);
} catch (PDOException $e) {
    responder_json(['ok' => false, 'mensaje' => 'Error del servidor.'], 500);
}
