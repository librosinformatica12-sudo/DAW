@echo off
chcp 65001 >nul
setlocal
cd /d "%~dp0"

echo ==========================================
echo   SINCRONIZAR dinocards-basico con GitHub
echo ==========================================
echo.

for /f "delims=" %%t in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd_HHmm"') do set "TS=%%t"
set "MSG=Sincronizacion del %TS%"

echo [1/3] Descargando cambios de GitHub...
git pull

echo [2/3] Preparando cambios...
git add -A

git diff --cached --quiet
if not errorlevel 1 goto sin_cambios

echo [3/3] Subiendo a GitHub con el mensaje:
echo        "%MSG%"
git commit -m "%MSG%"
git push

echo.
echo LISTO. Cambios subidos a GitHub.
goto fin

:sin_cambios
echo.
echo No hay cambios que subir. Todo esta actualizado.
:fin
echo.
pause