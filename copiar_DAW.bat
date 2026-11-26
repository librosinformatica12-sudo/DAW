@echo off
cd /d "%~dp0"

echo === Copiar cambios a DAW (GitHub) ===
echo.

git rev-parse --is-inside-work-tree >nul 2>nul
if errorlevel 1 (
    echo ERROR: esta carpeta no es un repositorio Git.
    pause
    exit /b 1
)

for /f "usebackq delims=" %%B in (`git rev-parse --abbrev-ref HEAD`) do set "RAMA=%%B"
if /i not "%RAMA%"=="main" (
    echo ERROR: estas en la rama "%RAMA%". Cambia a main antes de copiar.
    pause
    exit /b 1
)

if exist ".git\MERGE_HEAD" goto :ocupado
if exist ".git\rebase-merge" goto :ocupado
if exist ".git\rebase-apply" goto :ocupado

for /f "usebackq delims=" %%D in (`powershell -NoProfile -Command "Get-Date -Format 'dd/MM/yyyy HH:mm:ss'"`) do set "FECHA=%%D"

echo Descargando cambios de GitHub...
git fetch daw --prune
if errorlevel 1 goto :fallo

set "HAY=0"
for /f "delims=" %%A in ('git status --porcelain') do set "HAY=1"
if "%HAY%"=="1" goto :hay_cambios
echo No hay cambios locales nuevos; no se crea commit.
goto :despues_commit

:hay_cambios
echo Guardando tus cambios locales...
git add -A
if errorlevel 1 goto :fallo
git commit -m "%FECHA%"
if errorlevel 1 goto :fallo

:despues_commit
set "INTENTOS=0"

:bucle
echo Uniendo cambios de GitHub (en conflicto gana tu version)...
git merge --no-ff -X ours daw/main -m "Sincronizacion con daw/main %FECHA%"
if errorlevel 1 goto :fallo

set "CONF=0"
for /f "delims=" %%U in ('git diff --name-only --diff-filter=U') do set "CONF=1"
if "%CONF%"=="1" goto :conflictos
goto :subir

:conflictos
echo Quedan conflictos sin resolver en:
git diff --name-only --diff-filter=U
goto :fallo

:subir
echo Subiendo a daw/main...
git push daw main
if errorlevel 1 goto :reintentar
goto :listo

:reintentar
set /a INTENTOS+=1
if %INTENTOS% GEQ 3 goto :fallo
echo El remoto cambio; se sincroniza de nuevo...
git fetch daw --prune
if errorlevel 1 goto :fallo
goto :bucle

:ocupado
echo ERROR: hay una fusion o rebase sin terminar. Terminalo antes de copiar.
pause
exit /b 1

:listo
echo.
echo Listo: tus cambios estan en GitHub y los de GitHub estan aqui.
pause
exit /b 0

:fallo
echo.
echo No se ha terminado. Revisa el error de arriba.
echo Tus archivos siguen en la carpeta y no se ha hecho push.
pause
exit /b 1
