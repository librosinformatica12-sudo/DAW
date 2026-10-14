# ============================================================
#  generar-prompts-faltantes.ps1
#  ------------------------------------------------------------
#  Que hace, paso a paso:
#   1. Lee el ZIP de respaldo -> tiene el TAMANO ORIGINAL de cada foto.
#   2. Detecta las fotos APAISADAS (ancho > alto). Esas son las que
#      quedaron mal al recortarlas a 687x1024 (vertical).
#   3. Lee img/prompts-fotos.txt para sacar el nombre y la especie
#      de cada dinosaurio.
#   4. Escribe img/prompts-faltantes.txt con un prompt por dinosaurio
#      (pidiendo a la IA el tamano 687x1024 vertical) y, abajo,
#      la lista de IDs que hay que regenerar.
# ============================================================

Add-Type -AssemblyName System.Drawing
Add-Type -AssemblyName System.IO.Compression.FileSystem

$base   = "C:\xampp\htdocs\ejer\dino-prueba"
$zip    = "$base\img\img-cartas_backup.zip"
$fuente = "$base\img\prompts-fotos.txt"
$salida = "$base\img\prompts-faltantes.txt"

# --- 1 y 2) IDs de fotos apaisadas, segun el respaldo -------------------
$malos = New-Object System.Collections.Generic.List[int]
$zf = [System.IO.Compression.ZipFile]::OpenRead($zip)
try {
    foreach ($e in $zf.Entries) {
        if ($e.FullName -match '_original') { continue }           # solo las fotos principales
        if ($e.FullName -match '(\d+)\.(png|jpg|jpeg|webp|gif)$') {
            $id = [int]$Matches[1]
            $s  = $e.Open()                                        # abrimos el stream del fichero dentro del zip
            try {
                $img = [System.Drawing.Image]::FromStream($s)      # leemos la imagen sin extraerla
                if ($img.Width -gt $img.Height) { $malos.Add($id) }# apaisada => mala
                $img.Dispose()
            } catch { }
            finally { $s.Dispose() }
        }
    }
} finally { $zf.Dispose() }
$malos = $malos | Sort-Object -Unique

# --- 3) Leer prompts-fotos.txt: nombre, especie y prompt por id ---------
$lineas = [System.IO.File]::ReadAllLines($fuente, [System.Text.Encoding]::UTF8)
$datos  = @{}
for ($i = 0; $i -lt $lineas.Count; $i++) {
    if ($lineas[$i] -match '^\[(\d+)\]\s+(.*?)\s+\((.*)\)\s*$') {
        $id      = [int]$Matches[1]
        $nombre  = $Matches[2].Trim()
        $especie = $Matches[3].Trim()
        # La linea siguiente con texto "Generate..." es el prompt base
        $j = $i + 1
        while ($j -lt $lineas.Count -and $lineas[$j].Trim() -eq '') { $j++ }
        $promptBase = if ($j -lt $lineas.Count) { $lineas[$j].Trim() } else { '' }
        $datos[$id] = [pscustomobject]@{ Nombre = $nombre; Especie = $especie; Prompt = $promptBase }
    }
}

# --- 4) Generar el archivo de salida -----------------------------------
$out = New-Object System.Collections.Generic.List[string]
$out.Add("PROMPTS PARA REGENERAR LAS FOTOS QUE QUEDARON MAL (687x1024)")
$out.Add("======================================================================")
$out.Add("Estas fotos eran APAISADAS (mas anchas que altas) y al recortarlas a")
$out.Add("687x1024 el dinosaurio quedo mal. Pega cada prompt en la IA (Gemini).")
$out.Add("")

foreach ($id in $malos) {
    if (-not $datos.ContainsKey($id)) { continue }
    $d = $datos[$id]
    # El prompt pide explicitamente formato vertical 687x1024
    $prompt = "Generate a collectible dinosaur trading-card illustration of {0} (commonly known as {1}): a full-body, dynamic illustration in a vibrant, semi-realistic digital art style, dramatic cinematic lighting, rich textures, and an atmospheric background reflecting its natural prehistoric habitat. IMPORTANT: vertical portrait format (aspect ratio 2:3), exactly 687 x 1024 pixels, the whole dinosaur fully visible and centered with some margin, not cropped. High-resolution, sharp and engaging, no text, no logos, no borders." -f $d.Especie, $d.Nombre
    $out.Add(("[{0}] {1}  ({2})" -f $id, $d.Nombre, $d.Especie))
    $out.Add($prompt)
    $out.Add("")
}

$out.Add("======================================================================")
$out.Add("IDs que faltan por regenerar ({0} fotos):" -f $malos.Count)
$out.Add(($malos -join ', '))

[System.IO.File]::WriteAllLines($salida, $out, (New-Object System.Text.UTF8Encoding($false)))

Write-Output "Archivo generado: $salida"
Write-Output ("IDs malos ({0}): {1}" -f $malos.Count, ($malos -join ', '))
