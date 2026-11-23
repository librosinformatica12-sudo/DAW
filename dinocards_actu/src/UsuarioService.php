<?php

function obtenerUsuarioPorId(int $usuario_id): ?array
{
    return dal_usuarioPorId($usuario_id);
}
