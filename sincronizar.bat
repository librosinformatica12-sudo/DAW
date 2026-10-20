@echo off
chcp 65001 >nul
setlocal EnableExtensions
cd /d "%~dp0"

title Sincronizar REPO DAW con GitHub

echo ============================================
echo   SINCRONIZAR REPO DAW con GitHub
echo   Carpeta: %~dp0
echo ============================================
echo.

rem --- Comprobacion 1: esta instalado git? ---
where git >nul 2>&1
if errorlevel 1 (
    echo ERROR: no se encuentra "git" en el sistema.
    echo        Instala Git for Windows desde https://git-scm.com/download/win
    goto fin
)

rem --- Comprobacion 2: estamos dentro de un repo git? ---
git rev-parse --is-inside-work-tree >nul 2>&1
if errorlevel 1 (
    echo ERROR: esta carpeta NO es un repositorio git.
    echo        Este archivo tiene que estar dentro de la raiz del repo.
    goto fin
)

rem --- Mensaje del commit (fecha y hora actuales) ---
set "MSG="
for /f "delims=" %%t in ('powershell -NoProfile -Command "Get-Date -Format 'yyyy-MM-dd HH:mm'"') do set "MSG=Sincronizacion DAW %%t"
if "%MSG%"=="" set "MSG=Sincronizacion DAW"

echo [1/4] Preparando cambios (git add -A)...
git add -A
if errorlevel 1 goto error

git diff --cached --quiet >nul 2>&1
if not errorlevel 1 goto sin_cambios

echo [2/4] Guardando cambios en un commit...
git commit -m "%MSG%"
if errorlevel 1 goto error
goto descargar

:sin_cambios
echo       No hay cambios locales que guardar.
goto descargar

:descargar
echo [3/4] Descargando cambios de GitHub (git pull)...
git pull --no-rebase --no-edit
if errorlevel 1 goto conflicto

echo [4/4] Subiendo cambios a GitHub (git push)...
git push
if errorlevel 1 goto error

echo.
echo ============================================
echo   LISTO: repo DAW sincronizado con GitHub
echo ============================================
goto fin

:conflicto
echo.
echo AVISO: hay conflictos al descargar los cambios de GitHub.
echo.
echo Pasos para resolverlos:
echo   1. Abre los archivos marcados con "CONFLICT" (git status).
echo   2. Elige que version quedarse y guarda el archivo.
echo   3. Ejecuta:  git add nombre-del-archivo
echo   4. Ejecuta:  git commit -m "conflicto resuelto"
echo   5. Vuelve a ejecutar este sincronizar.bat
echo.
git status --short
goto fin

:error
echo.
echo ERROR: la operacion no se ha completado.
echo        Revisa el mensaje de git de arriba.
echo        Tus archivos locales NO se han tocado.
goto fin

:fin
echo.
pause
