<?php

function obtenerIdsColeccion(int $usuario_id): array
{
    return dal_idsColeccion($usuario_id);
}

function resumenColeccion(int $usuario_id): array
{
    return dal_resumenColeccion($usuario_id);
}

function contarDinosaurios(): int
{
    return dal_contarDinosaurios();
}

function dinosaurioAleatorio(): ?array
{
    return dal_dinosaurioAleatorio();
}

function dinosauriosAleatorios(int $n): array
{
    return dal_dinosauriosAleatorios($n);
}

function dinosauriosPorIds(array $ids): array
{
    return dal_dinosauriosPorIds($ids);
}

function obtenerTodosLosDinosaurios(): array
{
    return dal_todosLosDinosaurios();
}

function ultimasCartas(int $usuario_id, int $limite = 6): array
{
    return dal_ultimasCartas($usuario_id, $limite);
}
