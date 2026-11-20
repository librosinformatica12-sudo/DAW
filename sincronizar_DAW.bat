@echo off
cd /d "%~dp0"

echo === Sincronizando DAW con GitHub ===
echo.

echo Anadiendo cambios...
git add .

echo.
set /p COMMIT_MSG="Mensaje del commit (pulsa Enter para usar uno automatico): "
if "%COMMIT_MSG%"=="" (
    for /f "usebackq delims=" %%i in (`powershell -NoProfile -Command "Get-Date -Format 'yyyy-MM-dd HH:mm:ss'"`) do set FECHA=%%i
    set "COMMIT_MSG=Actualizacion %FECHA%"
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
echo Subiendo a daw/main...
git push daw main
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo El push ha fallado. Lee el mensaje de arriba.
    pause
    exit /b 1
)

echo.
echo Listo.
pause