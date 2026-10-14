# ============================================================
#  redimensionar-fotos.ps1
#  ------------------------------------------------------------
#  Objetivo: poner TODAS las fotos de img/img-cartas al mismo
#  tamano (687 x 1024), sin deformarlas.
#
#  Metodo: "cubrir y recortar" (cover + center crop).
#    1. Se calcula un factor de escala para que la imagen CUBRA
#       el objetivo (las dos dimensiones >= objetivo).
#    2. Se recorta el sobrante por el centro.
#  Asi el dinosaurio mantiene su proporcion (no se estira).
#
#  Antes de tocar nada se hace una copia de seguridad en un ZIP.
# ============================================================

# Cargamos la libreria de dibujo de Windows (.NET System.Drawing),
# que es la que sabe leer/crear imagenes.
Add-Type -AssemblyName System.Drawing

# Carpeta de las fotos y tamano objetivo
$cartas = "C:\xampp\htdocs\ejer\dino-prueba\img\img-cartas"
$destW  = 687
$destH  = 1024

# ------------------------------------------------------------
# 0) Copia de seguridad
# Se comprime todo el contenido de la carpeta en un ZIP.
# ------------------------------------------------------------
$backup = "C:\xampp\htdocs\ejer\dino-prueba\img\img-cartas_backup.zip"
Compress-Archive -Path (Join-Path $cartas '*') -DestinationPath $backup -Force
Write-Output "Backup creado en: $backup"

# ------------------------------------------------------------
# Funcion que convierte UNA imagen a 687x1024 (cover + recorte)
# ------------------------------------------------------------
function Convertir-Cover {
    param([string]$Ruta, [int]$Tw, [int]$Th)

    # Leemos el archivo a memoria para NO dejar el fichero bloqueado
    # (asi luego podemos sobrescribirlo en su misma ruta).
    $bytes = [System.IO.File]::ReadAllBytes($Ruta)
    $ms    = New-Object System.IO.MemoryStream(, $bytes)
    $img   = [System.Drawing.Image]::FromStream($ms)
    try {
        # Factor de escala: el mayor de los dos, para CUBRIR el objetivo
        $escala     = [Math]::Max($Tw / $img.Width, $Th / $img.Height)
        $nuevoAncho = [int][Math]::Ceiling($img.Width  * $escala)
        $nuevoAlto  = [int][Math]::Ceiling($img.Height * $escala)

        # Cuanto hay que desplazar para recortar por el CENTRO
        $offX = [int][Math]::Max(0, [Math]::Floor(($nuevoAncho - $Tw) / 2))
        $offY = [int][Math]::Max(0, [Math]::Floor(($nuevoAlto  - $Th) / 2))

        # Lienzo destino (687x1024) y pincel de dibujo
        $bmp = New-Object System.Drawing.Bitmap($Tw, $Th)
        $g   = [System.Drawing.Graphics]::FromImage($bmp)
        # Calidad maxima de reescalado
        $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
        $g.PixelOffsetMode   = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
        $g.SmoothingMode     = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality

        # Rectangulos: (destino 0,0,Tw,Th) <- (origen offX,offY,Tw,Th)
        $rectDest = New-Object System.Drawing.Rectangle(0, 0, $Tw, $Th)
        $rectOrig = New-Object System.Drawing.Rectangle($offX, $offY, $Tw, $Th)
        $g.DrawImage($img, $rectDest, $rectOrig, [System.Drawing.GraphicsUnit]::Pixel)
        $g.Dispose()

        # Guardamos conservando el formato original (.jpg o .png)
        $ext = [System.IO.Path]::GetExtension($Ruta).ToLower()
        if ($ext -eq '.jpg' -or $ext -eq '.jpeg') {
            $bmp.Save($Ruta, [System.Drawing.Imaging.ImageFormat]::Jpeg)
        } else {
            $bmp.Save($Ruta, [System.Drawing.Imaging.ImageFormat]::Png)
        }
        $bmp.Dispose()
    }
    finally {
        # Liberamos recursos siempre
        $img.Dispose()
        $ms.Dispose()
    }
}

# ------------------------------------------------------------
# Recorremos las imagenes y cambiamos SOLO las que no son 687x1024
# ------------------------------------------------------------
$cambiadas = 0
$patron = '\.(png|jpg|jpeg|webp|gif|avif|bmp)$'

Get-ChildItem -LiteralPath $cartas -File |
    Where-Object { $_.Extension -match '\.(png|jpg|jpeg|webp|gif|avif|bmp)$' } |
    ForEach-Object {
        # Leemos solo las dimensiones actuales
        $bytes = [System.IO.File]::ReadAllBytes($_.FullName)
        $ms    = New-Object System.IO.MemoryStream(, $bytes)
        $img   = [System.Drawing.Image]::FromStream($ms)
        $w = $img.Width
        $h = $img.Height
        $img.Dispose()
        $ms.Dispose()

        if ($w -ne $destW -or $h -ne $destH) {
            Convertir-Cover -Ruta $_.FullName -Tw $destW -Th $destH
            $cambiadas++
            Write-Output ("Cambiada: {0}  ({1}x{2} -> {3}x{4})" -f $_.Name, $w, $h, $destW, $destH)
        }
    }

Write-Output "----------------------------------------"
Write-Output "Total cambiadas: $cambiadas"
Write-Output "Backup: $backup"
