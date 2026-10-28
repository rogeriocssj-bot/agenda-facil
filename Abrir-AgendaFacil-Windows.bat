@echo off
chcp 65001 >nul
title AgendaFacil
REM ==============================================================
REM  AgendaFacil - abrir no Windows (modo local, sem XAMPP e sem MySQL)
REM  Na primeira vez baixa o PHP oficial (php.net) para a pasta php-portable.
REM  Para PARAR o sistema, feche esta janela.
REM ==============================================================
cd /d "%~dp0"

set "PHP="
set "EXTRA="
set "PORTATIL=%CD%\php-portable"

if exist "C:\xampp\php\php.exe" set "PHP=C:\xampp\php\php.exe"
if not defined PHP if exist "%PORTATIL%\php.exe" set "PHP=%PORTATIL%\php.exe"
if not defined PHP (
  where php >nul 2>nul && set "PHP=php"
)

if not defined PHP (
  echo.
  echo Primeira vez: baixando o PHP do site oficial ^(php.net^), cerca de 30 MB...
  echo Isso acontece so uma vez. Aguarde.
  echo.
  powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference='Stop'; [Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12; $urls=@('https://windows.php.net/downloads/releases/latest/php-8.3-nts-Win32-vs16-x64-latest.zip','https://www.php.net/~windows/releases/latest/php-8.3-nts-Win32-vs16-x64-latest.zip'); $ok=$false; foreach($u in $urls){ try { Invoke-WebRequest -UseBasicParsing -Uri $u -OutFile 'php.zip'; $ok=$true; break } catch {} }; if(-not $ok){ exit 1 }; Expand-Archive -Path 'php.zip' -DestinationPath 'php-portable' -Force; Remove-Item 'php.zip'"
  if not exist "%PORTATIL%\php.exe" (
    echo.
    echo Nao foi possivel baixar o PHP. Verifique a internet e abra este arquivo de novo.
    pause
    exit /b 1
  )
  set "PHP=%PORTATIL%\php.exe"
)

REM PHP portatil: indica onde estao as extensoes e liga as que o sistema usa
if /i "%PHP%"=="%PORTATIL%\php.exe" set "EXTRA=-d extension_dir="%PORTATIL%\ext" -d extension=pdo_sqlite -d extension=sqlite3 -d extension=mbstring"

REM O PHP para Windows precisa do Visual C++ (a maioria dos PCs ja tem)
"%PHP%" -v >nul 2>nul
if errorlevel 1 (
  echo.
  echo Falta um componente da Microsoft ^(Visual C++^). Instalando...
  echo Se o Windows perguntar se permite alteracoes, clique em SIM.
  powershell -NoProfile -ExecutionPolicy Bypass -Command "[Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -UseBasicParsing -Uri 'https://aka.ms/vs/17/release/vc_redist.x64.exe' -OutFile 'vc_redist.x64.exe'; Start-Process -FilePath '.\vc_redist.x64.exe' -ArgumentList '/install','/quiet','/norestart' -Verb RunAs -Wait; Remove-Item '.\vc_redist.x64.exe'"
)

REM XAMPP ou PHP instalado: liga o SQLite se estiver desligado
if not defined EXTRA (
  "%PHP%" -m | findstr /i "pdo_sqlite" >nul || set "EXTRA=-d extension=pdo_sqlite -d extension=sqlite3"
)

set "DB_DSN=sqlite:%CD%\database\agendafacil-local.sqlite"
set "APP_DEBUG=0"
cls
echo ============================================================
echo   AgendaFacil rodando em: http://localhost:8000
echo.
echo   Administrador: admin@agendafacil.com   / admin123
echo   Cliente:       cliente@agendafacil.com / cliente123
echo.
echo   Para PARAR o sistema, feche esta janela.
echo ============================================================
start "" http://localhost:8000
"%PHP%" %EXTRA% -S localhost:8000 -t public
pause
