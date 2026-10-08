@echo off
chcp 65001 >nul
setlocal
cd /d "%~dp0.."

echo ==========================================
echo    SINCRONIZAR dino-prueba con GitHub
echo ==========================================
echo.

echo [1/4] Descargando cambios de GitHub...
git pull

echo [2/4] Preparando cambios locales...
git add dino-prueba

git diff --cached --quiet -- dino-prueba
if not errorlevel 1 goto sin_cambios

echo.
set /p "DESC=Descripcion de los cambios  [Enter = automatica]: "
if not defined DESC set "DESC=Cambios en dino-prueba"

echo [3/4] Subiendo cambios...
git commit -m "%DESC%"
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