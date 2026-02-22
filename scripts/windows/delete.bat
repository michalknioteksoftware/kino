@echo off
setlocal enabledelayedexpansion
if "%JWT_TOKEN%"=="" (
  echo JWT_TOKEN not set, generating via CLI...
  cd /d "%~dp0..\.."
  set "JWT_TMP=%CD%\jwt_temp.txt"
  docker compose exec -T app php bin/console app:jwt:generate > "!JWT_TMP!" 2>&1
  for /f "delims=" %%i in ('type "!JWT_TMP!"') do set "JWT_TOKEN=%%i"
  if exist "!JWT_TMP!" del "!JWT_TMP!"
  if "!JWT_TOKEN!"=="" (
    echo Failed to get token. Is Docker running? Start with: docker compose up -d
    exit /b 1
  )
)
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0delete.ps1" %*
