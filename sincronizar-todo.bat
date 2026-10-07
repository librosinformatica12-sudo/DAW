@echo off
chcp 65001 >nul
setlocal
cd /d "%~dp0"

echo ==========================================
echo   SINCRONIZAR TODO el repo DAW con GitHub
echo ==========================================
echo.

for /f "delims=" %%t in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd_HHmm"') do set "TS=%%t"
set "REPORTE=cambios-todo\informe_%TS%.txt"

echo [1/4] Descargando cambios de GitHub...
git pull

echo [2/4] Preparando TODOS los cambios...
git add -A

git diff --cached --quiet
if not errorlevel 1 goto sin_cambios

echo.
set /p "DESC=Descripcion de los cambios  [Enter = automatica]: "
if not defined DESC set "DESC=Cambios en DAW"

echo [3/4] Creando informe de cambios...
if not exist "cambios-todo" mkdir "cambios-todo"

echo ===== INFORME DE CAMBIOS - TODO EL REPO ===== >"%REPORTE%"
echo Fecha: %date% %time% >>"%REPORTE%"
echo Descripcion: %DESC% >>"%REPORTE%"
echo. >>"%REPORTE%"
echo --- Archivos afectados --- >>"%REPORTE%"
git status --short >>"%REPORTE%"
echo. >>"%REPORTE%"
echo --- Detalle de cambios - diff --- >>"%REPORTE%"
git diff --cached >>"%REPORTE%"

git add cambios-todo

echo [4/4] Subiendo cambios...
git commit -m "%DESC%"
git push

for /f "delims=" %%h in ('git rev-parse --short HEAD') do set "HASH=%%h"
echo. >>"%REPORTE%"
echo Commit subido: %HASH% >>"%REPORTE%"
echo Mensaje: %DESC% >>"%REPORTE%"

echo.
echo LISTO. Informe guardado en: %REPORTE%
goto fin

:sin_cambios
echo.
echo No hay cambios que subir. Todo esta actualizado.
:fin
echo.
pause