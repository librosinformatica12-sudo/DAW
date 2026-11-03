@echo off
setlocal EnableDelayedExpansion
cd /d "%~dp0"

set "REMOTE=origin"
set "BRANCH=proyectos-clase"
set "DEST=main"
set "IDX=%TEMP%\proyectos-index-%RANDOM%%RANDOM%"
set "MSGFILE=%TEMP%\proyectos-msg-%RANDOM%%RANDOM%.txt"
set "PUSHLOG=%TEMP%\proyectos-push-%RANDOM%%RANDOM%.log"
set "EXITCODE=0"

echo ============================================
echo  Subida de DIV, portal_citas y SimForest
echo ============================================
echo.

call :check
if errorlevel 1 goto :fail

set "MSG=%~1"
if "!MSG!"=="" (
    set /p "MSG=Mensaje del commit (Enter = automatico): "
)
if "!MSG!"=="" set "MSG=Actualizacion %DATE% %TIME%"

echo.
echo Preparando archivos...
set "GIT_INDEX_FILE=%IDX%"
git read-tree --empty
if errorlevel 1 goto :gitfail

git add -- .gitignore DIV portal_citas SimForest
if errorlevel 1 goto :gitfail

git ls-files --cached | findstr /i /r /c:"\.env$" /c:"\.env\." /c:"\.key$" /c:"\.pem$" /c:"\.p12$" /c:"\.pfx$" /c:"AccesoDatos\.php$" >nul
if !ERRORLEVEL! EQU 0 (
    echo.
    echo [ERROR] Se han detectado archivos sensibles en la lista a subir:
    git ls-files --cached | findstr /i /r /c:"\.env$" /c:"\.env\." /c:"\.key$" /c:"\.pem$" /c:"\.p12$" /c:"\.pfx$" /c:"AccesoDatos\.php$"
    echo.
    echo Corrige .gitignore antes de volver a ejecutar este archivo.
    goto :gitfail
)

for /f "delims=" %%i in ('git write-tree') do set "TREE=%%i"
if not defined TREE goto :gitfail

set "PARENT="
for /f "delims=" %%i in ('git rev-parse --verify --quiet refs/heads/%BRANCH%') do set "PARENT=%%i"

if defined PARENT (
    for /f "delims=" %%i in ('git rev-parse --verify --quiet "%PARENT%^{tree}"') do set "PARENT_TREE=%%i"
    if "!TREE!"=="!PARENT_TREE!" (
        echo.
        echo [OK] No hay cambios en las 3 carpetas. Nada que subir.
        goto :cleanup
    )
    (echo(!MSG!)>"%MSGFILE%"
    for /f "delims=" %%i in ('git commit-tree %TREE% -p %PARENT% -F "%MSGFILE%"') do set "COMMIT=%%i"
) else (
    (echo(!MSG!)>"%MSGFILE%"
    for /f "delims=" %%i in ('git commit-tree %TREE% -F "%MSGFILE%"') do set "COMMIT=%%i"
)

if not defined COMMIT goto :gitfail

git update-ref refs/heads/%BRANCH% %COMMIT%
if errorlevel 1 goto :gitfail
set "GIT_INDEX_FILE="

echo.
echo Subiendo commit %COMMIT% a %REMOTE%/%DEST%...
git push %REMOTE% %BRANCH%:%DEST% > "%PUSHLOG%" 2>&1
if errorlevel 1 (
    echo.
    echo [ERROR] Fallo el push. Detalle:
    type "%PUSHLOG%"
    echo.

    findstr /i /c:"Authentication failed" /c:"Permission denied" /c:"could not read Username" /c:"Invalid username or password" "%PUSHLOG%" >nul
    if !ERRORLEVEL! EQU 0 (
        echo [AYUDA] Credenciales incorrectas o sesion caducada.
        echo         Ejecuta: git credential-manager configure
        echo         O revisa tu token de acceso personal de GitHub.
    )

    findstr /i /c:"rejected" /c:"non-fast-forward" /c:"fetch first" "%PUSHLOG%" >nul
    if !ERRORLEVEL! EQU 0 (
        echo [AYUDA] El remoto tiene commits nuevos que tu copia no tiene.
        echo         Ejecuta: git fetch %REMOTE%
        echo         Revisa los cambios antes de volver a subir.
    )

    findstr /i /c:"Could not resolve host" /c:"unable to access" /c:"Connection timed out" "%PUSHLOG%" >nul
    if !ERRORLEVEL! EQU 0 (
        echo [AYUDA] Problema de red o URL del remoto incorrecta.
        echo         Comprueba tu conexion y la URL con: git remote -v
    )

    goto :gitfail
)

echo.
echo [OK] Subida completada.
echo      Commit: %COMMIT%
echo      Rama remota: %REMOTE%/%DEST%
echo      Carpetas: DIV, portal_citas, SimForest
goto :cleanup

:check
where git >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Git no esta instalado o no esta en el PATH.
    echo         Instala Git desde https://git-scm.com/download/win
    exit /b 1
)

git rev-parse --is-inside-work-tree >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Esta carpeta no es un repositorio Git.
    exit /b 1
)

git remote get-url %REMOTE% >nul 2>&1
if errorlevel 1 (
    echo [ERROR] No existe el remoto "%REMOTE%".
    echo         Configuralo con: git remote add %REMOTE% https://github.com/USUARIO/REPO.git
    exit /b 1
)

for %%D in (DIV portal_citas SimForest) do (
    if not exist "%%D\" (
        echo [ERROR] No existe la carpeta %%D.
        exit /b 1
    )
)

if not exist ".gitignore" (
    echo [ERROR] No existe .gitignore. Se cancela para no subir archivos sensibles.
    exit /b 1
)

git check-ignore --no-index -q "portal_citas/AccesoDatos.php"
if errorlevel 1 (
    echo [ERROR] .gitignore no excluye portal_citas/AccesoDatos.php.
    echo         Anade esa linea antes de subir para no publicar credenciales.
    exit /b 1
)

exit /b 0

:gitfail
echo.
echo [ERROR] Fallo un comando de Git. Revisa el mensaje anterior.
set "EXITCODE=1"
goto :cleanup

:fail
set "EXITCODE=1"
goto :cleanup

:cleanup
set "GIT_INDEX_FILE="
del /q "%IDX%" >nul 2>&1
del /q "%MSGFILE%" >nul 2>&1
del /q "%PUSHLOG%" >nul 2>&1
echo.
pause
exit /b %EXITCODE%
