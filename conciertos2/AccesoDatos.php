<?php
/**
 * AccesoDatos: única clase que habla con MySQL (Singleton + PDO).
 * Toda la lógica de validación vive en los procedimientos almacenados;
 * aquí solo se llama a ellos y se devuelve el código _res (0 = OK).
 */
class AccesoDatos
{
    private static ?AccesoDatos $modelo = null;
    private PDO $pdo;

    private function __construct()
    {
        $dsn = 'mysql:host=localhost;dbname=GestorConciertos;charset=utf8mb4';
        $this->pdo = new PDO($dsn, 'gestor_user', 'gestor_pass', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public static function getModelo(): AccesoDatos
    {
        if (self::$modelo === null) {
            self::$modelo = new AccesoDatos();
        }
        return self::$modelo;
    }

    /** Ejecuta un CALL con parámetro OUT @res y devuelve su valor. */
    private function llamarConRes(string $call, array $params): int
    {
        $st = $this->pdo->prepare($call);
        $st->execute($params);
        $st->closeCursor();
        return (int) $this->pdo->query('SELECT @res')->fetchColumn();
    }

    /** Ejecuta un CALL que devuelve filas (SELECT dentro del procedure). */
    private function llamarLista(string $call, array $params = []): array
    {
        $st = $this->pdo->prepare($call);
        $st->execute($params);
        $filas = $st->fetchAll();
        $st->closeCursor();
        return $filas;
    }

    // ---------- CONCIERTOS ----------

    public function getConciertos(): array
    {
        return $this->llamarLista('CALL ListaConciertos()');
    }

    public function getConcierto(int $id): ?array
    {
        $f = $this->llamarLista('CALL DameConcierto(:id)', [':id' => $id]);
        return $f[0] ?? null;
    }

    public function addConcierto(?string $banda, ?string $fecha, ?float $precio): int
    {
        return $this->llamarConRes(
            'CALL AltaConcierto(:banda, :fecha, :precio, @res)',
            [':banda' => $banda, ':fecha' => $fecha, ':precio' => $precio]
        );
    }

    public function modificarConcierto(int $id, ?string $banda, ?string $fecha, ?float $precio): int
    {
        return $this->llamarConRes(
            'CALL ModificarConcierto(:id, :banda, :fecha, :precio, @res)',
            [':id' => $id, ':banda' => $banda, ':fecha' => $fecha, ':precio' => $precio]
        );
    }

    // ---------- ENTRADAS ----------

    public function getEntradas(): array
    {
        return $this->llamarLista('CALL ListaEntradas()');
    }

    public function addEntrada(?int $idConcierto): int
    {
        return $this->llamarConRes(
            'CALL AltaEntrada(:cid, @res)',
            [':cid' => $idConcierto]
        );
    }
}
