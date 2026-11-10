@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"

echo === Sincronizando DAW con GitHub (Modo Forzado) ===
echo.

echo Añadiendo cambios...
git add .

:: Verificar si hay cambios pendientes para commit
git status --porcelain | findstr /R "." >nul
if !ERRORLEVEL! EQU 0 (
    echo.
    set "COMMIT_MSG="
    set /p "COMMIT_MSG=Mensaje del commit (pulsa Enter para usar uno automatico): "
    
    if "!COMMIT_MSG!"=="" (
        set COMMIT_MSG=Actualizacion automatica %DATE% %TIME%
    )

    echo.
    echo Creando commit...
    git commit -m "!COMMIT_MSG!"
) else (
    echo.
    echo No hay cambios nuevos para commitear. Comprobando envio...
)

echo.
echo ----------------------------------------------------
echo Forzando subida a origin/main...
echo [AVISO] Si tu proyecto tiene muchos samples o WAVs, 
echo         esto puede tardar varios minutos. No lo cierres.
echo ----------------------------------------------------
echo.

git push origin main --force

if !ERRORLEVEL! EQU 0 (
    echo.
    echo ¡Sincronización completada con éxito!
) else (
    echo.
    echo Ocurrió un error al subir los archivos.
)

pause