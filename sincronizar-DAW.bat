@echo off
chcp 65001 >nul
setlocal
cd /d "%~dp0.."

echo ==========================================
echo    SINCRONIZAR dino-prueba con GitHub
echo ==========================================
echo.

<<<<<<< HEAD:sincronizar-DAW.bat
for /f "delims=" %%t in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd_HHmm"') do set "TS=%%t"
set "REPORTE=dino-prueba\cambios\cambios_%TS%.txt"
=======
for /f "delims=" %%t in ('powershell -NoProfile -Command "(Get-Date).ToString(\"yyyy-MM-dd HH-mm\")"') do set "TS=%%t"
set "DT_FMT=%date% %time%"
for /f "delims=" %%t in ('powershell -NoProfile -Command "(Get-Date).ToString(\"yyyy-MM-dd HH:mm\")"') do set "MSG=Sincronizacion del %%t"
set "REPORTE=cambios-todo\informe_%TS%.txt"
>>>>>>> b7032b5d8e0c3c0bd80c1f6144c9ef5dd5aee06c:sincronizar-todo.bat

echo [1/4] Descargando cambios de GitHub...
git pull

echo [2/4] Preparando cambios locales...
git add dino-prueba

git diff --cached --quiet -- dino-prueba
if not errorlevel 1 goto sin_cambios

<<<<<<< HEAD:sincronizar-DAW.bat
echo.
set /p "DESC=Descripcion de los cambios  [Enter = automatica]: "
if not defined DESC set "DESC=Cambios en dino-prueba"

=======
>>>>>>> b7032b5d8e0c3c0bd80c1f6144c9ef5dd5aee06c:sincronizar-todo.bat
echo [3/4] Creando informe de cambios...
if not exist "dino-prueba\cambios" mkdir "dino-prueba\cambios"

echo ===== INFORME DE CAMBIOS - dino-prueba ===== >"%REPORTE%"
echo Fecha: %date% %time% >>"%REPORTE%"
echo Descripcion: %MSG% >>"%REPORTE%"
echo. >>"%REPORTE%"
echo --- Archivos afectados --- >>"%REPORTE%"
git status --short -- dino-prueba >>"%REPORTE%"
echo. >>"%REPORTE%"
echo --- Detalle de cambios - diff --- >>"%REPORTE%"
git diff --cached -- dino-prueba >>"%REPORTE%"

git add dino-prueba

echo [4/4] Subiendo cambios...
git commit -m "%MSG%"
git push

echo.
echo LISTO. Informe guardado en: %REPORTE%
goto fin

:sin_cambios
echo.
echo No hay cambios que subir. Todo esta actualizado.
:fin
echo.
pause
