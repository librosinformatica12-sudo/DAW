@echo off
cd /d "%~dp0"

echo === Sincronizando DAW con GitHub ===
echo.

echo Añadiendo cambios...
git add .

echo.
set /p COMMIT_MSG="Mensaje del commit (pulsa Enter para usar uno automático): "
if "%COMMIT_MSG%"=="" (
    set COMMIT_MSG=Actualización %DATE% %TIME%
)

echo.
echo Creando commit...
git commit -m "%COMMIT_MSG%"
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo No hay cambios para commitear o ha ocurrido un error.
    pause
    exit /b
)

echo.
echo Subiendo a origin/main...
git push origin main

echo.
echo ¡Sincronización completada!
pause
