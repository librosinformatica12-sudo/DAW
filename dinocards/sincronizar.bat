@echo off
chcp 65001 >nul
setlocal
cd /d "%~dp0"

echo ==========================================
echo   SINCRONIZAR dinocards-basico con GitHub
echo ==========================================
echo.

rem --- Mensaje de la sincronizacion (fecha y hora) ---
for /f "delims=" %%t in ('powershell -NoProfile -Command "Get-Date -Format \"yyyy-MM-dd HH:mm\""') do set "MSG=Sincronizacion dinocards-basico %%t"

echo [1/4] Preparando cambios de esta carpeta...
rem Solo esta carpeta (el "-- ." limita a dinocards-basico).
rem Asi NO toca otras carpetas del repo (dino-prueba, DAW, ...).
git add -A -- .
if errorlevel 1 goto error

git diff --cached --quiet
if not errorlevel 1 (
    echo       No hay cambios nuevos que guardar.
    goto descargar
)

echo [2/4] Guardando cambios...
git commit -m "%MSG%"
if errorlevel 1 goto error
goto descargar

:descargar
echo [3/4] Descargando cambios de GitHub...
git pull --no-rebase --no-edit
if errorlevel 1 goto conflicto

echo [4/4] Subiendo a GitHub...
git push
if errorlevel 1 goto error

echo.
echo LISTO. dinocards-basico sincronizado con GitHub.
goto fin

:conflicto
echo.
echo AVISO: hay conflictos al descargar los cambios.
echo Resuelvelos, haz "git commit" y vuelve a ejecutar este archivo.
echo.
git status --short
goto fin

:error
echo.
echo ERROR: la operacion no se ha completado. Revisa el mensaje de arriba.
goto fin

:fin
echo.
pause
