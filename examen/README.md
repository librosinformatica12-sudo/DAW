[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# Kit de examen — Cliente + Servidor

> Todo está dentro de `C:\xampp\htdocs\ejer\examen`.
> La idea NO es copiar y pegar a lo loco, sino reconocer el patrón para rellenar los huecos que te ponga el profe.

## Versión fácil de leer

Abre `index.html` con doble clic. Es la misma información, pero con tablas, colores y las soluciones escondidas en desplegables. Si abres los `.md` en Notepad verás símbolos raros; usa `index.html`.

## Qué hay aquí

| Carpeta | Qué es | Nivel |
|---|---|---|
| `CHULETA-cliente.md` | Resumen de JS/DOM/CSS con los huecos típicos | Básico |
| `CHULETA-servidor.md` | Resumen de PHP/MySQL/mysqli/procedimientos | Básico |
| `cliente/01-tragamonedas` | Igual que tu TragaPerras, limpio y comentado | Cliente |
| `cliente/02-lista-tareas` | Añadir/borrar/completar tareas con DOM | Cliente |
| `cliente/03-adivina-numero` | Random, intentos, validación y mensajes | Cliente |
| `cliente/04-carrito` | Arrays de objetos, totales y render de tabla | Cliente |
| `cliente/05-slider-botones` | Slider, variables, funciones, botones y animación CSS | Cliente |
| `cliente/06-espaciado-letras` | Slider para espacio entre letras y animación de latido | Cliente |
| `cliente/07-espaciado-flex` | Slider para separar tarjetas y animación de balanceo | Cliente |
| `servidor/01-clinica` | Como `portal_citasV2`: citas + doctores | Servidor |
| `servidor/02-conciertos` | Tu proyecto terminado: conciertos + entradas | Servidor |
| `servidor/03-biblioteca` | Libros + préstamos + JOIN + UPDATE | Servidor |

Cada ejemplo tiene:

- `solucion/` → código completo y comentado (se puede ejecutar).
- `ENUNCIADO.md` → los huecos que debes rellenar, con la solución escondida en un desplegable.

## Cómo estudiar con esto

1. Lee la chuleta del lado que vayas a practicar.
2. Abre la `solucion/` y entiende el orden: **ENTRADA → VALIDAR → ACTUAR → PINTAR**.
3. Tapa la solución y haz el `ENUNCIADO.md` en un archivo aparte.
4. Mira la solución solo cuando termines.
5. Cronométrate: 30-45 min por ejercicio.

## Cómo probar

### Cliente

Abre el `index.html` con doble clic o entra en:

`http://localhost/ejer/examen/cliente/01-tragamonedas/solucion/`

### Servidor

1. Abre phpMyAdmin e importa el `.sql` de la carpeta `solucion/`.
2. Los ejemplos vienen con usuario `root` y contraseña `1234` (igual que `portal_citas`). Si tu MySQL usa otra, cámbiala en `AccesoDatos.php`.
3. Arranca Apache y MySQL en XAMPP.
4. Entra en:

`http://localhost/ejer/examen/servidor/01-clinica/solucion/portal_citas.html`

> Cada ejemplo usa una base de datos distinta (`clinica_examen`, `gestor_conciertos_examen`, `biblioteca_examen`), así no se pisan.
> El ejemplo de conciertos usa `gestor_conciertos_examen` a propósito, para no borrar la base de tu proyecto real.

## Mapa con tus proyectos reales

| Si el profe te da... | Mira... |
|---|---|
| Una tragaperras o un juego con botón | `cliente/01-tragamonedas` |
| Una lista que se actualiza sin recargar | `cliente/02-lista-tareas` |
| Un random con intentos y mensajes | `cliente/03-adivina-numero` |
| Productos, precios y total | `cliente/04-carrito` |
| Un slider, botones o una animación | `cliente/05-slider-botones` |
| Controlar el espacio entre letras | `cliente/06-espaciado-letras` |
| Separar tarjetas o elementos | `cliente/07-espaciado-flex` |
| Un formulario que guarda en MySQL | `servidor/01-clinica` |
| Conciertos, entradas, altas y bajas | `servidor/02-conciertos` |
| Libros, préstamos, stock | `servidor/03-biblioteca` |

## Regla de oro

No memorices líneas sueltas. Memoriza **bloques**:

- Cliente: `getElementById` → `addEventListener` → `función` → `innerText/setAttribute`.
- Servidor: `form name` → `$_POST` → `bind_param` → `CALL` → `@resultado` → `SELECT @resultado` → mensaje → tabla.

## Si el examen es una defensa

Ten preparada esta frase para cada proyecto:

> "El formulario envía por POST, lo recojo en PHP, valido, llamo a la función `PA_...`, esa función usa un procedimiento almacenado, recojo el `OUT`, muestro el mensaje y pinto la tabla."

Con eso demuestras que entiendes el viaje del dato.
