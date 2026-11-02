@echo off
cd /d "%~dp0"

echo === Sincronizando DAW con GitHub (Modo Forzado) ===
echo.

echo Añadiendo cambios...
git add .

echo.
set /p COMMIT_MSG="Mensaje del commit (pulsa Enter para usar uno automatico): "
if "%COMMIT_MSG%"=="" (
    set COMMIT_MSG=Actualizacion %DATE% %TIME%
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
echo Forzando subida a origin/main (sobrescribiendo remoto)...
git push origin main --force

echo.
echo ¡Ajuste completado!
pause