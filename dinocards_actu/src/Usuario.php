<?php
/**
 * Modelo Usuario: representa a una persona registrada en DinoCards.
 * No incluye la contraseña en texto plano. La base de datos guarda
 * únicamente password_hash, generado con password_hash().
 */
class Usuario
{
    public int $id;
    public string $username;
    public string $email;
    public ?string $fechaRegistro;
    public ?string $ultimaObtencion;
    public ?string $cartaPrueba;

    public function __construct(
        int $id,
        string $username,
        string $email,
        ?string $fechaRegistro = null,
        ?string $ultimaObtencion = null,
        ?string $cartaPrueba = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->fechaRegistro = $fechaRegistro;
        $this->ultimaObtencion = $ultimaObtencion;
        $this->cartaPrueba = $cartaPrueba;
    }

    /** Construye el objeto a partir de una fila obtenida de MySQL. */
    public static function desdeFila(array $fila): self
    {
        return new self(
            (int) ($fila['id'] ?? 0),
            (string) ($fila['username'] ?? ''),
            (string) ($fila['email'] ?? ''),
            $fila['fecha_registro'] ?? null,
            $fila['ultima_obtencion'] ?? null,
            $fila['carta_prueba'] ?? null
        );
    }
}
