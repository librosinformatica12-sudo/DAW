@echo off
chcp 65001 >nul
cd /d "%~dp0"

echo ==========================================
echo   Sincronizar DinoCards con GitHub
echo   Repo: dino-prueba
echo ==========================================
echo.

set "MSG=%~1"
if "%MSG%"=="" set "MSG=Sincronizacion del %DATE% a las %TIME%"

echo [1/3] Preparando cambios (git add -A)...
git add -A

echo [2/3] Guardando commit...
git commit -m "%MSG%"
if errorlevel 1 echo   ^(No habia cambios nuevos que guardar^)

echo [3/3] Subiendo a GitHub (git push)...
git push
if errorlevel 1 (
  echo.
  echo   *** ERROR al subir. Lee los mensajes de arriba. ***
  echo   *** Tus archivos locales NO se han tocado. ***
) else (
  echo.
  echo   *** LISTO: cambios subidos a GitHub correctamente. ***
)

echo.
pause
