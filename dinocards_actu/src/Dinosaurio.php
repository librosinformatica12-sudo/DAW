<?php
/** Modelo Dinosaurio: datos de una carta del catálogo DinoCards. */
class Dinosaurio
{
    public int $id;
    public string $nombre;
    public string $especie;
    public string $periodo;
    public string $imagenUrl;
    public ?float $altura;
    public ?float $largo;
    public ?float $peso;
    public array $estadisticas;

    public function __construct(
        int $id,
        string $nombre,
        string $especie = '',
        string $periodo = '',
        string $imagenUrl = '',
        ?float $altura = null,
        ?float $largo = null,
        ?float $peso = null,
        array $estadisticas = []
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->especie = $especie;
        $this->periodo = $periodo;
        $this->imagenUrl = $imagenUrl;
        $this->altura = $altura;
        $this->largo = $largo;
        $this->peso = $peso;
        $this->estadisticas = $estadisticas;
    }

    /** Construye el objeto a partir de una fila obtenida de MySQL. */
    public static function desdeFila(array $fila): self
    {
        return new self(
            (int) ($fila['id'] ?? 0),
            (string) ($fila['nombre'] ?? ''),
            (string) ($fila['especie'] ?? ''),
            (string) ($fila['periodo'] ?? ''),
            (string) ($fila['imagen_url'] ?? ''),
            isset($fila['altura']) ? (float) $fila['altura'] : null,
            isset($fila['largo']) ? (float) $fila['largo'] : null,
            isset($fila['peso']) ? (float) $fila['peso'] : null,
            [
                'hp' => (int) ($fila['hp'] ?? 0),
                'vigor' => (int) ($fila['vigor'] ?? 0),
                'ataque' => (int) ($fila['ataque'] ?? 0),
                'defensa' => (int) ($fila['defensa'] ?? 0),
                'agilidad' => (int) ($fila['agilidad'] ?? 0),
            ]
        );
    }
}
