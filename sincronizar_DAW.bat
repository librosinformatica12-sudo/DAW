@echo off
cd /d "%~dp0"

echo === Sincronizando DAW con GitHub ===
echo.

echo Anadiendo cambios...
git add .

rem Fecha y hora del momento del add
for /f "usebackq delims=" %%i in (`powershell -NoProfile -Command "Get-Date -Format 'dd/MM/yyyy HH:mm:ss'"`) do set "FECHA=%%i"

echo.
set "COMMIT_MSG="
set /p COMMIT_MSG="Mensaje del commit (pulsa Enter para usar la fecha y hora): "
if not defined COMMIT_MSG set "COMMIT_MSG=%FECHA%"

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
echo Subiendo a daw/main (el codigo local sobrescribe al remoto)...
git fetch daw
git push daw main --force-with-lease
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo El push ha fallado. Lee el mensaje de arriba.
    pause
    exit /b 1
)

echo.
echo Listo.
pause