@echo off
chcp 65001 >nul
title DinoCards - Chat de estudio
cd /d "C:\xampp\htdocs\ejer\dinocards"
echo ==========================================
echo   DINOCARDS - Abriendo el chat
echo ==========================================
echo Carpeta: %CD%
echo.
"D:\opencode\bin\opencode.exe"
pause
