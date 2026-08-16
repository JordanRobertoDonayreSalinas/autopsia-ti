@echo off
title Instalador de Escritorio - Autopsia TI
color 0A
echo ========================================================
echo   Instalando Autopsia TI como Aplicacion de Escritorio
echo ========================================================
echo.
set APP_URL=http://autopsia-ti.systemperu.digital/usuario/monitoreo
set SHORTCUT_PATH=%USERPROFILE%\Desktop\Autopsia TI.url

echo [InternetShortcut] > "%SHORTCUT_PATH%"
echo URL=%APP_URL% >> "%SHORTCUT_PATH%"
echo IconIndex=0 >> "%SHORTCUT_PATH%"
echo IconFile=%SystemRoot%\System32\shell32.dll >> "%SHORTCUT_PATH%"

echo.
echo [OK] Se creo el acceso directo "Autopsia TI" en tu Escritorio!
echo Puede abrir la aplicacion desde tu escritorio en cualquier momento.
echo.
ping 127.0.0.1 -n 4 >nul
exit
