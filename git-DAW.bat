@echo off
chcp 65001 >nul
cd /d "%~dp0"

echo === Sincronizando DAW con GitHub ===
echo.

REM --- 1. Comprobar que no hay marcadores de conflicto sin resolver ---
git grep -n -E "^(<<<<<<< |>>>>>>> )" >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo ERROR: quedan conflictos sin resolver en estos archivos:
    echo.
    git grep -n -E "^(<<<<<<< |>>>>>>> )"
    echo.
    echo Abrelos, borra las lineas con <<<<<<<, ======= y >>>>>>>,
    echo deja el codigo que quieras y vuelve a ejecutar este script.
    pause
    exit /b 1
)

REM --- 2. Mensaje automatico con fecha y hora ---
for /f "usebackq delims=" %%i in (`powershell -NoProfile -Command "Get-Date -Format 'yyyy-MM-dd HH:mm:ss'"`) do set FECHA=%%i
set COMMIT_MSG=Actualizacion %FECHA%

REM --- 3. Commit (si no hay cambios, sigue igualmente) ---
echo Añadiendo cambios...
git add .

echo Creando commit: %COMMIT_MSG%
git commit -m "%COMMIT_MSG%"
if %ERRORLEVEL% NEQ 0 (
    echo No hay cambios nuevos que commitear, sigo con la sincronizacion...
)

REM --- 4. Traer lo del remoto y mezclarlo ---
echo.
echo Trayendo cambios de daw/main...
git pull daw main --no-rebase --no-edit
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ================================================
    echo  HAY CONFLICTOS al mezclar. NO se ha subido nada.
    echo  1. Abre los archivos marcados ^(git status^)
    echo  2. Resuelve los conflictos y quita los marcadores
    echo  3. Vuelve a ejecutar este script
    echo ================================================
    echo.
    git status --short
    pause
    exit /b 1
)

REM --- 5. Subir sin force ---
echo.
echo Subiendo a daw/main...
git push daw main
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo El push ha fallado. Vuelve a ejecutar el script.
    pause
    exit /b 1
)

echo.
echo Listo, todo sincronizado.
pause