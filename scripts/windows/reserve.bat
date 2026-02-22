@echo off
REM Reserve seats (no JWT). Usage: reserve.bat <roomId> <reservedByName> <row,col> [row,col ...]
REM Example: reserve.bat 1 "John Doe" 1,2 1,3
REM Or set BODY (with reservedByName) and run: reserve.bat
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0reserve.ps1" %*
