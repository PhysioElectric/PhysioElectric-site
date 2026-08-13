@echo off
cd /d "%~dp0lab"
where node >nul 2>nul
if errorlevel 1 (
  echo Node.js نصب نیست. از https://nodejs.org نسخه LTS را نصب کن.
  pause
  exit /b 1
)
if not exist node_modules call npm install
if not exist dist\index.html call npm run build
echo.
echo سایت:  http://localhost:8080
echo لاب:   http://localhost:8080/simulations.html
echo.
node server/index.js
pause
