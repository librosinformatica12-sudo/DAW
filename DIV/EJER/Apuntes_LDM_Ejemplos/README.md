# Ejemplos y ejercicios de LDM (UD2)

Código de los ejemplos y ejercicios de las presentaciones:

- `UD2_Utilización de lenguajes de marcas en entornos web.pdf` (HTML)
- `UD2_Utilización de lenguajes de marcas en entornos web_CSS_hasta26.pdf` (CSS)

Los ejemplos se han reconstruido a partir de las diapositivas. Algunas capturas
del PDF venían como imagen, así que el código está limpio y completo para que
funcione, aunque puede variar ligeramente respecto a la captura original.

## Estructura

```
Apuntes_LDM_Ejemplos
├── index_html.html
├── index_css.html
├── examen
│   ├── index.html
│   ├── enunciado.html
│   ├── test.html
│   └── practica
├── HTML
│   ├── 01_estructura_basica.html
│   ├── 02_hola_mundo.html
│   ├── 03_cabecera.html
│   ├── 04_script_y_style.html
│   ├── 05_encabezados.html
│   ├── 06_parrafos.html
│   ├── 07_estilos_fuente.html
│   ├── 08_listas.html
│   ├── 09_listas_type.html
│   ├── 10_listas_anidadas.html
│   ├── 11_listas_definicion.html
│   ├── 12_enlaces.html
│   ├── 12_destino.html
│   ├── 13_imagenes.html
│   ├── 14_mapa_imagenes.html
│   ├── 15_ejercicio_mapa_espana.html
│   ├── 16_formulario.html
│   ├── 17_ejercicio_formulario_pedido.html
│   ├── 18_tablas.html
│   ├── 19_div_span.html
│   ├── 20_html5_semantico.html
│   └── 21_xhtml.html
├── CSS
│   ├── 00_mezcla_vs_separacion.html
│   ├── 01_css_en_linea.html
│   ├── 02_css_interno.html
│   ├── 03_css_externo.html
│   ├── 03_estilos.css
│   ├── 04_css_import.html
│   ├── 04_estilos2b.css
│   ├── 05_reglas_agrupadas.html
│   ├── 06_selectores_universal_tipo.html
│   ├── 07_selectores_descendientes.html
│   ├── 07_selectores_hijos.html
│   ├── 07_selectores_adyacentes.html
│   ├── 08_selectores_clase_id.html
│   ├── 09_pseudo_clases.html
│   ├── 10_texto.html
│   └── 11_font_face.html
└── LEEME_imagenes.txt
```

## Cómo usarlo

Empieza abriendo `index_html.html` o `index_css.html` (están en la raíz de la
carpeta): son páginas de inicio con todos los ejemplos enlazados y agrupados
por tema.

Para practicar el examen de DIV abre `examen/index.html`.

También puedes abrir cualquier `.html` directamente con el navegador. Los
ejercicios que usan imágenes las buscan en las carpetas `imagenes/` e `img/`:
mete ahí tus fotos con el mismo nombre o cambia la ruta en el `src`.

## Índice por diapositiva

### HTML
| Archivo | Diapositivas | Contenido |
|---|---|---|
| 01_estructura_basica.html | 5-7 | Estructura de un documento HTML |
| 02_hola_mundo.html | 10 | Hola Mundo y `lang` |
| 03_cabecera.html | 14-19 | `title`, `base`, `meta`, `link` (favicon) |
| 04_script_y_style.html | 20-22 | `script` y `style` en la cabecera |
| 05_encabezados.html | 24-25 | `h1` a `h6` |
| 06_parrafos.html | 26-31 | `p`, `br`, `hr` |
| 07_estilos_fuente.html | 32-33 | `b`, `i`, `big`, `small`, `tt`, `u`, `s` |
| 08_listas.html | 37-38 | Listas `ol` y `ul` |
| 09_listas_type.html | 42-44 | Atributo `type` en listas ordenadas |
| 10_listas_anidadas.html | 45-47 | Listas anidadas |
| 11_listas_definicion.html | 48-50 | Listas de definición `dl` |
| 12_enlaces.html | 51-59 | Enlaces internos, mismo servidor, externos y `_blank` |
| 13_imagenes.html | 63-67 | `img`, `alt`, `width`, `height`, `title` |
| 14_mapa_imagenes.html | 68-74 | `map` y `area` |
| 15_ejercicio_mapa_espana.html | 74 | Ejercicio del mapa de España |
| 16_formulario.html | 78-99 | Todos los controles de formulario |
| 17_ejercicio_formulario_pedido.html | 99-106 | Ejercicio "Formulario de pedido" |
| 18_tablas.html | 100-105 | Tablas, `thead`, `tbody`, `tfoot`, `colspan`, `rowspan` |
| 19_div_span.html | 109-114 | `div`, `span` y `float` |
| 20_html5_semantico.html | 119-123 | `header`, `nav`, `section`, `article`, `aside`, `footer` |
| 21_xhtml.html | 115-116 | Normas de XHTML |

### CSS
| Archivo | Diapositivas | Contenido |
|---|---|---|
| 00_mezcla_vs_separacion.html | 5-6 | Mezclar presentación vs separarla |
| 01_css_en_linea.html | 7-8 | CSS en línea |
| 02_css_interno.html | 7-9 | CSS interno con `<style>` |
| 03_css_externo.html + 03_estilos.css | 7-10 | CSS externo con `<link>` |
| 04_css_import.html + 04_estilos2b.css | 10-11 | CSS externo con `@import` |
| 05_reglas_agrupadas.html | 12-15 | Reglas, declaraciones y agrupación |
| 06_selectores_universal_tipo.html | 16 | Selector universal y de tipo |
| 07_selectores_descendientes.html | 17-18 | Selector descendiente `div p` |
| 07_selectores_hijos.html | 19 | Selector hijo `div > p` |
| 07_selectores_adyacentes.html | 20 | Selector adyacente `div + p` |
| 08_selectores_clase_id.html | 20-22 | Clase e ID |
| 09_pseudo_clases.html | 23 | `:link`, `:visited`, `:focus`, `:hover`, `:active` |
| 10_texto.html | 24-25 | Propiedades de texto |
| 11_font_face.html | 26 | `@font-face` |
